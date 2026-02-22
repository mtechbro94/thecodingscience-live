-- Production Data Import for The Coding Science
-- Import this into your MySQL database via phpMyAdmin

-- 1. CLEAN EXISTING DATA (Optional, but ensures fresh import)
DELETE FROM enrollments;
DELETE FROM course_reviews;
DELETE FROM courses;
DELETE FROM blogs;
DELETE FROM internship_applications;

-- 4. INSERT ADMIN USER (Use REPLACE to update if exists, or INSERT IGNORE to skip)
REPLACE INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'Admin', 'admin@thecodingscience.com', '$2y$10$.CBO2rN7dXyyyAcgopWEcugzsci0sfuTKvlRSIXET.on5l0PoH54O', 'admin', 1, NOW());

-- 2. INSERT COURSES (9 specified)
INSERT INTO `courses` (`name`, `description`, `summary`, `duration`, `price`, `level`, `image`, `curriculum`, `created_at`) VALUES 
('Full Stack Development', 'Master both frontend and backend development. Build complete, scalable web applications using modern stacks like MERN or Laravel.', 'Comprehensive bootcamp for modern web developers', '4 Months', 1499.00, 'Beginner to Advanced', 'fsd.jpg', '["Frontend (HTML, CSS, JS, React)", "Backend (Node.js/PHP)", "Database Management", "API Development", "Deployment"]', NOW()),
('Programming with Python', 'Learn the most versatile programming language. From basic syntax to advanced automation and scripting.', 'Core Python programming from absolute zero', '2 Months', 999.00, 'Beginner', 'pp.jpg', '["Python Syntax", "Data Structures", "OOP Concepts", "File Handling", "Automation Scripts"]', NOW()),
('Data Science from Scratch', 'Unlock the power of data. Learn statistics, data cleaning, and visualization to drive business insights.', 'Data analysis and visualization foundations', '3 Months', 1499.00, 'Intermediate', 'ds.jpg', '["Statistics & Probability", "Pandas & NumPy", "Data Visualization", "Cleaning Real Datasets", "Case Studies"]', NOW()),
('Ethical Hacking and Penetration Testing', 'Think like a hacker to protect systems. Learn network security, vulnerability assessment, and digital forensics.', 'Cybersecurity fundamentals and ethical hacking', '3 Months', 1499.00, 'Intermediate', 'EHPT.jpg', '["Networking Basics", "Kali Linux", "Vulnerability Scanning", "Wireless Hacking", "System Hardening"]', NOW()),
('Crash Course in Computer Science', 'A fast-paced introduction to the world of computing. Hardwares, OS, binary, and how computers really work.', 'The complete "under the hood" view of technology', '1 Month', 499.00, 'Beginner', 'ccc.jpg', '["Binary & Logic Gates", "Computer Architecture", "OS Concepts", "Networking Layouts", "Cloud Basics"]', NOW()),
('Machine Learning and AI Foundations', 'Step into the future of AI. Build and train models using Scikit-Learn and understand the math behind AI.', 'Introduction to predictive modeling and AI', '3 Months', 999.00, 'Intermediate', 'maf.jpg', '["Linear Regression", "Classification Models", "Neural Networks Intro", "Natural Language Processing", "Computer Vision Basics"]', NOW()),
('Data Analytics and BI Tools', 'Master tools like Power BI, Tableau, and Excel to transform raw data into stunning dashboards and reports.', 'Business Intelligence and dashboard mastery', '2 Months', 1499.00, 'Intermediate', 'dabt.jpg', '["Advanced Excel", "SQL for Analytics", "Power BI Dashboards", "Tableau Visualization", "Storytelling with Data"]', NOW()),
('Android App Development', 'Build native mobile apps for millions of users. Learn Kotlin/Java and the Android Studio ecosystem.', 'Mobile application development from scratch', '3 Months', 1499.00, 'Intermediate', 'aad.jpg', '["UI/UX Design for Mobile", "Activities & Fragments", "REST API Integration", "Firebase Backend", "Publishing to Play Store"]', NOW()),
('MS Office Automation and AI Tools', 'Supercharge your productivity. Learn how to use AI tools with Excel, Word, and PowerPoint to work 10x faster.', 'Modern productivity workflows with AI', '1 Month', 499.00, 'Beginner', 'moaat.jpg', '["Excel Automation", "AI Writing Assistants", "Automated Presentations", "Email Productivity", "Task Management Tools"]', NOW());

-- 3. INSERT BLOGS (3 specified)
INSERT INTO `blogs` (`title`, `slug`, `excerpt`, `content`, `image`, `author`, `date`, `is_published`, `created_at`) VALUES 
('Top 5 Programming Languages to Learn in 2026', 'top-5-programming-languages-2026', 'Discover the most in-demand programming languages that will shape your career in 2026 and beyond.', 'The tech landscape is constantly evolving. Here are the top 5 programming languages you should learn in 2026: 1. Python continues to dominate... 2. JavaScript remains essential... 3. Rust for systems... 4. Go for microservices... 5. TypeScript for large apps.', 'blog-web.png', 'The Coding Science', 'Jan 15, 2026', 1, NOW()),
('How to Break Into Tech Without a CS Degree', 'break-into-tech-without-cs-degree', 'A practical guide for non-CS graduates who want to start a career in technology.', 'You dont need a computer science degree to have a successful tech career. Build projects, Learn online, Get certified, Network.', 'blog-python.png', 'The Coding Science', 'Feb 01, 2026', 1, NOW()),
('Understanding Machine Learning: A Beginner''s Guide', 'understanding-machine-learning-beginners', 'An introduction to machine learning concepts, types, and real-world applications.', 'Machine Learning is a subset of AI that enables computers to learn from data. Types including Supervised, Unsupervised, and Reinforcement Learning.', 'blog-ai.png', 'The Coding Science', 'Feb 05, 2026', 1, NOW());


