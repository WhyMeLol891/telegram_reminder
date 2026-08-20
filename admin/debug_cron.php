<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// 1. Check Current Database Time vs Local Time
$timeStmt = $pdo->query("SELECT NOW() AS db_now");
$dbNow = $timeStmt->fetch()['db_now'];

echo "<h3>1. Time Diagnostics</h3>";
echo "<b>Server DB NOW():</b> " . $dbNow . "<br>";
echo "<b>PHP date():</b> " . date('Y-m-d H:i:s') . "<br><hr>";

// 2. Fetch All Pending Reminders regardless of time
$stmt = $pdo->query("SELECT id, title, scheduled_time, status FROM reminders");
$reminders = $stmt->fetchAll();

echo "<h3>2. Database Rows Debug</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse:collapse'>";
echo "<tr><th>ID</th><th>Title</th><th>Scheduled Time</th><th>Status</th><th>Status Match?</th><th>Time Comparison</th></tr>";

foreach ($reminders as $r) {
    $statusMatch = (strtolower(trim($r['status'])) === 'pending') ? 'YES' : 'NO';
    $timeMatch = ($r['scheduled_time'] <= $dbNow) ? 'DUE NOW' : 'FUTURE';
    
    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>{$r['title']}</td>";
    echo "<td>{$r['scheduled_time']}</td>";
    echo "<td>'{$r['status']}'</td>";
    echo "<td>{$statusMatch}</td>";
    echo "<td>{$timeMatch}</td>";
    echo "</tr>";
}
echo "</table>";