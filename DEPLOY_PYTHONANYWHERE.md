# Deploying to PythonAnywhere (Free Tier)

This guide deploys the Flask app to PythonAnywhere's free tier, which
includes a MySQL database and a WSGI web app. A free account is enough.

## 1. Create the account

1. Sign up at https://www.pythonanywhere.com/ (free "Beginner" tier).
2. Confirm your email and log in. Your username becomes part of the site URL:
   `https://<username>.pythonanywhere.com/`.

## 2. Create the MySQL database

1. Go to the **Databases** tab.
2. In the *MySQL* section, enter a password (or use "No password") and click
   **Initialize** / **Start a new MySQL database**.
3. Note the MySQL host shown (e.g. `username.mysql.pythonanywhere-services.com`)
   and your database name (usually `username$default`).

## 3. Clone the repository

Open the **Consoles** tab → **Bash**, then run:

```bash
cd ~
git clone https://github.com/mtechbro94/thecodingscience-live.git mysite
cd mysite
```

## 4. Import the database schema

In the same Bash console (or via the MySQL console on the Databases tab):

```bash
mysql -u <username> -h <host> -p '<password>' '<database>' < database/schema.sql
mysql -u <username> -h <host> -p '<password>' '<database>' < database/create_success_stories.sql
mysql -u <username> -h <host> -p '<password>' '<database>' < database/migrate_auth_refactor.sql
```

## 5. Create the virtualenv and install dependencies

```bash
python3.11 -m venv ~/.virtualenvs/mysite
source ~/.virtualenvs/mysite/bin/activate
cd ~/mysite
pip install -r requirements.txt
```

## 6. Create the `.env` file

Create `~/mysite/.env` with your real values:

```ini
ENVIRONMENT=production
SECRET_KEY=<generate a long random string>
SITE_URL=https://<username>.pythonanywhere.com

DB_HOST=<your pythonanywhere mysql host>
DB_NAME=<username>$default
DB_USER=<username>
DB_PASS=<your mysql password>

RAZORPAY_KEY_ID=<your razorpay key id>
RAZORPAY_KEY_SECRET=<your razorpay secret>

SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=<email>
SMTP_PASS=<app password>

GOOGLE_CLIENT_ID=<your google client id>
GOOGLE_CLIENT_SECRET=<your google client secret>
```

Generate a secret key:

```bash
python -c "import secrets; print(secrets.token_hex(32))"
```

## 7. Configure the web app

1. Go to the **Web** tab → **Add a new web app**.
2. Choose **Manual configuration** → **Python 3.11**.
3. In the **Code** section, set:
   - *Source code:* `/home/<username>/mysite`
   - *Working directory:* `/home/<username>/mysite`
   - *WSGI configuration file:* edit it to load the Flask app:
     ```python
     import sys
     sys.path.insert(0, "/home/<username>/mysite")
     from wsgi import application
     ```
4. In the **Virtualenv** section, set: `/home/<username>/.virtualenvs/mysite`
5. In the **Static files** section, add:
   - *URL:* `/assets`
   - *Directory:* `/home/<username>/mysite/assets`
6. Click **Reload** at the top.

## 8. Google OAuth callback URL

In Google Cloud Console, add an authorized redirect URI:
`https://<username>.pythonanywhere.com/social-callback/google` (see
`app/routes/auth.py`, `/social-callback/<provider>`).

## 9. Verify

Open `https://<username>.pythonanywhere.com/` — home, courses, blogs,
sitemap.xml, robots.txt should all return 200.

Free-tier caveat: the site sleeps after ~3 months of inactivity or goes to
sleep after periods of no traffic and wakes on first request.
