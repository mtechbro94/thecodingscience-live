"""
Database initialization script for production
Creates all tables and seeds initial data if needed
"""
import os
import sys
from app import app, db

def init_db():
    """Initialize database and create tables"""
    try:
        with app.app_context():
            # Create all tables
            print("Creating database tables...")
            db.create_all()
            print("✓ Database tables created successfully")
            
            # Check if tables have data, if not seed with sample data
            from app import Course
            
            course_count = Course.query.count()
            print(f"Current course count: {course_count}")
            
            if course_count == 0:
                print("Seeding Course data...")
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
                print(f"✓ Added {len(courses)} courses")
            else:
                print(f"✓ Database already has {course_count} courses, skipping seed")
            
            print("✓ Database initialization complete!")
            return 0
    except Exception as e:
        print(f"✗ Error during database initialization: {str(e)}")
        import traceback
        traceback.print_exc()
        return 1

if __name__ == '__main__':
    sys.exit(init_db())
