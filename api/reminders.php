<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    $search = trim($_GET['search'] ?? '');
    $filter = trim($_GET['filter'] ?? '');

    $query = "
        SELECT DISTINCT r.*, 
               (SELECT GROUP_CONCAT(chat_id SEPARATOR ', ') FROM reminder_recipients WHERE reminder_id = r.id) as recipients,
               (SELECT GROUP_CONCAT(message_text ORDER BY sort_order ASC SEPARATOR ' || ') FROM reminder_messages WHERE reminder_id = r.id) as messages
        FROM reminders r
        LEFT JOIN reminder_messages rm ON r.id = rm.reminder_id
        LEFT JOIN reminder_recipients rr ON r.id = rr.reminder_id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (r.title LIKE :s OR rr.chat_id LIKE :s OR rm.message_text LIKE :s)";
        $params['s'] = '%' . $search . '%';
    }

    if (!empty($filter)) {
        if ($filter === 'today') {
            $query .= " AND DATE(r.scheduled_time) = CURDATE()";
        } elseif (in_array($filter, ['pending', 'sent', 'failed'])) {
            $query .= " AND r.status = :status";
            $params['status'] = $filter;
        } elseif ($filter === 'last7') {
            $query .= " AND r.scheduled_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
    }

    $query .= " ORDER BY r.scheduled_time DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    exit();
}

if ($action === 'save') {
    $id = intval($_POST['reminder_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $scheduled_time = trim($_POST['scheduled_time'] ?? '');
    $recipients = $_POST['recipients'] ?? [];
    $messages = $_POST['messages'] ?? [];

    if (empty($title) || empty($scheduled_time) || empty($recipients) || empty($messages)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE reminders SET title = :t, scheduled_time = :st, status = 'pending' WHERE id = :id");
            $stmt->execute(['t' => $title, 'st' => $scheduled_time, 'id' => $id]);

            $pdo->prepare("DELETE FROM reminder_messages WHERE reminder_id = :id")->execute(['id' => $id]);
            $pdo->prepare("DELETE FROM reminder_recipients WHERE reminder_id = :id")->execute(['id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO reminders (title, scheduled_time, status) VALUES (:t, :st, 'pending')");
            $stmt->execute(['t' => $title, 'st' => $scheduled_time]);
            $id = $pdo->lastInsertId();
        }

        $msgStmt = $pdo->prepare("INSERT INTO reminder_messages (reminder_id, message_text, sort_order) VALUES (:rid, :txt, :ord)");
        foreach ($messages as $idx => $msg) {
            if (!empty(trim($msg))) {
                $msgStmt->execute(['rid' => $id, 'txt' => trim($msg), 'ord' => $idx + 1]);
            }
        }

        $recStmt = $pdo->prepare("INSERT INTO reminder_recipients (reminder_id, chat_id) VALUES (:rid, :cid)");
        foreach ($recipients as $chat_id) {
            $recStmt->execute(['rid' => $id, 'cid' => trim($chat_id)]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Reminder saved successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $reminder = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT message_text FROM reminder_messages WHERE reminder_id = :id ORDER BY sort_order ASC");
    $stmt->execute(['id' => $id]);
    $messages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("SELECT chat_id FROM reminder_recipients WHERE reminder_id = :id");
    $stmt->execute(['id' => $id]);
    $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['status' => 'success', 'reminder' => $reminder, 'messages' => $messages, 'recipients' => $recipients]);
    exit();
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(['status' => 'success']);
    exit();
}