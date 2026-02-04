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
    from content_data import COURSE_SEED_DATA, SERVICES_LIST, INTERNSHIPS_LIST, ABOUT_VALUES, BLOG_POSTS
    import markdown
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

# Register markdown filter
@app.template_filter('markdown')
def render_markdown(text):
    return markdown.markdown(text)

# Load configuration first
try:
    from config import get_config
    app.config.from_object(get_config())
    print("[OK] Configuration loaded successfully", file=sys.stdout, flush=True)
except Exception as e:
    print(f"✗ ERROR loading config: {e}", file=sys.stderr, flush=True)
    traceback.print_exc(file=sys.stderr)
    raise

# Add Whitenoise for serving static files in production
try:
    from whitenoise import WhiteNoise
    static_dir = os.path.join(os.path.dirname(__file__), 'static')
    app.wsgi_app = WhiteNoise(app.wsgi_app, root=static_dir, max_age=31536000)
    print(f"[OK] WhiteNoise initialized successfully with static dir: {static_dir}", file=sys.stdout, flush=True)
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
    """User/Student/Trainer Model"""
    __tablename__ = 'users'
    
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(120), unique=True, nullable=False, index=True)
    password_hash = db.Column(db.String(255), nullable=False)
    name = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(15), nullable=True)
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    role = db.Column(db.String(20), default='student')  # student, trainer, admin
    is_active = db.Column(db.Boolean, default=True)
    reset_token = db.Column(db.String(255), nullable=True)
    reset_token_time = db.Column(db.Integer, nullable=True)
    
    # Trainer-specific fields
    education = db.Column(db.String(255), nullable=True)  # Educational qualification
    expertise = db.Column(db.String(500), nullable=True)  # Areas of expertise
    experience = db.Column(db.Integer, nullable=True)      # Years of experience
    bio = db.Column(db.Text, nullable=True)                # Short biography
    is_approved = db.Column(db.Boolean, default=True)      # Trainers need approval
    
    # Relationships
    enrollments = db.relationship('Enrollment', backref='student', lazy=True, cascade='all, delete-orphan')
    
    def set_password(self, password):
        """Hash and set password"""
        self.password_hash = generate_password_hash(password)
    
    def check_password(self, password):
        """Verify password against hash"""
        return check_password_hash(self.password_hash, password)
    
    @property
    def is_admin(self):
        """Check if user is admin (backward compatibility)"""
        return self.role == 'admin'
    
    @property
    def is_trainer(self):
        """Check if user is trainer"""
        return self.role == 'trainer'
    
    @property
    def is_student(self):
        """Check if user is student"""
        return self.role == 'student'
    
    @property
    def is_pending_approval(self):
        """Check if trainer is pending approval"""
        return self.role == 'trainer' and not self.is_approved
    
    def __repr__(self):
        return f'<User {self.email}>'


class Course(db.Model):
    """Course Model"""
    __tablename__ = 'courses'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(200), nullable=False)
    description = db.Column(db.Text, nullable=True)
    summary = db.Column(db.String(200), nullable=True)  # Short summary for course cards
    duration = db.Column(db.String(50), nullable=True)
    price = db.Column(db.Float, nullable=False)
    level = db.Column(db.String(50), nullable=True)
    image = db.Column(db.String(200), nullable=True)
    curriculum = db.Column(db.Text, nullable=True)  # JSON string of modules
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    
    # Trainer assignment
    trainer_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True, index=True)
    trainer = db.relationship('User', backref=db.backref('assigned_courses', lazy=True))
    
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


class CourseReview(db.Model):
    """Course Reviews and Ratings"""
    __tablename__ = 'course_reviews'
    
    id = db.Column(db.Integer, primary_key=True)
    course_id = db.Column(db.Integer, db.ForeignKey('courses.id'), nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    rating = db.Column(db.Integer, nullable=False)  # 1-5 stars
    review_text = db.Column(db.Text, nullable=True)
    is_approved = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    course = db.relationship('Course', backref='reviews', lazy=True)
    user = db.relationship('User', backref='course_reviews', lazy=True)
    
    def __repr__(self):
        return f'<CourseReview {self.user.email} -> {self.course.name} ({self.rating} stars)>'


class Blog(db.Model):
    """Blog Model"""
    __tablename__ = 'blogs'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(200), nullable=False)
    slug = db.Column(db.String(200), unique=True, nullable=False)
    excerpt = db.Column(db.String(500), nullable=False)
    content = db.Column(db.Text, nullable=False)
    image = db.Column(db.String(200), nullable=True)
    author = db.Column(db.String(100), nullable=False)
    created_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    is_published = db.Column(db.Boolean, default=True)
    
    def __repr__(self):
        return f'<Blog {self.title}>'


class NewsletterSubscriber(db.Model):
    """Newsletter Subscriber Model"""
    __tablename__ = 'newsletter_subscribers'
    
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(120), unique=True, nullable=False)
    subscribed_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    is_active = db.Column(db.Boolean, default=True)
    
    def __repr__(self):
        return f'<NewsletterSubscriber {self.email}>'


# ==================== FLASK-LOGIN HELPER ====================

@login_manager.user_loader
def load_user(user_id):
    """Load user by ID for Flask-Login"""
    return User.query.get(int(user_id))


# ==================== SEED DATA FUNCTION ====================

def seed_courses(force=False):
    """Initialize database with course data"""
    if not force and Course.query.first() is not None:
        return  # Database already seeded
    
    import json
    
    courses = []
    
    # Loop through the imported seed data
    for course_data in COURSE_SEED_DATA:
        courses.append(Course(
            name=course_data['name'],
            duration=course_data['duration'],
            summary=course_data['summary'],
            description=course_data['description'],
            price=course_data['price'],
            level=course_data['level'],
            image=course_data['image'],
            curriculum=json.dumps(course_data['curriculum'])
        ))
    
    for course in courses:
        db.session.add(course)
    
    db.session.commit()
    logger.info('Database seeded with 9 comprehensive courses.')


# ==================== DATABASE INITIALIZATION ====================

