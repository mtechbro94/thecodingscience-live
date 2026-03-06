CREATE TABLE IF NOT EXISTS success_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    avatar_bg VARCHAR(50) DEFAULT 'bg-primary',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed existing data
INSERT INTO success_stories (name, title, content, rating, avatar_bg, sort_order) VALUES
('Ayesha Khan', 'Software Developer', 'The Coding Science helped me switch from a non-tech background to a full-time web developer in just a few months.', 5, 'bg-primary', 1),
('Rohit Sharma', 'Data Scientist', 'The projects and mentorship were exactly what I needed to crack my first Data Science internship.', 5, 'bg-success', 2),
('Simran Gupta', 'AI Engineer', 'Live classes, doubt support and career guidance made the learning journey smooth and focused.', 5, 'bg-info', 3);
