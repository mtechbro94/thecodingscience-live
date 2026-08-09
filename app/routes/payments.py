"""Payment routes: enroll, Razorpay checkout, UPI fallback, verification."""

import hashlib
import hmac
import time
from urllib.parse import quote

from flask import (
    Blueprint,
    jsonify,
    redirect,
    render_template,
    request,
    session,
)

from config import Config
from app.auth import login_required
from app.db import db
from app.helpers import (
    current_user,
    generate_csrf_token,
    get_setting,
    is_logged_in,
    sanitize,
    set_flash,
    use_coupon,
    validate_coupon,
    validate_csrf_token,
)

bp = Blueprint("payments", __name__)

UPI_ID = "u.e1@ybl"
UPI_PAYEE = "The Coding Science"

COMBOS = {
    "Programming Starter Pack": {
        "courses": ["Crash Course in Computer Science", "Programming with Python"],
        "original_price": 6998,
        "price": 4499,
    },
    "Developer Career Pack": {
        "courses": ["Programming with Python", "Full Stack Web Development"],
        "original_price": 11998,
        "price": 7999,
    },
    "AI and Data Science Career Track": {
        "courses": [
            "Programming with Python",
            "Data Science from Scratch",
            "Machine Learning and AI Foundations",
        ],
        "original_price": 18997,
        "price": 11999,
    },
}


def _fetch_enrollment(enrollment_id, user_id):
    """Fetch enrollment with course details (LEFT JOIN, NULL for tracks/combos)."""
    return db().fetch_one(
        "SELECT e.*, c.name as course_name, c.price as course_price "
        "FROM enrollments e "
        "LEFT JOIN courses c ON e.course_id = c.id "
        "WHERE e.id = %s AND e.user_id = %s",
        (enrollment_id, user_id),
    )


def _course_name_for_display(enrollment):
    """Tracks/combos have NULL course_id -> fall back to bundle label."""
    if not enrollment.get("course_name"):
        enrollment["course_name"] = "Career Track Bundle"
        enrollment["course_price"] = enrollment["amount_paid"]
    return enrollment


def _resolve_course(course_id, course_name, track_slug, combo_name):
    """Mirror views/enroll.php course resolution (id -> name -> track -> combo)."""
    course = None

    if course_id and course_id > 0:
        course = db().fetch_one("SELECT * FROM courses WHERE id = %s", (course_id,))
    elif course_name:
        course = db().fetch_one(
            "SELECT * FROM courses WHERE name LIKE %s", (f"%{course_name}%",)
        )
        if course:
            course_id = course["id"]

    if not course and track_slug:
        track = db().fetch_one(
            "SELECT * FROM career_tracks WHERE slug = %s AND is_active = 1", (track_slug,)
        )
        if track:
            track_courses = db().fetch_all(
                "SELECT c.* FROM courses c "
                "JOIN career_track_courses cc ON c.id = cc.course_id "
                "WHERE cc.track_id = %s ORDER BY cc.sort_order",
                (track["id"],),
            )
            course_names = [c["name"] for c in track_courses]
            course = {
                "id": 0,
                "name": track["name"],
                "description": "Career Track: " + " + ".join(course_names),
                "price": track["price"],
                "original_price": track["original_price"],
                "image": track["image"],
                "duration": track["duration"],
                "level": "Career Track",
                "is_track": 1,
                "track_id": track["id"],
                "track_courses": track_courses,
            }

    if not course and combo_name:
        combo = COMBOS.get(combo_name)
        if combo:
            course = {
                "id": 0,
                "name": combo_name,
                "description": "Combo Program: " + " + ".join(combo["courses"]),
                "price": combo["price"],
                "original_price": combo["original_price"],
                "is_combo": True,
                "included_courses": combo["courses"],
            }

    return course, course_id


