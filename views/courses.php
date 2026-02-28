<?php
// views/courses.php

$page_title = "Our Courses";
require_once 'includes/header.php';

// Individual Courses Data
$courses = [
    [
        'name' => 'Crash Course in Computer Science',
        'level' => 'Beginner',
        'price' => 2999,
        'description' => 'A foundational course designed for students starting their technology journey. It introduces how computers work, basic networking concepts, internet fundamentals, and problem-solving approaches used in computer science.',
        'image' => 'ccc.jpg',
        'color' => 'primary',
        'curriculum' => [
            ['module' => 'Module 1: Introduction to Computers', 'topics' => ['What is a computer?', 'How computers work', 'Hardware vs Software', 'Basic computer components']],
            ['module' => 'Module 2: Number Systems', 'topics' => ['Binary and Decimal', 'Hexadecimal', 'Data representation', 'ASCII and Unicode']],
            ['module' => 'Module 3: Programming Basics', 'topics' => ['What is programming?', 'Algorithms and flowcharts', 'Pseudocode basics', 'Your first program']],
            ['module' => 'Module 4: Internet & Networking', 'topics' => ['How the internet works', 'IP addresses and DNS', 'Web browsers explained', 'Introduction to HTML']],
            ['module' => 'Module 5: Problem Solving', 'topics' => ['Logical thinking', 'Breakdown problems', 'Pattern recognition', 'Practice exercises']]
        ]
    ],
    [
        'name' => 'Programming with Python',
        'level' => 'Beginner to Intermediate',
        'price' => 3999,
        'description' => 'A practical programming course that teaches Python fundamentals, logic building, problem solving, and small real-world projects. This course serves as the foundation for advanced fields like data science, automation, and AI.',
        'image' => 'pp.jpg',
        'color' => 'success',
        'curriculum' => [
            ['module' => 'Module 1: Python Basics', 'topics' => ['Installing Python', 'Your first Python program', 'Variables and data types', 'Basic operators']],
            ['module' => 'Module 2: Control Flow', 'topics' => ['If-else statements', 'For loops', 'While loops', 'Nested loops']],
            ['module' => 'Module 3: Functions', 'topics' => ['Defining functions', 'Parameters and return values', 'Scope of variables', 'Lambda functions']],
            ['module' => 'Module 4: Data Structures', 'topics' => ['Lists and list operations', 'Dictionaries', 'Tuples and sets', 'Working with files']],
            ['module' => 'Module 5: Object-Oriented Programming', 'topics' => ['Classes and objects', 'Inheritance', 'Encapsulation', 'Building a project']],
            ['module' => 'Module 6: Real-World Projects', 'topics' => ['Calculator app', 'Number guessing game', 'Simple inventory system', 'Final project']]
        ]
    ],
    [
        'name' => 'Full Stack Web Development',
        'level' => 'Intermediate',
        'price' => 7999,
        'description' => 'A complete web development program covering frontend and backend technologies. Students learn HTML, CSS, JavaScript, backend logic, databases, and how to build and deploy real web applications.',
        'image' => 'fsd.jpg',
        'color' => 'info',
        'curriculum' => [
            ['module' => 'Module 1: HTML Fundamentals', 'topics' => ['Document structure', 'HTML5 semantic tags', 'Forms and inputs', 'SEO basics']],
            ['module' => 'Module 2: CSS & Styling', 'topics' => ['CSS selectors', 'Flexbox layout', 'CSS Grid', 'Responsive design', 'Animations']],
            ['module' => 'Module 3: JavaScript Basics', 'topics' => ['Variables and data types', 'DOM manipulation', 'Events and listeners', 'ES6+ features']],
            ['module' => 'Module 4: Advanced JavaScript', 'topics' => ['Async/Await', 'Fetch API', 'Local storage', 'JavaScript frameworks intro']],
            ['module' => 'Module 5: Backend Development', 'topics' => ['Server basics', 'PHP fundamentals', 'MySQL database', 'REST APIs']],
            ['module' => 'Module 6: Deployment & Projects', 'topics' => ['Git version control', 'Deploying to servers', 'Building a blog', 'Building an e-commerce cart', 'Final portfolio project']]
        ]
    ],
    [
        'name' => 'Data Science from Scratch',
        'level' => 'Beginner to Intermediate',
        'price' => 6999,
        'description' => 'A hands-on introduction to data science covering data analysis, visualization, statistics basics, and working with real datasets using Python.',
        'image' => 'ds.jpg',
        'color' => 'warning',
        'curriculum' => [
            ['module' => 'Module 1: Introduction to Data Science', 'topics' => ['What is data science?', 'Data science workflow', 'Python for data science', 'Jupyter notebooks']],
            ['module' => 'Module 2: Python for Data Analysis', 'topics' => ['NumPy arrays', 'Pandas DataFrames', 'Data cleaning', 'Handling missing data']],
            ['module' => 'Module 3: Data Visualization', 'topics' => ['Matplotlib basics', 'Seaborn for statistical plots', 'Interactive charts', 'Storytelling with data']],
            ['module' => 'Module 4: Statistics Fundamentals', 'topics' => ['Descriptive statistics', 'Probability basics', 'Hypothesis testing', 'Correlation and regression']],
            ['module' => 'Module 5: Working with Real Data', 'topics' => ['Kaggle datasets', 'Data preprocessing', 'Feature engineering', 'Exploratory data analysis']],
            ['module' => 'Module 6: Capstone Project', 'topics' => ['Complete data analysis project', 'Visualization dashboard', 'Insights presentation', 'Final submission']]
        ]
    ],
    [
        'name' => 'Machine Learning and AI Foundations',
        'level' => 'Intermediate to Advanced',
        'price' => 7999,
        'description' => 'A foundational machine learning course that introduces core algorithms, model training, evaluation techniques, and real-world AI applications.',
        'image' => 'maf.jpg',
        'color' => 'danger',
        'curriculum' => [
            ['module' => 'Module 1: Introduction to ML', 'topics' => ['What is machine learning?', 'Types of ML algorithms', 'Supervised vs Unsupervised', 'Setting up ML environment']],
            ['module' => 'Module 2: Regression Models', 'topics' => ['Linear regression', 'Polynomial regression', 'Logistic regression', 'Model evaluation']],
            ['module' => 'Module 3: Classification', 'topics' => ['Decision trees', 'Random forests', 'K-Nearest Neighbors', 'Support Vector Machines']],
            ['module' => 'Module 4: Clustering & Dimensionality', 'topics' => ['K-Means clustering', 'Hierarchical clustering', 'PCA', 'Feature selection']],
            ['module' => 'Module 5: Neural Networks', 'topics' => ['Perceptrons', 'Activation functions', 'Building neural networks', 'TensorFlow basics']],
            ['module' => 'Module 6: AI Applications', 'topics' => ['Computer vision intro', 'NLP basics', 'Chatbot project', 'Deploying ML models']]
        ]
    ],
    [
        'name' => 'Ethical Hacking and Cybersecurity',
        'level' => 'Beginner to Intermediate',
        'price' => 6999,
        'description' => 'A cybersecurity course that teaches networking basics, common vulnerabilities, ethical hacking tools, and penetration testing concepts.',
        'image' => 'EHPT.jpg',
        'color' => 'dark',
        'curriculum' => [
            ['module' => 'Module 1: Networking Fundamentals', 'topics' => ['OSI model', 'TCP/IP protocols', 'IP addressing', 'DNS and DHCP']],
            ['module' => 'Module 2: Linux for Hackers', 'topics' => ['Linux basics', 'Command line mastery', 'Bash scripting', 'Essential tools']],
            ['module' => 'Module 3: Information Gathering', 'topics' => ['Footprinting', 'Network scanning', 'Vulnerability assessment', 'Enumeration']],
            ['module' => 'Module 4: System Hacking', 'topics' => ['Password attacks', 'Privilege escalation', 'Malware basics', 'Sniffing and spoofing']],
            ['module' => 'Module 5: Web Application Security', 'topics' => ['OWASP Top 10', 'SQL injection', 'XSS attacks', 'CSRF and security headers']],
            ['module' => 'Module 6: Penetration Testing', 'topics' => ['Metasploit framework', 'Writing penetration reports', 'Legal and ethical boundaries', 'Career in cybersecurity']]
        ]
    ]
];

