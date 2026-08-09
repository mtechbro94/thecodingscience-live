"""Tests for payment flows: enroll, Razorpay checkout, UPI fallback, apply."""

import hmac
import hashlib

from config import Config


def _login(client):
    with client.session_transaction() as sess:
        sess["user_id"] = 1
        sess["user_role"] = "student"
        sess["user_name"] = "Test Student"
        sess["user_email"] = "student@example.com"


COURSE_ROW = {
    "id": 3, "name": "Python Mastery", "price": 4499, "original_price": 5999,
    "summary": "Learn Python", "description": "Full course", "image": "python.jpg",
}


def _course_router(sql, params=None):
    if "FROM enrollments" in sql:
        return None
    return COURSE_ROW


def test_enroll_requires_login(client, fakedb):
    rv = client.get("/enroll/3")
    assert rv.status_code == 302
    assert "/login" in rv.headers.get("Location")


def test_enroll_page_renders(client, fakedb, app):
    fakedb._one_router = _course_router
    _login(client)
    rv = client.get("/enroll/3")
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "Python Mastery" in body
    assert "Proceed to Payment" in body


def test_enroll_track_renders(client, fakedb):
    fakedb._one_router = lambda sql, params=None: None
    fakedb._all = [{"id": 1, "name": "Python"}, {"id": 2, "name": "AI"}]
    _login(client)

    # career_tracks lookup returns a track; enrollments check returns None
    def router(sql, params=None):
        if "FROM career_tracks" in sql:
            return {
                "id": 1, "name": "AI Track", "slug": "ai-track", "price": 11999,
                "original_price": 18999, "image": "ai.jpg", "duration": "6 months",
            }
        return None

    fakedb._one_router = router
    rv = client.get("/enroll?track=ai-track")
    assert rv.status_code == 200
    assert "Career Track" in rv.get_data(as_text=True)


def test_enroll_now_creates_enrollment(client, fakedb):
    fakedb._one_router = _course_router
    fakedb._insert = 42
    _login(client)

    client.get("/enroll/3")
    with client.session_transaction() as sess:
        token = sess.get("csrf_token")

    rv = client.post(
        "/enroll/3",
        data={
            "csrf_token": token,
            "enroll_now": "1",
            "final_amount": "4499",
            "applied_coupon": "",
        },
    )
    assert rv.status_code == 302
    assert rv.headers.get("Location") == "/razorpay-payment/42?from=enroll"
    inserts = [q for q in fakedb.queries if q[0] == "insert"]
    assert len(inserts) == 1


def test_enroll_now_track_accepts_post(client, fakedb):
    """Track bundle enrollment must accept POST on /enroll (regression for 405)."""
    from decimal import Decimal

    track = {
        "id": 1, "name": "AI Track", "slug": "ai-track", "price": Decimal("11999.00"),
        "original_price": Decimal("18999.00"), "image": None, "duration": "6 months",
        "outcomes": "Learn AI", "requirements": "Python", "curriculum": None,
    }

    def router(sql, params=None):
        if "FROM career_tracks" in sql:
            return track
        if "FROM courses" in sql and "JOIN career_track_courses" in sql:
            return []
        if "FROM enrollments" in sql:
            return None
        return None

    fakedb._one_router = router
    fakedb._all = []
    fakedb._insert = 55
    _login(client)

    client.get("/enroll?track=ai-track")
    with client.session_transaction() as sess:
        token = sess.get("csrf_token")

    rv = client.post(
        "/enroll?track=ai-track",
        data={"csrf_token": token, "enroll_now": "1", "final_amount": "11999"},
    )
    assert rv.status_code == 302
    assert rv.headers.get("Location") == "/razorpay-payment/55?from=enroll"


def test_coupon_percentage_decimal_amount(client, fakedb):
    """Percentage coupon must work when total_amount is a Decimal (regression)."""
    from decimal import Decimal

    course_row = dict(COURSE_ROW)
    course_row["price"] = Decimal("4499.00")

    def router(sql, params=None):
        if "FROM coupons" in sql:
            return {
                "id": 1, "code": "SAVE20", "discount_type": "percentage",
                "discount_value": Decimal("20.00"), "min_purchase": Decimal("0.00"),
                "max_uses": 100, "used_count": 0, "valid_from": None, "valid_until": None,
            }
        if "FROM enrollments" in sql:
            return None
        return course_row

    fakedb._one_router = router
    _login(client)

    client.get("/enroll/3")
    with client.session_transaction() as sess:
        token = sess.get("csrf_token")

    rv = client.post(
        "/enroll/3",
        data={"csrf_token": token, "apply_coupon": "1", "coupon_code": "SAVE20"},
    )
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "SAVE20" in body


