<?php
// admin/career_tracks.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';



if (!is_admin()) {
    redirect('/');
}

$page_title = "Career Track Programs";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $track_id = (int) $_GET['id'];

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM career_tracks WHERE id = ?");
        $stmt->execute([$track_id]);
        set_flash('success', 'Career track deleted successfully.');
    } elseif ($action === 'toggle_featured') {
        $stmt = $pdo->prepare("UPDATE career_tracks SET is_featured = NOT is_featured WHERE id = ?");
        $stmt->execute([$track_id]);
        set_flash('success', 'Featured status updated.');
    } elseif ($action === 'toggle_active') {
        $stmt = $pdo->prepare("UPDATE career_tracks SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$track_id]);
        set_flash('success', 'Status updated.');
    }
    redirect('/admin/career_tracks');
}

$stmt = $pdo->query("SELECT * FROM career_tracks ORDER BY sort_order ASC, created_at DESC");
$tracks = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Career Track Programs</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/career_track_form" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add New Track
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
                        <th>Track Name</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Courses</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tracks as $track): 
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM career_track_courses WHERE track_id = ?");
                        $stmt->execute([$track['id']]);
                        $course_count = $stmt->fetchColumn();
                    ?>
                        <tr>
                            <td><?php echo $track['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($track['image']): ?>
                                        <img src="<?php echo get_image_url($track['image']); ?>" alt="" class="rounded me-2"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-road text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($track['name']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $track['slug']; ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($track['original_price'] > 0): ?>
                                    <span class="text-decoration-line-through text-muted">₹<?php echo number_format($track['original_price'], 2); ?></span>
                                <?php endif; ?>
                                <span class="fw-bold">₹<?php echo number_format($track['price'], 2); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($track['duration'] ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo $course_count; ?> courses</span></td>
                            <td>
                                <?php if ($track['is_featured']): ?>
                                    <span class="badge bg-warning me-1">Featured</span>
                                <?php endif; ?>
                                <span class="badge bg-<?php echo $track['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $track['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/career_tracks?action=toggle_featured&id=<?php echo $track['id']; ?>"
                                        class="btn btn-outline-<?php echo $track['is_featured'] ? 'warning' : 'secondary'; ?>"
                                        title="<?php echo $track['is_featured'] ? 'Remove Featured' : 'Mark Featured'; ?>">
                                        <i class="fas fa-star"></i>
                                    </a>
                                    <a href="/admin/career_tracks?action=toggle_active&id=<?php echo $track['id']; ?>"
                                        class="btn btn-outline-<?php echo $track['is_active'] ? 'success' : 'secondary'; ?>"
                                        title="<?php echo $track['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-toggle-on"></i>
                                    </a>
                                    <a href="/admin/career_track_form?id=<?php echo $track['id']; ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/career_tracks?action=delete&id=<?php echo $track['id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this career track?');"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tracks)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-road fa-2x mb-2 d-block"></i>
                                No career tracks found. <a href="/admin/career_track_form">Create your first track</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