// Combo Programs Data
$combo_programs = [
    [
        'name' => 'Programming Starter Pack',
        'courses' => ['Crash Course in Computer Science', 'Programming with Python'],
        'original_price' => 6998,
        'price' => 4499,
        'description' => 'A perfect entry point for beginners to understand computer science fundamentals and learn their first programming language.',
        'badge' => 'Best for Beginners',
        'badge_color' => 'success'
    ],
    [
        'name' => 'Developer Career Pack',
        'courses' => ['Programming with Python', 'Full Stack Web Development'],
        'original_price' => 11998,
        'price' => 7999,
        'description' => 'Designed for students who want to become software developers and build real-world web applications.',
        'badge' => 'Most Popular',
        'badge_color' => 'primary'
    ],
    [
        'name' => 'AI and Data Science Career Track',
        'courses' => ['Programming with Python', 'Data Science from Scratch', 'Machine Learning and AI Foundations'],
        'original_price' => 18997,
        'price' => 11999,
        'description' => 'A complete learning pathway that prepares students for careers in artificial intelligence, machine learning, and data science.',
        'badge' => 'Best Value',
        'badge_color' => 'warning'
    ]
];

function getLevelBadgeClass($level) {
    if (strpos($level, 'Beginner') !== false && strpos($level, 'Intermediate') !== false) {
        return 'bg-success';
    } elseif (strpos($level, 'Beginner') !== false) {
        return 'bg-info';
    } elseif (strpos($level, 'Intermediate to Advanced') !== false) {
        return 'bg-warning text-dark';
    } elseif (strpos($level, 'Intermediate') !== false) {
        return 'bg-primary';
    }
    return 'bg-secondary';
}
?>

