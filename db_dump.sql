SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+06:00";

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES
(1, 'Admin User', 'admin@iub.edu.bd', '$2y$10$EdbP7vWz8ziXhJra.rLFfu6E9SwYfbO24R1hCOYjB02XcB1MbT.6.', 'admin', '', 'active', '2025-12-03 21:27:35', '2025-12-08 22:41:55'),
(4, 'Rifat', '2222029@iub.edu.bd', '$2y$10$crQTonh7v66fAOUhRCYNTuSft5Vn3Q3vzVBJZ4nID6oV9ZkEJEY2K', 'user', '', 'active', '2025-12-04 21:59:58', '2025-12-16 13:05:58'),
(9, '2222028', '2222028@iub.edu.bd', '$2y$10$pQ7sT9uW1yB3dE5gH7jK9mN1pQ3sT5uW7yB9dE1gH3jK5nP7qR9sT1uW3yB5d', 'user', '', 'active', '2025-12-04 22:35:44', '2025-12-07 23:23:17'),
(10, 'Khan', '2222027@iub.edu.bd', '$2y$10$QKsiXS6qGOsRFaPHoEwHY.OaQaEwRV0m2T5XCk3r8XFe3uPvgNWqy', '', '', 'active', '2025-12-09 01:07:42', '2025-12-09 01:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `author_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `posts` VALUES
(2, 'Registration Open for Spring 2026 Semester', 'Dear Students,\n\nThe registration period for Spring 2026 semester is now open. Students can register for courses through the student portal from November 15 to November 30, 2025.\n\nPlease consult with your academic advisors before finalizing your course selection. Priority registration is available for senior students.', 'Academics', 1, '', '', 'approved', 567, '2025-12-03 21:27:35', '2025-12-03 21:27:35'),
(3, 'IUB Debate Club Wins National Championship', 'The IUB Debate Club has secured first place at the National Debate Championship held in Dhaka. The team competed against 25 universities from across the country and demonstrated exceptional critical thinking and public speaking skills.\n\nCongratulations to all team members and their coach for this remarkable achievement!', 'Student Life', 1, 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=800', '', 'approved', 423, '2025-12-03 21:27:35', '2025-12-03 21:27:35'),
(4, 'Career Fair 2025 - Connect with Top Employers', 'IUB Career Services is organizing the annual Career Fair on November 20, 2025. Over 50 leading companies from various industries will be participating to recruit talented graduates and interns.\n\nThis is an excellent opportunity for students to network with potential employers and explore career opportunities. Bring your updated CVs and dress professionally.', 'Events', 1, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800', '', 'approved', 792, '2025-12-03 21:27:35', '2025-12-07 22:11:48'),
(6, 'IUB Library Introduces 24/7 Digital Access', 'The IUB Central Library is excited to announce 24/7 digital access to all electronic resources. Students can now access thousands of journals, e-books, and research databases anytime, anywhere. This initiative aims to support remote learning and research activities.', 'Academics', 1, 'https://images.unsplash.com/photo-1589998059171-988d887df646?w=800', '', 'approved', 299, '2025-12-03 21:27:35', '2025-12-05 18:48:49'),
(13, 'IUB Students Launch Mental Health Awareness Campaign', 'A group of passionate students from IUB\'s Psychology Department has launched \"Mind Matters,\" a comprehensive mental health awareness campaign aimed at breaking the stigma surrounding mental health issues on campus.\n\nThe campaign, which kicked off on November 25, 2025, features a series of workshops, peer support groups, and interactive sessions designed to promote mental wellness among students and faculty members.\n\nKey initiatives include:\nWeekly mindfulness and meditation sessions every Wednesday at 4 PM\nAnonymous peer counseling service via a dedicated hotline\nMental health resource library in the Student Center\nGuest talks by clinical psychologists and wellness experts\nArt therapy workshops for stress management\n\n\"University life can be overwhelming, and many students struggle in silence,\" said Tasneem Rahman, campaign organizer and 4th-year Psychology major. \"We want to create a safe space where everyone feels comfortable discussing their mental health challenges.\"\n\nThe campaign has already received overwhelming support from the university administration, which has pledged additional funding for counseling services and mental health resources.\n\nStudents interested in volunteering or seeking support can visit the Mind Matters booth at the Student Center or email mindmatters@iub.edu.bd\n\nRemember: It\'s okay to not be okay. Reach out, talk, and take care of your mental health.', 'Student Life', 9, 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=800', '', 'approved', 5, '2025-12-04 22:37:42', '2025-12-04 23:20:51'),
(14, 'IUB Wins National Hackathon 2025: Team Develops AI-Powered Traffic Solution', 'A team of five computer science students from Independent University, Bangladesh (IUB) has won the prestigious National Hackathon 2025, competing against 87 teams from universities across the country.\n\nThe winning team, \"Code Crusaders,\" developed \"TrafficAI\" - an innovative artificial intelligence system that optimizes Dhaka\'s traffic flow using real-time data analysis and predictive algorithms.\n\nTeam Members:\nFahim Ahmed (Team Lead, CSE 4th Year)\nNusrat Jahan (CSE 3rd Year)\nRashed Kabir (CSE 4th Year)\nSadia Islam (CSE 3rd Year)\nTanvir Hossain (CSE 2nd Year)\n\nThe TrafficAI system uses machine learning to analyze traffic patterns, predict congestion hotspots, and suggest alternative routes to drivers through a mobile application. The prototype demonstrated a potential 35% reduction in average commute time during peak hours.\n\n\"We were inspired by the daily struggles of Dhaka commuters,\" said Fahim Ahmed. \"Our goal was to create a practical solution that could actually make a difference in people\'s lives.\"\n\nThe team received a prize of BDT 500,000, along with mentorship opportunities from leading tech companies and potential funding for further development of their solution.\n\nDr. Mohammad Rahman, Head of the CSE Department, expressed immense pride: \"This achievement showcases the exceptional talent and innovative thinking of our students. IUB continues to produce graduates who are ready to solve real-world problems.\"\n\nThe Dhaka Metropolitan Police has shown interest in piloting the TrafficAI system in selected areas of the city.\n\nCongratulations to Team Code Crusaders for making IUB proud! 🏆', 'Academics', 9, '', '', 'approved', 4, '2025-12-04 23:43:04', '2025-12-05 23:02:56'),
(15, 'Book Fair', 'hbjdbhlkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkka', 'Events', 4, 'uploads/693708ef9adff_grantt.png', '', 'approved', 0, '2025-12-08 23:20:47', '2025-12-09 00:01:58'),
(16, 'Sports lifr', 'jkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkawasmdnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn', 'Sports', 4, 'uploads/693711eb24726_grantt.png', '', 'rejected', 0, '2025-12-08 23:59:07', '2025-12-09 00:03:05'),
(18, 'Cricket Tournament', 'jksdbvhklkhsjdvbnjkeklhhsbv qi;ak kdjs.vn hkl,df    namv dkjkfb khld k;djb ;kad. d. nad dhfnk       jnnccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccx n dk  dkfj', 'Sports', 4, '', 'uploads/693c751387473_vid_4446327-hd_1920_1080_30fps.mp4', 'approved', 0, '2025-12-13 02:03:31', '2025-12-13 02:03:48'),
(19, 'Nothing', '<p>jjdsjbkgkmmjk. jekrrrrgjnddjikl;m,.mv./,zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzjneelkjng</p>', 'Announcements', 4, 'uploads/694186afc21c3_Screenshot2222029.png', '', 'approved', 0, '2025-12-16 22:19:59', '2025-12-16 22:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comments` VALUES
(4, 13, 1, 'good', '2025-12-04 23:14:42'),
(5, 18, 4, 'lsgk', '2025-12-16 13:05:39'),
(6, 19, 4, 'jhkajxzghvk;dhisb', '2025-12-16 22:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`,`post_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
