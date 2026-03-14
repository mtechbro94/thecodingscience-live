<?php
// views/enroll.php

// Ensure user is logged in
if (!is_logged_in()) {
    $course_id = isset($id) ? (int) $id : '';
    $redirect = $course_id ? '/course/' . $course_id : '/courses';
    set_flash('info', 'Please login to enroll in a course.');
    redirect('/login?redirect=' . urlencode($redirect));
}

$user = current_user();

// Get course by ID or by name
$course = null;
$course_id = isset($id) ? (int) $id : 0;
$course_name = isset($_GET['course']) ? $_GET['course'] : '';

if ($course_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
} elseif (!empty($course_name)) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE name LIKE ?");
    $stmt->execute(['%' . $course_name . '%']);
    $course = $stmt->fetch();
    if ($course) {
        $course_id = $course['id'];
    }
}

if (!$course) {
    // For career tracks
    $track_slug = isset($_GET['track']) ? $_GET['track'] : '';
    
    if (!empty($track_slug)) {
        $stmt = $pdo->prepare("SELECT * FROM career_tracks WHERE slug = ? AND is_active = 1");
        $stmt->execute([$track_slug]);
        $track = $stmt->fetch();
        
        if ($track) {
            $stmt = $pdo->prepare("
                SELECT c.* 
                FROM courses c 
                JOIN career_track_courses cc ON c.id = cc.course_id 
                WHERE cc.track_id = ? 
                ORDER BY cc.sort_order
            ");
            $stmt->execute([$track['id']]);
            $track_courses = $stmt->fetchAll();
            
            $course_names = array_column($track_courses, 'name');
            $course = [
                'id' => 0,
                'name' => $track['name'],
                'description' => 'Career Track: ' . implode(' + ', $course_names),
                'price' => $track['price'],
                'original_price' => $track['original_price'],
                'image' => $track['image'],
                'duration' => $track['duration'],
                'level' => 'Career Track',
                'is_track' => 1,
                'track_id' => $track['id'],
                'track_courses' => $track_courses
            ];
        }
    }
}

if (!$course) {
    // For combo programs - create a virtual course
    $combo_name = isset($_GET['combo']) ? $_GET['combo'] : '';
    
    if (!empty($combo_name)) {
        $combos = [
            'Programming Starter Pack' => [
                'courses' => ['Crash Course in Computer Science', 'Programming with Python'],
                'original_price' => 6998,
                'price' => 4499
            ],
            'Developer Career Pack' => [
                'courses' => ['Programming with Python', 'Full Stack Web Development'],
                'original_price' => 11998,
                'price' => 7999
            ],
            'AI and Data Science Career Track' => [
                'courses' => ['Programming with Python', 'Data Science from Scratch', 'Machine Learning and AI Foundations'],
                'original_price' => 18997,
                'price' => 11999
            ]
        ];
        
        if (isset($combos[$combo_name])) {
            $combo = $combos[$combo_name];
            $course = [
                'id' => 0,
                'name' => $combo_name,
                'description' => 'Combo Program: ' . implode(' + ', $combo['courses']),
                'price' => $combo['price'],
                'original_price' => $combo['original_price'],
                'is_combo' => true,
                'included_courses' => $combo['courses']
            ];
        }
    }
    
    if (!$course) {
        set_flash('danger', 'Course not found.');
        redirect('/courses');
    }
}

$original_price = $course['price'];
$final_price = $course['price'];
$coupon_applied = null;
$discount_amount = 0;

// Handle coupon code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = $_POST['coupon_code'] ?? '';
    
    if (!empty($coupon_code)) {
        $result = validate_coupon($coupon_code, $original_price, $pdo);
        
        if ($result['success']) {
            $final_price = $result['final_amount'];
            $discount_amount = $result['discount'];
            $coupon_applied = $result['coupon'];
            set_flash('success', 'Coupon applied! You saved ₹' . number_format($discount_amount, 2));
        } else {
            set_flash('danger', $result['message']);
        }
    }
}

// Check if already enrolled (only for real courses, not combos/tracks)
if (!empty($course_id) && !isset($course['is_combo']) && !isset($course['is_track'])) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user['id'], $course_id]);
    $existing_enrollment = $stmt->fetch();

    if ($existing_enrollment) {
        if ($existing_enrollment['status'] === 'completed') {
            set_flash('info', 'You are already enrolled in this course.');
            redirect('/dashboard');
        } else {
            set_flash('warning', 'You have a pending enrollment for this course.');
            redirect('/dashboard');
        }
    }
}

