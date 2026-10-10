<?php
require_once __DIR__ . '/includes/bootstrap.php';

$username = 'admin';
$email = 'esthernisui2@gmail.com';
$password = 'admin12345';
$hash = password_hash($password, PASSWORD_DEFAULT);

// admins table ထဲသို့ သီးသန့် insert လုပ်ခြင်း
$stmt = $pdo->prepare("
    INSERT INTO admins (username, email, password_hash, is_active, created_at, updated_at)
    VALUES (?, ?, ?, 1, NOW(), NOW())
    ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_active = 1
");
$stmt->execute([$username, $email, $hash]);

echo "<h2 style='color:green;'>Success! Admin created in 'admins' table.</h2>";
echo "Username: <b>admin</b><br>Email: <b>{$email}</b><br>Password: <b>{$password}</b>";