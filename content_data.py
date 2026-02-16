"""
Centralized storage for static site content.
Separating content from logic for easier maintenance.
"""

# Course Seed Data
COURSE_SEED_DATA = [
    # 1. Full Stack Development (FSD)
    {
        'name': 'Full Stack Development',
        'duration': '4 Months',
        'summary': "Master end-to-end web development from frontend to backend with hands-on projects.",
        'description': """Become a complete web developer mastering both frontend and backend technologies.

<b>Course Overview</b>
Build end-to-end web applications from database design to user interface. Master HTML/CSS/JavaScript on the frontend, Node.js/Python on the backend, and manage databases with SQL/MongoDB.

<b>What You'll Learn</b>
• <b>Frontend:</b> HTML5, CSS3, JavaScript ES6+, React.js, Redux
• <b>Backend:</b> Node.js/Express.js, RESTful API design, Authentication
• <b>Databases:</b> SQL (PostgreSQL), MongoDB, Database optimization
• <b>DevOps:</b> Docker, CI/CD pipelines, Git workflows
• <b>Cloud:</b> Deployment on AWS/Heroku, Payment integration

<b>Who Should Enroll</b>
Aspiring web developers, career changers, professionals advancing from frontend/backend-only roles, entrepreneurs building web products.

<b>Duration:</b> 4 Months (200+ hours) | <b>Prerequisites:</b> Basic programming knowledge""",
        'price': 1499,
        'level': 'Advanced',
        'image': 'fsd.jpg',
        'curriculum': [
            "Module 1: Frontend Foundation - HTML5, CSS3, Responsive Design",
            "Module 2: JavaScript Mastery - ES6+, DOM, Async Programming",
            "Module 3: React.js - Components, Hooks, State Management",
            "Module 4: Backend Basics - Node.js, Express.js, RESTful APIs",
            "Module 5: Databases - SQL & MongoDB, Design & Optimization",
            "Module 6: Authentication & Security - JWT, OAuth, Best Practices",
            "Module 7: DevOps & Deployment - Docker, Git, Cloud Platforms",
            "Module 8: Capstone Project - Build & Deploy Full Stack Application"
        ]
    },
    
    # 2. Programming with Python (PP)
    {
        'name': 'Programming with Python',
        'duration': '3 Months',
        'summary': "Learn Python from basics to advanced with real-world automation projects.",
        'description': """Master Python programming from fundamentals to advanced concepts.

<b>Course Overview</b>
Learn Python syntax, OOP, data structures, and popular libraries. Build automation tools, data processing scripts, and intelligent applications.

<b>What You'll Learn</b>
• <b>Fundamentals:</b> Syntax, variables, data types, control flow
• <b>OOP:</b> Classes, inheritance, polymorphism, encapsulation
• <b>Data Structures:</b> Lists, dictionaries, sets, tuples
• <b>Libraries:</b> NumPy, Pandas, Requests, BeautifulSoup
• <b>Projects:</b> Web scraper, automation tool, data processor

<b>Who Should Enroll</b>
Beginners starting their programming journey, students, professionals wanting to add Python to their skillset.

<b>Duration:</b> 3 Months (120+ hours) | <b>Prerequisites:</b> None""",
        'price': 999,
        'level': 'Intermediate',
        'image': 'pp.jpg',
        'curriculum': [
            "Module 1: Python Fundamentals - Syntax, Variables, Data Types",
            "Module 2: Control Flow - Conditionals, Loops, Functions",
            "Module 3: Data Structures - Lists, Dicts, Sets, and Operations",
            "Module 4: Object-Oriented Programming - Classes, Inheritance, Polymorphism",
            "Module 5: Working with Files & Data - JSON, CSV, APIs",
            "Module 6: Python Libraries - NumPy, Pandas, Requests",
            "Module 7: Building Projects - Web Scraper, Automation Tool",
            "Module 8: Best Practices - Testing, Debugging, Code Quality"
        ]
    },
    
    # 3. Data Science from Scratch (DS)
    {
        'name': 'Data Science from Scratch',
        'duration': '4 Months',
        'summary': "Transform raw data into insights using Python, statistics, and ML.",
        'description': """Transform raw data into actionable business insights.

<b>Course Overview</b>
Master data cleaning, exploration, visualization, and predictive modeling. Work with real datasets and solve business problems end-to-end.

<b>What You'll Learn</b>
• <b>Python:</b> NumPy, Pandas, Matplotlib, Seaborn
• <b>Statistics:</b> Probability, hypothesis testing, inference
• <b>ML:</b> Regression, classification, clustering algorithms
• <b>Visualization:</b> Charts, dashboards, storytelling with data
• <b>Tools:</b> Jupyter, Scikit-learn, real-world datasets

<b>Who Should Enroll</b>
Analysts wanting to upskill, engineers transitioning to data roles, business professionals seeking data-driven decision making.

<b>Duration:</b> 4 Months (180+ hours) | <b>Prerequisites:</b> Basic Python knowledge""",
        'price': 1499,
        'level': 'Advanced',
        'image': 'ds.jpg',
        'curriculum': [
            "Module 1: Data Science Foundation - Concepts, Workflow, Career Paths",
            "Module 2: Python for Data Science - NumPy, Pandas Mastery",
            "Module 3: Exploratory Data Analysis - Visualization, Insights Discovery",
            "Module 4: Statistical Methods - Probability, Hypothesis Testing, Inference",
            "Module 5: Machine Learning Fundamentals - Supervised Learning, Algorithms",
            "Module 6: Advanced ML - Unsupervised Learning, Ensemble Methods, Deep Learning Intro",
            "Module 7: Feature Engineering & Optimization - Improving Model Performance",
            "Module 8: Capstone Project - Real-World Data Science Problem"
        ]
    },
    
    # 4. Ethical Hacking and Penetration Testing (EHPT)
    {
        'name': 'Ethical Hacking and Penetration Testing',
        'duration': '3 Months',
        'summary': "Master cybersecurity through hands-on ethical hacking and pen testing.",
        'description': """Learn to identify and fix security vulnerabilities professionally.

<b>Course Overview</b>
Master networking, Linux, reconnaissance, scanning, and exploitation. Conduct real penetration tests and write professional security reports.

<b>What You'll Learn</b>
• <b>Networking:</b> TCP/IP, protocols, packet analysis
• <b>Linux:</b> System administration, command line mastery
• <b>Reconnaissance:</b> Passive and active information gathering
• <b>Exploitation:</b> Web app vulnerabilities, system exploits
• <b>Tools:</b> Kali Linux, Burp Suite, Metasploit, Nmap

<b>Who Should Enroll</b>
IT professionals, network admins, security enthusiasts, anyone interested in cybersecurity careers.

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic networking knowledge""",
        'price': 1499,
        'level': 'Advanced',
        'image': 'EHPT.jpg',
        'curriculum': [
            "Module 1: Cybersecurity Fundamentals & Ethical Hacking Introduction",
            "Module 2: Networking Essentials - TCP/IP, Protocols, Packet Analysis",
            "Module 3: Linux Administration - System management, Command Line Mastery",
            "Module 4: Reconnaissance & Information Gathering - Passive & Active",
            "Module 5: Scanning & Enumeration - Vulnerability Identification",
            "Module 6: Web Application Security - Common Vulnerabilities, Exploitation",
            "Module 7: Exploitation & Post-Exploitation Techniques",
            "Module 8: Penetration Testing Methodology - Reporting & Recommendations"
        ]
    },
    
    # 5. Crash Course in Computer Science (CCC)
    {
        'name': 'Crash Course in Computer Science',
        'duration': '2 Months',
        'summary': "Essential foundation covering how computers work from hardware to software.",
        'description': """Build a solid foundation for all tech careers.

<b>Course Overview</b>
Learn how computers work from hardware to software. Master binary, circuits, algorithms, data structures, operating systems, and networking concepts.

<b>What You'll Learn</b>
• <b>Hardware:</b> Binary, logic gates, CPUs, memory
• <b>Algorithms:</b> Complexity analysis, searching, sorting
• <b>Data Structures:</b> Arrays, linked lists, trees, graphs
• <b>Operating Systems:</b> Processes, memory management
• <b>Networking:</b> Internet fundamentals, protocols

<b>Who Should Enroll</b>
Complete beginners, students starting CS education, anyone wanting to understand how computers work.

<b>Duration:</b> 2 Months (80+ hours) | <b>Prerequisites:</b> None""",
        'price': 499,
        'level': 'Foundational',
        'image': 'ccc.jpg',
        'curriculum': [
            "Module 1: Computing History & Fundamentals",
            "Module 2: Binary, Logic Gates, and Basic Hardware",
            "Module 3: CPUs and Computing Components",
            "Module 4: Memory, Storage, and File Systems",
            "Module 5: Algorithms and Complexity Analysis",
            "Module 6: Data Structures - Arrays, Lists, Trees",
            "Module 7: Operating Systems Concepts",
            "Module 8: Networking and Internet Fundamentals"
        ]
    },
    
    # 6. Machine Learning and AI Foundations (MAF)
    {
        'name': 'Machine Learning and AI Foundations',
        'duration': '4 Months',
        'summary': "Build intelligent systems using ML algorithms, neural networks, and deep learning.",
        'description': """Build intelligent systems that learn and improve.

<b>Course Overview</b>
Master ML algorithms, neural networks, deep learning, NLP, and computer vision. Deploy predictive models in production environments.

<b>What You'll Learn</b>
• <b>ML Basics:</b> Regression, classification, clustering
• <b>Neural Networks:</b> Perceptrons, backpropagation, CNNs, RNNs
• <b>Deep Learning:</b> TensorFlow, PyTorch frameworks
• <b>NLP:</b> Text processing, sentiment analysis, chatbots
• <b>Computer Vision:</b> Image classification, object detection

<b>Who Should Enroll</b>
Developers wanting AI skills, data scientists expanding into ML, researchers and students in AI/ML.

<b>Duration:</b> 4 Months (200+ hours) | <b>Prerequisites:</b> Python, basic math/statistics""",
        'price': 999,
        'level': 'Intermediate',
        'image': 'maf.jpg',
        'curriculum': [
            "Module 1: Machine Learning Fundamentals & Concepts",
            "Module 2: Supervised Learning - Regression & Classification",
            "Module 3: Unsupervised Learning - Clustering & Dimensionality Reduction",
            "Module 4: Neural Networks & Backpropagation",
            "Module 5: Deep Learning - CNNs and RNNs",
            "Module 6: Natural Language Processing & Text Analysis",
            "Module 7: Computer Vision & Image Processing",
            "Module 8: Advanced Topics - Transfer Learning, AI Ethics, Deployment"
        ]
    },
    
    # 7. Data Analytics and BI Tools (DABT)
    {
        'name': 'Data Analytics and BI Tools',
        'duration': '3 Months',
        'summary': "Transform business data into insights using Excel, SQL, Tableau, and Power BI.",
        'description': """Turn business data into strategic insights and decisions.

<b>Course Overview</b>
Master Excel, SQL, Tableau, and Power BI. Create dashboards, analyze data, and present insights to executives effectively.

<b>What You'll Learn</b>
• <b>Excel:</b> Advanced functions, pivot tables, macros
• <b>SQL:</b> Complex queries, optimization, data extraction
• <b>Tableau:</b> Visualizations, interactive dashboards
• <b>Power BI:</b> Data modeling, DAX formulas, reports
• <b>Storytelling:</b> Executive presentations, data narratives

<b>Who Should Enroll</b>
Business analysts, managers needing data skills, professionals in marketing/finance/operations.

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic Excel knowledge""",
        'price': 1499,
        'level': 'Advanced',
        'image': 'dabt.jpg',
        'curriculum': [
            "Module 1: Excel for Analytics - Advanced Functions & Pivot Tables",
            "Module 2: SQL for Data Analysis - Complex Queries & Optimization",
            "Module 3: Tableau Fundamentals - Visualizations & Dashboards",
            "Module 4: Tableau Advanced - Calculations, Parameters, Interactivity",
            "Module 5: Power BI - Data Modeling & DAX Formulas",
            "Module 6: Power BI Advanced - Complex Reports & Refresh Strategies",
            "Module 7: Data Storytelling & Executive Presentation",
            "Module 8: Capstone Project - End-to-End Analytics Solution"
        ]
    },
    
    # 8. Android App Development (AAD)
    {
        'name': 'Android App Development',
        'duration': '3 Months',
        'summary': "Build production-ready Android apps with Kotlin and publish to Play Store.",
        'description': """Create professional Android applications from scratch.

<b>Course Overview</b>
Master Kotlin and modern Android frameworks. Build feature-rich apps with material design, networking, databases, and publish to Play Store.

<b>What You'll Learn</b>
• <b>Kotlin:</b> Modern Android programming language
• <b>UI:</b> Material Design, layouts, fragments, animations
• <b>Data:</b> SQLite, Room database, SharedPreferences
• <b>Networking:</b> Retrofit, REST APIs, JSON parsing
• <b>Publishing:</b> Play Store submission, monetization

<b>Who Should Enroll</b>
Aspiring mobile developers, Java/Kotlin programmers, entrepreneurs wanting to build Android apps.

<b>Duration:</b> 3 Months (150+ hours) | <b>Prerequisites:</b> Basic programming knowledge""",
        'price': 1499,
        'level': 'Advanced',
        'image': 'aad.jpg',
        'curriculum': [
            "Module 1: Kotlin Fundamentals - Modern Android Language",
            "Module 2: Android Basics - Activities, Intents, Lifecycle",
            "Module 3: Building Layouts - Material Design, XML, Fragments",
            "Module 4: Data Persistence - SQLite, Room, SharedPreferences",
            "Module 5: Networking & APIs - Retrofit, JSON, Web Services",
            "Module 6: Advanced Features - Location, Camera, Sensors",
            "Module 7: Testing & Performance Optimization",
            "Module 8: Publishing to Google Play - Monetization & Analytics"
        ]
    },
    
    # 9. MS Office Automation and AI Tools (MOAAT)
    {
        'name': 'MS Office Automation and AI Tools',
        'duration': '2.5 Months',
        'summary': "Automate tasks and boost productivity with VBA, Power Automate, and AI.",
        'description': """Automate repetitive tasks and supercharge productivity.

<b>Course Overview</b>
Master Excel VBA, Power Automate, Power Query, and AI tool integration. Build custom solutions that reduce manual work by 70-80%.

<b>What You'll Learn</b>
• <b>Excel VBA:</b> Macros, automation, custom functions
• <b>Power Automate:</b> Cloud workflows, connectors, triggers
• <b>Power Query:</b> Data transformation, ETL processes
• <b>AI Tools:</b> ChatGPT, Copilot, GPT API integration
• <b>Office Suite:</b> Teams, Outlook, Access automation

<b>Who Should Enroll</b>
Business professionals, admin staff, anyone wanting to save hours of work every week.

<b>Duration:</b> 2.5 Months (100+ hours) | <b>Prerequisites:</b> Basic Office skills""",
        'price': 499,
        'level': 'Foundational',
        'image': 'moaat.jpg',
        'curriculum': [
            "Module 1: Advanced Excel Automation - Macros & VBA Basics",
            "Module 2: Power Query for ETL - Transforming Dirty Data",
            "Module 3: Power Automate Cloud Flows - Trigger-Based Automation",
            "Module 4: Desktop Automation (RPA) - Power Automate Desktop",
            "Module 5: Outlook & Teams Automation - Notifications, Approvals",
            "Module 6: AI Tools Integration - ChatGPT & Copilot for Office",
            "Module 7: Building Custom Tools & Dashboards",
            "Module 8: Final Project - Complete Office Automation Solution"
        ]
    }
]

