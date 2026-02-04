
from app import app, db, NewsletterSubscriber

def migrate():
    with app.app_context():
        # Create table
        db.create_all()
        print("Database tables updated.")
        
        # Check if table works
        count = NewsletterSubscriber.query.count()
        print(f"Newsletter table ready. Current subscribers: {count}")

if __name__ == "__main__":
    migrate()
