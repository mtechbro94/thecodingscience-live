<?php
// views/apply_handler.php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$type = $type ?? ''; // From router
$id = $id ?? 0;     // From router

if (!$id || $type !== 'internship') {
    redirect('/');
}

// 1. Check if logged in
if (!is_logged_in()) {
    set_flash('info', 'Please login to your account to apply for this ' . $type . '.');
    redirect('/login?redirect=' . urlencode('/apply/' . $type . '/' . $id));
}

// 2. Fetch the link
$link = '';
if ($type === 'internship') {
    $item = get_internship($id);
    $link = $item['google_form_link'] ?? '';
}

if (empty($link)) {
    set_flash('danger', 'Application link not found or position is no longer active.');
    redirect($type === 'internship' ? '/internships' : '/career');
}

// 3. Redirect to Google Form
redirect($link);
?>