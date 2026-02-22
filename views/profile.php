<?php
// views/profile.php

if (!is_logged_in()) {
    redirect('/login');
}

$user = current_user();

// Fetch full user data including phone
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
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">My Profile</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; font-size: 2.5rem; border-radius: 50%;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Full Name:</div>
                            <div class="col-sm-9">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Email:</div>
                            <div class="col-sm-9">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Phone:</div>
                            <div class="col-sm-9">
                                <?php echo htmlspecialchars($userData['phone'] ?? 'Not provided'); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Role:</div>
                            <div class="col-sm-9"><span class="badge bg-secondary">
                                    <?php echo ucfirst($user['role']); ?>
                                </span></div>
                        </div>

                        <hr>

                        <div class="text-end">
                            <button class="btn btn-primary"
                                onclick="alert('Profile editing is not yet available in this version.')">
                                <i class="fas fa-edit me-2"></i> Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>