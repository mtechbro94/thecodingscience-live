<?php
// admin/users.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Users";

// Handle Edit User Form
$edit_id = 0;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];

    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_user = $stmt->fetch();

        if (!$edit_user) {
            set_flash('danger', 'User not found.');
            redirect('/admin/users');
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                set_flash('danger', 'Invalid request.');
                redirect('/admin/users');
            }
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $role = sanitize($_POST['role'] ?? 'student');
            $is_approved = isset($_POST['is_approved']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($email)) {
                set_flash('danger', 'Name and email are required.');
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, is_approved = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $is_approved, $is_active, $edit_id]);
                set_flash('success', 'User updated successfully.');
                redirect('/admin/users');
            }
        }

        $page_title = "Edit User";
        include __DIR__ . '/includes/header.php';
        ?>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Edit User</h1>
            <a href="/admin/users" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        <?php echo get_flash(); ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name"
                                    value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role">
                                    <option value="student" <?php echo $edit_user['role'] === 'student' ? 'selected' : ''; ?>>
                                        Student</option>
                                    <option value="trainer" <?php echo $edit_user['role'] === 'trainer' ? 'selected' : ''; ?>>
                                        Trainer</option>
                                    <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                    </option>
                                </select>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" <?php echo $edit_user['is_approved'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_approved">Approved (for Trainers)</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $edit_user['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active Account</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
}

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    if (!validate_csrf_token($_GET['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid or expired action link.');
        redirect('/admin/users');
    }

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
        <!-- <a href="/admin/users/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a> -->
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
                                    <?php if ($user['profile_image']): ?>
                                        <img src="<?php echo get_avatar($user); ?>" alt="Profile" class="rounded-circle me-2"
                                            style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #ddd;">
                                    <?php else: ?>
                                        <div class="avatar-circle avatar-circle-sm bg-secondary me-2">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
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
                                        <a href="/admin/users?action=approve_trainer&id=<?php echo $user['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>"
                                            class="btn btn-success" title="Approve Trainer">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/users?edit=<?php echo $user['id']; ?>" class="btn btn-outline-secondary"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] !== current_user()['id']): ?>
                                        <a href="/admin/users?action=delete&id=<?php echo $user['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>"
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