
from app import app, db, Blog
from content_data import BLOG_POSTS
import re

def slugify(text):
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s-]', '', text)
    text = re.sub(r'[-\s]+', '-', text).strip('-')
    return text

def migrate():
    with app.app_context():
        # Create table
        db.create_all()
        print("Database tables updated.")
        
        # Check if blogs exist
        if Blog.query.first():
            print("Blogs already seeded.")
            return

        # Seed data
        print(f"Seeding {len(BLOG_POSTS)} blogs...")
        for post in BLOG_POSTS:
            slug = slugify(post['title'])
            
            # Check for duplicates by slug to be safe
            if not Blog.query.filter_by(slug=slug).first():
                blog = Blog(
                    title=post['title'],
                    slug=slug,
                    excerpt=post['excerpt'],
                    content=post.get('content', ''), # Use get in case content key is missing in some version
                    image=post['image'],
                    author=post['author'],
                    is_published=True
                )
                db.session.add(blog)
        
        db.session.commit()
        print("Blog seeding complete!")

if __name__ == "__main__":
    migrate()
