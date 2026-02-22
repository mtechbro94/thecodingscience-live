<?php
// admin/messages.php

require_once dirname(__DIR__) . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';




if (!is_admin()) {
    redirect('/');
}

$page_title = "Messages";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $message_id = (int) $_GET['id'];

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        set_flash('success', 'Message deleted successfully.');
    }
    // Could add 'mark_read' logic here if schema supports it
    redirect('/admin/messages');
}

// Fetch Messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contact Messages</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <?php echo $message['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($message['name']); ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </td>
                            <td>
                                <?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="/admin/messages?action=delete&id=<?php echo $message['id']; ?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- View Message Modal -->
                        <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <?php echo htmlspecialchars($message['subject']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>From:</strong>
                                            <?php echo htmlspecialchars($message['name']); ?> &lt;
                                            <?php echo htmlspecialchars($message['email']); ?>&gt;
                                        </div>
                                        <div class="mb-3">
                                            <strong>Date:</strong>
                                            <?php echo date('F d, Y h:i A', strtotime($message['created_at'])); ?>
                                        </div>
                                        <hr>
                                        <p style="white-space: pre-wrap;">
                                            <?php echo htmlspecialchars($message['message']); ?>
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"
                                            class="btn btn-primary">
                                            <i class="fas fa-reply"></i> Reply
                                        </a>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>