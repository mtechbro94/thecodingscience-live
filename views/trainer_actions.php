<?php
// views/trainer_actions.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();
if ($user['role'] !== 'trainer' && $user['role'] !== 'admin') {
    redirect('/dashboard');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method.');
    redirect('/trainer-dashboard');
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Invalid request.');
    redirect('/trainer-dashboard');
}

$action = $_POST['action'] ?? '';
$course_id = (int)($_POST['course_id'] ?? 0);

if ($course_id <= 0) {
    set_flash('danger', 'Invalid request.');
    redirect('/trainer-dashboard');
}

// Verify Course Ownership (or Admin)
$stmt = $pdo->prepare("SELECT trainer_id FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course || ($user['role'] !== 'admin' && $course['trainer_id'] != $user['id'])) {
    set_flash('danger', 'Unauthorized action.');
    redirect('/trainer-dashboard');
}

// Handle Actions
switch ($action) {
    case 'update_live_settings':
        $live_link = sanitize($_POST['live_link'] ?? '');
        $batch_timing = sanitize($_POST['batch_timing'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE courses SET live_link = ?, batch_timing = ? WHERE id = ?");
        $stmt->execute([$live_link, $batch_timing, $course_id]);
        
        set_flash('success', 'Live session settings updated.');
        break;

    case 'add_recording':
        $title = sanitize($_POST['title'] ?? '');
        $url = $_POST['recording_url'] ?? '';
        
        if (!empty($title) && !empty($url)) {
            $stmt = $pdo->prepare("INSERT INTO course_recordings (course_id, title, recording_url) VALUES (?, ?, ?)");
            $stmt->execute([$course_id, $title, $url]);
            set_flash('success', 'Recording added successfully.');
        } else {
            set_flash('danger', 'Please provide both title and URL.');
        }
        break;

    case 'delete_recording':
        $rec_id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM course_recordings WHERE id = ? AND course_id = ?");
        $stmt->execute([$rec_id, $course_id]);
        set_flash('success', 'Recording deleted.');
        break;

    case 'add_resource':
        $title = sanitize($_POST['title'] ?? '');
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['resource_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'txt'];
            
            if (in_array($ext, $allowed)) {
                $filename = uniqid('res_', true) . '.' . $ext;
                $upload_dir = BASE_PATH . '/assets/uploads/resources/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    $stmt = $pdo->prepare("INSERT INTO course_resources (course_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$course_id, $title, $filename, $ext]);
                    set_flash('success', 'Resource uploaded successfully.');
                } else {
                    set_flash('danger', 'Failed to save file.');
                }
            } else {
                set_flash('danger', 'Invalid file type.');
            }
        }
        break;

    case 'delete_resource':
        $res_id = (int)($_POST['id'] ?? 0);
        // Fetch filename to delete from disk
        $stmt = $pdo->prepare("SELECT file_path FROM course_resources WHERE id = ? AND course_id = ?");
        $stmt->execute([$res_id, $course_id]);
        $res = $stmt->fetch();
        
        if ($res) {
            $full_path = BASE_PATH . '/assets/uploads/resources/' . $res['file_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            $stmt = $pdo->prepare("DELETE FROM course_resources WHERE id = ?");
            $stmt->execute([$res_id]);
            set_flash('success', 'Resource deleted.');
        }
        break;
}

redirect("/trainer-manage-course/$course_id");
?>
