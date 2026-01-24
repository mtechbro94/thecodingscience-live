"""
WSGI entry point for production servers (Gunicorn, uWSGI, etc.)
"""

import os
import sys
import traceback

# Ensure unbuffered output for better logging visibility
sys.stdout = os.fdopen(sys.stdout.fileno(), 'w', 0)
sys.stderr = os.fdopen(sys.stderr.fileno(), 'w', 0)

from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Import Flask app with error handling
try:
    from app import app
    print("✓ Successfully imported Flask app", file=sys.stdout, flush=True)
except Exception as e:
    print(f"✗ CRITICAL ERROR importing app: {e}", file=sys.stderr, flush=True)
    traceback.print_exc(file=sys.stderr)
    sys.exit(1)

# Configure app for production static file serving
if not app.debug:
    # In production, ensure proper static file configuration
    app.config['SEND_FILE_MAX_AGE_DEFAULT'] = 604800  # 7 days
    app.config['TEMPLATES_AUTO_RELOAD'] = False

if __name__ == "__main__":
    app.run()

