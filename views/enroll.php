<?php
// views/enroll.php

// Ensure user is logged in
if (!is_logged_in()) {
    $course_id = isset($id) ? (int) $id : '';
    $redirect = $course_id ? '/course/' . $course_id : '/courses';
    set_flash('info', 'Please login to enroll in a course.');
    redirect('/login?redirect=' . urlencode($redirect));
}

// Get Course ID from URL
$course_id = isset($id) ? (int) $id : 0;

if ($course_id === 0) {
    set_flash('danger', 'Invalid course specified.');
    redirect('/courses');
}

// Fetch Course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash('danger', 'Course not found.');
    redirect('/courses');
}

$user = current_user();

// Check if already enrolled
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $course['id']]);
$existing_enrollment = $stmt->fetch();

if ($existing_enrollment) {
    if ($existing_enrollment['status'] === 'completed') {
        set_flash('info', 'You are already enrolled in this course.');
        redirect('/dashboard');
    } else {
        // Resume payment or check status
        set_flash('warning', 'You have a pending enrollment for this course. Please complete payment.');
        redirect('/dashboard'); // Or redirect to a payment page
    }
}

// Handle Enrollment (POST request usually, or auto-create pending enrollment for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'upi'; // Default to UPI for now

    try {
        // Create Pending Enrollment
        $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, ?, 'pending', ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id'], $course['id'], $payment_method, $course['price']]);

        $enrollment_id = $pdo->lastInsertId();

        set_flash('success', 'Enrollment initiated! Please complete your payment.');
        
        // Redirect to payment page
        redirect('/razorpay-payment/' . $enrollment_id . '?from=enroll');

    } catch (PDOException $e) {
        set_flash('danger', 'Enrollment failed: ' . $e->getMessage());
        redirect('/course/' . $course['id']);
    }
}
?>