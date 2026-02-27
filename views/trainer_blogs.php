<?php
// views/trainer_blogs.php

require_once __DIR__ . '/../includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access your blogs.');
    redirect('/login');
}

$user = current_user();

if ($user['role'] !== 'trainer' && $user['role'] !== 'admin') {
    redirect('/dashboard');
}

if ($user['role'] === 'admin' && !isset($_GET['view_as_trainer'])) {
    redirect('/admin/blogs');
}

$page_title = "My Blogs";

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ? AND author_id = ?");
        $stmt->execute([$id, $user['id']]);
        if ($stmt->rowCount() > 0) {
            set_flash('success', 'Blog post deleted successfully.');
        } else {
            set_flash('danger', 'You do not have permission to delete this post.');
        }
    } catch (PDOException $e) {
        set_flash('danger', 'Error deleting blog: ' . $e->getMessage());
    }
    redirect('/trainer-blogs');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE author_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
    $blogs = [];
    set_flash('danger', 'Error fetching blogs: ' . $e->getMessage());
}

require_once 'includes/header.php';
?>

<section class="py-4" style="margin-top: 80px;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-journal-text me-2"></i>My Blogs</h2>
            <a href="/trainer-blog/new" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Create New Blog
            </a>
        </div>

        <?php echo get_flash(); ?>

        <?php if (empty($blogs)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-journal-plus fa-3x text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4>No blog posts yet</h4>
                    <p class="text-muted mb-4">Start sharing your knowledge with the world!</p>
                    <a href="/trainer-blog/new" class="btn btn-success btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Create Your First Blog
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($blogs as $blog): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="row g-0">
                                <?php if ($blog['image']): ?>
                                    <div class="col-md-4">
                                        <img src="<?php echo get_image_url($blog['image']); ?>" class="img-fluid rounded-start h-100" 
                                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                             style="object-fit: cover; max-height: 200px;">
                                    </div>
                                    <div class="col-md-8">
                                <?php else: ?>
                                    <div class="col-12">
                                <?php endif; ?>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <?php if ($blog['is_published']): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Draft</span>
                                                <?php endif; ?>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                                </small>
                                            </div>
                                            <h5 class="card-title">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                            </h5>
                                            <p class="card-text text-muted small">
                                                <?php echo htmlspecialchars($blog['excerpt'] ?: substr(strip_tags($blog['content']), 0, 100) . '...'); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/trainer-blog/<?php echo $blog['id']; ?>" class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <?php if ($blog['is_published']): ?>
                                                        <a href="/blog/<?php echo $blog['slug']; ?>" class="btn btn-outline-secondary" target="_blank">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="/trainer-blogs?action=delete&id=<?php echo $blog['id']; ?>" 
                                                       class="btn btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this post?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
