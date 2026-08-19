<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Reminder System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🤖 Reminder System</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Reminders</a>
            <a class="nav-link" href="users.php">Users</a>
            <a class="nav-link" href="logs.php">Logs</a>
        </div>
        <span class="navbar-text me-3 text-white">Logged in as: <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<div class="container">