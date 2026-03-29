-- Migration to add missing columns for production fixes

-- Add is_featured to courses table
ALTER TABLE courses ADD COLUMN is_featured TINYINT(1) DEFAULT 0;

-- Add OAuth fields to users table
ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN oauth_id VARCHAR(100) DEFAULT NULL;

-- Add indexes for performance
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_role ON users (role);
CREATE INDEX idx_courses_trainer_id ON courses (trainer_id);
CREATE INDEX idx_enrollments_user_id ON enrollments (user_id);
CREATE INDEX idx_enrollments_course_id ON enrollments (course_id);
CREATE INDEX idx_blogs_author_id ON blogs (author_id);
CREATE INDEX idx_blogs_slug ON blogs (slug);