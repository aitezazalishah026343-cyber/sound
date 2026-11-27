<?php
session_start();
require_once "config/db.php";
require_once "includes/helpers.php";

if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "index.php"));
    exit;
}

$error = "";
$first_name = "";
$last_name = "";
$email = "";
$address = "";
$phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $first_name === '' || $last_name === '' ||
        $email === '' || $address === '' ||
        $phone === '' || $password === ''
    ) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email address.";
    } elseif (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $error = "Enter a valid phone number.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = "This email is already registered.";
        } else {
            // Auto-generate unique user_id starting with "user_"
            $res = $conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM users");
            $num = $res ? (int)$res->fetch_assoc()['next_id'] : 1;

            do {
                $user_id = "user_" . $num;
                $chk = $conn->prepare("SELECT id FROM users WHERE user_id = ? LIMIT 1");
                $chk->bind_param("s", $user_id);
                $chk->execute();
                $taken = $chk->get_result()->num_rows > 0;
                $chk->close();
                $num++;
            } while ($taken);

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";

            $stmt = $conn->prepare(
                "INSERT INTO users (user_id, first_name, last_name, email, password, address, phone, role)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssss", $user_id, $first_name, $last_name, $email, $hashed, $address, $phone, $role);

            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                $stmt->close();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $newId;
                $_SESSION['user_code'] = $user_id;
                $_SESSION['fName'] = $first_name;
                $_SESSION['lName'] = $last_name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                header("Location: index.php?registered=1");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
    }
}

$pageTitle = "Register - SOUND Entertainment";
$basePath = "";
include "includes/header.php";
?>

<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-6">
        <div class="card p-4 p-md-5">
            <h2 class="fw-bold mb-1 text-center">Create Account</h2>
            <p class="text-muted small text-center mb-4">Join SOUND to rate music and write reviews</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name *</label>
                        <input name="first_name" class="form-control" required value="<?= e($first_name) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name *</label>
                        <input name="last_name" class="form-control" required value="<?= e($last_name) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required
                               placeholder="you@example.com" value="<?= e($email) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-control" required
                               placeholder="0300 0000000" value="<?= e($phone) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address *</label>
                        <textarea name="address" class="form-control" rows="2" required><?= e($address) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Password (min 8 characters) *</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary w-100 py-2">Create Account</button>
                    </div>
                </div>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                Already have an account? <a href="login.php" class="link-accent fw-semibold">Login</a>
            </p>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
