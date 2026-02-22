<?php
// admin/blogs.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Blog Management";

// Handle Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Blog post deleted successfully.');
    } catch (PDOException $e) {
        set_flash('danger', 'Error deleting blog: ' . $e->getMessage());
    }
    redirect('/admin/blogs');
}

// Fetch Blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Blog Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/blog_form" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Blog Post
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
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($blogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center p-4 text-muted">No blog posts found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($blogs as $blog): ?>
                        <tr>
                            <td>
                                <?php echo $blog['id']; ?>
                            </td>
                            <td>
                                <?php if ($blog['image']): ?>
                                    <img src="<?php echo get_image_url($blog['image']); ?>" alt="Blog" class="rounded"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <?php echo htmlspecialchars($blog['title']); ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($blog['slug']); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($blog['author']); ?>
                            </td>
                            <td>
                                <?php if ($blog['is_published']): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/blog_form?id=<?php echo $blog['id']; ?>" class="btn btn-outline-primary"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/blog/<?php echo $blog['slug']; ?>" class="btn btn-outline-secondary"
                                        target="_blank" title="View">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <a href="/admin/blogs?action=delete&id=<?php echo $blog['id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this post?');"
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