def init_db_on_startup():
    """Initialize database and create tables on app startup"""
    with app.app_context():
        try:
            # Create all tables (if they don't exist)
            # SQLAlchemy create_all() is supposed to be idempotent, but we catch any errors
            db.create_all()
            logger.info("[OK] Database tables created/verified")
            
            # Migration: Add summary column if it doesn't exist (for existing databases)
            try:
                from sqlalchemy import text
                with db.engine.connect() as conn:
                    # Check if summary column exists
                    result = conn.execute(text("PRAGMA table_info(courses)"))
                    columns = [row[1] for row in result.fetchall()]
                    if 'summary' not in columns:
                        conn.execute(text("ALTER TABLE courses ADD COLUMN summary VARCHAR(200)"))
                        conn.commit()
                        logger.info("[OK] Added 'summary' column to courses table")
            except Exception as migration_error:
                logger.warning(f"Migration check for summary column: {migration_error}")
            
            # Update existing course descriptions and summaries with new formatted content
            try:
                import json
                course_updates = {
                    'Full Stack Development': {
                        'summary': "Master end-to-end web development from frontend to backend with hands-on projects.",
                        'description': """Become a complete web developer mastering both frontend and backend technologies.<br><br>

<b>Course Overview</b><br>
Build end-to-end web applications from database design to user interface. Master HTML/CSS/JavaScript on the frontend, Node.js/Python on the backend, and manage databases with SQL/MongoDB.<br><br>

<b>What You'll Learn</b><br>
• <b>Frontend:</b> HTML5, CSS3, JavaScript ES6+, React.js, Redux<br>
• <b>Backend:</b> Node.js/Express.js, RESTful API design, Authentication<br>
• <b>Databases:</b> SQL (PostgreSQL), MongoDB, Database optimization<br>
• <b>DevOps:</b> Docker, CI/CD pipelines, Git workflows<br>
• <b>Cloud:</b> Deployment on AWS/Heroku, Payment integration<br><br>

<b>Who Should Enroll</b><br>
Aspiring web developers, career changers, professionals advancing from frontend/backend-only roles, entrepreneurs building web products.<br><br>

<b>Duration:</b> 4 Months (200+ hours) | <b>Prerequisites:</b> Basic programming knowledge"""
                    },
                    'Programming with Python': {
                        'summary': "Learn Python from basics to advanced with real-world automation projects.",
                        'description': """Master Python programming from fundamentals to advanced concepts.<br><br>

<b>Course Overview</b><br>
Learn Python syntax, OOP, data structures, and popular libraries. Build automation tools, data processing scripts, and intelligent applications.<br><br>

<b>What You'll Learn</b><br>
• <b>Fundamentals:</b> Syntax, variables, data types, control flow<br>
• <b>OOP:</b> Classes, inheritance, polymorphism, encapsulation<br>
• <b>Data Structures:</b> Lists, dictionaries, sets, tuples<br>
• <b>Libraries:</b> NumPy, Pandas, Requests, BeautifulSoup<br>
• <b>Projects:</b> Web scraper, automation tool, data processor<br><br>

<b>Who Should Enroll</b><br>
Beginners starting their programming journey, students, professionals wanting to add Python to their skillset.<br><br>

<b>Duration:</b> 3 Months (120+ hours) | <b>Prerequisites:</b> None"""
                    },
                    'Data Science from Scratch': {
                        'summary': "Transform raw data into insights using Python, statistics, and ML.",
                        'description': """Transform raw data into actionable business insights.<br><br>

<b>Course Overview</b><br>
Master data cleaning, exploration, visualization, and predictive modeling. Work with real datasets and solve business problems end-to-end.<br><br>

<b>What You'll Learn</b><br>
• <b>Python:</b> NumPy, Pandas, Matplotlib, Seaborn<br>
• <b>Statistics:</b> Probability, hypothesis testing, inference<br>
• <b>ML:</b> Regression, classification, clustering algorithms<br>
• <b>Visualization:</b> Charts, dashboards, storytelling with data<br>
• <b>Tools:</b> Jupyter, Scikit-learn, real-world datasets<br><br>

<b>Who Should Enroll</b><br>
Analysts wanting to upskill, engineers transitioning to data roles, business professionals seeking data-driven decision making.<br><br>

<b>Duration:</b> 4 Months (180+ hours) | <b>Prerequisites:</b> Basic Python knowledge"""
                    },
                    'Ethical Hacking and Penetration Testing': {
                        'summary': "Master cybersecurity through hands-on ethical hacking and pen testing.",
                        'description': """Learn to identify and fix security vulnerabilities professionally.<br><br>

<b>Course Overview</b><br>
Master networking, Linux, reconnaissance, scanning, and exploitation. Conduct real penetration tests and write professional security reports.<br><br>

<b>What You'll Learn</b><br>
• <b>Networking:</b> TCP/IP, protocols, packet analysis<br>
• <b>Linux:</b> System administration, command line mastery<br>
• <b>Reconnaissance:</b> Passive and active information gathering<br>
• <b>Exploitation:</b> Web app vulnerabilities, system exploits<br>
• <b>Tools:</b> Kali Linux, Burp Suite, Metasploit, Nmap<br><br>

<b>Who Should Enroll</b><br>
IT professionals, network admins, security enthusiasts, anyone interested in cybersecurity careers.<br><br>

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic networking knowledge"""
                    },
                    'Crash Course in Computer Science': {
                        'summary': "Essential foundation covering how computers work from hardware to software.",
                        'description': """Build a solid foundation for all tech careers.<br><br>

<b>Course Overview</b><br>
Learn how computers work from hardware to software. Master binary, circuits, algorithms, data structures, operating systems, and networking concepts.<br><br>

<b>What You'll Learn</b><br>
• <b>Hardware:</b> Binary, logic gates, CPUs, memory<br>
• <b>Algorithms:</b> Complexity analysis, searching, sorting<br>
• <b>Data Structures:</b> Arrays, linked lists, trees, graphs<br>
• <b>Operating Systems:</b> Processes, memory management<br>
• <b>Networking:</b> Internet fundamentals, protocols<br><br>

<b>Who Should Enroll</b><br>
Complete beginners, students starting CS education, anyone wanting to understand how computers work.<br><br>

<b>Duration:</b> 2 Months (80+ hours) | <b>Prerequisites:</b> None"""
                    },
                    'Machine Learning and AI Foundations': {
                        'summary': "Build intelligent systems using ML algorithms, neural networks, and deep learning.",
                        'description': """Build intelligent systems that learn and improve.<br><br>

<b>Course Overview</b><br>
Master ML algorithms, neural networks, deep learning, NLP, and computer vision. Deploy predictive models in production environments.<br><br>

<b>What You'll Learn</b><br>
• <b>ML Basics:</b> Regression, classification, clustering<br>
• <b>Neural Networks:</b> Perceptrons, backpropagation, CNNs, RNNs<br>
• <b>Deep Learning:</b> TensorFlow, PyTorch frameworks<br>
• <b>NLP:</b> Text processing, sentiment analysis, chatbots<br>
• <b>Computer Vision:</b> Image classification, object detection<br><br>

<b>Who Should Enroll</b><br>
Developers wanting AI skills, data scientists expanding into ML, researchers and students in AI/ML.<br><br>

<b>Duration:</b> 4 Months (200+ hours) | <b>Prerequisites:</b> Python, basic math/statistics"""
                    },
                    'Data Analytics and BI Tools': {
                        'summary': "Transform business data into insights using Excel, SQL, Tableau, and Power BI.",
                        'description': """Turn business data into strategic insights and decisions.<br><br>

<b>Course Overview</b><br>
Master Excel, SQL, Tableau, and Power BI. Create dashboards, analyze data, and present insights to executives effectively.<br><br>

<b>What You'll Learn</b><br>
• <b>Excel:</b> Advanced functions, pivot tables, macros<br>
• <b>SQL:</b> Complex queries, optimization, data extraction<br>
• <b>Tableau:</b> Visualizations, interactive dashboards<br>
• <b>Power BI:</b> Data modeling, DAX formulas, reports<br>
• <b>Storytelling:</b> Executive presentations, data narratives<br><br>

<b>Who Should Enroll</b><br>
Business analysts, managers needing data skills, professionals in marketing/finance/operations.<br><br>

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic Excel knowledge"""
                    },
                    'Android App Development': {
                        'summary': "Build production-ready Android apps with Kotlin and publish to Play Store.",
                        'description': """Create professional Android applications from scratch.<br><br>

<b>Course Overview</b><br>
Master Kotlin and modern Android frameworks. Build feature-rich apps with material design, networking, databases, and publish to Play Store.<br><br>

<b>What You'll Learn</b><br>
• <b>Kotlin:</b> Modern Android programming language<br>
• <b>UI:</b> Material Design, layouts, fragments, animations<br>
• <b>Data:</b> SQLite, Room database, SharedPreferences<br>
• <b>Networking:</b> Retrofit, REST APIs, JSON parsing<br>
• <b>Publishing:</b> Play Store submission, monetization<br><br>

<b>Who Should Enroll</b><br>
Aspiring mobile developers, Java/Kotlin programmers, entrepreneurs wanting to build Android apps.<br><br>

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic programming knowledge"""
                    },
                    'MS Office Automation and AI Tools': {
                        'summary': "Automate tasks and boost productivity with VBA, Power Automate, and AI.",
                        'description': """Automate repetitive tasks and supercharge productivity.<br><br>

<b>Course Overview</b><br>
Master Excel VBA, Power Automate, Power Query, and AI tool integration. Build custom solutions that reduce manual work by 70-80%.<br><br>

<b>What You'll Learn</b><br>
• <b>Excel VBA:</b> Macros, automation, custom functions<br>
• <b>Power Automate:</b> Cloud workflows, connectors, triggers<br>
• <b>Power Query:</b> Data transformation, ETL processes<br>
• <b>AI Tools:</b> ChatGPT, Copilot, GPT API integration<br>
• <b>Office Suite:</b> Teams, Outlook, Access automation<br><br>

<b>Who Should Enroll</b><br>
Office workers, admins, managers wanting to automate daily tasks, anyone looking to boost productivity.<br><br>

<b>Duration:</b> 2.5 Months (100+ hours) | <b>Prerequisites:</b> Basic Excel knowledge"""
                    }
                }
                
                for name, data in course_updates.items():
                    course = Course.query.filter_by(name=name).first()
                    if course:
                        course.description = data['description']
                        course.summary = data['summary']
                
                db.session.commit()
                logger.info("[OK] Course descriptions updated with new format")
            except Exception as update_error:
                logger.warning(f"Course description update: {update_error}")
            
            # Check if courses are incomplete or have wrong data
            course_count = Course.query.count()
            
            # Define what courses should exist
            required_course_names = {
                'Full Stack Development',
                'Programming with Python',
                'Data Science from Scratch',
                'Ethical Hacking and Penetration Testing',
                'Crash Course in Computer Science',
                'Machine Learning and AI Foundations',
                'Data Analytics and BI Tools',
                'Android App Development',
                'MS Office Automation and AI Tools',
            }
            
            # Expected valid image filenames
            valid_images = {
                'fsd.jpg',
                'pp.jpg',
                'ds.jpg',
                'EHPT.jpg',
                'ccc.jpg',
                'maf.jpg',
                'dabt.jpg',
                'aad.jpg',
                'moaat.jpg',
            }
            
            # Get actual course data from DB
            actual_courses = Course.query.all()
            actual_course_names = {c.name for c in actual_courses}
            actual_images = {c.image for c in actual_courses if c.image}
            # Define expected course data
            expected_courses = {
                'Full Stack Development': (1499, 'Advanced'),
                'Programming with Python': (999, 'Intermediate'),
                'Data Science from Scratch': (1499, 'Advanced'),
                'Ethical Hacking and Penetration Testing': (1499, 'Advanced'),
                'Crash Course in Computer Science': (499, 'Foundational'),
                'Machine Learning and AI Foundations': (999, 'Intermediate'),
                'Data Analytics and BI Tools': (1499, 'Advanced'),
                'Android App Development': (1499, 'Advanced'),
                'MS Office Automation and AI Tools': (499, 'Foundational'),
            }
            
            # Determine if database needs fixing
            # Fix if: wrong count, wrong names, invalid images, OR wrong prices/levels
            needs_fix = (
                course_count != 9 or 
                actual_course_names != required_course_names or
                not actual_images.issubset(valid_images)
            )
            
            # Also check if any course has wrong price or level
            if not needs_fix:
                for course in actual_courses:
                    if course.name in expected_courses:
                        exp_price, exp_level = expected_courses[course.name]
                        if course.price != exp_price or course.level != exp_level:
                            needs_fix = True
                            logger.warning(f"⚠️  Wrong pricing/level detected for {course.name}: Rs.{course.price} {course.level} (expected Rs.{exp_price} {exp_level})")
                            break
            
            if needs_fix:
                logger.warning(f"⚠️  Database inconsistent - triggering fix")
                logger.warning(f"    Course count: {course_count} (expected 9)")
                logger.warning(f"    Expected names: {required_course_names}")
                logger.warning(f"    Found names: {actual_course_names}")
                logger.warning(f"    Found images: {actual_images}")
                logger.warning(f"    Valid images: {valid_images}")
                logger.warning(f"    Fixing database...")
                
                # Delete all courses and reseed
                Course.query.delete()
                CourseReview.query.delete()  # Also delete reviews to keep data consistent
                db.session.commit()
                logger.info("[OK] Cleared all course and review data")
                
                # Call seed_courses to create all 9 courses (force=True to bypass the check)
                seed_courses(force=True)
                logger.info("[OK] Database reseeded with all 9 comprehensive courses")
            else:
                logger.info(f"[OK] Database verified: {course_count} courses with valid images")
            
            # Final verification
            final_count = Course.query.count()
            final_images = {c.image for c in Course.query.all() if c.image}
            logger.info(f"[OK] Database init complete: {final_count} courses, images: {final_images}")
            db.session.commit()
        except Exception as e:
            error_str = str(e).lower()
            # Ignore "table already exists" errors - happens when multiple workers initialize
            if "already exists" in error_str or "table" in error_str and "exists" in error_str:
                logger.info(f"✓ Database tables already exist (expected in multi-worker setup)")
                print(f"[OK] Database tables already exist (expected in multi-worker setup)", file=sys.stdout, flush=True)
            else:
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
            print("[OK] Database initialized successfully on first request", file=sys.stdout, flush=True)
        except Exception as e:
            print(f"✗ ERROR during database initialization: {e}", file=sys.stderr, flush=True)
            traceback.print_exc(file=sys.stderr)

