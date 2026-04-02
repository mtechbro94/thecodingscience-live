<?php
// views/profile.php - Student & Trainer Profile Management
require_once 'includes/header.php';

$user = current_user();

// Redirect if not logged in
if (!is_logged_in()) {
    redirect('/login');
}

// Handle profile updates
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Trainer-specific fields
    $education = isset($_POST['education']) ? sanitize($_POST['education']) : null;
    $expertise = isset($_POST['expertise']) ? sanitize($_POST['expertise']) : null;
    $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : null;

    if (empty($name)) {
        $error_msg = 'Name is required';
    }

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $error_msg = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
        } elseif ($file['size'] > $max_size) {
            $error_msg = 'File is too large. Maximum 5MB allowed.';
        } else {
            // Create upload directory if not exists
            $upload_dir = __DIR__ . '/../assets/images/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename based on role
            $role_prefix = is_trainer() ? 'trainer' : 'student';
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $role_prefix . '_' . $user['id'] . '_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Delete old profile photo if exists
                $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $old_data = $stmt->fetch();
                if ($old_data['profile_image']) {
                    $old_file = $upload_dir . basename($old_data['profile_image']);
                    if (file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }

                $profile_image = 'assets/images/profiles/' . $filename;
            } else {
                $error_msg = 'Failed to upload profile photo';
            }
        }
    } else {
        $profile_image = null;
    }

    if (empty($error_msg)) {
        try {
            if (is_trainer()) {
                // Update trainer profile
                if ($profile_image) {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_image = ?, education = ?, expertise = ?, bio = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $phone, $profile_image, $education, $expertise, $bio, $user['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, education = ?, expertise = ?, bio = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $phone, $education, $expertise, $bio, $user['id']]);
                }
            } else {
                // Update student profile
                if ($profile_image) {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $phone, $profile_image, $user['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $phone, $user['id']]);
                }
            }

            // Update session
            $_SESSION['user_name'] = $name;
            if ($profile_image) {
                $_SESSION['user_profile_image'] = $profile_image;
            }

            $success_msg = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user_data = $stmt->fetch();

        } catch (Exception $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            $error_msg = 'Failed to update profile. Please try again.';
        }
    }
} else {
    // Fetch user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch();
}

$page_title = "My Profile";
?>

<div class="container py-5" style="margin-top: 30px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h1 class="fw-bold mb-0">My Profile</h1>
                <a href="<?php echo is_trainer() ? '/trainer-dashboard' : '/dashboard'; ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>

            <!-- Messages -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">

                    <!-- Profile Photo -->
                    <div class="mb-5 text-center">
                        <div class="position-relative d-inline-block">
                            <img id="profilePreview" 
                                src="<?php echo htmlspecialchars($user_data['profile_image'] ?? 'https://via.placeholder.com/150?text=No+Photo'); ?>" 
                                alt="Profile Photo" 
                                class="rounded-circle" 
                                width="150" height="150" 
                                style="object-fit: cover; border: 4px solid #f0f0f0;">
                            
                            <label for="profilePhotoInput" 
                                class="btn btn-primary btn-sm rounded-circle position-absolute" 
                                style="bottom: 0; right: 0; width: 48px; height: 48px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" 
                                class="d-none" onchange="previewImage(event)">
                        </div>
                        <p class="text-muted small mt-2">Click camera icon to upload photo (Max 5MB)</p>
                    </div>

                    <!-- Full Name -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Full Name *</label>
                        <input type="text" class="form-control form-control-lg" name="name" 
                            value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" 
                            placeholder="Enter your full name" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control form-control-lg" 
                            value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" 
                            disabled>
                        <small class="text-muted"><?php echo is_trainer() ? 'Username/Email for login. Cannot be changed.' : 'Connected to your Google account. Cannot be changed.'; ?></small>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="tel" class="form-control form-control-lg" name="phone" 
                            value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" 
                            placeholder="+1 (555) 000-0000">
                        <small class="text-muted">Optional - Used for course communication</small>
                    </div>

                    <!-- Trainer-specific fields -->
                    <?php if (is_trainer()): ?>
                        <hr class="my-4">
                        <h5 class="fw-bold mb-4">Professional Information</h5>

                        <!-- Education -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Education & Qualifications</label>
                            <textarea class="form-control" name="education" rows="2" 
                                placeholder="e.g., B.Tech in Computer Science, Certified by XYZ..."><?php echo htmlspecialchars($user_data['education'] ?? ''); ?></textarea>
                            <small class="text-muted">Share your educational background and certifications</small>
                        </div>

                        <!-- Expertise -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Areas of Expertise</label>
                            <textarea class="form-control" name="expertise" rows="2" 
                                placeholder="e.g., Web Development, Python, React..."><?php echo htmlspecialchars($user_data['expertise'] ?? ''); ?></textarea>
                            <small class="text-muted">Topics you're experienced in teaching</small>
                        </div>

                        <!-- Bio -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Professional Bio</label>
                            <textarea class="form-control" name="bio" rows="4" 
                                placeholder="Tell students about yourself, your teaching style, and why you're passionate about teaching..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                            <small class="text-muted">This will be displayed on your course pages</small>
                        </div>
                    <?php endif; ?>

                    <!-- Account Info -->
                    <div class="alert alert-info border-0 rounded-3 mb-4">
                        <p class="mb-1"><strong>Account Type:</strong> <?php echo ucfirst($user_data['role']); ?></p>
                        <p class="mb-1"><strong>Account Status:</strong> <?php echo $user_data['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Deactivated</span>'; ?></p>
                        <?php if (is_trainer() && !$user_data['is_approved']): ?>
                            <p class="mb-0"><strong class="text-warning">Account Status:</strong> <span class="badge bg-warning">Pending Admin Approval</span></p>
                        <?php endif; ?>
                        <p class="mb-0"><small class="text-muted">Member since <?php echo date('F j, Y', strtotime($user_data['created_at'])); ?></small></p>
                    </div>

                    <!-- Save Button -->
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <button type="reset" class="btn btn-light btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>

                </div>
            </form>

            <!-- Danger Zone -->
            <div class="card border-danger mt-5">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">These actions cannot be undone. Please be careful.</p>
                    <a href="/logout" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate file size
        if (file.size > 5 * 1024 * 1024) {
            alert('File is too large. Maximum 5MB allowed.');
            event.target.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Profile Card -->
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0 text-center"><i class="fas fa-user-circle me-2"></i> My Profile</h4>
                    </div>
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <!-- Left Side: Profile Photo -->
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <?php if (!empty($userData['profile_image'])): ?>
                                        <img src="<?php echo get_avatar($userData); ?>" alt="Profile"
                                            class="rounded-circle shadow"
                                            style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;">
                                    <?php else: ?>
                                        <div class="avatar-circle avatar-circle-lg bg-primary mx-auto shadow"
                                            style="border: 4px solid #f8f9fa;">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <button
                                        class="btn btn-sm btn-dark position-absolute bottom-0 end-0 rounded-circle p-2"
                                        onclick="document.getElementById('profile_photo_input').click()"
                                        title="Update Photo" style="width: 40px; height: 40px;">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>

                                <form id="photo_form" action="/profile" method="POST" enctype="multipart/form-data"
                                    class="d-none">
                                    <input type="file" name="profile_photo" id="profile_photo_input" accept="image/*"
                                        onchange="handleImageSelect(this)">
                                    <input type="hidden" name="cropped_image" id="cropped_image_data">
                                </form>

                                <p class="text-muted small mt-3">Click the camera icon to update your photo</p>
                            </div>

                            <!-- Right Side: User Info -->
                            <div class="col-md-8">
                                <div class="info-group mb-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Full Name</label>
                                    <p class="h5">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </p>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Email Address</label>
                                    <p class="h5">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Phone Number</label>
                                    <p class="h5">
                                        <?php echo htmlspecialchars($userData['phone'] ?? 'Not provided'); ?>
                                    </p>
                                </div>
                                <div class="info-group">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Account Role</label>
                                    <div>
                                        <?php if ($userData['role'] === 'student'): ?>
                                            <span class="badge bg-primary px-3 py-2">Student</span>
                                        <?php elseif ($userData['role'] === 'trainer'): ?>
                                            <span class="badge bg-success px-3 py-2">Trainer</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger px-3 py-2">Administrator</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="d-flex justify-content-between">
                            <a href="/logout" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit me-2"></i> Edit Account Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .info-group label {
        letter-spacing: 0.5px;
    }

    .avatar-circle,
    img.rounded-circle {
        transition: transform 0.3s ease;
    }

    .position-relative:hover .avatar-circle,
    .position-relative:hover img.rounded-circle {
        transform: scale(1.02);
    }

    #cropper-container {
        max-height: 400px;
        background: #f8f9fa;
    }

    #image_to_crop {
        max-width: 100%;
    }
