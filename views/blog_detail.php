<?php
// views/blog_detail.php

// Get Blog Slug from URL
$slug = isset($blog_slug) ? $blog_slug : '';

if (empty($slug)) {
    redirect('/blogs');
}

// Fetch Blog Details
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    redirect('/blogs');
}

$page_title = $blog['title'];

// Open Graph Meta Tags
$og_title = $blog['title'];
$og_description = $blog['summary'] ?: substr(strip_tags($blog['content']), 0, 160);
$og_image = get_image_url($blog['image']);
$og_url = SITE_URL . '/blog/' . $blog['slug'];
$og_type = 'article';

require_once 'includes/header.php';
?>


<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/blogs">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo htmlspecialchars($blog['title']); ?>
                        </li>
                    </ol>
                </nav>

                <?php if ($blog['image']): ?>
                    <img src="<?php echo get_image_url($blog['image']); ?>" class="img-fluid rounded shadow-sm mb-4"
                        alt="<?php echo htmlspecialchars($blog['title']); ?>"
                        style="width: 100%; max-height: 450px; object-fit: cover;">
                <?php endif; ?>

                <div class="d-flex align-items-center mb-4 text-muted">
                    <div class="d-flex align-items-center me-4">
                        <i class="fas fa-user-circle me-2"></i>
                        <span>
                            <?php echo htmlspecialchars($blog['author']); ?>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span>
                            <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                        </span>
                    </div>
                </div>

                <h1 class="display-4 font-weight-bold mb-4">
                    <?php echo htmlspecialchars($blog['title']); ?>
                </h1>

                <!-- Markdown Content -->
                <div id="blogContent" class="blog-content" style="line-height: 1.8; color: #333; font-size: 1.1rem;">
                    <!-- Content will be rendered here by marked.js -->
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Hidden raw content for JS -->
                <textarea id="rawContent"
                    style="display:none;"><?php echo htmlspecialchars($blog['content']); ?></textarea>

                <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const raw = document.getElementById('rawContent').value;
                        document.getElementById('blogContent').innerHTML = marked.parse(raw);
                    });
                </script>


                <hr class="my-5">

                <div class="d-flex justify-content-between">
                    <a href="/blogs" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Blog
                    </a>
                    <?php
                    $full_url = SITE_URL . '/blog/' . $blog['slug'];
                    $encoded_url = urlencode($full_url);
                    $encoded_title = urlencode($blog['title']);

                    $share_links = [
                        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
                        'twitter' => "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}",
                        'linkedin' => "https://www.linkedin.com/shareArticle?mini=true&url={$encoded_url}&title={$encoded_title}"
                    ];
                    ?>
                    <div class="share-buttons">
                        <span class="me-2 text-muted">Share:</span>
                        <a href="<?php echo $share_links['facebook']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle me-2"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo $share_links['twitter']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle me-2"><i
                                class="fab fa-twitter"></i></a>
                        <a href="<?php echo $share_links['linkedin']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle"><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>