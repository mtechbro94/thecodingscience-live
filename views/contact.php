<?php
// views/contact.php

$page_title = "Contact Us";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    $errors = [];
    if (empty($name))
        $errors[] = "Name is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($message))
        $errors[] = "Message is required";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);

            echo json_encode(['status' => 'success', 'message' => 'Thank you! Your message has been sent.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => implode(', ', $errors)]);
    }
    exit;
}

require_once 'includes/header.php';
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <h1 class="mb-4">Contact Us</h1>
        <p class="lead mb-5">Have questions? We'd love to hear from you. Get in touch with our team.</p>

        <div class="row">
            <!-- Contact Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="contact-icon">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <h5>Email</h5>
                        <p class="mb-0">
                            <a href="mailto:<?php echo CONTACT_EMAIL; ?>" class="text-decoration-none">
                                <?php echo CONTACT_EMAIL; ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="contact-icon">
                            <i class="fas fa-phone fa-2x text-primary"></i>
                        </div>
                        <h5>Phone</h5>
                        <p class="mb-0">
                            <a href="tel:<?php echo CONTACT_PHONE; ?>" class="text-decoration-none">
                                <?php echo CONTACT_PHONE; ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <h5>Location</h5>
                        <p class="mb-0">Srinagar, Jammu & Kashmir, India</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Send us a Message</h4>
                        <form id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" rows="5"
                                    placeholder="Your message here..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" name="privacy"
                                        required>
                                    <label class="form-check-label" for="privacy">
                                        I agree to the privacy policy and terms of service
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/contact', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(d => {
                alert(d.message);
                if (d.status === 'success') {
                    document.getElementById('contactForm').reset();
                }
            })
            .catch(e => alert('Error: ' + e));
    });
</script>

<?php require_once 'includes/footer.php'; ?>