@bp.route("/enroll", methods=["GET", "POST"])
@bp.route("/enroll/<int:course_id>", methods=["GET", "POST"])
def enroll(course_id=0):
    if not is_logged_in():
        redirect_to = f"/course/{course_id}" if course_id else "/courses"
        set_flash("info", "Please login to enroll in a course.")
        return redirect("/login?redirect=" + quote(redirect_to))

    user = current_user()

    course_name = request.args.get("course", "")
    track_slug = request.args.get("track", "")
    combo_name = request.args.get("combo", "")

    course, course_id = _resolve_course(course_id, course_name, track_slug, combo_name)

    if not course:
        set_flash("danger", "Course not found.")
        return redirect("/courses")

    original_price = course["price"]
    final_price = course["price"]
    coupon_applied = None
    discount_amount = 0

    # Coupon application
    if request.method == "POST" and request.form.get("apply_coupon"):
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request. Please try again.")
            return redirect(request.full_path)

        coupon_code = request.form.get("coupon_code", "")
        if coupon_code:
            result = validate_coupon(coupon_code, original_price)
            if result["success"]:
                final_price = result["final_amount"]
                discount_amount = result["discount"]
                coupon_applied = result["coupon"]
                set_flash("success", f"Coupon applied! You saved ₹{discount_amount:,.2f}")
            else:
                set_flash("danger", result["message"])

    # Existing enrollment check (real courses only)
    if course_id and not course.get("is_combo") and not course.get("is_track"):
        existing = db().fetch_one(
            "SELECT * FROM enrollments WHERE user_id = %s AND course_id = %s",
            (user["id"], course_id),
        )
        if existing:
            if existing["status"] == "completed":
                set_flash("info", "You are already enrolled in this course.")
            else:
                set_flash("warning", "You have a pending enrollment for this course.")
            return redirect("/dashboard")

    # Enrollment submission
    if request.method == "POST" and request.form.get("enroll_now"):
        if not validate_csrf_token(request.form.get("csrf_token", "")):
            set_flash("danger", "Invalid request. Please try again.")
            return redirect(request.full_path)

        payment_method = request.form.get("payment_method", "upi")
        try:
            amount_to_pay = float(request.form.get("final_amount", final_price))
        except (TypeError, ValueError):
            amount_to_pay = final_price
        applied_coupon = request.form.get("applied_coupon", "")

        try:
            if course_id and not course.get("is_combo") and not course.get("is_track"):
                enrollment_id = db().insert(
                    "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) "
                    "VALUES (%s, %s, 'pending', %s, %s, NOW())",
                    (user["id"], course_id, payment_method, amount_to_pay),
                )
            else:
                included_rows = []
                if course.get("is_track"):
                    included_rows = course["track_courses"]
                elif course.get("is_combo"):
                    for name in course["included_courses"]:
                        found = db().fetch_one(
                            "SELECT id FROM courses WHERE name LIKE %s", (f"%{name}%",)
                        )
                        if found:
                            included_rows.append(found)

                for row in included_rows:
                    check = db().fetch_one(
                        "SELECT id FROM enrollments WHERE user_id = %s AND course_id = %s",
                        (user["id"], row["id"]),
                    )
                    if not check:
                        db().execute(
                            "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) "
                            "VALUES (%s, %s, 'pending', %s, 0, NOW())",
                            (user["id"], row["id"], payment_method),
                        )

                enrollment_id = db().insert(
                    "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) "
                    "VALUES (%s, NULL, 'pending', %s, %s, NOW())",
                    (user["id"], payment_method, amount_to_pay),
                )

            if applied_coupon:
                use_coupon(applied_coupon)

            set_flash("success", "Enrollment initiated! Please complete your payment.")
            return redirect(f"/razorpay-payment/{enrollment_id}?from=enroll")
        except Exception as exc:
            print(f"Enrollment failed: {exc}")
            set_flash(
                "danger", "Enrollment failed. Please try again or contact support if the issue continues."
            )

    return render_template(
        "payments/enroll.html",
        course=course,
        course_id=course_id,
        original_price=original_price,
        final_price=final_price,
        coupon_applied=coupon_applied,
        discount_amount=discount_amount,
        page_title="Enroll - " + course["name"],
    )


