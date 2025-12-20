-- Live Support Module schema (MySQL 8+)
-- This is optional if you use Laravel migrations. It matches the new migrations.

CREATE TABLE `support_team_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `specialization` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_team_members_user_id_unique` (`user_id`),
  CONSTRAINT `support_team_members_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE `support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `assigned_to_user_id` BIGINT UNSIGNED NULL,
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('open','in_progress','resolved','closed','escalated') NOT NULL DEFAULT 'open',
  `resolved_at` TIMESTAMP NULL,
  `closed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_user_id_status_index` (`user_id`, `status`),
  KEY `support_tickets_assigned_to_user_id_status_index` (`assigned_to_user_id`, `status`),
  CONSTRAINT `support_tickets_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_tickets_assigned_to_user_id_foreign`
    FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

CREATE TABLE `support_ticket_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `support_ticket_id` BIGINT UNSIGNED NOT NULL,
  `sender_user_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_messages_support_ticket_id_created_at_index` (`support_ticket_id`, `created_at`),
  CONSTRAINT `support_ticket_messages_support_ticket_id_foreign`
    FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_messages_sender_user_id_foreign`
    FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

