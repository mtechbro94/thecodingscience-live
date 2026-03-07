<?php
// admin/trainer_positions.php - Manage Trainer Career Positions

require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!is_admin()) {
    redirect('/login');
}

$page_title = "Manage Trainer Positions";

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $position_id = intval($_POST['delete_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM trainer_positions WHERE id = ?");
        $stmt->execute([$position_id]);
        set_flash('success', 'Trainer position deleted successfully!');
    } catch (PDOException $e) {
        set_flash('danger', 'Error deleting position: ' . $e->getMessage());
    }
    redirect('/admin/trainer_positions');
}

// Fetch all trainer positions
try {
    $stmt = $pdo->prepare("SELECT * FROM trainer_positions ORDER BY created_at DESC");
    $stmt->execute();
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $positions = [];
    set_flash('danger', 'Error fetching positions: ' . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-briefcase"></i> Trainer Positions</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="/admin/trainer_position_form" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Position
            </a>
        </div>
    </div>

    <?php
    $flash = get_flash();
    if ($flash) {
        echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($flash['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    ?>

    <?php if (!empty($positions)): ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($positions as $position): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($position['title']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($position['location'] ?? 'Remote'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($position['employment_type']); ?></td>
                            <td><?php echo $position['minimum_experience'] ? htmlspecialchars($position['minimum_experience']) . ' years' : 'Not specified'; ?></td>
                            <td>
                                <?php if ($position['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo date('M d, Y', strtotime($position['created_at'])); ?></small></td>
                            <td>
                                <a href="/admin/trainer_position_form?id=<?php echo $position['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $position['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No trainer positions found. <a href="/admin/trainer_position_form">Create one now</a>!
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