def test_enroll_now_requires_csrf(client, fakedb):
    _login(client)
    rv = client.post("/enroll/3", data={"csrf_token": "", "enroll_now": "1", "final_amount": "100"})
    assert rv.status_code == 302


def test_razorpay_page_renders(client, fakedb):
    fakedb._one = {
        "id": 5, "user_id": 1, "course_id": 3, "status": "pending", "amount_paid": 4499.0,
        "course_name": "Python Mastery", "course_price": 4499.0,
    }
    _login(client)
    rv = client.get("/razorpay-payment/5")
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "Complete Payment" in body
    assert "checkout.razorpay.com" in body


def test_razorpay_completed_redirects(client, fakedb):
    fakedb._one = {
        "id": 5, "user_id": 1, "course_id": 3, "status": "completed", "amount_paid": 4499.0,
        "course_name": "Python Mastery", "course_price": 4499.0,
    }
    _login(client)
    rv = client.get("/razorpay-payment/5")
    assert rv.status_code == 302
    assert "/dashboard" in rv.headers.get("Location")


def test_verify_payment_signature_mismatch(client, fakedb):
    fakedb._one = {"id": 5, "user_id": 1, "course_id": 3, "status": "pending", "amount_paid": 4499.0}
    _login(client)
    rv = client.post(
        "/verify-payment",
        data={
            "enrollment_id": "5",
            "razorpay_payment_id": "pay_x",
            "razorpay_order_id": "TCS_5_1",
            "razorpay_signature": "bad-signature",
        },
    )
    data = rv.get_json()
    assert data["status"] == "error"


def test_verify_payment_success(client, fakedb):
    fakedb._one = {"id": 5, "user_id": 1, "course_id": 3, "status": "pending", "amount_paid": 4499.0}
    _login(client)
    signature = hmac.new(
        Config.RAZORPAY_KEY_SECRET.encode(),
        b"TCS_5_1|pay_ok",
        hashlib.sha256,
    ).hexdigest()
    rv = client.post(
        "/verify-payment",
        data={
            "enrollment_id": "5",
            "razorpay_payment_id": "pay_ok",
            "razorpay_order_id": "TCS_5_1",
            "razorpay_signature": signature,
        },
    )
    data = rv.get_json()
    assert data["status"] == "success"
    updates = [q for q in fakedb.queries if q[0] == "execute" and "status = 'completed'" in q[1]]
    assert len(updates) == 1


def test_verify_payment_requires_login(client, fakedb):
    rv = client.post("/verify-payment", data={})
    assert rv.get_json()["status"] == "error"


def test_submit_payment_renders(client, fakedb):
    fakedb._one = {
        "id": 5, "user_id": 1, "course_id": 3, "status": "pending", "amount_paid": 4499.0,
        "course_name": "Python Mastery", "course_price": 4499.0,
    }
    _login(client)
    rv = client.get("/submit-payment/5")
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "Pay via UPI" in body
    assert "qrserver.com" in body


def test_submit_payment_utr(client, fakedb):
    fakedb._one = {
        "id": 5, "user_id": 1, "course_id": 3, "status": "pending", "amount_paid": 4499.0,
        "course_name": "Python Mastery", "course_price": 4499.0,
    }
    _login(client)
    rv = client.post("/submit-payment/5", data={"utr": "123456789012"})
    assert rv.status_code == 302
    assert "/dashboard" in rv.headers.get("Location")
    updates = [q for q in fakedb.queries if q[0] == "execute" and "utr = %s" in q[1]]
    assert len(updates) == 1


def test_payment_success_renders(client, fakedb):
    fakedb._one = {
        "id": 5, "user_id": 1, "course_id": 3, "status": "completed", "amount_paid": 4499.0,
        "course_name": "Python Mastery", "razorpay_payment_id": "pay_123",
    }
    _login(client)
    rv = client.get("/payment-success?enrollment_id=5")
    assert rv.status_code == 200
    assert "Payment Successful" in rv.get_data(as_text=True)


def test_payment_failed_renders(client, fakedb):
    _login(client)
    rv = client.get("/payment-failed?enrollment_id=5&error=Card+declined")
    assert rv.status_code == 200
    body = rv.get_data(as_text=True)
    assert "Payment Failed" in body
    assert "Card declined" in body


def test_apply_internship_requires_login(client, fakedb):
    rv = client.get("/apply/internship/2")
    assert rv.status_code == 302
    assert "/login" in rv.headers.get("Location")


def test_apply_internship_redirects(client, fakedb):
    fakedb._one = {"id": 2, "google_form_link": "https://forms.google.com/xyz"}
    _login(client)
    rv = client.get("/apply/internship/2")
    assert rv.status_code == 302
    assert rv.headers.get("Location") == "https://forms.google.com/xyz"