@bp.route("/razorpay-payment/<int:enrollment_id>")
@login_required
def razorpay_payment(enrollment_id):
    user = current_user()

    if enrollment_id == 0:
        return redirect("/dashboard")

    enrollment = _fetch_enrollment(enrollment_id, user["id"])
    if not enrollment:
        set_flash("danger", "Enrollment not found.")
        return redirect("/dashboard")

    _course_name_for_display(enrollment)

    if enrollment["status"] == "completed":
        set_flash("info", "You are already enrolled in this course.")
        return redirect("/dashboard")

    receipt_id = f"TCS_{enrollment_id}_{int(time.time())}"
    amount_in_paise = int(enrollment["course_price"] * 100)

    # Store receipt ID for verification later
    db().execute(
        "UPDATE enrollments SET razorpay_order_id = %s WHERE id = %s",
        (receipt_id, enrollment_id),
    )

    logo = get_setting("site_logo", "")
    logo_url = f"/assets/images/{logo}" if logo else "/assets/images/logo.jpeg"

    return render_template(
        "payments/razorpay_payment.html",
        enrollment=enrollment,
        enrollment_id=enrollment_id,
        receipt_id=receipt_id,
        amount_in_paise=amount_in_paise,
        logo_url=logo_url,
        page_title="Complete Payment",
    )


def _razorpay_verify(enrollment_id, payment_id, order_id, signature, user_id, mark_failed=False):
    """Shared signature verification for verify-payment and payment-callback."""
    enrollment = db().fetch_one(
        "SELECT * FROM enrollments WHERE id = %s AND user_id = %s",
        (enrollment_id, user_id),
    )
    if not enrollment:
        return {"status": "error", "message": "Enrollment not found"}

    if not order_id or not signature:
        return {"status": "error", "message": "Missing required parameters"}

    expected = hmac.new(
        Config.RAZORPAY_KEY_SECRET.encode(),
        f"{order_id}|{payment_id}".encode(),
        hashlib.sha256,
    ).hexdigest()

    if signature != expected:
        print(f"Razorpay signature mismatch for enrollment {enrollment_id}")
        if mark_failed:
            db().execute(
                "UPDATE enrollments SET status = 'failed', razorpay_payment_id = %s, razorpay_signature = %s "
                "WHERE id = %s",
                (payment_id, signature, enrollment_id),
            )
        return {"status": "error", "message": "Payment verification failed. Signature mismatch. Please contact support."}

    db().execute(
        "UPDATE enrollments SET "
        "status = 'completed', razorpay_payment_id = %s, razorpay_signature = %s, "
        "payment_method = 'razorpay', verified_at = NOW() "
        "WHERE id = %s",
        (payment_id, signature, enrollment_id),
    )
    return {"status": "success", "message": "Payment verified successfully"}


@bp.route("/verify-payment", methods=["POST"])
def verify_payment():
    if not is_logged_in():
        return jsonify({"status": "error", "message": "Please login first"})

    user = current_user()

    enrollment_id = request.form.get("enrollment_id", 0)
    payment_id = request.form.get("razorpay_payment_id", "")
    order_id = request.form.get("razorpay_order_id", "")
    signature = request.form.get("razorpay_signature", "")

    if not enrollment_id or not payment_id:
        return jsonify({"status": "error", "message": "Missing payment details"})

    result = _razorpay_verify(enrollment_id, payment_id, order_id, signature, user["id"])
    return jsonify(result)


