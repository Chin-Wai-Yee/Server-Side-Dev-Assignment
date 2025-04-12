-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2025 at 07:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recipe_culinary`

CREATE DATABASE IF NOT EXISTS `recipe_culinary` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `recipe_culinary`;
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments_vote`
--

CREATE TABLE `comments_vote` (
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `vote_value` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `voting_end_date` datetime NOT NULL,
  `status` enum('upcoming','active','voting','closed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `title`, `description`, `start_date`, `end_date`, `voting_end_date`, `status`, `created_at`) VALUES
(1, 'Summer BBQ Showdown', 'Who can grill the best BBQ?', '2025-05-01 10:00:00', '2025-05-07 23:59:59', '2025-05-10 23:59:59', 'active', '2025-04-11 15:15:35'),
(2, 'Vegan Delights Challenge', 'Create the most delicious vegan dish.', '2025-06-01 08:00:00', '2025-06-07 22:00:00', '2025-06-09 22:00:00', 'upcoming', '2025-04-11 15:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `competition_recipes`
--

CREATE TABLE `competition_recipes` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_recipes`
--

INSERT INTO `competition_recipes` (`id`, `competition_id`, `recipe_id`, `submitted_at`) VALUES
(1, 1, 2, '2025-04-11 15:15:35'),
(2, 1, 28, '2025-04-11 15:15:35'),
(3, 1, 30, '2025-04-11 15:15:35'),
(4, 2, 31, '2025-04-11 15:15:35'),
(5, 2, 32, '2025-04-11 15:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `cuisine_type` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `user_id`, `title`, `ingredients`, `instructions`, `cuisine_type`, `image_path`) VALUES
(2, NULL, 'Aglio Olio Seafood Pasta', 'Pasta: 300g spaghetti or linguine (adjust based on servings)\r\nSeafood: 200g mixed seafood (shrimp, squid, mussels, or clams)\r\nOlive oil: 4 tablespoons (extra virgin for best flavor)\r\nGarlic: 5-6 cloves, thinly sliced\r\nRed chili flakes: 1 teaspoon (adjust to taste)\r\nParsley: A handful of fresh parsley, chopped\r\nLemon: Zest and juice from 1 lemon\r\nSalt and pepper: To taste\r\nParmesan cheese: Optional, for garnishing', '1. Cook the Pasta:\r\n\r\nBring a large pot of salted water to a boil. Add the pasta and cook according to package instructions until al dente. Reserve about 1 cup of pasta cooking water and then drain the pasta.\r\n\r\n2. Cook the Seafood:\r\n\r\nIn a large skillet, heat 2 tablespoons of olive oil over medium-high heat. Add the seafood and cook for about 3-4 minutes, or until the seafood is fully cooked. Remove from the skillet and set aside.\r\n\r\n3. Prepare the Aglio Olio Sauce:\r\n\r\nIn the same skillet, add the remaining 2 tablespoons of olive oil. Add the thinly sliced garlic and red chili flakes, and sauté until the garlic becomes fragrant and golden (about 2-3 minutes). Be careful not to burn the garlic.\r\n\r\n4. Combine Pasta and Sauce:\r\n\r\nAdd the cooked pasta to the skillet with the garlic and chili oil. Toss well to coat the pasta in the oil. If needed, add a little bit of the reserved pasta water to help coat the pasta and create a silky sauce.\r\n\r\n5. Add the Seafood and Season:\r\n\r\nAdd the cooked seafood back into the skillet. Toss everything together. Add salt and pepper to taste.\r\n\r\n6. Finish the Dish:\r\n\r\nSqueeze fresh lemon juice over the pasta and stir in the lemon zest for an extra burst of freshness.\r\n\r\nGarnish with chopped parsley and optional parmesan cheese.\r\n\r\n7. Serve:\r\n\r\nServe immediately, and enjoy your delicious aglio olio seafood pasta!', 'Italian', 'uploads/aglio_olio_seafood.jpg'),
(28, NULL, 'Chocolate Chip Cookies', '1 cup (227g) unsalted butter, softened  \r\n1 cup (200g) granulated sugar  \r\n1 cup (220g) packed brown sugar  \r\n2 large eggs  \r\n2 teaspoons vanilla extract  \r\n3 cups (375g) all-purpose flour  \r\n1 teaspoon baking soda  \r\n0.5 teaspoon baking powder  \r\n1 teaspoon salt  \r\n2 cups (340g) semi-sweet chocolate chips  \r\n(Optional) 1 cup chopped walnuts or pecans\r\n', '1. Preheat oven to 175°C (350°F). Line a baking tray with parchment paper.  \r\n2. In a large bowl, cream together the butter, granulated sugar, and brown sugar until smooth.  \r\n3. Beat in the eggs one at a time, then stir in the vanilla.  \r\n4. In another bowl, combine the flour, baking soda, baking powder, and salt. Gradually blend into the creamed mixture.  \r\n5. Stir in chocolate chips (and nuts if using).  \r\n6. Drop rounded spoonfuls of dough onto the baking trays.  \r\n7. Bake for 10–12 minutes, or until edges are golden brown.  \r\n8. Allow cookies to cool on the baking sheet for 5 minutes before transferring to a wire rack.', 'Western', 'uploads/chocolate-chip-cookie.webp'),
(30, NULL, 'Chinese Fried Rice', '2 cups cooked jasmine rice (preferably day-old)\r\n2 tablespoons vegetable oil\r\n2 cloves garlic, minced\r\n½ onion, finely chopped\r\n2 eggs, beaten\r\n½ cup frozen peas and carrots\r\n2 tablespoons light soy sauce\r\n1 teaspoon sesame oil\r\n½ teaspoon white pepper\r\n¼ cup chopped spring onions\r\nOptional: cooked chicken, shrimp, or char siu (Chinese BBQ pork)', '1. Prepare the Rice:  \r\n   Use cold, day-old rice for best results. Break up any clumps with your fingers or a fork.\r\n\r\n2. Scramble the Eggs:  \r\n   Heat 1 tablespoon of oil in a wok or large pan over medium heat. Pour in the beaten eggs and scramble until just cooked. Remove and set aside.\r\n\r\n3. Stir-fry Aromatics:\r\n   Add the remaining 1 tablespoon of oil. Sauté garlic and onions until fragrant and slightly golden.\r\n\r\n4. Add Vegetables & Protein:  \r\n   Stir in peas and carrots. If using cooked meat, add it now and stir-fry for 2 minutes.\r\n\r\n5. Add the Rice:  \r\n   Increase the heat to high. Add the rice to the pan and stir-fry everything together, pressing down to sear and break up clumps.\r\n\r\n6. Season:  \r\n   Pour in soy sauce, sesame oil, and sprinkle white pepper. Stir well to distribute evenly.\r\n\r\n7. Mix in Eggs & Green Onions:  \r\n   Return scrambled eggs to the pan and add spring onions. Mix everything for another 1–2 minutes.\r\n\r\n8. Serve Hot:\r\n   Serve immediately with extra soy sauce or chili oil on the side if desired.', 'Chinese', 'uploads/chinese_fried_rice.webp'),
(31, NULL, 'Chicken Tikka Masala', '500g boneless chicken breast or thighs, cut into cubes  \r\n150g plain yogurt\r\n2 tablespoons lemon juice  \r\n2 teaspoons ground cumin  \r\n2 teaspoons ground coriander  \r\n1 teaspoon turmeric powder  \r\n1 teaspoon chili powder  \r\n1 teaspoon garam masala  \r\n4 garlic cloves, minced  \r\n1 tablespoon ginger, grated  \r\n2 tablespoons vegetable oil  \r\n1 large onion, finely chopped  \r\n400g canned chopped tomatoes  \r\n150ml heavy cream or coconut cream\r\nSalt, to taste  \r\nFresh coriander, for garnish  ', '1. Marinate the Chicken \r\n   - In a bowl, mix yogurt, lemon juice, cumin, coriander, turmeric, chili powder, garam masala, garlic, and ginger.  \r\n   - Add chicken and coat well. Cover and refrigerate for at least 1 hour or overnight.\r\n\r\n2. Cook the Chicken \r\n   - Heat 1 tablespoon of oil in a large pan over medium heat.  \r\n   - Add marinated chicken (shake off excess marinade) and cook until lightly browned and cooked through. Set aside.\r\n\r\n3. Prepare the Sauce  \r\n   - In the same pan, add another tablespoon of oil and sauté the chopped onions until soft and golden.  \r\n   - Add the canned tomatoes and cook for 10 minutes, stirring often.  \r\n   - Use a spoon to mash the tomatoes slightly and let the sauce thicken.\r\n\r\n4. Combine and Simmer  \r\n   - Add cooked chicken to the sauce and stir.  \r\n   - Pour in the cream and mix well. Simmer for another 10 minutes.  \r\n   - Season with salt to taste.\r\n\r\n5. Serve  \r\n   - Garnish with chopped fresh coriander.  \r\n   - Serve hot with basmati rice, naan, or chapati.', 'Indian', 'uploads/chicken-tikka-masala.jpeg'),
(32, NULL, 'Nasi Goreng Kampung', '2 cups cooked cold rice (preferably from the day before)  \r\n1 cup kangkung (water spinach), roughly chopped  \r\n1 egg  \r\n2 tablespoons cooking oil  \r\n1 tablespoon soy sauce  \r\nSalt to taste  \r\nFried anchovies (ikan bilis) for garnish  \r\nSliced cucumber and tomato (optional for serving)\r\n\r\nSpice Paste (blended or pounded):\r\n3 shallots  \r\n2 cloves garlic  \r\n5 dried chilies (soaked in hot water until soft)  \r\n1 fresh red chili (optional for extra heat)  \r\nA pinch of belacan (fermented shrimp paste)', '1. Prepare the Spice Paste  \r\n   - Blend or pound all the ingredients for the spice paste until fine.\r\n\r\n2. Sauté the Paste  \r\n   - Heat oil in a wok over medium heat.  \r\n   - Add the spice paste and fry until fragrant and the oil separates (about 5 minutes).\r\n\r\n3. Add Egg  \r\n   - Push the spice paste to one side.  \r\n   - Crack in the egg and scramble until halfway cooked.\r\n\r\n4. Add Rice  \r\n   - Add the cold rice and stir-fry everything together until evenly coated with the paste.\r\n\r\n5. Season  \r\n   - Add soy sauce and a pinch of salt. Mix well.  \r\n   - Toss in the chopped kangkung and stir-fry briefly until wilted.\r\n\r\n6. Serve  \r\n   - Plate the Nasi Goreng Kampung hot.  \r\n   - Top with crispy fried anchovies and serve with sliced cucumber and tomato if desired.', 'Malay', 'uploads/Nasi-goreng-kampung.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ratings`
--

CREATE TABLE `recipe_ratings` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `profile_image`, `bio`, `created_at`) VALUES
(1, 'chef_anna', 'anna@example.com', 'hashed_password1', 'anna.jpg', 'Passionate home cook and food blogger.', '2025-04-11 15:10:51'),
(2, 'chef_bob', 'bob@example.com', 'hashed_password2', 'bob.jpg', 'Professional chef specializing in Italian cuisine.', '2025-04-11 15:10:51'),
(3, 'chef_clara', 'clara@example.com', 'hashed_password3', 'clara.jpg', 'Baking enthusiast and cookbook author.', '2025-04-11 15:10:51'),
(4, 'chef_dave', 'dave@example.com', 'hashed_password4', 'dave.jpg', 'BBQ master and grill expert.', '2025-04-11 15:10:51'),
(5, 'chef_ella', 'ella@example.com', 'hashed_password5', 'ella.jpg', 'Vegan chef and nutrition coach.', '2025-04-11 15:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `recipe_id`, `user_id`, `created_at`) VALUES
(1, 1, 2, '2025-04-11 15:15:35'),
(2, 1, 3, '2025-04-11 15:15:35'),
(3, 2, 1, '2025-04-11 15:15:35'),
(4, 3, 4, '2025-04-11 15:15:35'),
(5, 4, 5, '2025-04-11 15:15:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `comments_ibfk_2` (`discussion_id`),
  ADD KEY `comments_ibfk_3` (`parent_comment_id`),
  ADD KEY `comments_ibfk_1` (`user_id`);

--
-- Indexes for table `comments_vote`
--
ALTER TABLE `comments_vote`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `unique_vote` (`user_id`,`comment_id`),
  ADD KEY `comments_vote_ibfk_2` (`comment_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_recipes`
--
ALTER TABLE `competition_recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`discussion_id`),
  ADD KEY `discussions_ibfk_1` (`user_id`),
  ADD KEY `discussions_ibfk_2` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `recipes_ibfk_1` (`user_id`);

--
-- Indexes for table `recipe_ratings`
--
ALTER TABLE `recipe_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_user_recipe` (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`recipe_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments_vote`
--
ALTER TABLE `comments_vote`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `competition_recipes`
--
ALTER TABLE `competition_recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `discussion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `recipe_ratings`
--
ALTER TABLE `recipe_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`discussion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_recipes`
--
ALTER TABLE `competition_recipes`
  ADD CONSTRAINT `competition_recipes_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `competition_recipes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `competition_recipes` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

