-- Salla Gamification System Schema
-- Created on: April 11, 2025

-- --------------------------------------------------------
-- Tasks: Individual actions merchants need to complete
-- --------------------------------------------------------
CREATE TABLE `gamification_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL COMMENT 'Unique identifier for the task',
  `name` varchar(255) NOT NULL COMMENT 'Task title',
  `description` text COMMENT 'Task description',
  `points` int unsigned DEFAULT '0' COMMENT 'Points awarded for completing this task',
  `icon` varchar(255) DEFAULT NULL COMMENT 'Icon representing the task',
  `event_name` varchar(255) NOT NULL COMMENT 'Platform event that triggers completion',
  `event_payload_conditions` json DEFAULT NULL COMMENT 'JSON conditions for event payload validation',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_tasks_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Missions: Collections of related tasks
-- --------------------------------------------------------
CREATE TABLE `gamification_missions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL COMMENT 'Unique identifier for the mission',
  `name` varchar(255) NOT NULL COMMENT 'Mission title',
  `description` text COMMENT 'Mission description',
  `image` varchar(255) DEFAULT NULL COMMENT 'Mission banner image',
  `total_points` int unsigned DEFAULT '0' COMMENT 'Total points for completing the mission',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` timestamp NULL DEFAULT NULL COMMENT 'When the mission becomes available',
  `end_date` timestamp NULL DEFAULT NULL COMMENT 'When the mission expires',
  `sort_order` int unsigned DEFAULT '0' COMMENT 'Order in which missions are displayed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_missions_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Mission Tasks: Mapping between missions and tasks (one mission has many tasks)
-- --------------------------------------------------------
CREATE TABLE `gamification_mission_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` bigint unsigned NOT NULL,
  `task_id` bigint unsigned NOT NULL,
  `sort_order` int unsigned DEFAULT '0' COMMENT 'Order in which tasks are displayed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gamification_mission_tasks_mission_id_foreign` (`mission_id`),
  KEY `gamification_mission_tasks_task_id_foreign` (`task_id`),
  CONSTRAINT `gamification_mission_tasks_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gamification_mission_tasks_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `gamification_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Rules: Conditions for starting and completing missions
-- --------------------------------------------------------
CREATE TABLE `gamification_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` bigint unsigned NOT NULL,
  `rule_type` enum('start', 'finish') NOT NULL COMMENT 'Rule for starting or finishing mission',
  `condition_type` enum('mission_completion', 'tasks_completion', 'date_range', 'custom') NOT NULL,
  `condition_payload` json NOT NULL COMMENT 'JSON containing rule conditions',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gamification_rules_mission_id_foreign` (`mission_id`),
  CONSTRAINT `gamification_rules_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Lockers: Conditions that keep missions locked until prerequisites are met
-- --------------------------------------------------------
CREATE TABLE `gamification_lockers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` bigint unsigned NOT NULL COMMENT 'Mission being locked',
  `condition_type` enum('mission_completion', 'date', 'tasks_completion', 'custom') NOT NULL,
  `condition_payload` json NOT NULL COMMENT 'JSON containing unlock conditions',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gamification_lockers_mission_id_foreign` (`mission_id`),
  CONSTRAINT `gamification_lockers_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Rewards: Points, badges, or coupons granted upon mission completion
-- --------------------------------------------------------
CREATE TABLE `gamification_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mission_id` bigint unsigned NOT NULL,
  `reward_type` enum('points', 'badge', 'coupon', 'feature_unlock') NOT NULL,
  `reward_value` varchar(255) NOT NULL COMMENT 'Value or identifier for the reward',
  `reward_meta` json DEFAULT NULL COMMENT 'Additional reward metadata',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gamification_rewards_mission_id_foreign` (`mission_id`),
  CONSTRAINT `gamification_rewards_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Merchant Progress: Track multi-tenant progress per store
-- --------------------------------------------------------
CREATE TABLE `gamification_store_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL COMMENT 'Reference to stores table',
  `mission_id` bigint unsigned NOT NULL,
  `status` enum('not_started', 'in_progress', 'completed', 'ignored') NOT NULL DEFAULT 'not_started',
  `progress_percentage` decimal(5,2) DEFAULT '0.00',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_store_progress_store_mission_unique` (`store_id`, `mission_id`),
  KEY `gamification_store_progress_mission_id_foreign` (`mission_id`),
  CONSTRAINT `gamification_store_progress_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gamification_store_progress_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Task Completion: Track individual task completion per store
-- --------------------------------------------------------
CREATE TABLE `gamification_task_completion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL COMMENT 'Reference to stores table',
  `task_id` bigint unsigned NOT NULL,
  `mission_id` bigint unsigned NOT NULL,
  `status` enum('not_started', 'completed', 'ignored') NOT NULL DEFAULT 'not_started',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_task_completion_store_task_mission_unique` (`store_id`, `task_id`, `mission_id`),
  KEY `gamification_task_completion_task_id_foreign` (`task_id`),
  KEY `gamification_task_completion_mission_id_foreign` (`mission_id`),
  CONSTRAINT `gamification_task_completion_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `gamification_missions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gamification_task_completion_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gamification_task_completion_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `gamification_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Badges: Achievements that can be earned by stores
-- --------------------------------------------------------
CREATE TABLE `gamification_badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL COMMENT 'Unique identifier for the badge',
  `name` varchar(255) NOT NULL COMMENT 'Badge name',
  `description` text COMMENT 'Badge description',
  `image` varchar(255) DEFAULT NULL COMMENT 'Badge image',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_badges_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Store Badges: Mapping between stores and earned badges
-- --------------------------------------------------------
CREATE TABLE `gamification_store_badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL COMMENT 'Reference to stores table',
  `badge_id` bigint unsigned NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_store_badges_store_badge_unique` (`store_id`, `badge_id`),
  KEY `gamification_store_badges_badge_id_foreign` (`badge_id`),
  CONSTRAINT `gamification_store_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `gamification_badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gamification_store_badges_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Translations: For internationalization of content
-- --------------------------------------------------------
CREATE TABLE `gamification_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL COMMENT 'Type of model being translated',
  `model_id` bigint unsigned NOT NULL COMMENT 'ID of the model being translated',
  `locale` varchar(10) NOT NULL COMMENT 'Language code',
  `field` varchar(255) NOT NULL COMMENT 'Field being translated',
  `value` text NOT NULL COMMENT 'Translated value',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gamification_translations_model_locale_field_unique` (`model_type`,`model_id`,`locale`,`field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Events Log: For tracking events and debugging
-- --------------------------------------------------------
CREATE TABLE `gamification_events_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL COMMENT 'Reference to stores table',
  `event_name` varchar(255) NOT NULL,
  `event_payload` json DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `processed_at` timestamp NULL DEFAULT NULL,
  `result` json DEFAULT NULL COMMENT 'Result of event processing',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gamification_events_log_store_id_index` (`store_id`),
  KEY `gamification_events_log_event_name_index` (`event_name`),
  KEY `gamification_events_log_created_at_index` (`created_at`),
  CONSTRAINT `gamification_events_log_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;