# Services / Live Trainings List
SERVICES_LIST = [
    {
        'id': 1,
        'title': 'Computer Science Engineering',
        'icon': 'fa-microchip',
        'description': 'Master programming fundamentals, data structures, algorithms, and computer architecture.',
        'duration': '2-4 Months',
        'price': '499-1499'
    },
    {
        'id': 2,
        'title': 'AI & Machine Learning',
        'icon': 'fa-robot',
        'description': 'Build intelligent systems with neural networks, deep learning, NLP, and computer vision.',
        'duration': '3-4 Months',
        'price': '999-1499'
    },
    {
        'id': 3,
        'title': 'Programming & DSA',
        'icon': 'fa-code',
        'description': 'Learn Python, Java, problem-solving, and crack coding interviews with confidence.',
        'duration': '3-4 Months',
        'price': '999-1499'
    },
    {
        'id': 4,
        'title': 'Cloud Computing & DevOps',
        'icon': 'fa-cloud',
        'description': 'Master AWS, Docker, Kubernetes, CI/CD pipelines, and modern deployment practices.',
        'duration': '3-4 Months',
        'price': '1499'
    }
]

# Internships List
INTERNSHIPS_LIST = [
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

# About Page Values
ABOUT_VALUES = [
    {
        'icon': 'fa-lightbulb',
        'title': 'Innovation',
        'desc': 'Embracing the latest technology and teaching methods to stay ahead.'
    },
    {
        'icon': 'fa-star',
        'title': 'Excellence',
        'desc': 'Delivering the highest quality training with industry standards.'
    },
    {
        'icon': 'fa-hand-holding-heart',
        'title': 'Integrity',
        'desc': 'Transparency and honesty in guiding student careers.'
    },
    {
        'icon': 'fa-users',
        'title': 'Community',
        'desc': 'Building a supportive network of lifelong learners and mentors.'
    }
]

# Blog Posts
BLOG_POSTS = [
    {
        'id': 1,
        'title': 'The Future of AI in 2026',
        'excerpt': 'Discover how Artificial Intelligence is reshaping industries and what you need to learn to stay ahead.',
        'date': 'Feb 2, 2026',
        'image': 'blog-ai.png',
        'author': 'Aaqib Rashid Mir',
        'content': '''
            <p class="lead">Artificial Intelligence is no longer just a buzzword; it's the driving force behind the next industrial revolution. In 2026, we are witnessing AI integration at an unprecedented scale.</p>
            
            <h3>The Rise of Agentic AI</h3>
            <p>Gone are the days of simple chatbots. Today, we have <strong>Agentic AI</strong> systems that can plan, reason, and execute complex tasks autonomously. From optimizing supply chains to writing entire software modules, these agents are force multipliers for productivity.</p>
            
            <h3>AI in Software Development</h3>
            <p>For developers, AI has become an indispensable pair programmer. Tools like GitHub Copilot and Google's Gemini have evolved to understand entire codebases, making refactoring and debugging significantly faster. However, this doesn't mean human developers are obsolete. On the contrary, the demand for "AI Architects" who can design and orchestrate these systems is skyrocketing.</p>
            
            <h3>What You Need to Learn</h3>
            <ul>
                <li><strong>Prompt Engineering:</strong> Mastering the art of communicating with LLMs.</li>
                <li><strong>RAG (Retrieval-Augmented Generation):</strong> Building systems that can access real-time data.</li>
                <li><strong>Ethics & Safety:</strong> Ensuring AI systems are unbiased and secure.</li>
            </ul>
            
            <p>At The Coding Science, we integrate these cutting-edge topics into our curriculum to ensure our students are future-ready.</p>
        '''
    },
    {
        'id': 2,
        'title': 'Web Development Roadmap',
        'excerpt': 'A comprehensive guide to becoming a Full Stack Developer in 2026. From HTML to Cloud Deployment.',
        'date': 'Jan 23, 2026',
        'image': 'blog-web.png',
        'author': 'Jaffar Hameed Bhat',
        'content': '''
            <p class="lead">The landscape of web development changes rapidly. What was popular in 2023 might be legacy code today. Here is your roadmap to becoming a top-tier Full Stack Developer in 2026.</p>
            
            <h3>1. The Foundations: HTML, CSS, & Vanilla JS</h3>
            <p>No matter how many frameworks exist, the core trio remains essential. Focus on semantic HTML, modern CSS (Flexbox, Grid, container queries), and ES6+ JavaScript features.</p>
            
            <h3>2. Frontend Frameworks</h3>
            <p>React is still king, but the ecosystem has matured. You need to understand:</p>
            <ul>
                <li><strong>Next.js / Remix:</strong> For server-side rendering and static site generation.</li>
                <li><strong>State Management:</strong> Redux Toolkit, Zustand, or Context API.</li>
                <li><strong>Tailwind CSS:</strong> For rapid UI development.</li>
            </ul>
            
            <h3>3. Backend & Database</h3>
            <p>Node.js is the standard for JavaScript developers. Pair it with:</p>
            <ul>
                <li><strong>PostgreSQL:</strong> The gold standard for relational databases.</li>
                <li><strong>Prisma / Drizzle:</strong> Modern ORMs for type-safe database access.</li>
            </ul>
            
            <h3>4. Cloud & DevOps</h3>
            <p>You can't just code it; you must ship it. Learn Docker for containerization and deploy your apps on platforms like AWS, Vercel, or Railway.</p>
        '''
    },
    {
        'id': 3,
        'title': 'Why Python is Still King',
        'excerpt': 'From Data Science to Automation, explore why Python remains the most popular programming language.',
        'date': 'Jan 15, 2026',
        'image': 'blog-python.png',
        'author': 'Ubaid Ashraf',
        'content': '''
            <p class="lead">Python has held the top spot in programming language rankings for years, and in 2026, its dominance is stronger than ever. But why?</p>
            
            <h3>Versatility is Key</h3>
            <p>Python is the "Swiss Army Knife" of coding. whether you are building a web server with Django, analyzing financial trends with Pandas, or training a neural network with PyTorch, Python is the language of choice.</p>
            
            <h3>The AI Boom</h3>
            <p>The explosion of Generative AI has cemented Python's position. All major AI frameworks and libraries are Python-first. If you want to work in AI, you simply must know Python.</p>
            
            <h3>Automation & Scripting</h3>
            <p>Beyond complex applications, Python shines in everyday automation. From organizing files to scraping web data, Python scripts save countless hours of manual work in enterprises globally.</p>
            
            <p><strong>Conclusion:</strong> If you are looking for a first language to learn that offers the widest range of career opportunities, Python is the answer. Join our "Mastering Python" course to start your journey today.</p>
        '''
    }
]
