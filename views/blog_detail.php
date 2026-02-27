<?php
// views/blog_detail.php

// Get Blog Slug from URL
$slug = isset($blog_slug) ? $blog_slug : '';

if (empty($slug)) {
    redirect('/blogs');
}

// Fetch Blog Details with Author Info
$stmt = $pdo->prepare("SELECT b.*, u.name as author_name, u.profile_image as author_image FROM blogs b 
                        LEFT JOIN users u ON b.author_id = u.id 
                        WHERE b.slug = ? AND b.is_published = 1");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

// Fallback to author field if no author_id
if (empty($blog['author_name'])) {
    $blog['author_name'] = $blog['author'];
    $blog['author_image'] = null;
}

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
                        <?php if ($blog['author_image']): ?>
                            <img src="/assets/images/profiles/<?php echo htmlspecialchars($blog['author_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['author_name']); ?>"
                                 class="rounded-circle me-2"
                                 style="width: 32px; height: 32px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-circle me-2"></i>
                        <?php endif; ?>
                        <span class="fw-bold text-dark">
                            <?php echo htmlspecialchars($blog['author_name']); ?>
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
                    $whatsapp_text = urlencode($blog['title'] . "\n\n" . $full_url);

                    $share_links = [
                        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
                        'twitter' => "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}",
                        'linkedin' => "https://www.linkedin.com/shareArticle?mini=true&url={$encoded_url}&title={$encoded_title}",
                        'whatsapp' => "https://wa.me/?text={$whatsapp_text}"
                    ];
                    ?>
                    <div class="share-buttons">
                        <span class="me-2 text-muted">Share:</span>
                        <a href="<?php echo $share_links['whatsapp']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-success rounded-circle me-2" title="Share on WhatsApp"><i
                                class="fab fa-whatsapp"></i></a>
                        <a href="<?php echo $share_links['facebook']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-primary rounded-circle me-2" title="Share on Facebook"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo $share_links['twitter']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-dark rounded-circle me-2" title="Share on X/Twitter"><i
                                class="fab fa-twitter"></i></a>
                        <a href="<?php echo $share_links['linkedin']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-primary rounded-circle" title="Share on LinkedIn"><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>