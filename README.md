# The Coding Science - Advanced Tech Platform

## 🎓 School of Technology and AI Innovations

A production-ready Flask web application for managing online courses, student enrollments, and payments.

## ✨ Features

### Core Features
- ✅ **User Authentication** - Registration, login, and session management
- ✅ **Course Management** - Browse courses with pagination and detailed views
- ✅ **Student Dashboard** - View enrolled courses and track progress
- ✅ **Payment Integration** - UPI payment with QR code generation
- ✅ **Email Notifications** - Automated welcome emails after enrollment
- ✅ **Admin Panel** - Complete admin dashboard for managing students, courses, and enrollments
- ✅ **Responsive Design** - Mobile-friendly Bootstrap 5 interface
- ✅ **Database Backend** - SQLite database with SQLAlchemy ORM

### Admin Features
- 📊 Dashboard with statistics
- 👥 Student management (view, delete)
- 📚 Course management
- ✅ Enrollment verification (single and bulk)
- 📧 Automated email sending on verification

## 🚀 Quick Start

### Prerequisites
- Python 3.8 or higher
- Virtual environment (`.venv` folder)

### Installation

1. **Navigate to project directory:**
   ```bash
   cd C:\Users\Mtechbro-94\Desktop\TheCodingScience
   ```

2. **Activate virtual environment:**
   ```bash
   .\.venv\Scripts\activate
   ```

3. **Install dependencies (if not already installed):**
   ```bash
   pip install -r requirements.txt
   ```

4. **Create `.env` file** (copy from `.env.example` if available):
   ```env
   SECRET_KEY=your-secret-key-change-this
   SENDER_EMAIL=your-email@gmail.com
   SENDER_PASSWORD=your-app-password
   ADMIN_EMAIL=admin@thecodingscience.com
   ADMIN_PASSWORD=admin123
   ```

5. **Run the application:**
   ```bash
   .\.venv\Scripts\python.exe app.py
   ```

6. **Open browser:**
   ```
   http://localhost:5000
   ```

## 📁 Project Structure

```
TheCodingScience/
├── app.py                      # Main Flask application
├── requirements.txt            # Python dependencies
├── README.md                   # This file
├── SETUP_PAYMENT_EMAIL.md      # Payment & email setup guide
├── instance/
│   └── coding_science.db      # SQLite database (created on first run)
├── templates/                  # Jinja2 HTML templates
│   ├── base.html              # Base template
│   ├── index.html             # Homepage
│   ├── courses.html           # Courses listing
│   ├── course_detail.html     # Course detail page
│   ├── dashboard.html         # Student dashboard
│   ├── login.html             # Login page
│   ├── register.html          # Registration page
│   ├── admin_panel.html        # Admin dashboard
│   ├── admin_students.html     # Student management
│   ├── admin_enrollments.html  # Enrollment management
│   ├── admin_courses.html     # Course management
│   └── ...                    # Other templates
└── static/                     # Static files
    ├── css/
    │   └── style.css          # Custom styles
    ├── js/
    │   └── main.js            # Custom JavaScript
    └── images/                 # Course images
```

## 🔐 Admin Access

### Default Admin Account
On first run, a default admin user is created:
- **Email**: `admin@thecodingscience.com` (or from `.env`)
- **Password**: `admin123` (or from `.env`)

**⚠️ Change the default password immediately in production!**

### Create Additional Admin Users

Using Flask CLI:
```bash
.\.venv\Scripts\python.exe -m flask create-admin
```

Or using Python shell:
```python
from app import app, db, User
with app.app_context():
    admin = User(name='admin', email='admin@theccodingscience.com', is_admin=True)
    admin.set_password('secure-password')
    db.session.add(admin)
    db.session.commit()
```

## 📧 Email Configuration

### Gmail SMTP Setup

1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account → Security → App passwords
   - Select "Mail" and your device
   - Copy the 16-character password
3. Update `.env` file:
   ```env
   SENDER_EMAIL=your-email@gmail.com
   SENDER_PASSWORD=your-16-char-app-password
   ```

See `SETUP_PAYMENT_EMAIL.md` for detailed instructions.

## 💳 Payment Setup

The platform supports UPI payments with QR code generation. Configure UPI IDs in `app.py`:

```python
UPI_IDS = [
    {'id': 'your-upi@ybl', 'label': 'PhonePe'},
    {'id': 'your-upi@oksbi', 'label': 'SBI'},
]
```

## 🗄️ Database

### Models
- **User** - Students and admins
- **Course** - Course information
- **Enrollment** - Student course enrollments with payment tracking

### Database Commands

**Initialize database:**
```python
from app import app, db
with app.app_context():
    db.create_all()
```

**Access Flask shell:**
```bash
.\.venv\Scripts\python.exe -m flask shell
```

## 🛠️ Development

### Running in Development Mode
```bash
.\.venv\Scripts\python.exe app.py
```

### Environment Variables
Create a `.env` file with:
```env
SECRET_KEY=your-secret-key
DATABASE_URL=sqlite:///coding_science.db
SENDER_EMAIL=your-email@gmail.com
SENDER_PASSWORD=your-app-password
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin123
FLASK_ENV=development
FLASK_PORT=5000
```

## 📝 Available Routes

### Public Routes
- `/` - Homepage
- `/courses` - Browse all courses
- `/course/<id>` - Course detail page
- `/about` - About page
- `/services` - Services page
- `/internships` - Internships page
- `/contact` - Contact form
- `/register` - User registration
- `/login` - User login

### Student Routes (Login Required)
- `/dashboard` - Student dashboard
- `/enroll/<course_id>` - Enroll in a course
- `/logout` - Logout

### Admin Routes (Admin Only)
- `/admin/panel` - Admin dashboard
- `/admin/students` - Manage students
- `/admin/student/<id>` - Student details
- `/admin/enrollments` - Manage enrollments
- `/admin/courses` - Manage courses

## 🔧 Technologies Used

- **Backend**: Flask 2.3.3
- **Database**: SQLite with Flask-SQLAlchemy 3.0.5
- **Authentication**: Flask-Login 0.6.2
- **Frontend**: Bootstrap 5.3.3, jQuery 3.6.0, Font Awesome 6.7.2
- **Email**: SMTP (Gmail)
- **QR Codes**: segno 1.6.6
- **Environment**: python-dotenv 1.2.1

## 📊 Current Data

### Courses (6 courses available)
- Web Development Foundations (₹499)
- Computer Science Foundations (₹499)
- Microsoft Office Automation (₹499)
- AI & Machine Learning Foundations (₹499)
- Programming Foundations with Python (₹499)
- Data Science and Analytics (₹499)

### Internships (3 positions)
- Web Development Intern (₹999)
- Python Development Intern (₹999)
- Data Science and AI Intern (₹999)

## 🚨 Troubleshooting

### Port Already in Use
Edit `app.py` and change the port:
```python
app.run(port=8000)  # Change from 5000 to 8000
```

### Database Issues
Delete `instance/coding_science.db` and restart the app to recreate the database.

### Email Not Sending
- Verify Gmail app password is correct
- Check firewall/antivirus settings
- Ensure 2FA is enabled on Gmail

### Admin Panel Not Accessible
- Ensure you're logged in as an admin user
- Check `is_admin=True` in database for your user

## 📞 Support

- **Email**: academy@thecodingscience.com
- **Phone**: +917006196821
- **Location**: Jammu and Kashmir, India

## 📄 License

This project is proprietary software for The Coding Science.

---

**Built with ❤️ for The Coding Science**
