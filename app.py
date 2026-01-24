"""
The Coding Science - Advanced Flask Application with Database & Authentication
School of Technology and AI Innovations
Production-ready with User Management, Authentication, and Admin Panel
"""

import sys
import traceback

# ==================== IMPORTS ====================
try:
    from flask import Flask, render_template, request, jsonify, redirect, url_for, flash
    from flask_sqlalchemy import SQLAlchemy
    from flask_login import LoginManager, UserMixin, login_user, logout_user, login_required, current_user
    from werkzeug.security import generate_password_hash, check_password_hash
    from werkzeug.exceptions import BadRequest
    from datetime import datetime, timedelta
    import os
    import smtplib
    from email.mime.text import MIMEText
    from email.mime.multipart import MIMEMultipart
    import segno
    from io import BytesIO
    import base64
    import uuid
    import logging
    import logging.handlers
    from dotenv import load_dotenv
    from functools import wraps
    import re
except Exception as e:
    print(f"✗ CRITICAL ERROR during imports: {e}", file=sys.stderr, flush=True)
    traceback.print_exc(file=sys.stderr)
    raise

# ==================== CONFIGURATION & SETUP ====================
load_dotenv()

# Configure logging first (before app creation)
logger = logging.getLogger(__name__)
logger.setLevel(logging.DEBUG)

app = Flask(__name__, static_folder='static', static_url_path='/static')

# Load configuration first
try:
    from config import get_config
    app.config.from_object(get_config())
    print("✓ Configuration loaded successfully", file=sys.stdout, flush=True)
except Exception as e:
    print(f"✗ ERROR loading config: {e}", file=sys.stderr, flush=True)
    traceback.print_exc(file=sys.stderr)
    raise

# Add Whitenoise for serving static files in production
try:
    from whitenoise import WhiteNoise
    app.wsgi_app = WhiteNoise(app.wsgi_app, root='static')
    print("✓ WhiteNoise initialized successfully", file=sys.stdout, flush=True)
except Exception as e:
    print(f"⚠ WhiteNoise initialization failed: {e}", file=sys.stderr, flush=True)
    logger.warning(f"WhiteNoise initialization failed: {e}")


# Security: Enable HTTPS only in production (disabled for Railway)
# if not app.debug:
#     Talisman(app, force_https=True, strict_transport_security=True)

# Email Configuration
SMTP_SERVER = 'smtp.gmail.com'
SMTP_PORT = 587
SENDER_EMAIL = os.getenv('SENDER_EMAIL', '')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD', '')
WHATSAPP_GROUP_LINK = os.getenv('WHATSAPP_GROUP_LINK', '')

# UPI Configuration (load from environment variables)
UPI_IDS = [
    {'id': os.getenv('UPI_ID_1', 'upi@example'), 'label': 'Bank 1'},
    {'id': os.getenv('UPI_ID_2', 'upi@example'), 'label': 'Bank 2'},
    {'id': os.getenv('UPI_ID_3', 'upi@example'), 'label': 'Bank 3'}
]
UPI_NAME = os.getenv('UPI_NAME', 'The Coding Science')

# Logging Configuration with rotation
log_dir = os.path.dirname(app.config.get('LOG_FILE', 'logs/app.log'))
if not os.path.exists(log_dir):
    os.makedirs(log_dir, exist_ok=True)

handler = logging.handlers.RotatingFileHandler(
    app.config.get('LOG_FILE', 'logs/app.log'),
    maxBytes=10485760,  # 10MB
    backupCount=10
)
handler.setFormatter(logging.Formatter(
    '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
))

logger.setLevel(getattr(logging, app.config.get('LOG_LEVEL', 'INFO')))
logger.addHandler(handler)

# Database Initialization
db = SQLAlchemy(app)
login_manager = LoginManager(app)
login_manager.login_view = 'login'
login_manager.login_message = 'Please log in to access this page.'


# ==================== DATABASE MODELS ====================


class User(UserMixin, db.Model):
    """User/Student Model"""
    __tablename__ = 'users'
    
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(120), unique=True, nullable=False, index=True)
    password_hash = db.Column(db.String(255), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(15), nullable=True)
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    is_admin = db.Column(db.Boolean, default=False)
    is_active = db.Column(db.Boolean, default=True)
    
    # Relationships
    enrollments = db.relationship('Enrollment', backref='student', lazy=True, cascade='all, delete-orphan')
    
    def set_password(self, password):
        """Hash and set password"""
        self.password_hash = generate_password_hash(password)
    
    def check_password(self, password):
        """Verify password against hash"""
        return check_password_hash(self.password_hash, password)
    
    def __repr__(self):
        return f'<User {self.email}>'


class Course(db.Model):
    """Course Model"""
    __tablename__ = 'courses'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(200), nullable=False)
    description = db.Column(db.Text, nullable=True)
    duration = db.Column(db.String(50), nullable=True)
    price = db.Column(db.Float, nullable=False)
    level = db.Column(db.String(50), nullable=True)
    image = db.Column(db.String(200), nullable=True)
    curriculum = db.Column(db.Text, nullable=True)  # JSON string of modules
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    
    # Relationships
    enrollments = db.relationship('Enrollment', backref='course', lazy=True, cascade='all, delete-orphan')
    
    def __repr__(self):
        return f'<Course {self.name}>'
    
    def get_curriculum_list(self):
        """Return curriculum as list"""
        import json
        if not self.curriculum:
            return []
        try:
            return json.loads(self.curriculum)
        except:
            return []