// Handle Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_now'])) {
    $payment_method = $_POST['payment_method'] ?? 'upi';
    $amount_to_pay = $_POST['final_amount'] ?? $final_price;
    $applied_coupon = $_POST['applied_coupon'] ?? '';
    
    try {
        if (!empty($course_id) && !isset($course['is_combo']) && !isset($course['is_track'])) {
            // Regular course enrollment
            $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, ?, 'pending', ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['id'], $course_id, $payment_method, $amount_to_pay]);
            $enrollment_id = $pdo->lastInsertId();
        } else {
            // Career track - enroll in all courses
            if (isset($course['is_track']) && $course['is_track']) {
                foreach ($course['track_courses'] as $track_course) {
                    $check = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
                    $check->execute([$user['id'], $track_course['id']]);
                    if (!$check->fetch()) {
                        $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, ?, 'pending', ?, ?, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$user['id'], $track_course['id'], $payment_method, 0]);
                    }
                }
                
                // Create a pending payment record
                $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, NULL, 'pending', ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user['id'], $payment_method, $amount_to_pay]);
                $enrollment_id = $pdo->lastInsertId();
            }
            // Combo program - create enrollment for each course
            elseif (isset($course['is_combo']) && $course['is_combo']) {
                // For combo, we need to enroll in each course
                foreach ($course['included_courses'] as $included_course_name) {
                    $stmt = $pdo->prepare("SELECT id FROM courses WHERE name LIKE ?");
                    $stmt->execute(['%' . $included_course_name . '%']);
                    $included_course = $stmt->fetch();
                    
                    if ($included_course) {
                        // Check if already enrolled
                        $check = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
                        $check->execute([$user['id'], $included_course['id']]);
                        if (!$check->fetch()) {
                            $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, ?, 'pending', ?, ?, NOW())";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$user['id'], $included_course['id'], $payment_method, 0]);
                        }
                    }
                }
                
                // Create a pending payment record
                $sql = "INSERT INTO enrollments (user_id, course_id, status, payment_method, amount_paid, enrolled_at) VALUES (?, NULL, 'pending', ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user['id'], $payment_method, $amount_to_pay]);
                $enrollment_id = $pdo->lastInsertId();
            }
        }
        
        // Use coupon if applied
        if (!empty($applied_coupon)) {
            use_coupon($applied_coupon, $pdo);
        }
        
        set_flash('success', 'Enrollment initiated! Please complete your payment.');
        redirect('/razorpay-payment/' . $enrollment_id . '?from=enroll');

    } catch (PDOException $e) {
        set_flash('danger', 'Enrollment failed: ' . $e->getMessage());
    }
}

$page_title = "Enroll - " . $course['name'];
require_once 'includes/header.php';
?>

<style>
.coupon-input-group .btn {
    border-radius: 0 8px 8px 0;
}
.coupon-success {
    background: #dcfce7;
    border: 1px solid #86efac;
    border-radius: 8px;
    padding: 0.75rem;
    margin-top: 0.5rem;
}
.discount-badge {
    background: #dcfce7;
    color: #166534;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
}
</style>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h2 class="mb-4 fw-bold">Complete Your Enrollment</h2>
                        
                        <!-- Course Info -->
                        <div class="bg-light rounded-3 p-4 mb-4">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($course['name']); ?></h5>
                            <?php if (isset($course['is_combo']) && $course['is_combo']): ?>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($course['description']); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars(substr($course['description'] ?? $course['summary'] ?? '', 0, 150)); ?>...</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pricing -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Original Price:</span>
                                <span class="text-decoration-line-through text-muted">₹<?php echo number_format($original_price); ?></span>
                            </div>
                            
                            <?php if ($discount_amount > 0): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-success">Discount:</span>
                                    <span class="text-success">-₹<?php echo number_format($discount_amount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-3 text-primary">₹<?php echo number_format($final_price); ?></span>
                            </div>
                            
                            <?php if ($discount_amount > 0): ?>
                                <div class="discount-badge mt-2 text-center">
                                    <i class="fas fa-tag me-1"></i> You save ₹<?php echo number_format($discount_amount, 2); ?>!
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Coupon Code -->
                        <form method="POST" class="mb-4">
                            <label class="form-label fw-semibold">Have a coupon code?</label>
                            <div class="coupon-input-group input-group">
                                <input type="text" class="form-control" name="coupon_code" placeholder="Enter coupon code" 
                                       value="<?php echo isset($_POST['coupon_code']) ? htmlspecialchars($_POST['coupon_code']) : ''; ?>">
                                <button type="submit" name="apply_coupon" class="btn btn-outline-primary">Apply</button>
                            </div>
                            <?php if ($coupon_applied): ?>
                                <div class="coupon-success">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong><?php echo htmlspecialchars($coupon_applied['code']); ?></strong> applied successfully!
                                </div>
                            <?php endif; ?>
                        </form>
                        
                        <!-- Enrollment Form -->
                        <form method="POST">
                            <input type="hidden" name="final_amount" value="<?php echo $final_price; ?>">
                            <input type="hidden" name="applied_coupon" value="<?php echo $coupon_applied ? $coupon_applied['code'] : ''; ?>">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="enroll_now" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i> Proceed to Payment - ₹<?php echo number_format($final_price); ?>
                                </button>
                            </div>
                        </form>
                        
                        <p class="text-center text-muted small mt-3 mb-0">
                            <i class="fas fa-shield-alt me-1"></i> Secure payment powered by Razorpay
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
