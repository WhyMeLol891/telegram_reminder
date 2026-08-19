CREATE DATABASE IF NOT EXISTS `telegram_reminder_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `telegram_reminder_db`;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin Credential: admin / admin123
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `chat_id` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reminders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `scheduled_time` DATETIME NOT NULL,
  `status` ENUM('pending', 'sent', 'partial', 'failed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reminder_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reminder_id` INT NOT NULL,
  `message_text` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`reminder_id`) REFERENCES `reminders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reminder_recipients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reminder_id` INT NOT NULL,
  `chat_id` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`reminder_id`) REFERENCES `reminders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `message_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reminder_id` INT NOT NULL,
  `chat_id` VARCHAR(100) NOT NULL,
  `message_text` TEXT NOT NULL,
  `status` ENUM('sent', 'failed') NOT NULL,
  `sent_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reminder_id`) REFERENCES `reminders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;