class Enrollment(db.Model):
    """Enrollment Model - tracks student course enrollments and payments"""
    __tablename__ = 'enrollments'
    
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False, index=True)
    course_id = db.Column(db.Integer, db.ForeignKey('courses.id'), nullable=False, index=True)
    status = db.Column(db.String(20), default='pending')  # pending, completed, failed, refunded
    payment_method = db.Column(db.String(50), nullable=True)  # upi, razorpay, etc.
    payment_id = db.Column(db.String(100), nullable=True, unique=True)
    amount_paid = db.Column(db.Float, nullable=True)
    enrolled_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    verified_at = db.Column(db.DateTime, nullable=True)
    
    def __repr__(self):
        return f'<Enrollment {self.user_id} -> {self.course_id}>'


class ContactMessage(db.Model):
    """Contact Form Messages"""
    __tablename__ = 'contact_messages'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(120), nullable=False)
    email = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(20), nullable=True)
    subject = db.Column(db.String(200), nullable=True)
    message = db.Column(db.Text, nullable=False)
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    
    def __repr__(self):
        return f'<ContactMessage {self.email}>'


class InternshipApplication(db.Model):
    """Internship Applications"""
    __tablename__ = 'internship_applications'
    
    id = db.Column(db.Integer, primary_key=True)
    internship_id = db.Column(db.Integer, nullable=False)
    internship_role = db.Column(db.String(200), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    email = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    cover_letter = db.Column(db.Text, nullable=True)
    status = db.Column(db.String(20), default='pending')
    applied_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    
    def __repr__(self):
        return f'<InternshipApplication {self.email} -> {self.internship_role}>'


# ==================== FLASK-LOGIN HELPER ====================

@login_manager.user_loader
def load_user(user_id):
    """Load user by ID for Flask-Login"""
    return User.query.get(int(user_id))


# ==================== DATABASE INITIALIZATION ====================

def init_db_on_startup():
    """Initialize database and create tables on app startup"""
    with app.app_context():
        try:
            # Create all tables
            db.create_all()
            logger.info("✓ Database tables created/verified")
            
            # Seed initial courses if empty
            if Course.query.first() is None:
                logger.info("Seeding initial course data...")
                courses = [
                    Course(
                        name='Web Development Foundations',
                        description='Learn HTML, CSS, JavaScript, and React',
                        duration='8 weeks',
                        level='Beginner',
                        price=99.99,
                        image='/static/images/web-dev.jpg'
                    ),
                    Course(
                        name='Computer Science Foundations',
                        description='Data Structures, Algorithms, and OOP concepts',
                        duration='10 weeks',
                        level='Beginner',
                        price=99.99,
                        image='/static/images/cs-fundamentals.jpg'
                    ),
                    Course(
                        name='Programming Foundations with Python',
                        description='Python basics to advanced concepts',
                        duration='8 weeks',
                        level='Beginner',
                        price=79.99,
                        image='/static/images/python-fundamentals.jpg'
                    ),
                    Course(
                        name='Microsoft Office Automation',
                        description='Automate Excel, Word, and Outlook with Python',
                        duration='6 weeks',
                        level='Intermediate',
                        price=69.99,
                        image='/static/images/office-automation.jpg'
                    ),
                ]
                db.session.add_all(courses)
                db.session.commit()
                logger.info(f"✓ Seeded {len(courses)} courses")
        except Exception as e:
            logger.error(f"Error initializing database: {str(e)}")
            print(f"✗ Database init error: {str(e)}", file=sys.stderr, flush=True)
            traceback.print_exc(file=sys.stderr)


# Initialize database on startup
db_initialized = False

@app.before_request
def init_db_if_needed():
    """Lazy initialization of database on first request"""
    global db_initialized
    if not db_initialized:
        try:
            init_db_on_startup()
            db_initialized = True
            print("✓ Database initialized successfully on first request", file=sys.stdout, flush=True)
        except Exception as e:
            print(f"✗ ERROR during database initialization: {e}", file=sys.stderr, flush=True)
            traceback.print_exc(file=sys.stderr)

# Also try to initialize at startup but don't block if it fails
try:
    init_db_on_startup()
    db_initialized = True
    print("✓ Database initialized successfully at startup", file=sys.stdout, flush=True)
except Exception as e:
    print(f"⚠ Database initialization deferred to first request: {e}", file=sys.stderr, flush=True)



# App startup complete message
print("=" * 50, file=sys.stdout, flush=True)
print("✓ FLASK APP INITIALIZATION COMPLETE", file=sys.stdout, flush=True)
print("✓ Ready to handle requests", file=sys.stdout, flush=True)
print("=" * 50, file=sys.stdout, flush=True)

# ==================== CUSTOM DECORATORS ====================


def admin_required(f):
    """Decorator to require admin role"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or not current_user.is_admin:
            flash('You do not have permission to access this page.', 'danger')
            return redirect(url_for('home'))
        return f(*args, **kwargs)
    return decorated_function


# ==================== SEED DATA FUNCTION ====================

def seed_courses():
    """Initialize database with course data"""
    if Course.query.first() is not None:
        return  # Database already seeded
    
    import json
    
    courses = [
        Course(
            name='Web Development Foundations',
            duration='3 Months',
            description='Build a solid base in HTML, CSS, JavaScript, and responsive design to launch your web career.',
            price=499,
            level='Foundational',
            image='webdev.jpg',
            curriculum=json.dumps([
                "Module 1: HTML5 & Semantic Web",
                "Module 2: CSS3 Styling & Flexbox/Grid",
                "Module 3: JavaScript Basics & DOM Manipulation",
                "Module 4: Responsive Design with Bootstrap",
                "Module 5: Git & Version Control",
                "Module 6: Deployment & Portfolio Building"
            ])
        ),
        Course(
            name='Computer Science Foundations',
            duration='3 Months',
            description='Strengthen core CS skills with programming fundamentals, data structures, and problem-solving.',
            price=499,
            level='Foundational',
            image='CS.jpg',
            curriculum=json.dumps([
                "Module 1: Introduction to Computing & Binary",
                "Module 2: Algorithms & Logic Building",
                "Module 3: Data Structures (Arrays, Lists, Stacks)",
                "Module 4: Object-Oriented Programming Concepts",
                "Module 5: Database Fundamentals (SQL)",
                "Module 6: Operating Systems & Networking Basics"
            ])
        ),
        Course(
            name='Microsoft Office Automation and Digital Tools',
            duration='3 Months',
            description='Master Excel, Word, PowerPoint, and automation workflows to boost productivity across teams.',
            price=499,
            level='Foundational',
            image='MS.jpg',
            curriculum=json.dumps([
                "Module 1: Advanced Excel Formulas & Functions",
                "Module 2: Data Visualization in Excel",
                "Module 3: Word Formatting & Mail Merge",
                "Module 4: PowerPoint Professional Presentations",
                "Module 5: Outlook & Communication Management",
                "Module 6: Introduction to Macros & Automation"
            ])
        ),
        Course(
            name='AI & Machine Learning Foundations',
            duration='3 Months',
            description='Explore the fundamentals of AI, machine learning workflows, and hands-on model building.',
            price=499,
            level='Foundational',
            image='AIML.jpg',
            curriculum=json.dumps([
                "Module 1: Introduction to AI & Data Science",
                "Module 2: Python for Data Science (NumPy, Pandas)",
                "Module 3: Data Visualization (Matplotlib, Seaborn)",
                "Module 4: Machine Learning Concepts (Supervised/Unsupervised)",
                "Module 5: Building Simple Models (Scikit-Learn)",
                "Module 6: Ethics in AI & Future Trends"
            ])
        ),
        Course(
            name='Programming Foundations with Python',
            duration='3 Months',
            description='Master Python programming from basics to intermediate level with real-world projects and problem-solving techniques.',
            price=499,
            level='Foundational',
            image='PFP.jpg',
            curriculum=json.dumps([
                "Module 1: Python Syntax & Variables",
                "Module 2: Control Flow (If/Else, Loops)",
                "Module 3: Functions & Modules",
                "Module 4: Data Structures (Lists, Dictionaries)",
                "Module 5: File Handling & APIs",
                "Module 6: Final Project: Building a Tool"
            ])
        ),
        Course(
            name='Data Science and Analytics',
            duration='3 Months',
            description='Learn data analysis, visualization, and business intelligence with Python, Pandas, and powerful analytics tools.',
            price=499,
            level='Intermediate',
            image='DS&A.jpg',
            curriculum=json.dumps([
                "Module 1: Python for Data Analysis",
                "Module 2: Data Wrangling with Pandas",
                "Module 3: Exploratory Data Analysis",
                "Module 4: Statistical Analysis Basics",
                "Module 5: Data Storytelling & Visualization",
                "Module 6: Capstone Project"
            ])
        ),
    ]
    
    for course in courses:
        db.session.add(course)
    
    db.session.commit()
    logger.info('Database seeded with courses.')


# ==================== HELPER FUNCTIONS ====================

def generate_qr_code(upi_string):
    """Generate QR code for UPI payment using segno"""
    try:
        qr = segno.make(upi_string, micro=False)
        img_io = BytesIO()
        qr.save(img_io, kind='png', scale=8, border=2)
        img_io.seek(0)
        img_base64 = base64.b64encode(img_io.getvalue()).decode()
        return f"data:image/png;base64,{img_base64}"
    except Exception as e:
        logger.error(f'QR code generation failed: {str(e)}')
        return None


def send_email(to_email, subject, html_body):
    """Send email with HTML content"""
    # Validate email
    if not is_valid_email(to_email):
        logger.error(f'Invalid email address: {to_email}')
        return False
    
    try:
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = SENDER_EMAIL
        msg['To'] = to_email
        msg.attach(MIMEText(html_body, 'html'))
        
        logger.info(f'Sending email to {to_email}: {subject}')
        
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT, timeout=10) as server:
            server.starttls()
            server.login(SENDER_EMAIL, SENDER_PASSWORD)
            server.send_message(msg)
        
        logger.info(f'Email sent successfully to {to_email}')
        return True
    except smtplib.SMTPAuthenticationError:
        logger.error('Email authentication failed. Check SENDER_EMAIL and SENDER_PASSWORD.')
        return False
    except smtplib.SMTPException as e:
        logger.error(f'SMTP error while sending email to {to_email}: {str(e)}')
        return False
    except Exception as e:
        logger.error(f'Failed to send email to {to_email}: {str(e)}')
        return False


def is_valid_email(email):
    """Validate email format"""
    pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    return re.match(pattern, email) is not None


def send_welcome_email(student_name, student_email, course_name, amount):
    """Send welcome email with course details"""
    html_body = f"""
    <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #0066cc;">Welcome to The Coding Science! 🎉</h2>
                
                <p>Hi <strong>{student_name}</strong>,</p>
                
                <p>Congratulations! Your payment has been successfully processed and you are now enrolled in:</p>
                
                <div style="background-color: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc; margin: 20px 0;">
                    <h3 style="margin: 0; color: #0066cc;">{course_name}</h3>
                    <p style="margin: 10px 0; font-size: 14px;"><strong>Course Duration:</strong> 3 Months</p>
                    <p style="margin: 10px 0; font-size: 14px;"><strong>Amount Paid:</strong> ₹{amount}</p>
                </div>
                
                <h3 style="color: #0066cc;">Next Steps:</h3>
                <ol>
                    <li><strong>Join WhatsApp Group:</strong> <a href="{WHATSAPP_GROUP_LINK}" style="color: #0066cc; text-decoration: none;">Click here to join our WhatsApp group</a> for live course updates, doubt sessions, and community support.</li>
                    <li><strong>Access Dashboard:</strong> Log in to your student dashboard to access course content and track progress.</li>
                    <li><strong>Get Started:</strong> Check your email for course materials and schedule details.</li>
                </ol>
                
                <h3 style="color: #0066cc;">Course Support:</h3>
                <p>If you have any questions or need assistance, feel free to reach out:</p>
                <ul>
                    <li>📧 Email: <a href="mailto:academy@thecodingscience.com" style="color: #0066cc;">academy@thecodingscience.com</a></li>
                    <li>📱 Phone: <a href="tel:+917006196821" style="color: #0066cc;">+917006196821</a></li>
                </ul>
                
                <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                    Best regards,<br>
                    <strong>The Coding Science</strong><br>
                    School of Technology and AI Innovations<br>
                    Jammu and Kashmir, India
                </p>
            </div>
        </body>
    </html>
    """
    
    subject = f'Welcome to {course_name}! - The Coding Science'
    return send_email(to_email, subject, html_body)


# ==================== CONTEXT PROCESSOR ====================

@app.context_processor
def inject_config():
    """Inject site configuration into all templates from environment variables"""
    return {
        'site_name': os.getenv('SITE_NAME', 'The Coding Science'),
        'site_tagline': os.getenv('SITE_TAGLINE', 'Learn Technology, Transform Your Future'),
        'contact_email': os.getenv('CONTACT_EMAIL', 'academy@thecodingscience.com'),
        'contact_phone': os.getenv('CONTACT_PHONE', '+917006196821'),
        'contact_location': os.getenv('CONTACT_LOCATION', 'Jammu and Kashmir, India'),
        'social_links': {
            'instagram': os.getenv('INSTAGRAM_URL', 'https://www.instagram.com/thecodingscience'),
            'youtube': os.getenv('YOUTUBE_URL', 'https://youtube.com/@thecodingscience-q7z'),
            'facebook': os.getenv('FACEBOOK_URL', 'https://www.facebook.com/thecodingscience'),
            'linkedin': os.getenv('LINKEDIN_URL', 'https://www.linkedin.com/company/thecodingscience')
        }
    }


# ==================== PUBLIC ROUTES ====================

@app.route('/health')
def health_check():
    """Simple health check endpoint for deployment verification"""
    return jsonify({'status': 'ok', 'service': 'TheCodingScience'}), 200

@app.route('/')
def home():
    """Homepage"""
    featured_courses = Course.query.limit(4).all()
    return render_template('index.html', courses=featured_courses)

@app.route('/courses')
def courses():
    """Courses listing page with pagination"""
    page = request.args.get('page', 1, type=int)
    per_page = 6
    
    pagination = Course.query.paginate(page=page, per_page=per_page)
    courses = pagination.items
    
    return render_template('courses.html', courses=pagination)


@app.route('/course/<int:id>')
def course_detail(id):
    """Course detail page"""
    course = Course.query.get_or_404(id)
    # Check if current user is enrolled
    is_enrolled = False
    if current_user.is_authenticated:
        is_enrolled = Enrollment.query.filter_by(
            user_id=current_user.id,
            course_id=course.id,
            status='completed'
        ).first() is not None
    
    return render_template('course_detail.html', course=course, is_enrolled=is_enrolled)


@app.route('/about')
def about():
    """About page"""
    stats = {
        'students': '5000+',
        'placement_rate': '95%',
        'partners': '50+',
        'trainers': '20+'
    }
    return render_template('about.html', stats=stats)


@app.route('/services')
def services():
    """Services page"""
    services_list = [
        {
            'id': 1,
            'title': 'Web Development Foundations',
            'icon': 'fa-laptop-code',
            'description': 'Build responsive sites with HTML, CSS, JavaScript, and modern workflows.',
            'duration': '3 Months',
            'price': 499
        },
        {
            'id': 2,
            'title': 'Computer Science Foundations',
            'icon': 'fa-database',
            'description': 'Strengthen programming fundamentals, data structures, and problem-solving.',
            'duration': '3 Months',
            'price': 499
        },
        {
            'id': 3,
            'title': 'Microsoft Office Automation and Digital Tools',
            'icon': 'fa-tools',
            'description': 'Master Excel, Word, PowerPoint, and automation to streamline daily work.',
            'duration': '3 Months',
            'price': 499
        },
        {
            'id': 4,
            'title': 'AI & Machine Learning Foundations',
            'icon': 'fa-robot',
            'description': 'Understand ML workflows, model building, and core AI concepts.',
            'duration': '3 Months',
            'price': 499
        }
    ]
    return render_template('services.html', services=services_list)


@app.route('/internships')
def internships():
    """Internships page"""
    internships_list = [
        {
            'id': 1,
            'role': 'Web Development Intern',
            'company': 'School of Technology and AI Innovations',
            'duration': '3 Months',
            'location': 'Remote',
            'stipend': 999,
            'description': 'Build real-world websites with React, Node.js & MongoDB. Gain hands-on experience with modern web technologies and industry best practices.'
        },
        {
            'id': 2,
            'role': 'Python Development Intern',
            'company': 'School of Technology and AI Innovations',
            'duration': '3 Months',
            'location': 'Remote',
            'stipend': 999,
            'description': 'Master backend development with Python. Build APIs, manage databases, and work on real-world projects with experienced mentors.'
        },
        {
            'id': 3,
            'role': 'Data Science and AI Intern',
            'company': 'School of Technology and AI Innovations',
            'duration': '3 Months',
            'location': 'Remote',
            'stipend': 999,
            'description': 'Work with real datasets and build ML models. Learn machine learning, deep learning, and solve real-world AI problems.'
        }
    ]
    return render_template('internships.html', internships=internships_list)


@app.route('/apply/<int:internship_id>', methods=['POST'])
def apply_internship(internship_id):
    """Handle internship applications"""
    try:
        name = request.form.get('name', '').strip()
        email = request.form.get('email', '').strip().lower()
        phone = request.form.get('phone', '').strip()
        cover_letter = request.form.get('cover_letter', '').strip()
        
        # Validate inputs
        if not all([name, email, phone]):
            return jsonify({'status': 'error', 'message': 'Please fill in all required fields'}), 400
        
        if not is_valid_email(email):
            return jsonify({'status': 'error', 'message': 'Invalid email address'}), 400
        
        if not re.match(r'^\+?1?\d{9,15}$', phone):
            return jsonify({'status': 'error', 'message': 'Invalid phone number'}), 400
        
        if len(name) > 120 or len(cover_letter) > 2000:
            return jsonify({'status': 'error', 'message': 'Input text is too long'}), 400
        
        # Validate internship ID
        if internship_id < 1 or internship_id > 3:
            return jsonify({'status': 'error', 'message': 'Invalid internship'}), 400
        
        # Map internship ID to role name
        roles = {
            1: 'Web Development Intern',
            2: 'Python Development Intern',
            3: 'Data Science and AI Intern'
        }
        role_name = roles.get(internship_id, 'Unknown Role')
        
        application = InternshipApplication(
            internship_id=internship_id,
            internship_role=role_name,
            name=name,
            email=email,
            phone=phone,
            cover_letter=cover_letter
        )
        db.session.add(application)
        db.session.commit()
        
        logger.info(f'Internship application received: {name} ({email}) for internship {internship_id}')
        
        # Send confirmation email
        html_body = f"""
        <html>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                    <h2 style="color: #0066cc;">Thank you for applying! 🎉</h2>
                    <p>Hi <strong>{name}</strong>,</p>
                    <p>We have received your application for the <strong>{role_name}</strong> position.</p>
                    <p>Our team will review your application and get back to you soon.</p>
                    <p><strong>Contact Info:</strong></p>
                    <ul>
                        <li>Phone: {os.getenv('CONTACT_PHONE', '+917006196821')}</li>
                        <li>Email: {os.getenv('CONTACT_EMAIL', 'academy@thecodingscience.com')}</li>
                    </ul>
                    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                        Best regards,<br>
                        <strong>The Coding Science</strong>
                    </p>
                </div>
            </body>
        </html>
        """
        send_email(email, 'Application Received - The Coding Science', html_body)
        
        return jsonify({'status': 'success', 'message': 'Application submitted successfully! We will contact you soon.'})
    
    except Exception as e:
        logger.error(f'Error processing internship application: {e}')
        return jsonify({'status': 'error', 'message': 'An error occurred. Please try again.'}), 500


@app.route('/contact', methods=['GET', 'POST'])
def contact():
    """Contact page"""
    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        email = request.form.get('email', '').strip().lower()
        phone = request.form.get('phone', '').strip()
        subject = request.form.get('subject', '').strip()
        message = request.form.get('message', '').strip()
        
        # Validation
        if not (name and email and message):
            return jsonify({
                'status': 'error',
                'message': 'Please fill in all required fields.'
            }), 400
        
        if not is_valid_email(email):
            return jsonify({
                'status': 'error',
                'message': 'Please enter a valid email address.'
            }), 400
        
        # Sanitize inputs
        if len(name) > 120 or len(message) > 5000:
            return jsonify({
                'status': 'error',
                'message': 'Input text is too long.'
            }), 400
        
        # Store contact message
        try:
            contact_msg = ContactMessage(
                name=name,
                email=email,
                phone=phone,
                subject=subject,
                message=message
            )
            db.session.add(contact_msg)
            db.session.commit()
            logger.info(f'Contact form submission from {email}: {subject}')
        except Exception as e:
            db.session.rollback()
            logger.error(f"Error saving contact message: {e}")
            return jsonify({'status': 'error', 'message': 'Could not save message, but we are working on it.'}), 500
        
        return jsonify({
            'status': 'success',
            'message': 'Thank you for your message! We will get back to you soon.'
        })
    
    return render_template('contact.html')


# ==================== AUTHENTICATION ROUTES ====================

@app.route('/register', methods=['GET', 'POST'])
def register():
    """User registration route"""
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        email = request.form.get('email', '').strip().lower()
        phone = request.form.get('phone', '').strip()
        password = request.form.get('password', '')
        confirm_password = request.form.get('confirm_password', '')
        
        # Validation
        if not (name and email and password):
            flash('Please fill in all required fields.', 'danger')
            return redirect(url_for('register'))
        
        if not is_valid_email(email):
            flash('Please enter a valid email address.', 'danger')
            return redirect(url_for('register'))
        
        if password != confirm_password:
            flash('Passwords do not match.', 'danger')
            return redirect(url_for('register'))
        
        if len(password) < 8:
            flash('Password must be at least 8 characters long.', 'danger')
            return redirect(url_for('register'))
        
        # Validate phone format (basic validation)
        if phone and not re.match(r'^\+?1?\d{9,15}$', phone):
            flash('Please enter a valid phone number.', 'danger')
            return redirect(url_for('register'))
        
        # Check if user exists
        if User.query.filter_by(email=email).first():
            flash('Email already registered. Please log in.', 'info')
            next_page = request.args.get('next', '')
            return redirect(url_for('login', next=next_page))
        
        try:
            # Create new user
            user = User(name=name, email=email, phone=phone)
            user.set_password(password)
            db.session.add(user)
            db.session.commit()
            
            logger.info(f'New user registered: {email}')
            flash('Account created successfully! Please log in.', 'success')
            next_page = request.args.get('next', '')
            return redirect(url_for('login', next=next_page))
        except Exception as e:
            db.session.rollback()
            logger.error(f'Registration error: {str(e)}')
            flash('An error occurred during registration. Please try again.', 'danger')
            return redirect(url_for('register'))
    
    return render_template('register.html')


@app.route('/login', methods=['GET', 'POST'])
def login():
    """User login route"""
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        email = request.form.get('email', '').strip().lower()
        password = request.form.get('password', '')
        
        if not (email and password):
            flash('Please fill in all required fields.', 'danger')
            return redirect(url_for('login'))
        
        if not is_valid_email(email):
            flash('Invalid email or password.', 'danger')
            logger.warning(f'Login attempt with invalid email format: {email}')
            return redirect(url_for('login'))
        
        user = User.query.filter_by(email=email).first()
        
        if user and user.check_password(password) and user.is_active:
            login_user(user, remember=request.form.get('remember'))
            logger.info(f'User logged in: {email}')
            
            # Redirect to requested page or dashboard/admin panel
            next_page = request.args.get('next')
            if next_page and next_page.startswith('/'):
                return redirect(next_page)
            
            return redirect(url_for('admin_panel' if user.is_admin else 'dashboard'))
        else:
            flash('Invalid email or password.', 'danger')
            logger.warning(f'Failed login attempt for: {email}')
            return redirect(url_for('login'))
    
    return render_template('login.html')


@app.route('/logout')
@login_required
def logout():
    """User logout route"""
    logger.info(f'User logged out: {current_user.email}')
    logout_user()
    flash('You have been logged out.', 'info')
    return redirect(url_for('home'))


# ==================== STUDENT DASHBOARD ROUTES ====================

@app.route('/dashboard')
@login_required
def dashboard():
    """Student dashboard showing enrolled courses"""
    page = request.args.get('page', 1, type=int)
    per_page = 6
    
    # Get enrollments with course details
    enrollments = Enrollment.query.filter_by(
        user_id=current_user.id,
        status='completed'
    ).paginate(page=page, per_page=per_page)
    
    # Get pending enrollments for information
    pending_enrollments = Enrollment.query.filter_by(
        user_id=current_user.id,
        status='pending'
    ).all()
    
    return render_template(
        'dashboard.html',
        enrollments=enrollments,
        pending_enrollments=pending_enrollments
    )


@app.route('/enroll/<int:course_id>', methods=['POST'])
@login_required
def enroll_course(course_id):
    """Enroll in a course"""
    course = Course.query.get_or_404(course_id)
    
    # Check for duplicate enrollment
    existing = Enrollment.query.filter_by(
        user_id=current_user.id,
        course_id=course_id
    ).first()
    
    if existing:
        return jsonify({
            'status': 'error',
            'message': f'You are already enrolled in {course.name}.'
        }), 400
    
    try:
        payment_method = request.form.get('payment_method', 'razorpay')
        payment_id = request.form.get('payment_id', f'TCS-{uuid.uuid4().hex[:8].upper()}')
        
        # UPI: pending, Razorpay: completed (in production, verify with payment provider)
        status = 'pending' if payment_method == 'upi' else 'completed'
        
        enrollment = Enrollment(
            user_id=current_user.id,
            course_id=course_id,
            status=status,
            payment_method=payment_method,
            payment_id=payment_id,
            amount_paid=course.price
        )
        db.session.add(enrollment)
        db.session.commit()
        
        logger.info(f'Enrollment created: {current_user.email} -> {course.name}')
        
        # Send welcome email only if payment is completed
        if status == 'completed':
            send_welcome_email(current_user.name, current_user.email, course.name, course.price)
            message = f'✓ Payment successful! You are now enrolled in {course.name}. Check your email for details.'
        else:
            message = f'✓ Enrollment received! Your UPI payment is pending verification. You will receive a confirmation email within 2 hours.'
        
        return jsonify({
            'status': 'success',
            'message': message
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Enrollment error: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred during enrollment. Please try again.'
        }), 500


@app.route('/upi-payment/<int:course_id>', methods=['GET'])
def upi_payment(course_id):
    """Generate UPI payment QR code"""
    try:
        course = Course.query.get_or_404(course_id)
        
        selected_upi = request.args.get('upi_id') or UPI_IDS[0]['id']
        transaction_id = f"TCS-{uuid.uuid4().hex[:8].upper()}"
        
        upi_string = (
            f"upi://pay?pa={selected_upi}&pn={UPI_NAME}&am={course.price}"
            f"&tn=Course%20Enrollment%20-%20{course.name.replace(' ', '%20')}"
            f"&tr={transaction_id}"
        )
        
        qr_code = generate_qr_code(upi_string)
        
        if not qr_code:
            raise Exception('QR code generation failed')
        
        return jsonify({
            'status': 'success',
            'qr_code': qr_code,
            'upi_id': selected_upi,
            'amount': course.price,
            'course_name': course.name,
            'transaction_id': transaction_id,
            'upi_options': UPI_IDS
        })
    except Exception as e:
        logger.error(f'UPI payment error: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'Failed to generate QR code.'
        }), 500


# ==================== ADMIN ROUTES ====================

@app.route('/admin/panel')
@login_required
@admin_required
def admin_panel():
    """Admin dashboard"""
    total_students = User.query.filter_by(is_admin=False).count()
    total_courses = Course.query.count()
    total_enrollments = Enrollment.query.count()
    completed_enrollments = Enrollment.query.filter_by(status='completed').count()
    pending_enrollments = Enrollment.query.filter_by(status='pending').count()
    
    stats = {
        'total_students': total_students,
        'total_courses': total_courses,
        'total_enrollments': total_enrollments,
        'completed_enrollments': completed_enrollments,
        'pending_enrollments': pending_enrollments
    }
    
    return render_template('admin_panel.html', stats=stats)


@app.route('/admin/students')
@login_required
@admin_required
def admin_students():
    """List all students"""
    page = request.args.get('page', 1, type=int)
    per_page = 10
    
    # Get all non-admin users
    students = User.query.filter_by(is_admin=False).paginate(
        page=page,
        per_page=per_page
    )
    
    return render_template('admin_students.html', students=students)


@app.route('/admin/student/<int:user_id>')
@login_required
@admin_required
def admin_student_detail(user_id):
    """View student details and enrollments"""
    student = User.query.get_or_404(user_id)
    
    if student.is_admin:
        flash('Cannot view admin user details.', 'danger')
        return redirect(url_for('admin_students'))
    
    enrollments = Enrollment.query.filter_by(user_id=user_id).all()
    
    return render_template('admin_student_detail.html', student=student, enrollments=enrollments)


@app.route('/admin/student/<int:user_id>/delete', methods=['POST'])
@login_required
@admin_required
def admin_delete_student(user_id):
    """Delete a student and their enrollments"""
    student = User.query.get_or_404(user_id)
    
    if student.is_admin:
        return jsonify({'status': 'error', 'message': 'Cannot delete admin users.'}), 400
    
    try:
        email = student.email
        db.session.delete(student)
        db.session.commit()
        
        logger.info(f'Student deleted by admin: {email}')
        flash(f'Student {email} has been deleted.', 'success')
        return redirect(url_for('admin_students'))
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting student: {str(e)}')
        flash('An error occurred while deleting the student.', 'danger')
        return redirect(url_for('admin_students'))


@app.route('/admin/enrollments')
@login_required
@admin_required
def admin_enrollments():
    """Manage enrollments"""
    page = request.args.get('page', 1, type=int)
    status_filter = request.args.get('status', 'all')
    per_page = 10
    
    query = Enrollment.query
    
    if status_filter != 'all':
        query = query.filter_by(status=status_filter)
    
    enrollments = query.paginate(page=page, per_page=per_page)
    
    return render_template(
        'admin_enrollments.html',
        enrollments=enrollments,
        status_filter=status_filter
    )


@app.route('/admin/enrollment/<int:enrollment_id>/verify', methods=['POST'])
@login_required
@admin_required
def admin_verify_enrollment(enrollment_id):
    """Verify pending enrollment and send welcome email"""
    enrollment = Enrollment.query.get_or_404(enrollment_id)
    
    if enrollment.status == 'completed':
        return jsonify({
            'status': 'info',
            'message': 'Enrollment already completed.'
        }), 200
    
    try:
        enrollment.status = 'completed'
        enrollment.verified_at = datetime.utcnow()
        db.session.commit()
        
        # Send welcome email
        send_welcome_email(
            enrollment.student.name,
            enrollment.student.email,
            enrollment.course.name,
            enrollment.amount_paid
        )
        
        logger.info(f'Enrollment verified: {enrollment.id}')
        
        return jsonify({
            'status': 'success',
            'message': f'Enrollment verified and email sent to {enrollment.student.email}.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error verifying enrollment: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while verifying the enrollment.'
        }), 500


@app.route('/admin/enrollments/bulk-verify', methods=['POST'])
@login_required
@admin_required
def admin_bulk_verify():
    """Bulk verify pending enrollments"""
    try:
        data = request.get_json()
        enrollment_ids = data.get('enrollment_ids', [])
        
        if not enrollment_ids:
            return jsonify({'status': 'error', 'message': 'No enrollments selected.'}), 400
        
        verified_count = 0
        for enrollment_id in enrollment_ids:
            enrollment = Enrollment.query.get(enrollment_id)
            if enrollment and enrollment.status == 'pending':
                enrollment.status = 'completed'
                enrollment.verified_at = datetime.utcnow()
                send_welcome_email(
                    enrollment.student.name,
                    enrollment.student.email,
                    enrollment.course.name,
                    enrollment.amount_paid
                )
                verified_count += 1
        
        db.session.commit()
        logger.info(f'Bulk verified {verified_count} enrollments')
        
        return jsonify({
            'status': 'success',
            'message': f'{verified_count} enrollments verified and emails sent.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Bulk verify error: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred during bulk verification.'
        }), 500


@app.route('/admin/enrollment/<int:enrollment_id>/delete', methods=['POST'])
@login_required
@admin_required
def admin_delete_enrollment(enrollment_id):
    """Delete an enrollment"""
    enrollment = Enrollment.query.get_or_404(enrollment_id)
    
    try:
        course_name = enrollment.course.name
        db.session.delete(enrollment)
        db.session.commit()
        
        logger.info(f'Enrollment deleted: {enrollment_id}')
        
        return jsonify({
            'status': 'success',
            'message': f'Enrollment for {course_name} has been deleted.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting enrollment: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while deleting the enrollment.'
        }), 500


@app.route('/admin/courses')
@login_required
@admin_required
def admin_courses():
    """Manage courses"""
    courses = Course.query.all()
    return render_template('admin_courses.html', courses=courses)


@app.route('/admin/messages')
@login_required
@admin_required
def admin_messages():
    """View all messages and applications"""
    messages = ContactMessage.query.all()
    enrollments = Enrollment.query.all()
    applications = InternshipApplication.query.all()
    
    return render_template(
        'admin_messages.html',
        messages=messages,
        enrollments=enrollments,
        applications=applications
    )


# ==================== ERROR HANDLERS ====================

@app.errorhandler(404)
def not_found_error(error):
    """Handle 404 errors"""
    logger.warning(f'404 error: {request.url}')
    return render_template('404.html'), 404


@app.errorhandler(500)
def internal_error(error):
    """Handle 500 errors"""
    db.session.rollback()
    logger.error(f'500 error: {str(error)}')
    return render_template('500.html'), 500


@app.errorhandler(403)
def forbidden_error(error):
    """Handle 403 errors"""
    logger.warning(f'403 error: {request.url}')
    return jsonify({'status': 'error', 'message': 'Access forbidden.'}), 403


# ==================== CLI COMMANDS ====================

@app.shell_context_processor
def make_shell_context():
    """Make database objects available in Flask shell"""
    return {
        'db': db,
        'User': User,
        'Course': Course,
        'Enrollment': Enrollment,
        'ContactMessage': ContactMessage,
        'InternshipApplication': InternshipApplication
    }


@app.cli.command('create-admin')
def create_admin():
    """Create an admin user via CLI"""
    import getpass
    email = input('Email: ')
    name = input('Name: ')
    password = getpass.getpass('Password: ')
    
    if User.query.filter_by(email=email).first():
        print(f'User with email {email} already exists!')
        return
    
    admin = User(name=name, email=email, is_admin=True, is_active=True)
    admin.set_password(password)
    db.session.add(admin)
    db.session.commit()
    print(f'Admin user {email} created successfully!')


# ==================== INITIALIZATION ====================

def init_db():
    """Initialize the database"""
    with app.app_context():
        db.create_all()
        seed_courses()
        
        # Create default admin user if no admin exists
        admin_email = os.getenv('ADMIN_EMAIL', 'admin@thecodingscience.com')
        admin_password = os.getenv('ADMIN_PASSWORD', 'admin123')
        
        if not User.query.filter_by(is_admin=True).first():
            admin = User(
                name='Admin',
                email=admin_email,
                is_admin=True,
                is_active=True
            )
            admin.set_password(admin_password)
            db.session.add(admin)
            db.session.commit()
            logger.info(f'Default admin user created: {admin_email}')
        
        logger.info('Database initialized.')


# ==================== MAIN ====================

if __name__ == '__main__':
    # Initialize database on first run
    init_db()
    
    # Run development server (use gunicorn in production)
    if app.config.get('DEBUG'):
        app.run(
            debug=True,
            host='127.0.0.1',
            port=int(os.getenv('FLASK_PORT', 5000))
        )
    else:
        # In production, use gunicorn instead
        logger.warning('Running in production mode. Use gunicorn for production deployment.')
        app.run(
            debug=False,
            host='0.0.0.0',
            port=int(os.getenv('FLASK_PORT', 5000))
        )
