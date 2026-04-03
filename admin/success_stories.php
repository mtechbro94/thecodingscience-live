<?php
// admin/success_stories.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

function success_story_image_full_path($relative_path)
{
    if (empty($relative_path)) {
        return null;
    }

    $normalized = ltrim(str_replace('\\', '/', $relative_path), '/');
    if (strpos($normalized, 'success-stories/') !== 0) {
        return null;
    }

    return BASE_PATH . '/assets/images/' . $normalized;
}

$page_title = "Success Stories Management";
$has_photo_path = table_has_column('success_stories', 'photo_path');

// Handle Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        if ($has_photo_path) {
            $stmt = $pdo->prepare("SELECT photo_path FROM success_stories WHERE id = ?");
            $stmt->execute([$id]);
            $story_to_delete = $stmt->fetch();
            $old_file = success_story_image_full_path($story_to_delete['photo_path'] ?? null);
            if ($old_file && file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM success_stories WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Success story deleted successfully.');
    } catch (PDOException $e) {
        set_flash('danger', 'Error deleting success story: ' . $e->getMessage());
    }
    redirect('/admin/success_stories');
}

// Handle Status Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("UPDATE success_stories SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Status updated.');
    } catch (PDOException $e) {
        set_flash('danger', 'Error updating status.');
    }
    redirect('/admin/success_stories');
}

// Fetch Success Stories
try {
    $stmt = $pdo->query("SELECT * FROM success_stories ORDER BY sort_order ASC, created_at DESC");
    $stories = $stmt->fetchAll();
} catch (PDOException $e) {
    $stories = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Success Stories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/success_story_form" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Success Story
        </a>
    </div>
</div>

<?php echo get_flash(); ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Sort</th>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Photo</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stories)): ?>
                        <tr>
                            <td colspan="7" class="text-center p-4 text-muted">No success stories found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($stories as $story): ?>
                        <tr>
                            <td>
                                <?php echo $story['sort_order']; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar px-2 py-1 <?php echo $story['avatar_bg']; ?> text-white rounded-circle me-2 font-weight-bold"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($story['name'], 0, 1)); ?>
                                    </div>
                                    <span class="fw-bold">
                                        <?php echo htmlspecialchars($story['name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($story['title']); ?>
                            </td>
                            <td>
                                <?php if ($has_photo_path && !empty($story['photo_path'])): ?>
                                    <img src="<?php echo htmlspecialchars(get_image_url($story['photo_path'])); ?>" alt="<?php echo htmlspecialchars($story['name']); ?>" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted small">No photo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-warning">
                                    <?php for ($i = 0; $i < $story['rating']; $i++): ?>
                                        <i class="fas fa-star small"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <a href="/admin/success_stories?action=toggle&id=<?php echo $story['id']; ?>"
                                    class="badge <?php echo $story['is_active'] ? 'bg-success' : 'bg-secondary'; ?> text-decoration-none">
                                    <?php echo $story['is_active'] ? 'Active' : 'Inactive'; ?>
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/success_story_form?id=<?php echo $story['id']; ?>"
                                        class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/success_stories?action=delete&id=<?php echo $story['id']; ?>"
                                        class="btn btn-outline-danger" onclick="return confirm('Are you sure?');"
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
