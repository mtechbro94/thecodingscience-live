# Developer Quick Start Guide

## Local Development Setup

### 1. Clone and Navigate
```bash
cd TheCodingScience
```

### 2. Create Virtual Environment
```bash
# Windows
python -m venv .venv
.\.venv\Scripts\activate

# Linux/Mac
python3 -m venv .venv
source .venv/bin/activate
```

### 3. Install Dependencies
```bash
# Production dependencies
pip install -r requirements.txt

# Development tools (optional but recommended)
pip install -r requirements-dev.txt
```

### 4. Environment Configuration
```bash
# Copy example to .env
cp .env.example .env

# Edit .env with your values
# Minimum required:
# - FLASK_ENV=development
# - SECRET_KEY=your-dev-secret
# - SENDER_EMAIL=your-email@gmail.com
# - SENDER_PASSWORD=your-app-password
```

### 5. Run Application
```bash
python app.py
```

Open `http://localhost:5000` in your browser.

## Development Workflow

### Code Formatting
```bash
# Format code with Black
black app.py config.py wsgi.py

# Sort imports
isort app.py config.py wsgi.py

# Check code quality
flake8 app.py config.py wsgi.py
pylint app.py
```

### Testing
```bash
# Run all tests
pytest

# Run with coverage
pytest --cov

# Run specific test
pytest tests/test_auth.py
```

### Database Operations
```bash
# Create admin user
flask create-admin

# Database shell
flask shell
>>> User.query.all()
>>> Course.query.all()

# Reset database (development only)
rm instance/coding_science.db
python app.py
```

### Debugging
```bash
# Enable Flask debugger toolbar
# (Already in requirements-dev.txt when installed)

# Interactive debugger with ipdb
pip install ipdb

# In code:
import ipdb; ipdb.set_trace()
```

## Environment Variables

### Development (.env)
```env
FLASK_ENV=development
FLASK_APP=app.py
FLASK_PORT=5000
SECRET_KEY=dev-secret-key-change-in-production
DATABASE_URL=sqlite:///coding_science.db

SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your-app-password
ADMIN_EMAIL=admin@thecodingscience.com
ADMIN_PASSWORD=admin123

WHATSAPP_GROUP_LINK=https://your-whatsapp-link
CONTACT_PHONE=+91-xxxxxxxxxxxx
CONTACT_EMAIL=academy@thecodingscience.com
```

### Testing (.env.test)
```env
FLASK_ENV=testing
SECRET_KEY=test-secret-key
DATABASE_URL=sqlite:///:memory:
```

## Project Structure
```
TheCodingScience/
├── app.py                    # Main Flask application
├── config.py                 # Configuration management
├── wsgi.py                   # WSGI entry point
├── requirements.txt          # Production dependencies
├── requirements-dev.txt      # Development tools
├── .env.example             # Environment variables template
├── README.md                # Project documentation
├── DEPLOYMENT.md            # Deployment guide
├── SECURITY.md              # Security guidelines
├── PRODUCTION_CHECKLIST.md  # Pre-launch checklist
│
├── templates/               # Jinja2 templates
│   ├── base.html
│   ├── index.html
│   ├── courses.html
│   ├── dashboard.html
│   ├── admin_*.html
│   └── ...
│
├── static/                  # Static files
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│
├── instance/                # Instance folder (gitignored)
│   └── coding_science.db
│
├── logs/                    # Log files (gitignored)
│   └── app.log
│
└── .git/                    # Git repository
```

## Common Commands

### Python Flask Commands
```bash
# Show all routes
flask routes

# Enter Flask shell
flask shell

# Run create-admin command
flask create-admin

# Export environment
set FLASK_APP=app.py
set FLASK_ENV=development
```

### Database Management
```bash
# In Python shell
python

# Inside Python:
from app import app, db, User, Course
with app.app_context():
    # Query operations
    users = User.query.all()
    courses = Course.query.filter_by(level='Foundational').all()
```

### Git Workflow
```bash
# Check status
git status

# Stage changes
git add .

# Commit
git commit -m "Feature: Add new functionality"

# Push
git push origin main

# Pull latest
git pull origin main
```

## Troubleshooting

### Virtual Environment Issues
```bash
# If venv doesn't work, reinstall
rm -rf .venv
python -m venv .venv
.\.venv\Scripts\activate
pip install -r requirements.txt
```

### Database Locked Error
```bash
# Reset database
rm instance/coding_science.db
python app.py
```

### Port Already in Use
```bash
# On Windows
netstat -ano | findstr :5000
taskkill /PID <PID> /F

# On Linux/Mac
lsof -i :5000
kill -9 <PID>
```

### Email Not Working
1. Check `.env` credentials
2. Verify Gmail app password (not account password)
3. Enable "Less secure apps" or use app-specific password
4. Check port 587 is accessible
5. Review logs: `tail -f logs/app.log`

### Import Errors
```bash
# Reinstall packages
pip install --force-reinstall -r requirements.txt
```

## IDE Setup

### VS Code
```json
{
    "python.linting.enabled": true,
    "python.linting.pylintEnabled": true,
    "python.formatting.provider": "black",
    "python.testing.pytestEnabled": true,
    "editor.formatOnSave": true
}
```

### PyCharm
- Mark `venv` as excluded
- Set interpreter to `.venv/Scripts/python.exe`
- Enable Flask integration
- Configure run configurations

## Performance Tips

### Development
- Use Flask development server for testing
- Enable query logging: `SQLALCHEMY_ECHO=True`
- Use Flask debugger for debugging

### Production
- Use Gunicorn with multiple workers
- Enable caching for static files
- Use PostgreSQL instead of SQLite
- Monitor application performance
- Set up error tracking (Sentry)

## Testing Checklist

Before submitting PR:
- [ ] Code follows PEP 8 (use Black)
- [ ] All tests pass (`pytest`)
- [ ] No security issues (safety check)
- [ ] No linting errors (flake8)
- [ ] Code coverage > 80% (pytest-cov)
- [ ] All imports are used
- [ ] No hardcoded credentials
- [ ] Error messages are user-friendly

## Documentation

When adding features:
1. Update README.md with new features
2. Document database models
3. Add docstrings to functions
4. Update API documentation if applicable
5. Add examples if complex

## Support & Questions

- **Documentation**: See README.md, DEPLOYMENT.md
- **Issues**: Check existing issues on GitHub
- **Email**: academy@thecodingscience.com
- **Phone**: +917006196821

---
**Happy Coding! 🚀**
