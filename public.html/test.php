<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

echo "<h2>System Diagnostic Tool</h2>";

// 1. Test Database Connection
echo "<h4>1. Database Connection Test:</h4>";
try {
    $pdo = getDBConnection();
    echo "<p style='color:green;'>✓ Successfully connected to database: <b>" . DB_NAME . "</b></p>";
} catch (Exception $e) {
    die("<p style='color:red;'>✗ Connection Failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// 2. Test Admin Account
echo "<h4>2. Admin User Check:</h4>";
$stmt = $pdo->query("SELECT * FROM admins");
$admins = $stmt->fetchAll();

if (empty($admins)) {
    echo "<p style='color:red;'>✗ The 'admins' table is EMPTY! Auto-creating admin user...</p>";
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', :pass)");
    $stmt->execute(['pass' => $hash]);
    echo "<p style='color:green;'>✓ Created user <b>admin</b> with password <b>admin123</b>. Try logging in now!</p>";
} else {
    foreach ($admins as $admin) {
        echo "User found: <b>" . htmlspecialchars($admin['username']) . "</b><br>";
        $check = password_verify('admin123', $admin['password']);
        if ($check) {
            echo "<p style='color:green;'>✓ Password verification OK! Use <b>admin</b> / <b>admin123</b> to log in.</p>";
        } else {
            echo "<p style='color:red;'>✗ Password hash mismatch! Updating password hash to 'admin123' now...</p>";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password = :pass WHERE id = :id");
            $update->execute(['pass' => $newHash, 'id' => $admin['id']]);
            echo "<p style='color:green;'>✓ Password hash successfully updated to <b>admin123</b>!</p>";
        }
    }
}