@bp.route("/payment-callback", methods=["POST"])
def payment_callback():
    if not is_logged_in():
        return jsonify({"status": "error", "message": "Unauthorized"}), 401

    user = current_user()

    enrollment_id = request.form.get("enrollment_id", 0)
    order_id = request.form.get("razorpay_order_id", "")
    payment_id = request.form.get("razorpay_payment_id", "")
    signature = request.form.get("razorpay_signature", "")

    if not enrollment_id or not order_id or not payment_id:
        return jsonify({"status": "error", "message": "Missing required parameters"})

    result = _razorpay_verify(enrollment_id, payment_id, order_id, signature, user["id"], mark_failed=True)
    return jsonify(result)


@bp.route("/submit-payment/<int:enrollment_id>", methods=["GET", "POST"])
@login_required
def submit_payment(enrollment_id):
    user = current_user()

    if enrollment_id == 0:
        return redirect("/dashboard")

    enrollment = _fetch_enrollment(enrollment_id, user["id"])
    if not enrollment:
        set_flash("danger", "Enrollment not found.")
        return redirect("/dashboard")

    _course_name_for_display(enrollment)

    if enrollment["status"] == "completed":
        set_flash("info", "Your payment is already verified.")
        return redirect("/dashboard")

    amount = enrollment["course_price"]
    note = "Course: " + enrollment["course_name"]

    upi_link = (
        "upi://pay?pa=" + UPI_ID + "&pn=" + quote(UPI_PAYEE)
        + "&am=" + str(amount) + "&tn=" + quote(note)
    )
    qr_code_url = (
        "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" + quote(upi_link, safe="")
    )

    error = None
    if request.method == "POST":
        utr = sanitize(request.form.get("utr", ""))

        if not utr:
            error = "Transaction ID (UTR) is required."
        else:
            try:
                db().execute(
                    "UPDATE enrollments SET utr = %s, status = 'pending' WHERE id = %s",
                    (utr, enrollment_id),
                )
                set_flash("success", "Payment details submitted successfully! Our team will verify it soon.")
                return redirect("/dashboard")
            except Exception as exc:
                print(f"Payment submission failed: {exc}")
                error = "Failed to process payment. Please try again or contact support."

    return render_template(
        "payments/submit_payment.html",
        enrollment=enrollment,
        enrollment_id=enrollment_id,
        upi_id=UPI_ID,
        amount=amount,
        qr_code_url=qr_code_url,
        error=error,
        page_title="Submit Payment Details",
    )


@bp.route("/payment-success")
@login_required
def payment_success():
    user = current_user()
    enrollment_id = request.args.get("enrollment_id", 0)

    if not enrollment_id:
        return redirect("/dashboard")

    enrollment = db().fetch_one(
        "SELECT e.*, c.name as course_name "
        "FROM enrollments e JOIN courses c ON e.course_id = c.id "
        "WHERE e.id = %s AND e.user_id = %s",
        (enrollment_id, user["id"]),
    )

    return render_template(
        "payments/payment_success.html",
        enrollment=enrollment,
        page_title="Payment Successful",
    )


@bp.route("/payment-failed")
@login_required
def payment_failed():
    user = current_user()
    enrollment_id = request.args.get("enrollment_id", 0)
    error = request.args.get("error", "Payment was not completed")

    if enrollment_id:
        db().execute(
            "UPDATE enrollments SET status = 'failed' WHERE id = %s AND user_id = %s",
            (enrollment_id, user["id"]),
        )

    return render_template(
        "payments/payment_failed.html",
        error=error,
        page_title="Payment Failed",
    )


@bp.route("/apply/internship/<int:internship_id>")
def apply_internship(internship_id):
    if not internship_id:
        return redirect("/")

    if not is_logged_in():
        set_flash("info", "Please login to your account to apply for this internship.")
        return redirect("/login?redirect=" + quote(f"/apply/internship/{internship_id}"))

    from app.helpers import get_internship

    item = get_internship(internship_id)
    link = item.get("google_form_link", "") if item else ""

    if not link:
        set_flash("danger", "Application link not found or position is no longer active.")
        return redirect("/internships")

    return redirect(link)
