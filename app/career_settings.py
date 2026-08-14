"""Career page configuration, mirroring config/career_settings.php."""

CAREER_SETTINGS = {
    "hero": {
        "badge": "We are hiring!",
        "heading": "Teach the Skills That Employers Want Most",
        "description": (
            "Join The Coding Science as a trainer and help learners master the same AI, data, "
            "software, and digital skills that power modern careers."
        ),
        "apply_btn_text": "Apply Now",
        "background": {
            "type": "gradient",
            "value": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
            "image_url": "/assets/images/heroBg.png",
        },
    },
    "apply_link": (
        "https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/"
        "viewform?usp=sharing"
    ),
    "about_role": {
        "heading": "Shape the Future of Tech Education",
        "subheading": (
            "As a Technical Trainer, you will play a crucial role in delivering high-quality education "
            "and mentoring students to achieve their career goals."
        ),
        "image_url": "/assets/images/careerImg.png",
        "points": [
            {"title": "Teaching Students", "desc": "Deliver engaging and insightful lectures on industry-standard technologies.", "icon": "fas fa-users"},
            {"title": "Mentoring & Guidance", "desc": "Provide one-on-one mentorship and resolve student doubts.", "icon": "fas fa-hands-helping"},
            {"title": "Practical Sessions", "desc": "Lead hands-on coding sessions and real-world project building.", "icon": "fas fa-laptop-code"},
            {"title": "Learning Materials", "desc": "Design and update course curriculums and assignments.", "icon": "fas fa-book-open"},
        ],
    },
    "hiring_domains": [
        {"title": "Python For Data Science", "icon": "fab fa-python"},
        {"title": "Machine Learning With Python", "icon": "fas fa-brain"},
        {"title": "Generative AI For GenZ", "icon": "fas fa-robot"},
        {"title": "Agentic AI Mastery", "icon": "fas fa-microchip"},
        {"title": "Full Stack Development", "icon": "fas fa-layer-group"},
        {"title": "Prompt Engineering", "icon": "fas fa-comment-dots"},
        {"title": "AI Tools & Workflow Automation", "icon": "fas fa-wand-magic-sparkles"},
        {"title": "Career-Ready Tech Skills", "icon": "fas fa-rocket"},
    ],
}
