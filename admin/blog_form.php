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
    $author_id = $_SESSION['user_id'] ?? null;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $date = date('Y-m-d');

    // Validation
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";

    // Handle Image Upload
    $image = $blog['image'] ?? null;
    // DEBUG: Log upload attempt
    error_log("Image upload check: " . print_r($_FILES['image'] ?? [], true));
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['image']['error'];
        
        if ($file_error === UPLOAD_ERR_INI_SIZE) {
            $errors[] = "Image is too large. Maximum size is 2MB.";
        } elseif ($file_error === UPLOAD_ERR_FORM_SIZE) {
            $errors[] = "Image exceeds the maximum allowed file size.";
        } elseif ($file_error === UPLOAD_ERR_PARTIAL) {
            $errors[] = "Image was only partially uploaded.";
        } elseif ($file_error === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = uniqid('blog_', true) . '.' . $ext;
                $upload_dir = __DIR__ . '/../assets/images/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                    $image = $new_filename;
                } else {
                    $errors[] = "Failed to upload image. Check directory permissions.";
                }
            } else {
                $errors[] = "Invalid file type. Allowed: jpg, jpeg, png, webp";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                $sql = "UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, author = ?, author_id = ?, is_published = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $author, $author_id, $is_published, $blog_id]);
                set_flash('success', 'Blog post updated successfully.');
            } else {
                $sql = "INSERT INTO blogs (title, slug, excerpt, content, image, author, author_id, date, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $author, $author_id, $date, $is_published]);
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
.blog-preview-content {
    line-height: 1.8;
    color: #333;
}
.blog-preview-content h1, .blog-preview-content h2, .blog-preview-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}
.blog-preview-content pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}
.blog-preview-content blockquote {
    border-left: 4px solid #0d6efd;
    padding-left: 1rem;
    margin-left: 0;
    color: #6c757d;
}
.blog-preview-content img {
    max-width: 100%;
    border-radius: 0.5rem;
}
</style>

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
<?php endif; 

// DEBUG: Show image value being saved
if (isset($image) && !empty($image)): ?>
    <div class="alert alert-info">Image being saved: <?php echo htmlspecialchars($image); ?></div>
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Content (Markdown) *</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('heading')">
                                    <i class="bi bi-type-h1"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('bold')">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('italic')">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('link')">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('image')">
                                    <i class="bi bi-image"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('code')">
                                    <i class="bi bi-code-slash"></i>
                                </button>
                            </div>
                        </div>
                        <textarea id="markdownEditor" name="content"><?php echo htmlspecialchars($blog['content'] ?? ($_POST['content'] ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Live Preview</h6>
                </div>
                <div class="card-body">
                    <div id="previewContent" class="blog-preview-content">
                        <p class="text-muted">Start typing to see preview...</p>
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
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    const easyMDE = new EasyMDE({
        element: document.getElementById('markdownEditor'),
        spellChecker: false,
        autosave: {
            enabled: true,
            uniqueId: "blog_editor_<?php echo $blog_id; ?>",
            delay: 10,
        },
        height: "400px",
        placeholder: "Write your masterpiece here...",
        status: ['autosave', 'lines', 'words'],
        toolbar: [
            'bold', 'italic', 'heading', '|',
            'code', 'quote', 'unordered-list', 'ordered-list', '|',
            'link', 'image', '|',
            'preview', 'side-by-side', 'fullscreen', '|'
        ],
    });

    // Live preview
    const previewContent = document.getElementById('previewContent');
    const updatePreview = () => {
        const raw = easyMDE.value();
        if (raw.trim()) {
            previewContent.innerHTML = marked.parse(raw);
        } else {
            previewContent.innerHTML = '<p class="text-muted">Start typing to see preview...</p>';
        }
    };
    easyMDE.codemirror.on('change', updatePreview);
    updatePreview();

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

    // Quick template insertion
    function insertTemplate(type) {
        const cm = easyMDE.codemirror;
        const selection = cm.getSelection();
        let text = '';
        
        switch(type) {
            case 'heading':
                text = selection ? `## ${selection}` : '## New Heading';
                break;
            case 'bold':
                text = selection ? `**${selection}**` : '**bold text**';
                break;
            case 'italic':
                text = selection ? `*${selection}*` : '*italic text*';
                break;
            case 'link':
                text = selection ? `[${selection}](url)` : '[link text](https://example.com)';
                break;
            case 'image':
                text = selection ? `![${selection}](image-url)` : '![Alt text](https://example.com/image.jpg)';
                break;
            case 'code':
                text = selection ? `\`\`\`\n${selection}\n\`\`\`` : '```\ncode here\n```';
                break;
        }
        
        cm.replaceSelection(text);
        cm.focus();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

