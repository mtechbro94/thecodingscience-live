<?php
// views/trainer_blog_form.php

require_once __DIR__ . '/../includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_logged_in()) {
    set_flash('danger', 'Please login to access this page.');
    redirect('/login');
}

$user = current_user();

if ($user['role'] !== 'trainer' && $user['role'] !== 'admin') {
    redirect('/dashboard');
}

if ($user['role'] === 'admin' && !isset($_GET['view_as_trainer'])) {
    redirect('/admin/blog_form' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
}

$page_title = "Create Blog Post";
$blog = [];
$errors = [];

$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $blog_id > 0;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? AND (author_id = ? OR ? = 1)");
    $stmt->execute([$blog_id, $user['id'], $user['role'] === 'admin' ? 1 : 0]);
    $blog = $stmt->fetch();

    if (!$blog) {
        set_flash('danger', 'Blog post not found or you do not have permission to edit it.');
        redirect('/trainer-blogs');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? generate_slug($title));
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $date = date('Y-m-d');

    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";

    $image = $blog['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['image']['error'];
        
        if ($file_error === UPLOAD_ERR_INI_SIZE) {
            $errors[] = "Image is too large. Maximum size is 2MB.";
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
                $sql = "UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, is_published = ? WHERE id = ? AND author_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $is_published, $blog_id, $user['id']]);
                set_flash('success', 'Blog post updated successfully.');
            } else {
                $sql = "INSERT INTO blogs (title, slug, excerpt, content, image, author, author_id, date, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $excerpt, $content, $image, $user['name'], $user['id'], $date, $is_published]);
                set_flash('success', 'Blog post created successfully.');
            }
            redirect('/trainer-blogs');
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
.editor-toolbar {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}
.editor-preview-side {
    border-radius: 0 0.5rem 0.5rem 0 !important;
}
.EasyMDEContainer {
    border-radius: 0.5rem;
}
.editor-preview {
    border-radius: 0 0 0.5rem 0.5rem;
}
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
    border-left: 4px solid #198754;
    padding-left: 1rem;
    margin-left: 0;
    color: #6c757d;
}
.blog-preview-content img {
    max-width: 100%;
    border-radius: 0.5rem;
}
</style>

<section class="py-4" style="margin-top: 80px;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo $is_edit ? 'Edit Blog' : 'Create New Blog'; ?></h2>
            <a href="/trainer-blogs" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to My Blogs
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success">Draft auto-saved!</div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="blogForm">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Post Title *</label>
                                <input type="text" class="form-control form-control-lg" name="title" id="blogTitle" required
                                       value="<?php echo htmlspecialchars($blog['title'] ?? ($_POST['title'] ?? '')); ?>"
                                       placeholder="Enter an engaging title for your blog">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text">/blog/</span>
                                    <input type="text" class="form-control" name="slug" id="blogSlug" 
                                           value="<?php echo htmlspecialchars($blog['slug'] ?? ($_POST['slug'] ?? '')); ?>">
                                </div>
                                <small class="text-muted">The URL for this post</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Short Summary</label>
                                <textarea class="form-control" name="excerpt" rows="2" 
                                          placeholder="A brief summary that appears in blog listings"><?php echo htmlspecialchars($blog['excerpt'] ?? ($_POST['excerpt'] ?? '')); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0 fw-bold">Content (Markdown)</label>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('heading')">
                                            <i class="bi bi-type-h1"></i> Heading
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('bold')">
                                            <i class="bi bi-type-bold"></i> Bold
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('italic')">
                                            <i class="bi bi-type-italic"></i> Italic
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('link')">
                                            <i class="bi bi-link-45deg"></i> Link
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('image')">
                                            <i class="bi bi-image"></i> Image
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('code')">
                                            <i class="bi bi-code-slash"></i> Code
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('quote')">
                                            <i class="bi bi-quote"></i> Quote
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="insertTemplate('list')">
                                            <i class="bi bi-list-ul"></i> List
                                        </button>
                                    </div>
                                </div>
                                <textarea id="markdownEditor" name="content"><?php echo htmlspecialchars($blog['content'] ?? ($_POST['content'] ?? '')); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Live Preview</h5>
                        </div>
                        <div class="card-body">
                            <div id="previewContent" class="blog-preview-content">
                                <p class="text-muted">Start typing to see preview...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Publish Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Author</label>
                                <div class="d-flex align-items-center">
                                    <?php
                                    $profile_img = $_SESSION['user_profile_image'] ?? null;
                                    if ($profile_img): ?>
                                        <img src="/assets/images/profiles/<?php echo $profile_img; ?>" alt="Profile"
                                            class="rounded-circle me-2"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-success text-white rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="fw-bold"><?php echo $user['name']; ?></span>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                       <?php echo ($blog['is_published'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_published">Publish immediately</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i><?php echo $is_edit ? 'Update Post' : 'Publish Post'; ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-image me-2"></i>Featured Image</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($blog['image'])): ?>
                                <img src="<?php echo get_image_url($blog['image']); ?>" class="img-fluid rounded mb-3 shadow-sm" 
                                     id="previewImage" alt="Blog featured image">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                    <label class="form-check-label text-danger" for="remove_image">Remove image</label>
                                </div>
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                                     style="height: 150px;" id="imagePlaceholder">
                                    <i class="fas fa-image fa-3x text-secondary"></i>
                                </div>
                                <img src="" class="img-fluid rounded mb-3 shadow-sm d-none" id="previewImage" alt="Blog featured image">
                            <?php endif; ?>
                            <input class="form-control" type="file" name="image" accept="image/*" id="imageInput">
                            <small class="text-muted">Recommended: 1200x630px</small>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-markdown me-2"></i>Markdown Tips</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-2"><code># Heading 1</code> - Main heading</li>
                                <li class="mb-2"><code>## Heading 2</code> - Subheading</li>
                                <li class="mb-2"><code>**bold**</code> - <strong>Bold text</strong></li>
                                <li class="mb-2"><code>*italic*</code> - <em>Italic text</em></li>
                                <li class="mb-2"><code>[link](url)</code> - Add links</li>
                                <li class="mb-2"><code>![alt](img url)</code> - Add images</li>
                                <li class="mb-2"><code>- item</code> - Bullet list</li>
                                <li class="mb-2"><code>1. item</code> - Numbered list</li>
                                <li class="mb-2"><code>> quote</code> - Blockquote</li>
                                <li><code>```code```</code> - Code block</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    const easyMDE = new EasyMDE({
        element: document.getElementById('markdownEditor'),
        spellChecker: false,
        autosave: {
            enabled: true,
            uniqueId: "trainer_blog_editor_<?php echo $blog_id ?: 'new'; ?>",
            delay: 10,
        },
        height: "450px",
        placeholder: "Write your amazing blog post here...",
        status: ['autosave', 'lines', 'words'],
        toolbar: [
            'bold', 'italic', 'heading', '|',
            'code', 'quote', 'unordered-list', 'ordered-list', '|',
            'link', 'image', '|',
            'preview', 'side-by-side', 'fullscreen', '|'
        ],
        uploadImage: true,
        imagePathAbsolute: true,
    });

    // Live preview update
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
            case 'quote':
                text = selection ? `> ${selection}` : '> Your quote here';
                break;
            case 'list':
                text = selection ? `- ${selection.replace(/\n/g, '\n- ')}` : '- Item 1\n- Item 2\n- Item 3';
                break;
        }
        
        cm.replaceSelection(text);
        cm.focus();
    }

    // Image preview
    const imageInput = document.getElementById('imageInput');
    const previewImage = document.getElementById('previewImage');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImage) {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('d-none');
                        if (imagePlaceholder) imagePlaceholder.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>
