#!/bin/bash
# Initialize database and start the app
python init_db.py
gunicorn -w 4 -b 0.0.0.0:$PORT wsgi:app
