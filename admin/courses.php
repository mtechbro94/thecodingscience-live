<?php
// admin/courses.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Courses";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/courses');
    }

    $action = $_POST['action'];
    $course_id = (int) $_POST['id'];

    if ($action === 'delete') {
        // Check if there are enrollments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $enrollment_count = $stmt->fetchColumn();

        if ($enrollment_count > 0) {
            set_flash('danger', 'Cannot delete course with existing enrollments.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$course_id]);
            set_flash('success', 'Course deleted successfully.');
        }
    }
    redirect('/admin/courses');
}

// Fetch Courses
$stmt = $pdo->prepare("SELECT c.*, u.name as trainer_name FROM courses c LEFT JOIN users u ON c.trainer_id = u.id ORDER BY c.created_at DESC");
$stmt->execute();
$courses = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Course Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/course_form" class="btn btn-sm btn-primary">

            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Price</th>
                        <th>Trainer</th>
                        <th>Duration</th>
                        <th>Level</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td>
                                <?php echo $course['id']; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($course['image']): ?>
                                        <img src="<?php echo get_image_url($course['image']); ?>" alt="" class="rounded me-2"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($course['name']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo substr($course['summary'], 0, 50) . '...'; ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>₹
                                <?php echo number_format($course['price'], 2); ?>
                            </td>
                            <td>
                                <?php echo $course['trainer_name'] ? htmlspecialchars($course['trainer_name']) : '<span class="text-muted">Unassigned</span>'; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($course['duration']); ?>
                            </td>
                            <td><span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($course['level']); ?>
                                </span></td>
                            <td>
                                <?php if ($course['is_featured']): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Featured</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/course/<?php echo $course['id']; ?>" class="btn btn-outline-info"
                                        target="_blank" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/course-content?id=<?php echo $course['id']; ?>"
                                        class="btn btn-outline-info" title="Manage Content">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <a href="/admin/course_form?id=<?php echo $course['id']; ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this course?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
