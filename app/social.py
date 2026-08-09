"""Google OAuth (student login), mirroring includes/SocialAuth.php."""

from urllib.parse import urlencode

import requests

from config import Config

GOOGLE = {
    "auth_url": "https://accounts.google.com/o/oauth2/v2/auth",
    "token_url": "https://oauth2.googleapis.com/token",
    "user_info_url": "https://www.googleapis.com/oauth2/v3/userinfo",
    "scope": "openid email profile",
}


def get_auth_url(redirect_uri, state):
    params = {
        "client_id": Config.GOOGLE_CLIENT_ID,
        "redirect_uri": redirect_uri,
        "response_type": "code",
        "scope": GOOGLE["scope"],
        "state": state,
    }
    return GOOGLE["auth_url"] + "?" + urlencode(params)


def exchange_code(code, redirect_uri):
    params = {
        "client_id": Config.GOOGLE_CLIENT_ID,
        "client_secret": Config.GOOGLE_CLIENT_SECRET,
        "redirect_uri": redirect_uri,
        "grant_type": "authorization_code",
        "code": code,
    }
    resp = requests.post(
        GOOGLE["token_url"],
        data=params,
        headers={"Accept": "application/json"},
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def get_user_info(access_token):
    resp = requests.get(
        GOOGLE["user_info_url"],
        headers={
            "Authorization": "Bearer " + access_token,
            "User-Agent": "The Coding Science Auth",
        },
        timeout=30,
    )
    resp.raise_for_status()
    data = resp.json()
    return {
        "id": data.get("id") or data.get("sub"),
        "name": data.get("name"),
        "email": data.get("email"),
        "avatar": data.get("picture"),
    }
