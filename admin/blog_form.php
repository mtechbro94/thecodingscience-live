<?php
// admin/blog_form.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Blog Post";
$blog = [];
$errors = [];

// Get Blog ID if editing
$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $blog_id > 0;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$blog_id]);
    $blog = $stmt->fetch();

    if (!$blog) {
        set_flash('danger', 'Blog post not found.');
        redirect('/admin/blogs');
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? generate_slug($title));
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $author = sanitize($_POST['author'] ?? $_SESSION['user_name']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $date = date('Y-m-d');

    // Validation
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";

    // Handle Image Upload
    $image = $blog['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('blog_', true) . '.' . $ext;
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image = $new_filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                $sql = "UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, author = ?, is_published = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $author, $is_published, $blog_id]);
                set_flash('success', 'Blog post updated successfully.');
            } else {
                $sql = "INSERT INTO blogs (title, slug, excerpt, content, image, author, date, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $author, $date, $is_published]);
                set_flash('success', 'Blog post created successfully.');
            }
            redirect('/admin/blogs');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<!-- EasyMDE CSS -->
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $is_edit ? 'Edit Blog' : 'Create Blog'; ?></h1>
    <a href="/admin/blogs" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Blogs
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Post Title *</label>
                        <input type="text" class="form-control form-control-lg" name="title" id="blogTitle" required
                               value="<?php echo htmlspecialchars($blog['title'] ?? ($_POST['title'] ?? '')); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="blogSlug" 
                               value="<?php echo htmlspecialchars($blog['slug'] ?? ($_POST['slug'] ?? '')); ?>">
                        <small class="text-muted">The URL fragment for this post.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea class="form-control" name="excerpt" rows="2"><?php echo htmlspecialchars($blog['excerpt'] ?? ($_POST['excerpt'] ?? '')); ?></textarea>
                        <small class="text-muted">A short summary for lists.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content (Markdown) *</label>
                        <textarea id="markdownEditor" name="content"><?php echo htmlspecialchars($blog['content'] ?? ($_POST['content'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Publish Details</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" name="author" 
                               value="<?php echo htmlspecialchars($blog['author'] ?? ($_POST['author'] ?? $_SESSION['user_name'])); ?>">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                               <?php echo ($blog['is_published'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Featured Image</h5></div>
                <div class="card-body text-center">
                    <?php if (!empty($blog['image'])): ?>
                        <img src="<?php echo get_image_url($blog['image']); ?>" class="img-fluid rounded mb-3 shadow-sm">
                    <?php endif; ?>
                    <input class="form-control" type="file" name="image" accept="image/*">
                </div>
            </div>

            <div class="d-grid shadow-sm">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Save Post
                </button>
            </div>
        </div>
    </div>
</form>

<!-- EasyMDE JS -->
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script>
    const easyMDE = new EasyMDE({
        element: document.getElementById('markdownEditor'),
        spellChecker: false,
        autosave: {
            enabled: true,
            uniqueId: "blog_editor_<?php echo $blog_id; ?>",
        },
        height: "400px",
        placeholder: "Write your masterpiece here...",
    });

    // Auto-generate slug from title
    document.getElementById('blogTitle').addEventListener('blur', function() {
        const title = this.value;
        const slugInput = document.getElementById('blogSlug');
        if (slugInput.value === '') {
            slugInput.value = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

