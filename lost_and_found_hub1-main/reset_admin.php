<?php
require_once __DIR__ . '/includes/bootstrap.php';

$password = 'admin12345';
// လက်ရှိ PHP version အတိုင်း တိကျစွာ hash ပြုလုပ်ခြင်း
$hash = password_hash($password, PASSWORD_DEFAULT);

// admins table ထဲရှိ id = 1 ကို တိုက်ရိုက် update လုပ်ခြင်း
$stmt = $pdo->prepare("UPDATE admins SET password_hash = ?, is_active = 1 WHERE email = 'esthernisui2@gmail.com' OR id = 1");
$stmt->execute([$hash]);

echo "<div style='font-family: sans-serif; padding: 20px;'>";
echo "<h2 style='color: #1b4332;'>Password Hash Updated Successfully!</h2>";
echo "<p>Admin Email: <b>esthernisui2@gmail.com</b></p>";
echo "<p>Admin Password: <b>admin12345</b></p>";
echo "<a href='admin/login.php' style='display:inline-block; padding: 10px 16px; background:#1b4332; color:#fff; text-decoration:none; border-radius:8px;'>Go to Admin Login</a>";
echo "</div>";