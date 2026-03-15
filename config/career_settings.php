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
            // 'image_url' => '/assets/images/career-hero-bg.jpg' 
        ]
    ],
    
    'apply_link' => 'https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/viewform?usp=sharing',

    'specific_openings' => [
        [
            'title' => 'Python Full Stack Trainer',
            'location' => 'Remote / Hybrid',
            'employment_type' => 'Full-time',
            'description' => 'We are looking for a highly skilled Python Full Stack Developer who has a passion for teaching and mentoring students.',
            'minimum_experience' => 2,
            'expertise' => 'Python, Django/Flask, React, MySQL',
            'requirements' => 'Excellent communication skills, ability to simplify complex concepts, and hands-on experience with production-grade applications.',
            'stipend' => 'Competitive base salary + performance incentives.',
            'growth' => 'Opportunity to lead curriculum development and transition into a Senior Training Manager role.',
            'application_link' => 'https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/viewform?usp=sharing'
        ],
        [
            'title' => 'Cyber Security Specialist & Trainer',
            'location' => 'Remote',
            'employment_type' => 'Part-time / Freelance',
            'description' => 'Looking for an expert in ethical hacking and penetration testing to lead our advanced security batches.',
            'minimum_experience' => 3,
            'expertise' => 'Kali Linux, Web App Pentesting, Network Security',
            'requirements' => 'CEH or OSCP certifications are a plus. Must have experience in conducting live training sessions.',
            'stipend' => 'Industry-standard hourly/monthly rates.',
            'growth' => 'Lead our project-based security consulting wing.',
            'application_link' => 'https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/viewform?usp=sharing'
        ]
    ],

    'about_role' => [
        'heading' => 'Shape the Future of Tech Education',
        'subheading' => 'As a Technical Trainer, you will play a crucial role in delivering high-quality education and mentoring students to achieve their career goals.',
        'points' => [
            ['title' => 'Teaching Students', 'desc' => 'Deliver engaging and insightful lectures on industry-standard technologies.', 'icon' => 'fas fa-users'],
            ['title' => 'Mentoring & Guidance', 'desc' => 'Provide one-on-one mentorship and resolve student doubts.', 'icon' => 'fas fa-hands-helping'],
            ['title' => 'Practical Sessions', 'desc' => 'Lead hands-on coding sessions and real-world project building.', 'icon' => 'fas fa-laptop-code'],
            ['title' => 'Learning Materials', 'desc' => 'Design and update course curriculums and assignments.', 'icon' => 'fas fa-book-open']
        ]
    ]
];
