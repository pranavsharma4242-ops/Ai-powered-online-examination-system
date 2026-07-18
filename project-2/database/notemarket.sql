CREATE DATABASE IF NOT EXISTS `notemarket`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `notemarket`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','student') NOT NULL DEFAULT 'student',
  `otp` VARCHAR(10) DEFAULT NULL,
  `otp_expire` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `notes_final` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `subject` VARCHAR(150) NOT NULL,
  `topic` VARCHAR(150) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `uploaded_by` VARCHAR(150) NOT NULL,
  `role` ENUM('admin','student') NOT NULL DEFAULT 'admin',
  `resource_type` ENUM('note','video') NOT NULL DEFAULT 'note',
  `note_type` ENUM('free','premium') NOT NULL DEFAULT 'free',
  `material_format` ENUM('pdf','handwritten') NOT NULL DEFAULT 'pdf',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `rating` DECIMAL(3,1) NOT NULL DEFAULT 4.0,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `youtube_link` VARCHAR(255) DEFAULT NULL,
  `difficulty_level` ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `upload_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `otp` VARCHAR(10) DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`name`, `email`, `password`, `role`)
SELECT 'Admin', 'saggy@gmail.com', '$2y$10$AFt38BD7f0ZhKtCDW.lO6O8PdNygLJemsheRr6RV6gQFDOjHebwyO', 'admin'
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `email` = 'saggy@gmail.com'
);
