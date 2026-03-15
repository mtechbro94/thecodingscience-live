<?php
/**
 * Career Page Configuration
 * 
 * This file allows you to modify all content on the /career page without 
 * touching the database or complex admin panels.
 */

return [
    'hero' => [
        'badge' => 'We are hiring!',
        'heading' => 'Join Our Team as a Technical Trainer',
        'description' => 'Inspire the next generation of developers and technologists. Help shape futures, impart practical skills, and grow your own expertise with The Coding Science.',
        'apply_btn_text' => 'Apply Now',
        'background' => [
            'type' => 'gradient', // 'gradient' or 'image'
            'value' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'image_url' => '/assets/images/career-hero-bg.jpg'
        ]
    ],

    'apply_link' => 'https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/viewform?usp=sharing',

    'about_role' => [
        'heading' => 'Shape the Future of Tech Education',
        'subheading' => 'As a Technical Trainer, you will play a crucial role in delivering high-quality education and mentoring students to achieve their career goals.',
        'image_url' => '/assets/images/shaping-future.jpg', // Add your image path here
        'points' => [
            ['title' => 'Teaching Students', 'desc' => 'Deliver engaging and insightful lectures on industry-standard technologies.', 'icon' => 'fas fa-users'],
            ['title' => 'Mentoring & Guidance', 'desc' => 'Provide one-on-one mentorship and resolve student doubts.', 'icon' => 'fas fa-hands-helping'],
            ['title' => 'Practical Sessions', 'desc' => 'Lead hands-on coding sessions and real-world project building.', 'icon' => 'fas fa-laptop-code'],
            ['title' => 'Learning Materials', 'desc' => 'Design and update course curriculums and assignments.', 'icon' => 'fas fa-book-open']
        ]
    ],

    'hiring_domains' => [
        ['title' => 'Python Programming', 'icon' => 'fab fa-python'],
        ['title' => 'Full Stack Web Development', 'icon' => 'fas fa-layer-group'],
        ['title' => 'AI & Machine Learning', 'icon' => 'fas fa-brain'],
        ['title' => 'Ethical Hacking & Pen Testing', 'icon' => 'fas fa-user-secret'],
        ['title' => 'Data Science & Analytics', 'icon' => 'fas fa-chart-pie'],
        ['title' => 'Prompt Engineering', 'icon' => 'fas fa-comment-dots'],
        ['title' => 'AI Tools', 'icon' => 'fas fa-microchip'],
        ['title' => 'Mathematical Aptitude', 'icon' => 'fas fa-square-root-alt']
    ]
];
