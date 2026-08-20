<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'synergy1_derricklim_telegram_reminder');
define('DB_USER', 'synergy1_yenping');
define('DB_PASS', 'R.zb0ZwEuGZ}*fW2');

// Telegram Bot API Token
define('TELEGRAM_BOT_TOKEN', '8999637533:AAGHPe48_v4S1rO0dCyhWesghKjHlCqGDb8');

// Cron Security Secret Key (To prevent direct HTTP access)
define('CRON_SECRET_KEY', 'MY_SUPER_SECRET_KEY_123');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Fix Timezone Sync
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $pdo->exec("SET time_zone = '+08:00'");

        } catch (PDOException $e) {
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function sendTelegramMessage($chat_id, $message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id'    => $chat_id,
        'text'       => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    $resData = json_decode($response, true);
    if (isset($resData['ok']) && $resData['ok'] === true) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => $resData['description'] ?? 'Unknown Error'];
    }
}