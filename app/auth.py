"""Auth decorators, mirroring is_admin()/is_trainer()/is_student() guards."""

from functools import wraps

from flask import redirect, session

from app.helpers import set_flash


def login_required(fn):
    @wraps(fn)
    def wrapper(*args, **kwargs):
        if not session.get("user_id"):
            set_flash("danger", "Please login to continue.")
            return redirect("/login")
        return fn(*args, **kwargs)

    return wrapper


def admin_required(fn):
    @wraps(fn)
    def wrapper(*args, **kwargs):
        if session.get("user_role") != "admin":
            set_flash("danger", "Access denied. Administrator privileges required.")
            return redirect("/login")
        return fn(*args, **kwargs)

    return wrapper


def trainer_required(fn):
    @wraps(fn)
    def wrapper(*args, **kwargs):
        if session.get("user_role") != "trainer":
            set_flash("danger", "Access denied. Trainer privileges required.")
            return redirect("/login")
        return fn(*args, **kwargs)

    return wrapper


def student_required(fn):
    @wraps(fn)
    def wrapper(*args, **kwargs):
        if session.get("user_role") != "student":
            set_flash("danger", "Please login as a student to continue.")
            return redirect("/login")
        return fn(*args, **kwargs)

    return wrapper
