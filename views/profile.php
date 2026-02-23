<?php
// views/profile.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();

// Handle Profile Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $filename = null;
    
    // Check for cropped base64 image first
    if (!empty($_POST['cropped_image'])) {
        $base64_data = $_POST['cropped_image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches)) {
            $ext = strtolower($matches[1]);
            if (!in_array($ext, ['jpeg', 'jpg', 'png', 'webp'])) {
                $errors[] = "Invalid image format.";
            }
            
            if (empty($errors)) {
                $filename = 'profile_' . $user['id'] . '_' . time() . '.jpg';
                $upload_path = BASE_PATH . '/assets/images/profiles/' . $filename;
                
                $image_data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64_data));
                if (file_put_contents($upload_path, $image_data) === false) {
                    $errors[] = "Failed to save image.";
                }
            }
        } else {
            $errors[] = "Invalid image data.";
        }
    } 
    // Regular file upload
    elseif (isset($_FILES['profile_photo'])) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed with error code: " . $file['error'];
        } elseif (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size too large. Maximum size is 2MB.";
        }

        if (empty($errors)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $ext;
            $upload_path = BASE_PATH . '/assets/images/profiles/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }
    
    if (empty($errors) && $filename) {
        // Fetch old image to delete later
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $old_image = $stmt->fetchColumn();

        // Update database
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        if ($stmt->execute([$filename, $user['id']])) {
            // Update session
            $_SESSION['user_profile_image'] = $filename;
            
            // Delete old image if exists
            if ($old_image && file_exists(BASE_PATH . '/assets/images/profiles/' . $old_image)) {
                unlink(BASE_PATH . '/assets/images/profiles/' . $old_image);
            }
            set_flash('success', 'Profile photo updated successfully!');
        } else {
            set_flash('danger', 'Failed to update profile in database.');
        }
    } elseif (!empty($errors)) {
        foreach ($errors as $error) {
            set_flash('danger', $error);
        }
    }
    
    if (!empty($filename) || !empty($errors)) {
        redirect('/profile');
    }
}

// Fetch full user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

$page_title = "My Profile";
require_once 'includes/header.php';
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
                                        <div class="avatar-circle bg-primary text-white mx-auto shadow d-flex align-items-center justify-content-center"
                                            style="width: 150px; height: 150px; font-size: 4rem; border-radius: 50%; border: 4px solid #f8f9fa;">
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
                                    <p class="h5"><?php echo htmlspecialchars($user['name']); ?></p>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Email Address</label>
                                    <p class="h5"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-1">Phone Number</label>
                                    <p class="h5"><?php echo htmlspecialchars($userData['phone'] ?? 'Not provided'); ?>
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
                            <button class="btn btn-primary" onclick="alert('Full profile editing is coming soon!')">
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
                    <p class="text-muted small mb-2">Use the handles to adjust. Square crop recommended for profile photo.</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.setAspectRatio(1)">1:1</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.setAspectRatio(4/3)">4:3</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.setAspectRatio(16/9)">16:9</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.setAspectRatio(null)">Free</button>
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    let cropper = null;
    
    function handleImageSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
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