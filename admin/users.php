<?php
// admin/users.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Users";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int) $_GET['id'];

    if ($action === 'approve_trainer') {
        $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        set_flash('success', 'Trainer approved successfully.');
    } elseif ($action === 'delete') {
        // Prevent deleting self
        if ($user_id !== current_user()['id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            set_flash('success', 'User deleted successfully.');
        } else {
            set_flash('danger', 'You cannot delete your own account.');
        }
    }
    redirect('/admin/users');
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Fetch Users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Total Users for Pagination
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_pages = ceil($total_users / $per_page);

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/users/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add User
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php echo $user['id']; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-secondary text-white me-2 d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; border-radius: 50%; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'bg-secondary';
                                if ($user['role'] === 'admin')
                                    $badge_class = 'bg-danger';
                                if ($user['role'] === 'trainer')
                                    $badge_class = 'bg-success';
                                if ($user['role'] === 'student')
                                    $badge_class = 'bg-primary';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'trainer'): ?>
                                    <?php if ($user['is_approved']): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($user['role'] === 'trainer' && !$user['is_approved']): ?>
                                        <a href="/admin/users?action=approve_trainer&id=<?php echo $user['id']; ?>"
                                            class="btn btn-success" title="Approve Trainer">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/users/edit/<?php echo $user['id']; ?>" class="btn btn-outline-secondary"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] !== current_user()['id']): ?>
                                        <a href="/admin/users?action=delete&id=<?php echo $user['id']; ?>"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>