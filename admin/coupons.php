<?php
// admin/coupons.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';

if (!is_admin()) {
    redirect('/');
}

$page_title = "Manage Coupons";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid request.');
        redirect('/admin/coupons');
    }

    if (isset($_POST['add_coupon'])) {
        $code = strtoupper(trim($_POST['code']));
        $discount_type = $_POST['discount_type'];
        $discount_value = floatval($_POST['discount_value']);
        $min_purchase = floatval($_POST['min_purchase'] ?? 0);
        $max_uses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
        $valid_from = !empty($_POST['valid_from']) ? $_POST['valid_from'] : null;
        $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
        
        if (empty($code) || empty($discount_value)) {
            set_flash('danger', 'Please fill all required fields');
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_purchase, max_uses, valid_from, valid_until) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $discount_type, $discount_value, $min_purchase, $max_uses, $valid_from, $valid_until]);
                set_flash('success', 'Coupon created successfully!');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    set_flash('danger', 'Coupon code already exists');
                } else {
                    error_log('Coupon create failed: ' . $e->getMessage());
                    set_flash('danger', 'Error creating coupon. Please try again.');
                }
            }
        }
    } elseif (isset($_POST['toggle_coupon'])) {
        $coupon_id = intval($_POST['coupon_id']);
        $is_active = intval($_POST['is_active']);
        $stmt = $pdo->prepare("UPDATE coupons SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $coupon_id]);
        set_flash('success', 'Coupon status updated!');
    } elseif (isset($_POST['delete_coupon'])) {
        $coupon_id = intval($_POST['coupon_id']);
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$coupon_id]);
        set_flash('success', 'Coupon deleted!');
    }
    redirect('/admin/coupons');
}

// Fetch all coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Coupons</h1>
</div>

<!-- Add New Coupon -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Create New Coupon</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="col-md-3">
                <label class="form-label">Coupon Code *</label>
                <input type="text" class="form-control" name="code" placeholder="e.g., SAVE20" required>
                <small class="text-muted">Uppercase letters & numbers only</small>
            </div>
            <div class="col-md-2">
                <label class="form-label">Discount Type *</label>
                <select class="form-select" name="discount_type">
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₹)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Discount Value *</label>
                <input type="number" class="form-control" name="discount_value" placeholder="20" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Purchase (₹)</label>
                <input type="number" class="form-control" name="min_purchase" placeholder="0" step="0.01" min="0" value="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max Uses</label>
                <input type="number" class="form-control" name="max_uses" placeholder="Unlimited" min="1">
                <small class="text-muted">Leave empty for unlimited</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Valid From</label>
                <input type="datetime-local" class="form-control" name="valid_from">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valid Until</label>
                <input type="datetime-local" class="form-control" name="valid_until">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" name="add_coupon" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i> Create Coupon
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Coupons List -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">All Coupons (<?php echo count($coupons); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($coupons)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                <p>No coupons created yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min Purchase</th>
                            <th>Usage</th>
                            <th>Valid Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                <td>
                                    <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                        <span class="badge bg-success"><?php echo $coupon['discount_value']; ?>% OFF</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">₹<?php echo number_format($coupon['discount_value']); ?> OFF</span>
                                    <?php endif; ?>
                                </td>
                                <td>₹<?php echo number_format($coupon['min_purchase']); ?></td>
                                <td>
                                    <?php echo $coupon['used_count']; ?>
                                    <?php if ($coupon['max_uses']): ?>
                                        / <?php echo $coupon['max_uses']; ?>
                                    <?php else: ?>
                                        / ∞
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($coupon['valid_from'] || $coupon['valid_until']): ?>
                                        <small>
                                            <?php if ($coupon['valid_from']): ?>
                                                From: <?php echo date('d M, Y', strtotime($coupon['valid_from'])); ?><br>
                                            <?php endif; ?>
                                            <?php if ($coupon['valid_until']): ?>
                                                Until: <?php echo date('d M, Y', strtotime($coupon['valid_until'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">No expiry</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($coupon['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $coupon['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_coupon" class="btn btn-sm btn-outline-<?php echo $coupon['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $coupon['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-toggle-<?php echo $coupon['is_active'] ? 'on' : 'off'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                        <button type="submit" name="delete_coupon" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