</style>

<!-- Crop Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop & Resize Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cropper-container" class="text-center">
                    <img id="image_to_crop" src="">
                </div>
                <div class="mt-3 text-center">
                    <p class="text-muted small mb-2">Use the handles to adjust. Square crop recommended for profile
                        photo.</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="cropper.setAspectRatio(1)">1:1</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="cropper.setAspectRatio(4/3)">4:3</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="cropper.setAspectRatio(16/9)">16:9</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="cropper.setAspectRatio(null)">Free</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCroppedImage()">Save Photo</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/profile" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Account Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_details" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    let cropper = null;

    function handleImageSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                const image = document.getElementById('image_to_crop');
                image.src = e.target.result;

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.8,
                    responsive: true
                });

                const modal = new bootstrap.Modal(document.getElementById('cropModal'));
                modal.show();
            };

            reader.readAsDataURL(file);
        }
    }

    function saveCroppedImage() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
            document.getElementById('cropped_image_data').value = croppedDataUrl;

            // Create form and submit
            const form = document.getElementById('photo_form');
            const fileInput = document.getElementById('profile_photo_input');

            // Convert data URL to blob and create a file
            fetch(croppedDataUrl)
                .then(res => res.blob())
                .then(blob => {
                    const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    bootstrap.Modal.getInstance(document.getElementById('cropModal')).hide();
                    form.submit();
                });
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
