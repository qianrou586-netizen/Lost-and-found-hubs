<?php
// Database ချိတ်ဆက်မှု Configuration

$host     = '127.0.0.1;port=3307';
$db       = 'lostfound';
$user     = 'root';
$pass     = ''; // XAMPP Default password မှာ အလွတ်ဖြစ်ပါသည်။ password ရှိပါက ထည့်ပေးပါ
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}