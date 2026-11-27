<?php
require_once "config/db.php";
require_once "includes/helpers.php";
requireLogin("login.php");

$message = "";
$messageType = "danger";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || strlen($newPassword) < 8 || $newPassword !== $confirmPassword) {
        $message = "New password must be at least 8 characters and match the confirmation.";
    } else {
        $userId = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $userId);

            if ($update->execute()) {
                session_regenerate_id(true);
                $message = "Password updated!";
                $messageType = "success";
            } else {
                $message = "Could not update password.";
            }
            $update->close();
        } else {
            $message = "Current password is incorrect.";
        }
    }
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$pageTitle = "Change Password - SOUND";
$basePath = "";
$activePage = $isAdmin ? "dashboard" : "account";
include "includes/header.php";
?>

<div class="row justify-content-center my-4">
    <div class="col-md-7 col-lg-5">
        <div class="card p-4">
            <h2 class="mb-4">Change Password</h2>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control mb-3" required>

                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control mb-3" minlength="8" required>

                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control mb-4" minlength="8" required>

                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>

            <div class="d-flex gap-2 mt-3">
                <?php if ($isAdmin): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-accent flex-fill">Dashboard</a>
                <?php else: ?>
                    <a href="user/profile.php" class="btn btn-outline-accent flex-fill">My Profile</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-light flex-fill">Home</a>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