# Also try to initialize at startup but don't block if it fails
try:
    init_db_on_startup()
    db_initialized = True
    print("[OK] Database initialized successfully at startup", file=sys.stdout, flush=True)
except Exception as e:
    print(f"⚠ Database initialization deferred to first request: {e}", file=sys.stderr, flush=True)



# App startup complete message
print("=" * 50, file=sys.stdout, flush=True)
print("[OK] FLASK APP INITIALIZATION COMPLETE", file=sys.stdout, flush=True)
print("[OK] Ready to handle requests", file=sys.stdout, flush=True)
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


def trainer_required(f):
    """Decorator to require trainer role"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or not current_user.is_trainer:
            flash('You do not have permission to access this page.', 'danger')
            return redirect(url_for('home'))
        return f(*args, **kwargs)
    return decorated_function



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
            'facebook': os.getenv('FACEBOOK_URL', 'https://www.facebook.com/share/184mEoARX8/'),
            'linkedin': os.getenv('LINKEDIN_URL', 'https://www.linkedin.com/company/the-coding-science/')
        }
    }


# ==================== PUBLIC ROUTES ====================
@app.route('/')
def home():
    """Homepage"""
    try:
        featured_courses = Course.query.limit(4).all()
        return render_template('index.html', courses=featured_courses, blogs=BLOG_POSTS)
    except Exception as e:
        logger.error(f"Error loading home page: {str(e)}")
        return jsonify({'status': 'error', 'message': 'Unable to load home page'}), 500


@app.route('/blog')
def blog():
    """Blog listing page"""
    blogs = Blog.query.filter_by(is_published=True).order_by(Blog.created_at.desc()).all()
    return render_template('blog.html', blogs=blogs)


@app.route('/blog/<int:blog_id>')
def blog_detail(blog_id):
    """Individual blog post page"""
    post = Blog.query.get_or_404(blog_id)
    
    # Check publication status
    if not post.is_published:
        # Allow admin/author to preview
        if not current_user.is_authenticated or not current_user.is_admin:
            return render_template('404.html'), 404
            
    recent_posts = Blog.query.filter_by(is_published=True).filter(Blog.id != blog_id).order_by(Blog.created_at.desc()).limit(3).all()
    return render_template('blog_detail.html', blog=post, recent_posts=recent_posts)




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
    
    return render_template('about.html', stats=stats, values=ABOUT_VALUES)


@app.route('/services')
def services():
    """Services page - Course Categories"""
    return render_template('services.html', services=SERVICES_LIST)


@app.route('/internships')
def internships():
    """Internships page"""
    return render_template('internships.html', internships=INTERNSHIPS_LIST)






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


# ==================== COURSE REVIEWS ====================

@app.route('/course/<int:course_id>/review', methods=['POST'])
@login_required
def submit_review(course_id):
    """Submit a course review"""
    course = Course.query.get_or_404(course_id)
    
    try:
        # Check if user is enrolled in the course
        is_enrolled = Enrollment.query.filter_by(
            user_id=current_user.id,
            course_id=course_id,
            status='completed'
        ).first()
        
        if not is_enrolled:
            return jsonify({
                'status': 'error',
                'message': 'You must be enrolled in this course to leave a review.'
            }), 403
        
        # Check if user already reviewed this course
        existing_review = CourseReview.query.filter_by(
            user_id=current_user.id,
            course_id=course_id
        ).first()
        
        if existing_review:
            return jsonify({
                'status': 'error',
                'message': 'You have already reviewed this course. You can edit your review.'
            }), 400
        
        # Validate input
        rating = request.json.get('rating')
        review_text = request.json.get('review_text', '').strip()
        
        if not rating or rating < 1 or rating > 5:
            return jsonify({
                'status': 'error',
                'message': 'Rating must be between 1 and 5 stars.'
            }), 400
        
        if len(review_text) > 1000:
            return jsonify({
                'status': 'error',
                'message': 'Review text is too long (maximum 1000 characters).'
            }), 400
        
        # Create review
        review = CourseReview(
            course_id=course_id,
            user_id=current_user.id,
            rating=int(rating),
            review_text=review_text if review_text else None,
            is_approved=False  # Require moderation
        )
        
        db.session.add(review)
        db.session.commit()
        
        logger.info(f'Course review submitted: {current_user.email} -> {course.name} ({rating} stars)')
        
        return jsonify({
            'status': 'success',
            'message': 'Review submitted successfully! It will appear after moderation.'
        })
    
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error submitting review: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while submitting your review.'
        }), 500


@app.route('/course/<int:course_id>/reviews', methods=['GET'])
def get_course_reviews(course_id):
    """Get approved reviews for a course"""
    course = Course.query.get_or_404(course_id)
    
    try:
        page = request.args.get('page', 1, type=int)
        reviews = CourseReview.query.filter_by(
            course_id=course_id,
            is_approved=True
        ).order_by(CourseReview.created_at.desc()).paginate(page=page, per_page=5)
        
        # Calculate average rating
        all_reviews = CourseReview.query.filter_by(
            course_id=course_id,
            is_approved=True
        ).all()
        
        avg_rating = 0
        total_reviews = len(all_reviews)
        if total_reviews > 0:
            avg_rating = round(sum(r.rating for r in all_reviews) / total_reviews, 1)
        
        reviews_data = [{
            'id': r.id,
            'user_name': r.user.name,
            'rating': r.rating,
            'review_text': r.review_text,
            'created_at': r.created_at.strftime('%Y-%m-%d')
        } for r in reviews.items]
        
        return jsonify({
            'status': 'success',
            'reviews': reviews_data,
            'average_rating': avg_rating,
            'total_reviews': total_reviews,
            'current_page': page,
            'total_pages': reviews.pages
        })
    
    except Exception as e:
        logger.error(f'Error fetching reviews: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while fetching reviews.'
        }), 500


@app.route('/review/<int:review_id>/edit', methods=['PUT'])
@login_required
def edit_review(review_id):
    """Edit own review"""
    review = CourseReview.query.get_or_404(review_id)
    
    # Check ownership
    if review.user_id != current_user.id and not current_user.is_admin:
        return jsonify({
            'status': 'error',
            'message': 'You can only edit your own review.'
        }), 403
    
    try:
        rating = request.json.get('rating')
        review_text = request.json.get('review_text', '').strip()
        
        if rating and (rating < 1 or rating > 5):
            return jsonify({
                'status': 'error',
                'message': 'Rating must be between 1 and 5 stars.'
            }), 400
        
        if len(review_text) > 1000:
            return jsonify({
                'status': 'error',
                'message': 'Review text is too long (maximum 1000 characters).'
            }), 400
        
        if rating:
            review.rating = int(rating)
        if review_text:
            review.review_text = review_text
        
        review.is_approved = False  # Require re-approval after edit
        db.session.commit()
        
        logger.info(f'Review edited: {review_id} by {current_user.email}')
        
        return jsonify({
            'status': 'success',
            'message': 'Review updated successfully! It will reappear after moderation.'
        })
    
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error editing review: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while editing your review.'
        }), 500


@app.route('/review/<int:review_id>/delete', methods=['DELETE'])
@login_required
def delete_review(review_id):
    """Delete own review"""
    review = CourseReview.query.get_or_404(review_id)
    
    # Check ownership
    if review.user_id != current_user.id and not current_user.is_admin:
        return jsonify({
            'status': 'error',
            'message': 'You can only delete your own review.'
        }), 403
    
    try:
        course_name = review.course.name
        db.session.delete(review)
        db.session.commit()
        
        logger.info(f'Review deleted: {review_id}')
        
        return jsonify({
            'status': 'success',
            'message': f'Your review for {course_name} has been deleted.'
        })
    
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting review: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while deleting your review.'
        }), 500


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
    """User registration route - handles both student and trainer registration"""
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        name = request.form.get('name', '').strip()
        email = request.form.get('email', '').strip().lower()
        phone = request.form.get('phone', '').strip()
        password = request.form.get('password', '')
        confirm_password = request.form.get('confirm_password', '')
        register_role = request.form.get('register_role', 'student')
        
        # Trainer-specific fields
        education = request.form.get('education', '').strip()
        expertise = request.form.get('expertise', '').strip()
        experience = request.form.get('experience', '')
        bio = request.form.get('bio', '').strip()
        
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
        
        if len(password) < 6:
            flash('Password must be at least 6 characters long.', 'danger')
            return redirect(url_for('register'))
        
        # Validate phone format (basic validation)
        if phone and not re.match(r'^\+?1?\d{9,15}$', phone):
            flash('Please enter a valid phone number.', 'danger')
            return redirect(url_for('register'))
        
        # Trainer-specific validation
        if register_role == 'trainer':
            if not (education and expertise and experience):
                flash('Please fill in all trainer information fields.', 'danger')
                return redirect(url_for('register'))
        
        # Check if user exists
        if User.query.filter_by(email=email).first():
            flash('Email already registered. Please log in.', 'info')
            next_page = request.args.get('next', '')
            return redirect(url_for('login', next=next_page))
        
        try:
            # Create new user
            user = User(
                name=name, 
                email=email, 
                phone=phone,
                role=register_role if register_role in ['student', 'trainer'] else 'student'
            )
            user.set_password(password)
            
            # Set trainer-specific fields
            if register_role == 'trainer':
                user.education = education
                user.expertise = expertise
                user.experience = int(experience) if experience else 0
                user.bio = bio
                user.is_approved = False  # Trainers need admin approval
            
            db.session.add(user)
            db.session.commit()
            
            logger.info(f'New {register_role} registered: {email}')
            
            if register_role == 'trainer':
                flash('Trainer account created! Your account is pending admin approval.', 'info')
            else:
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
    """User login route with role-based authentication"""
    if current_user.is_authenticated:
        # Redirect based on user's role
        if current_user.is_admin:
            return redirect(url_for('admin_panel'))
        elif current_user.is_trainer:
            return redirect(url_for('trainer_dashboard'))
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        email = request.form.get('email', '').strip().lower()
        password = request.form.get('password', '')
        selected_role = request.form.get('login_role', 'student')
        
        if not (email and password):
            flash('Please fill in all required fields.', 'danger')
            return redirect(url_for('login'))
        
        if not is_valid_email(email):
            flash('Invalid email or password.', 'danger')
            logger.warning(f'Login attempt with invalid email format: {email}')
            return redirect(url_for('login'))
        
        user = User.query.filter_by(email=email).first()
        
        if user and user.check_password(password) and user.is_active:
            # Verify role matches
            if user.role != selected_role:
                flash(f'This account is registered as a {user.role}. Please select the correct role.', 'warning')
                return redirect(url_for('login'))
            
            login_user(user, remember=request.form.get('remember'))
            logger.info(f'User logged in: {email} (role: {user.role})')
            
            # Redirect to requested page or appropriate dashboard
            next_page = request.args.get('next')
            if next_page and next_page.startswith('/'):
                return redirect(next_page)
            
            # Role-based redirect
            if user.is_admin:
                return redirect(url_for('admin_panel'))
            elif user.is_trainer:
                return redirect(url_for('trainer_dashboard'))
            else:
                return redirect(url_for('dashboard'))
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


@app.route('/forgot-password', methods=['GET', 'POST'])
def forgot_password():
    """Forgot password route - sends reset email"""
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        email = request.form.get('email', '').strip().lower()
        
        if not email:
            return render_template('forgot_password.html', error='Please enter your email address')
        
        user = User.query.filter_by(email=email).first()
        
        if user:
            # Generate reset token (using UUID)
            reset_token = str(uuid.uuid4())
            # Store token in cache/session with 30 minute expiry
            import time
            user.reset_token = reset_token
            user.reset_token_time = int(time.time())
            db.session.commit()
            
            reset_link = url_for('reset_password', token=reset_token, _external=True)
            
            html_body = f"""
            <html>
                <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h2 style="color: #007bff; text-align: center;">Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset your password. Click the link below to proceed:</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{reset_link}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                                Reset Your Password
                            </a>
                        </div>
                        <p>Or copy and paste this link in your browser:</p>
                        <p style="word-break: break-all; color: #666;">{reset_link}</p>
                        <p style="color: #999; font-size: 12px;">This link will expire in 30 minutes.</p>
                        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                        <p style="color: #666; font-size: 12px;">If you didn't request a password reset, please ignore this email.</p>
                        <p style="color: #666; font-size: 12px;">Best regards,<br><strong>{app.config.get('SITE_NAME', 'The Coding Science')}</strong></p>
                    </div>
                </body>
            </html>
            """
            
            email_sent = send_email(email, "Password Reset Request - The Coding Science", html_body)
            if email_sent:
                logger.info(f'Password reset email sent to: {email}')
            else:
                logger.error(f'Failed to send password reset email to: {email}')
                # Revert the token since email failed
                user.reset_token = None
                user.reset_token_time = None
                db.session.commit()
                return render_template('forgot_password.html', error='Unable to send email. Please check your email configuration or try again later.')
        
        # Don't reveal whether email exists or not (security best practice)
        return render_template('forgot_password.html', message='If an account exists with that email, you will receive a password reset link shortly.')
    
    return render_template('forgot_password.html')


@app.route('/reset-password/<token>', methods=['GET', 'POST'])
def reset_password(token):
    """Reset password route"""
    if current_user.is_authenticated:
        return redirect(url_for('dashboard'))
    
    # Find user by reset token
    user = User.query.filter_by(reset_token=token).first()
    
    if not user:
        return render_template('reset_password.html', error='Invalid or expired reset link.')
    
    # Check if token expired (30 minutes)
    import time
    current_time = int(time.time())
    token_age = current_time - (user.reset_token_time or 0)
    
    if token_age > 1800:  # 30 minutes
        user.reset_token = None
        user.reset_token_time = None
        db.session.commit()
        return render_template('reset_password.html', error='Reset link has expired. Please request a new one.')
    
    if request.method == 'POST':
        password = request.form.get('password', '')
        confirm_password = request.form.get('confirm_password', '')
        
        if not password or not confirm_password:
            return render_template('reset_password.html', error='Please fill in all fields.')
        
        if password != confirm_password:
            return render_template('reset_password.html', error='Passwords do not match.')
        
        if len(password) < 6:
            return render_template('reset_password.html', error='Password must be at least 6 characters.')
        
        # Update password
        user.set_password(password)
        user.reset_token = None
        user.reset_token_time = None
        db.session.commit()
        
        logger.info(f'Password reset successfully for: {user.email}')
        flash('Your password has been reset successfully. Please log in with your new password.', 'success')
        return redirect(url_for('login'))
    
    return render_template('reset_password.html')


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


@app.route('/trainer/dashboard')
@login_required
@trainer_required
def trainer_dashboard():
    """Trainer dashboard showing assigned courses and students"""
    # Get only courses assigned to this trainer
    assigned_courses = Course.query.filter_by(trainer_id=current_user.id).all()
    
    # Count total students in trainer's assigned courses
    if assigned_courses:
        course_ids = [c.id for c in assigned_courses]
        total_students = Enrollment.query.filter(
            Enrollment.course_id.in_(course_ids),
            Enrollment.status == 'completed'
        ).count()
    else:
        total_students = 0
    
    # Count pending reviews for trainer's courses
    if assigned_courses:
        pending_reviews = CourseReview.query.filter(
            CourseReview.course_id.in_(course_ids),
            CourseReview.is_approved == False
        ).count()
    else:
        pending_reviews = 0
    
    return render_template(
        'trainer_dashboard.html',
        assigned_courses=assigned_courses,
        total_students=total_students,
        pending_reviews=pending_reviews
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


@app.route('/subscribe', methods=['POST'])
def subscribe():
    """Newsletter subscription route"""
    email = request.form.get('email')
    
    if not email or not is_valid_email(email):
        flash('Please enter a valid email address.', 'danger')
        return redirect(request.referrer)
    
    # Check if already subscribed
    if NewsletterSubscriber.query.filter_by(email=email).first():
        flash('You are already subscribed to our newsletter!', 'info')
        return redirect(request.referrer)
        
    try:
        subscriber = NewsletterSubscriber(email=email)
        db.session.add(subscriber)
        db.session.commit()
        flash('Successfully subscribed to the newsletter!', 'success')
        
        # Optional: Send welcome email to subscriber
        # send_newsletter_welcome_email(email) 
        
    except Exception as e:
        db.session.rollback()
        logger.error(f'Newsletter subscription error: {str(e)}')
        flash('An error occurred. Please try again later.', 'danger')
        
    return redirect(request.referrer)


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
    """List all users with optional role filter"""
    page = request.args.get('page', 1, type=int)
    role_filter = request.args.get('role', None)
    per_page = 10
    
    # Build query with optional role filter
    query = User.query
    if role_filter and role_filter in ['student', 'trainer', 'admin']:
        query = query.filter_by(role=role_filter)
    
    students = query.order_by(User.created_at.desc()).paginate(
        page=page,
        per_page=per_page
    )
    
    return render_template('admin_students.html', students=students, role_filter=role_filter)


@app.route('/admin/trainer/<int:user_id>/approve', methods=['POST'])
@login_required
@admin_required
def admin_approve_trainer(user_id):
    """Approve a pending trainer account"""
    user = User.query.get_or_404(user_id)
    
    if user.role != 'trainer':
        return jsonify({'status': 'error', 'message': 'User is not a trainer.'}), 400
    
    if user.is_approved:
        return jsonify({'status': 'error', 'message': 'Trainer is already approved.'}), 400
    
    try:
        user.is_approved = True
        db.session.commit()
        
        logger.info(f'Trainer {user.email} approved by admin {current_user.email}')
        return jsonify({
            'status': 'success',
            'message': f'Trainer {user.name} has been approved!'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error approving trainer: {str(e)}')
        return jsonify({'status': 'error', 'message': 'An error occurred while approving the trainer.'}), 500


@app.route('/admin/user/<int:user_id>/role', methods=['POST'])
@login_required
@admin_required
def admin_change_user_role(user_id):
    """Change a user's role"""
    user = User.query.get_or_404(user_id)
    
    # Prevent changing own role
    if user.id == current_user.id:
        return jsonify({'status': 'error', 'message': 'Cannot change your own role.'}), 400
    
    data = request.get_json()
    new_role = data.get('role', '')
    
    if new_role not in ['student', 'trainer', 'admin']:
        return jsonify({'status': 'error', 'message': 'Invalid role specified.'}), 400
    
    try:
        old_role = user.role
        user.role = new_role
        db.session.commit()
        
        logger.info(f'User {user.email} role changed from {old_role} to {new_role} by admin {current_user.email}')
        return jsonify({
            'status': 'success', 
            'message': f'Role changed from {old_role} to {new_role} successfully!'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error changing user role: {str(e)}')
        return jsonify({'status': 'error', 'message': 'An error occurred while changing the role.'}), 500


@app.route('/admin/student/<int:user_id>')
@login_required
@admin_required
def admin_student_detail(user_id):
    """View user details and enrollments"""
    student = User.query.get_or_404(user_id)
    enrollments = Enrollment.query.filter_by(user_id=user_id).all()
    
    return render_template('admin_student_detail.html', student=student, enrollments=enrollments)


@app.route('/admin/student/<int:user_id>/delete', methods=['POST'])
@login_required
@admin_required
def admin_delete_student(user_id):
    """Delete a user and their enrollments"""
    student = User.query.get_or_404(user_id)
    
    # Prevent deleting self
    if student.id == current_user.id:
        return jsonify({'status': 'error', 'message': 'Cannot delete your own account.'}), 400
    
    try:
        email = student.email
        db.session.delete(student)
        db.session.commit()
        
        logger.info(f'User deleted by admin: {email}')
        flash(f'User {email} has been deleted.', 'success')
        return redirect(url_for('admin_students'))
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting user: {str(e)}')
        flash('An error occurred while deleting the user.', 'danger')
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


@app.route('/admin/message/<int:message_id>/delete', methods=['POST'])
@login_required
@admin_required
def admin_delete_message(message_id):
    """Delete a contact message"""
    message = ContactMessage.query.get_or_404(message_id)
    
    try:
        sender_email = message.email
        db.session.delete(message)
        db.session.commit()
        
        logger.info(f'Contact message deleted: {message_id} from {sender_email}')
        
        return jsonify({
            'status': 'success',
            'message': f'Message from {sender_email} has been deleted.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting message: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while deleting the message.'
        }), 500


@app.route('/admin/application/<int:application_id>/delete', methods=['POST'])
@login_required
@admin_required
def admin_delete_application(application_id):
    """Delete an internship application"""
    application = InternshipApplication.query.get_or_404(application_id)
    
    try:
        applicant_email = application.email
        applicant_role = application.internship_role
        db.session.delete(application)
        db.session.commit()
        
        logger.info(f'Internship application deleted: {application_id} from {applicant_email}')
        
        return jsonify({
            'status': 'success',
            'message': f'Application from {applicant_email} for {applicant_role} has been deleted.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error deleting application: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while deleting the application.'
        }), 500


# ==================== ADMIN BLOG ROUTES ====================

@app.route('/admin/blogs')
@login_required
@admin_required
def admin_blogs():
    """List all blogs"""
    page = request.args.get('page', 1, type=int)
    blogs = Blog.query.order_by(Blog.created_at.desc()).paginate(page=page, per_page=20)
    return render_template('admin_blogs.html', blogs=blogs.items, pagination=blogs)


@app.route('/admin/blogs/new', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_bloq_new():
    """Create new blog post"""
    if request.method == 'POST':
        title = request.form.get('title')
        content = request.form.get('content')
        excerpt = request.form.get('excerpt')
        author = request.form.get('author')
        is_published = request.form.get('is_published') == 'on'
        
        # Handle Image Upload
        image_filename = None
        if 'image' in request.files:
            file = request.files['image']
            if file and file.filename:
                from werkzeug.utils import secure_filename
                filename = secure_filename(file.filename)
                # Add timestamp to prevent duplicates
                filename = f"{int(datetime.utcnow().timestamp())}_{filename}"
                save_path = os.path.join(app.root_path, 'static', 'images', filename)
                file.save(save_path)
                image_filename = filename
        
        # Generate Slug
        slug = re.sub(r'[^a-z0-9\s-]', '', title.lower())
        slug = re.sub(r'[-\s]+', '-', slug).strip('-')
        
        # Check for unique slug
        if Blog.query.filter_by(slug=slug).first():
            slug = f"{slug}-{uuid.uuid4().hex[:4]}"
            
        blog = Blog(
            title=title,
            slug=slug,
            content=content,
            excerpt=excerpt,
            author=author,
            image=image_filename,
            is_published=is_published
        )
        db.session.add(blog)
        db.session.commit()
        flash('Blog post created successfully!', 'success')
        return redirect(url_for('admin_blogs'))
        
    return render_template('admin_blog_form.html', blog=None)


@app.route('/admin/blogs/edit/<int:blog_id>', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_blog_edit(blog_id):
    """Edit blog post"""
    blog = Blog.query.get_or_404(blog_id)
    
    if request.method == 'POST':
        blog.title = request.form.get('title')
        blog.content = request.form.get('content')
        blog.excerpt = request.form.get('excerpt')
        blog.author = request.form.get('author')
        blog.is_published = request.form.get('is_published') == 'on'
        
        if 'image' in request.files:
            file = request.files['image']
            if file and file.filename:
                from werkzeug.utils import secure_filename
                filename = secure_filename(file.filename)
                filename = f"{int(datetime.utcnow().timestamp())}_{filename}"
                save_path = os.path.join(app.root_path, 'static', 'images', filename)
                file.save(save_path)
                blog.image = filename
                
        db.session.commit()
        flash('Blog post updated successfully!', 'success')
        return redirect(url_for('admin_blogs'))
        
    return render_template('admin_blog_form.html', blog=blog)


@app.route('/admin/blogs/delete/<int:blog_id>', methods=['POST'])
@login_required
@admin_required
def admin_blog_delete(blog_id):
    """Delete blog post"""
    blog = Blog.query.get_or_404(blog_id)
    db.session.delete(blog)
    db.session.commit()
    flash('Blog post deleted.', 'success')
    return redirect(url_for('admin_blogs'))


@app.route('/admin/application/<int:application_id>/update-status', methods=['POST'])
@login_required
@admin_required
def admin_update_application_status(application_id):
    """Update internship application status"""
    application = InternshipApplication.query.get_or_404(application_id)
    
    try:
        new_status = request.json.get('status', '').strip()
        if new_status not in ['pending', 'accepted', 'rejected']:
            return jsonify({
                'status': 'error',
                'message': 'Invalid status.'
            }), 400
        
        application.status = new_status
        db.session.commit()
        
        logger.info(f'Application status updated: {application_id} -> {new_status}')
        
        return jsonify({
            'status': 'success',
            'message': f'Application status updated to {new_status}.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error updating application status: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while updating the application status.'
        }), 500


@app.route('/admin/trainers')
@login_required
@admin_required
def admin_trainers():
    """Manage trainers - approve, view, assign courses"""
    trainers = User.query.filter_by(role='trainer').order_by(User.created_at.desc()).all()
    courses = Course.query.all()
    return render_template('admin_trainers.html', trainers=trainers, courses=courses)


@app.route('/admin/trainer/<int:user_id>/courses')
@login_required
@admin_required
def get_trainer_courses(user_id):
    """Get courses assigned to a trainer"""
    trainer = User.query.get_or_404(user_id)
    course_ids = [course.id for course in trainer.assigned_courses]
    return jsonify({'courses': course_ids})


@app.route('/admin/trainer/<int:user_id>/assign-courses', methods=['POST'])
@login_required
@admin_required
def assign_courses_to_trainer(user_id):
    """Assign multiple courses to a trainer"""
    trainer = User.query.get_or_404(user_id)
    
    if trainer.role != 'trainer':
        return jsonify({'status': 'error', 'message': 'User is not a trainer.'}), 400
    
    data = request.get_json()
    course_ids = data.get('course_ids', [])
    
    try:
        # First, unassign all courses from this trainer
        Course.query.filter_by(trainer_id=user_id).update({'trainer_id': None})
        
        # Then assign selected courses
        for course_id in course_ids:
            course = Course.query.get(course_id)
            if course:
                course.trainer_id = user_id
        
        db.session.commit()
        logger.info(f'Courses assigned to trainer {trainer.email}: {course_ids}')
        return jsonify({
            'status': 'success',
            'message': f'{len(course_ids)} course(s) assigned to {trainer.name}'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error assigning courses: {str(e)}')
        return jsonify({'status': 'error', 'message': 'An error occurred.'}), 500


@app.route('/admin/course/<int:course_id>/assign-trainer', methods=['POST'])
@login_required
@admin_required
def assign_trainer_to_course(course_id):
    """Assign a trainer to a course"""
    course = Course.query.get_or_404(course_id)
    
    data = request.get_json()
    trainer_id = data.get('trainer_id')
    
    try:
        if trainer_id:
            trainer = User.query.get(trainer_id)
            if not trainer or trainer.role != 'trainer':
                return jsonify({'status': 'error', 'message': 'Invalid trainer.'}), 400
            course.trainer_id = trainer_id
            message = f'{course.name} assigned to {trainer.name}'
        else:
            course.trainer_id = None
            message = f'{course.name} unassigned'
        
        db.session.commit()
        return jsonify({'status': 'success', 'message': message})
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error assigning trainer to course: {str(e)}')
        return jsonify({'status': 'error', 'message': 'An error occurred.'}), 500


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


@app.route('/admin/reviews')
@login_required
@admin_required
def admin_reviews():
    """Manage and moderate course reviews"""
    pending_reviews = CourseReview.query.filter_by(is_approved=False).all()
    approved_reviews = CourseReview.query.filter_by(is_approved=True).all()
    
    return render_template(
        'admin_reviews.html',
        pending_reviews=pending_reviews,
        approved_reviews=approved_reviews,
        total_reviews=len(pending_reviews) + len(approved_reviews)
    )


@app.route('/admin/review/<int:review_id>/approve', methods=['POST'])
@login_required
@admin_required
def approve_review(review_id):
    """Approve a review"""
    review = CourseReview.query.get_or_404(review_id)
    
    try:
        review.is_approved = True
        db.session.commit()
        
        logger.info(f'Review approved: {review_id} by {current_user.email}')
        
        return jsonify({
            'status': 'success',
            'message': f'Review from {review.user.name} has been approved.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error approving review: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while approving the review.'
        }), 500


@app.route('/admin/review/<int:review_id>/reject', methods=['POST'])
@login_required
@admin_required
def reject_review(review_id):
    """Delete/reject a review"""
    review = CourseReview.query.get_or_404(review_id)
    
    try:
        user_name = review.user.name
        course_name = review.course.name
        db.session.delete(review)
        db.session.commit()
        
        logger.info(f'Review rejected: {review_id} by {current_user.email}')
        
        return jsonify({
            'status': 'success',
            'message': f'Review from {user_name} for {course_name} has been rejected.'
        })
    except Exception as e:
        db.session.rollback()
        logger.error(f'Error rejecting review: {str(e)}')
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while rejecting the review.'
        }), 500


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
    
    admin = User(name=name, email=email, role='admin', is_active=True)
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
        
        if not User.query.filter_by(role='admin').first():
            admin = User(
                name='Admin',
                email=admin_email,
                role='admin',
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
