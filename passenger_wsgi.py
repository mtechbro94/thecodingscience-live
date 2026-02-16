import sys
import os

# Add the application directory to the sys.path
sys.path.insert(0, os.path.dirname(__file__))

# Import the Flask app and rename it to 'application' for Passenger
from wsgi import app as application