<style>
.bg-primary-subtle { background-color: #e0e7ff !important; }
.bg-success-subtle { background-color: #dcfce7 !important; }
.bg-warning-subtle { background-color: #fef3c7 !important; }
.bg-info-subtle { background-color: #cff4fc !important; }
.bg-dark-subtle { background-color: #e9ecef !important; }

.course-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.3s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.course-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.course-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: var(--primary-color);
}
.course-card .btn {
    white-space: nowrap;
}
@media (max-width: 400px) {
    .course-card .btn {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
    }
}
.level-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
}
.combo-card {
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.3s ease;
    background: linear-gradient(to bottom, #ffffff, #f8fafc);
}
.combo-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}
.combo-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}
.original-price {
    text-decoration: line-through;
    color: #94a3b8;
    font-size: 0.9rem;
}
.combo-price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
}
.save-badge {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.course-list-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
.section-header {
    position: relative;
    display: inline-block;
}
.section-header::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}
.modal-header.bg-primary { background: var(--primary-color) !important; }
.modal-header.bg-success { background: var(--success-color) !important; }
.modal-header.bg-info { background: var(--info-color) !important; }
.modal-header.bg-warning { background: #f59e0b !important; }
.modal-header.bg-danger { background: var(--danger-color) !important; }
.modal-header.bg-dark { background: var(--dark-color) !important; }
</style>

<!-- Individual Courses Section -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Our Courses</h1>
            <p class="lead text-muted">Master the latest technologies with our industry-aligned curriculum - from foundation to advanced level with job ready skillset.</p>
        </div>

        <!-- Level Legend -->
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
            <span class="level-badge bg-info">Beginner</span>
            <span class="level-badge bg-success">Beginner to Intermediate</span>
            <span class="level-badge bg-primary">Intermediate</span>
            <span class="level-badge bg-warning text-dark">Intermediate to Advanced</span>
        </div>

        <div class="row g-4">
            <?php foreach ($courses as $index => $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 course-card shadow-sm">
                        <?php if (!empty($course['image'])): ?>
                            <div class="position-relative" style="height: 160px; overflow: hidden;">
                                <img src="/assets/images/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="w-100 h-100" style="object-fit: cover;">
                                <span class="position-absolute top-0 end-0 m-2 level-badge <?php echo getLevelBadgeClass($course['level']); ?>">
                                    <?php echo $course['level']; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3"><?php echo $course['name']; ?></h5>
                            <p class="card-text text-muted small mb-4"><?php echo $course['description']; ?></p>
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top gap-2">
                                <span class="fw-bold fs-6 text-primary" style="white-space: nowrap;">₹<?php echo number_format($course['price']); ?></span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#curriculumModal<?php echo $index; ?>">
                                        <i class="fas fa-list-alt me-1"></i> View Curriculum
                                    </button>
                                    <a href="/enroll?course=<?php echo urlencode($course['name']); ?>" class="btn btn-primary btn-sm py-1 px-2" style="font-size: 0.75rem;">Enroll Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Curriculum Modal -->
                <div class="modal fade" id="curriculumModal<?php echo $index; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-<?php echo $course['color']; ?> text-white">
                                <h5 class="modal-title fw-bold">
                                    <?php echo $course['name']; ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($course['image'])): ?>
                                    <div class="mb-3">
                                        <img src="/assets/images/<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                                <div class="mb-4">
                                    <span class="badge bg-<?php echo $course['color']; ?>"><?php echo $course['level']; ?></span>
                                    <span class="badge bg-secondary ms-2"><?php echo count($course['curriculum']); ?> Modules</span>
                                </div>
                                
                                <div class="curriculum-timeline">
                                    <?php foreach ($course['curriculum'] as $modIndex => $module): ?>
                                        <div class="curriculum-module mb-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="module-number bg-<?php echo $course['color']; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                                                    <?php echo $modIndex + 1; ?>
                                                </div>
                                                <h6 class="fw-bold mb-0"><?php echo $module['module']; ?></h6>
                                            </div>
                                            <div class="module-topics ms-5">
                                                <?php foreach ($module['topics'] as $topic): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-check-circle text-<?php echo $course['color']; ?> me-2" style="font-size: 0.8rem;"></i>
                                                        <span class="text-muted"><?php echo $topic; ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php if ($modIndex < count($course['curriculum']) - 1): ?>
                                            <div class="curriculum-line bg-<?php echo $course['color']; ?>-subtle mx-5 mb-4" style="height: 2px;"></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <a href="/enroll?course=<?php echo urlencode($course['name']); ?>" class="btn btn-primary">
                                    <i class="fas fa-graduation-cap me-1"></i> Enroll Now - ₹<?php echo number_format($course['price']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Combo Programs Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">
                <span class="section-header">Career Track Programs</span>
            </h2>
            <p class="lead text-muted">Bundle courses together and save big! Our recommended learning pathways for your career success.</p>
            <span class="badge bg-warning text-dark fs-6 mt-2"><i class="fas fa-star me-1"></i> Best Value Programs</span>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($combo_programs as $index => $combo): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 combo-card shadow-sm position-relative">
                        <span class="combo-badge bg-<?php echo $combo['badge_color']; ?> text-white">
                            <i class="fas fa-star me-1"></i> <?php echo $combo['badge']; ?>
                        </span>
                        <div class="card-body p-4 pt-5">
                            <h5 class="card-title fw-bold text-center mb-3"><?php echo $combo['name']; ?></h5>
                            
                            <div class="mb-4">
                                <p class="text-muted small mb-2 fw-semibold">Includes:</p>
                                <?php foreach ($combo['courses'] as $course_name): ?>
                                    <div class="course-list-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo $course_name; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <p class="card-text text-muted small text-center mb-4"><?php echo $combo['description']; ?></p>
                            
                            <div class="text-center mb-4">
                                <span class="original-price">₹<?php echo number_format($combo['original_price']); ?></span>
                                <span class="save-badge ms-2">Save ₹<?php echo number_format($combo['original_price'] - $combo['price']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="combo-price">₹<?php echo number_format($combo['price']); ?></span>
                                <a href="/enroll?combo=<?php echo urlencode($combo['name']); ?>" class="btn btn-primary btn-lg">
                                    Get This Bundle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Why Choose Combos -->
        <div class="mt-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-percentage text-primary fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Save Upto 40%</h6>
                        <p class="text-muted small mb-0">Get significant discounts when you bundle courses together</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-certificate text-success fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Complete Certification</h6>
                        <p class="text-muted small mb-0">Get certificates for each course upon completion</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-warning-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-infinity text-warning fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Lifetime Access</h6>
                        <p class="text-muted small mb-0">Access course materials anytime, forever</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
