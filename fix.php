<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'synergy1_derricklim_telegram_reminder';
$user = 'synergy1_yenping';
$pass = 'R.zb0ZwEuGZ}*fW2';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "<h3 style='color:green'>✓ Database Connected Successfully!</h3>";
} catch (PDOException $e) {
    die("<h3 style='color:red'>✗ Database Connection Failed: " . $e->getMessage() . "</h3>");
}

$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("REPLACE INTO admins (id, username, password) VALUES (1, 'admin', :pass)");
$stmt->execute(['pass' => $hash]);

echo "<h3 style='color:green'>✓ Admin User Successfully Reset!</h3>";
echo "<p>Try logging in at <b>admin/login.php</b> with:</p>";
echo "<b>Username:</b> admin<br>";
echo "<b>Password:</b> admin123";