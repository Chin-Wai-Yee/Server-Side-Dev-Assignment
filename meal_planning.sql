-- Meal Planning Tables

CREATE TABLE `meal_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`plan_id`),
  KEY `user_id` (`user_id`),
  KEY `date_range` (`start_date`, `end_date`),
  CONSTRAINT `meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `meal_entries` (
  `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `custom_meal_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`entry_id`),
  KEY `plan_id` (`plan_id`),
  KEY `meal_date` (`meal_date`),
  KEY `recipe_id` (`recipe_id`),
  CONSTRAINT `meal_entries_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `meal_plans` (`plan_id`) ON DELETE CASCADE,
  CONSTRAINT `meal_entries_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 