"""
WSGI entry point for production servers (Gunicorn, uWSGI, etc.)
"""

import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Import Flask app
from app import app

# Configure app for production static file serving
if not app.debug:
    # In production, ensure proper static file configuration
    app.config['SEND_FILE_MAX_AGE_DEFAULT'] = 604800  # 7 days
    app.config['TEMPLATES_AUTO_RELOAD'] = False

if __name__ == "__main__":
    app.run()

