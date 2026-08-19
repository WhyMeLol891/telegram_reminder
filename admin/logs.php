<?php 
include '../includes/header.php'; 
require_once '../config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT l.*, r.title 
    FROM message_logs l 
    JOIN reminders r ON l.reminder_id = r.id 
    ORDER BY l.sent_time DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<h3>Message Delivery Logs</h3>
<div class="card p-3">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Time</th>
                <th>Reminder Title</th>
                <th>Chat ID</th>
                <th>Message Text</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $log['sent_time'] ?></td>
                <td><?= htmlspecialchars($log['title']) ?></td>
                <td><code><?= htmlspecialchars($log['chat_id']) ?></code></td>
                <td><?= htmlspecialchars($log['message_text']) ?></td>
                <td>
                    <span class="badge <?= $log['status'] === 'sent' ? 'bg-success' : 'bg-danger' ?>">
                        <?= strtoupper($log['status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>