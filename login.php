<?php
session_start();
require_once "config/db.php";
require_once "includes/helpers.php";

if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "index.php"));
    exit;
}

$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, user_id, first_name, last_name, email, password, role
         FROM users WHERE email = ? LIMIT 1"
    );

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_code'] = $user['user_id'];
            $_SESSION['fName'] = $user['first_name'];
            $_SESSION['lName'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? "admin/dashboard.php" : "index.php"));
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Unable to log in right now.";
    }
}

$pageTitle = "Login - SOUND Entertainment";
$basePath = "";
include "includes/header.php";
?>

<div class="row justify-content-center my-4">
    <div class="col-md-7 col-lg-5">
        <div class="card p-4 p-md-5">
            <h2 class="fw-bold mb-1 text-center">Sign In</h2>
            <p class="text-muted small text-center mb-4">Log in to your SOUND account</p>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Account created. You can log in now.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="you@example.com" value="<?= e($email) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Your password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                New here? <a href="register.php" class="link-accent fw-semibold">Create an account</a>
            </p>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
