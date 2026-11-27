<?php
require_once "config/db.php";
require_once "includes/helpers.php";

$settings = getSiteSettings($conn);
$siteName = $settings['site_name'] ?? 'SOUND Entertainment';
$contactEmail = $settings['contact_email'] ?? 'admin@sound.com';

$pageTitle = "Contact - " . $siteName;
$basePath = "";
include "includes/header.php";

$message = "";
$messageType = "info";
$name = "";
$email = "";
$subject = "";
$messageText = "";

if (isset($_SESSION['fName'], $_SESSION['lName']) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $name = trim($_SESSION['fName'] . ' ' . $_SESSION['lName']);
    $email = $_SESSION['email'] ?? '';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');

    if (
        $name === '' || strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 ||
        $subject === '' || strlen($subject) > 200 ||
        $messageText === ''
    ) {
        $message = "Please fill in all fields with a valid email address.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $messageText);
            if ($stmt->execute()) {
                $message = "Thanks! Your message has been sent.";
                $messageType = "success";
                $name = "";
                $email = "";
                $subject = "";
                $messageText = "";
            } else {
                $message = "Could not send your message. Please try again.";
                $messageType = "danger";
            }
            $stmt->close();
        } else {
            $message = "Database error. Please try again later.";
            $messageType = "danger";
        }
    }
}
?>

<div class="hero-banner py-4 px-4">
    <h1 class="mb-2">Contact Us</h1>
    <p class="mb-0">Questions, artist submissions, or feedback? Send us a note below.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h4 class="mb-3">Reach Us</h4>
            <p class="text-muted small">
                You can email us directly or use the form here. Messages go straight to our admin inbox.
            </p>

            <div class="mb-3">
                <div class="text-muted small">Email</div>
                <a href="mailto:<?= e($contactEmail) ?>" class="link-accent fw-semibold"><?= e($contactEmail) ?></a>
            </div>

            <div class="mb-4">
                <div class="text-muted small">Support Hours</div>
                <div>Monday &ndash; Saturday, 9:00 AM &ndash; 6:00 PM</div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-auto">
                <a href="music.php" class="btn btn-sm btn-outline-light">Music</a>
                <a href="videos.php" class="btn btn-sm btn-outline-light">Videos</a>
                <a href="search.php" class="btn btn-sm btn-outline-accent">Search</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="mb-3">Send a Message</h4>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="contact.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="contactName" class="form-label">Name *</label>
                        <input type="text" id="contactName" name="name" class="form-control"
                               maxlength="100" required placeholder="Your name" value="<?= e($name) ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="contactEmail" class="form-label">Email *</label>
                        <input type="email" id="contactEmail" name="email" class="form-control"
                               maxlength="150" required placeholder="you@example.com" value="<?= e($email) ?>">
                    </div>

                    <div class="col-12">
                        <label for="contactSubject" class="form-label">Subject *</label>
                        <input type="text" id="contactSubject" name="subject" class="form-control"
                               maxlength="200" required placeholder="What is this regarding?" value="<?= e($subject) ?>">
                    </div>

                    <div class="col-12">
                        <label for="contactMessage" class="form-label">Message *</label>
                        <textarea id="contactMessage" name="message" class="form-control"
                                  rows="5" required placeholder="Write your message..."><?= e($messageText) ?></textarea>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                        <button type="submit" class="btn btn-primary px-4">Send Message</button>
                        <a href="index.php" class="btn btn-outline-light px-4">Back to Home</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
