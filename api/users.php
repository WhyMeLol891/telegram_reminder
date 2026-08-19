<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    exit();
}

if ($action === 'save') {
    $id = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $chat_id = trim($_POST['chat_id'] ?? '');

    if (empty($name) || empty($chat_id)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields required']);
        exit();
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, chat_id = :cid WHERE id = :id");
            $stmt->execute(['name' => $name, 'cid' => $chat_id, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, chat_id) VALUES (:name, :cid)");
            $stmt->execute(['name' => $name, 'cid' => $chat_id]);
        }
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Chat ID already exists.']);
    }
    exit();
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    echo json_encode(['status' => 'success']);
    exit();
}