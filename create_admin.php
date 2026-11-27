
<?php
require_once "config/db.php";

$user_id = "admin";
$first_name = "System";
$last_name = "Administrator";
$email = "admin@sound.com";
$password = password_hash("Admin@12345", PASSWORD_DEFAULT);
$address = "SOUND Headquarters";
$phone = "03000000000";
$role = "admin";

$stmt = $conn->prepare(
    "INSERT INTO users
    (user_id, first_name, last_name, email, password, address, phone, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssssssss",
    $user_id,
    $first_name,
    $last_name,
    $email,
    $password,
    $address,
    $phone,
    $role
);

if ($stmt->execute()) {
    echo "Admin account created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
