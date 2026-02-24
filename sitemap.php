<?php
// sitemap.php - Dynamic XML Sitemap

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Static pages
$static_pages = [
    ['loc' => SITE_URL . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => SITE_URL . '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => SITE_URL . '/courses', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => SITE_URL . '/blogs', 'priority' => '0.8', 'changefreq' => 'daily'],
    ['loc' => SITE_URL . '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
];

foreach ($static_pages as $page) {
    echo '<url>';
    echo '<loc>' . htmlspecialchars($page['loc']) . '</loc>';
    echo '<changefreq>' . $page['changefreq'] . '</changefreq>';
    echo '<priority>' . $page['priority'] . '</priority>';
    echo '</url>';
}

// Fetch published blogs
$stmt = $pdo->query("SELECT slug, updated_at FROM blogs WHERE is_published = 1 ORDER BY id DESC");
$blogs = $stmt->fetchAll();

foreach ($blogs as $blog) {
    $blog_url = SITE_URL . '/blog/' . htmlspecialchars($blog['slug']);
    $lastmod = $blog['updated_at'] ? date('Y-m-d', strtotime($blog['updated_at'])) : date('Y-m-d');
    
    echo '<url>';
    echo '<loc>' . $blog_url . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<lastmod>' . $lastmod . '</lastmod>';
    echo '<priority>0.7</priority>';
    echo '</url>';
}

// Fetch published courses
$stmt = $pdo->query("SELECT slug, updated_at FROM courses WHERE is_published = 1 ORDER BY id DESC");
$courses = $stmt->fetchAll();

foreach ($courses as $course) {
    $course_url = SITE_URL . '/course/' . htmlspecialchars($course['slug']);
    $lastmod = $course['updated_at'] ? date('Y-m-d', strtotime($course['updated_at'])) : date('Y-m-d');
    
    echo '<url>';
    echo '<loc>' . $course_url . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<lastmod>' . $lastmod . '</lastmod>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

echo '</urlset>';
