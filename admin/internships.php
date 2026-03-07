<?php
// admin/internships.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$page_title = "Student Internships";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $internship_id = (int) $_GET['id'];

    if ($action === 'delete') {
        // In a real application, you might want to check for associated applications before deleting.
        // For simplicity, we'll allow direct deletion for now.
        $stmt = $pdo->prepare("DELETE FROM internships WHERE id = ?");
        $stmt->execute([$internship_id]);
        set_flash('success', 'Internship deleted successfully.');
    }
    redirect('/admin/internships');
}

// Fetch Internships (Industrial only for students)
$stmt = $pdo->prepare("SELECT * FROM internships WHERE category = 'industrial' ORDER BY created_at DESC");
$stmt->execute();
$internships = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Student Internship Management</h1>
        <p class="text-muted">Manage internship opportunities for students</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/internship_form" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add New Internship
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
                        <th>Title</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($internships as $internship): ?>
                        <tr>
                            <td>
                                <?php echo $internship['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($internship['title']); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($internship['category'] == 'teaching' ? 'info' : 'success'); ?>">
                                    <?php echo ucfirst($internship['category']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($internship['duration']); ?>
                            </td>
                            <td>
                                <?php if ($internship['is_active']): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/internship_form?id=<?php echo $internship['id']; ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/internships?action=delete&id=<?php echo $internship['id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this internship?');"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
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