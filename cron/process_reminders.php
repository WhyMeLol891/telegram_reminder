<?php
// cron/process_reminders.php

// Ensure script is executed via CLI or authorized HTTP key
if (php_sapi_name() !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($key !== 'MY_SUPER_SECRET_KEY_123') {
        http_response_code(403);
        die("Unauthorized Cron Access");
    }
}

require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// Step 1: Find due pending reminders
$stmt = $pdo->prepare("
    SELECT * FROM reminders 
    WHERE scheduled_time <= NOW() AND status = 'pending'
");
$stmt->execute();
$dueReminders = $stmt->fetchAll();

foreach ($dueReminders as $reminder) {
    $reminderId = $reminder['id'];

    // Step 2: Fetch sequence messages
    $mStmt = $pdo->prepare("SELECT message_text FROM reminder_messages WHERE reminder_id = :id ORDER BY sort_order ASC");
    $mStmt->execute(['id' => $reminderId]);
    $messages = $mStmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 3: Fetch assigned recipient chat IDs
    $rStmt = $pdo->prepare("SELECT chat_id FROM reminder_recipients WHERE reminder_id = :id");
    $rStmt->execute(['id' => $reminderId]);
    $recipients = $rStmt->fetchAll(PDO::FETCH_COLUMN);

    $totalAttempted = 0;
    $totalSuccess = 0;

    // Step 4 & 5: Dispatch Messages sequentially
    foreach ($recipients as $chatId) {
        foreach ($messages as $msg) {
            $totalAttempted++;
            
            // Dispatch via Telegram API
            $res = sendTelegramMessage($chatId, $msg);
            $status = $res['success'] ? 'sent' : 'failed';

            if ($res['success']) {
                $totalSuccess++;
            }

            // Log attempt
            $logStmt = $pdo->prepare("
                INSERT INTO message_logs (reminder_id, chat_id, message_text, status) 
                VALUES (:rid, :cid, :txt, :st)
            ");
            $logStmt->execute([
                'rid' => $reminderId,
                'cid' => $chatId,
                'txt' => $msg,
                'st'  => $status
            ]);

            // Prevent API rate-limiting delays (0.5 seconds between dispatches)
            usleep(500000);
        }
    }

    // Step 6: Update Reminder overall Status
    $finalStatus = 'failed';
    if ($totalSuccess === $totalAttempted && $totalAttempted > 0) {
        $finalStatus = 'sent';
    } elseif ($totalSuccess > 0 && $totalSuccess < $totalAttempted) {
        $finalStatus = 'partial';
    }

    $uStmt = $pdo->prepare("UPDATE reminders SET status = :st WHERE id = :id");
    $uStmt->execute(['st' => $finalStatus, 'id' => $reminderId]);
}

echo "Cron Processed Successfully. Reminders Executed: " . count($dueReminders);