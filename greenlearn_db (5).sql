-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 20 déc. 2024 à 00:42
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `greenlearn_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `carbon_data_points`
--

CREATE TABLE `carbon_data_points` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `minute_elapsed` int(11) NOT NULL,
  `co2_value` double NOT NULL,
  `data_transferred` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `carbon_sessions`
--

CREATE TABLE `carbon_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `co2_total` double DEFAULT 0,
  `data_total` double DEFAULT 0,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Programmation', 'Apprenez les technologies du web moderne', '2024-11-21 23:40:45'),
(2, 'Web Design', 'Créez des designs respectueux de l\'environnement', '2024-11-21 23:40:45'),
(3, 'Énergie Renouvelable', 'Découvrez les solutions énergétiques durables', '2024-11-21 23:40:45'),
(4, 'Agriculture Durable', 'Techniques d\'agriculture respectueuses de la nature', '2024-11-21 23:40:45');

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `level` enum('débutant','intermédiaire','avancé') DEFAULT NULL,
  `carbon_footprint` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `image`, `teacher_id`, `price`, `created_at`, `category_id`, `duration`, `level`, `carbon_footprint`) VALUES
(17, 'Python', 'Apprendre Python', '../uploads/images/Python.svg.png', 10, 0.00, '2024-12-08 16:40:13', 1, '20h', 'débutant', 0.02),
(25, 'JavaScript', 'Getting Started with JavaScript', 'Unofficial_JavaScript_logo_2.svg.png', 10, 0.00, '2024-12-14 16:34:15', 2, '3', 'débutant', 0);

-- --------------------------------------------------------

--
-- Structure de la table `course_parts`
--

CREATE TABLE `course_parts` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `video_path` varchar(255) NOT NULL,
  `carbon_footprint` float NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `course_parts`
--

INSERT INTO `course_parts` (`id`, `course_id`, `video_path`, `carbon_footprint`, `title`, `description`, `created_at`, `pdf_path`) VALUES
(14, 17, '../uploads/videos/675983ef9e23e.mp4', 0.11, '1-What is Python', 'Brief intro to python', '2024-12-11 12:23:27', NULL),
(15, 17, '../uploads/videos/675984e13cce3.mp4', 0.22, '2- Installing Python', 'Step by step tutorial for Python Installation', '2024-12-11 12:27:28', NULL),
(16, 17, '../uploads/videos/67598610ae8fb.mp4', 0.02, '3-Code Editors', 'Learn about Code Editors', '2024-12-11 12:31:24', NULL),
(17, 17, '../uploads/videos/675986636e7ed.mp4', 0.05, '4-Your First Python Program', 'Code your first program !!', '2024-12-11 12:32:57', NULL),
(18, 17, '../uploads/videos/67598b05d7e72.mp4', 0.07, 'Extension Python', 'jdzijf', '2024-12-11 12:52:52', NULL),
(20, 25, '../uploads/videos/675db3d01c35c.mp4', 0.12, '1. What is JavaScript ?', 'Brief Intro to JS', '2024-12-14 16:36:28', NULL),
(22, 17, '../uploads/videos/6762a36210dc7.mp4', 0.09, 'fqzfjzl', 'fqzzf', '2024-12-18 10:27:38', NULL),
(24, 25, '../uploads/videos/6763d9a01af44.mp4', 0.07, '3. 3- Setting Up the Development', 'Set up the IDE to start programming with JS !', '2024-12-19 08:30:55', '../uploads/pdfs/6763d9bf5398c.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `course_progress`
--

CREATE TABLE `course_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `part_id` int(11) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `course_progress`
--

INSERT INTO `course_progress` (`id`, `user_id`, `part_id`, `completed`, `completed_at`) VALUES
(1, 15, 20, 1, '2024-12-17 23:31:26'),
(2, 15, 21, 1, '2024-12-17 23:31:41'),
(3, 15, 17, 1, '2024-12-17 23:44:02'),
(4, 15, 14, 1, '2024-12-18 00:01:26'),
(5, 12, 21, 1, '2024-12-18 08:57:11'),
(6, 12, 17, 1, '2024-12-18 08:57:28'),
(7, 12, 18, 1, '2024-12-18 08:58:28'),
(8, 12, 14, 1, '2024-12-18 08:58:40'),
(9, 12, 15, 1, '2024-12-18 08:58:57'),
(10, 14, 14, 1, '2024-12-18 09:57:59'),
(11, 10, 17, 1, '2024-12-18 14:46:52'),
(12, 10, 20, 1, '2024-12-18 15:00:40');

-- --------------------------------------------------------

--
-- Structure de la table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `teacher_applications`
--

CREATE TABLE `teacher_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `course_type` varchar(255) NOT NULL,
  `experience` text DEFAULT NULL,
  `bio` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cv_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teacher_applications`
--

INSERT INTO `teacher_applications` (`id`, `user_id`, `specialization`, `course_type`, `experience`, `bio`, `status`, `applied_at`, `cv_path`) VALUES
(1, 9, 'web', 'PHP', 'qfaqzfz', 'gqfeqéz', 'rejected', '2024-12-02 14:48:46', NULL),
(2, 10, 'security', 'Python', 'zedqz', 'qdzz', 'approved', '2024-12-08 15:58:05', NULL),
(3, 17, 'mobile', 'Android Studio ', 'jfpiejqf', 'ilfhqief', 'approved', '2024-12-19 23:27:02', 'uploads/cvprofappending/6764abc6581b8_Guide_utilisateur (1).pdf');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'active',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `role`, `created_at`, `first_name`, `last_name`, `status`, `registration_date`) VALUES
(3, 'Admin', 'System', '', 'admin@greenlearn.com', '$2y$10$YourHashedPasswordHere', 'admin', '2024-12-02 14:28:51', NULL, NULL, 'active', '2024-12-02 14:28:51'),
(9, 'Ali', 'ECHCHACHOUI', '', 'aliechchachoui@gmail.com', '$2y$10$kyc/Us4hzoeqvvq/UJnay.pwKIF5o9qBm9Jv9RppfxH4Mxs63DUqS', 'teacher', '2024-12-02 14:48:46', NULL, NULL, 'pending', '2024-12-02 14:48:46'),
(10, 'Eli', 'ECHCHACHOUI', '', 'test@gmail.com', '$2y$10$n21trD/PtpaaJaWVbiILi.9F3BstwPO0Rppd.hR6LlOjt22CLKtEq', 'teacher', '2024-12-08 15:58:05', NULL, NULL, 'active', '2024-12-08 15:58:05'),
(11, 'El', 'Ech', '', 'taziech@gmail.com', '$2y$10$y05xN.cca/phS2YyNw2MiuHpQIMFa/K19HjtCb2S4WXiZOEmY.0Hy', 'student', '2024-12-10 20:21:55', NULL, NULL, 'active', '2024-12-10 20:21:55'),
(12, 'eli', 'ECHCHACHOUI', '', 'test1@gmail.com', '$2y$10$1Iwpvj3ODAAQx6UkDUdKC.igXmPTv2PIQOZB/G0PY8yGyCQh6C73S', 'student', '2024-12-11 14:17:07', NULL, NULL, 'active', '2024-12-11 14:17:07'),
(13, 'test2', 'ECH', '', 'test2@gmail.com', '$2y$10$Btoq/KgNtFcZWcObG6Lx6O8Pkn2QDZAi4FiwA9sUxvwX.O1c4U1OG', 'student', '2024-12-17 10:29:25', NULL, NULL, 'active', '2024-12-17 10:29:25'),
(14, 'test4', 'ECHCHACHOUI', '', 'test4@gmail.com', '$2y$10$Hht4nWmi7kzNYL1u8AIZ4OBKuxrbld1nst6zHC.4iycUzbRbGUuqO', 'student', '2024-12-17 16:24:28', NULL, NULL, 'active', '2024-12-17 16:24:28'),
(15, 'test6', 'ECHCHACHOUI', '', 'test6@gmail.com', '$2y$10$XZ1YJXSD4Di4kA18T8zGyOowHqsv3yNT3MUaSvdxUe59druAqPdfm', 'student', '2024-12-17 16:35:49', NULL, NULL, 'active', '2024-12-17 16:35:49'),
(16, 'Saif Eddine', 'Kaddouri', '', 'saifeddinekaddouri@gmail.com', '$2y$10$FrKWQRPreN1ihIagQsSxEO8WFOEaI6k0rV7IjEH6/Tp/AuYHBRuCO', 'student', '2024-12-17 16:47:05', NULL, NULL, 'active', '2024-12-17 16:47:05'),
(17, 'testprof', 'pf', '', 'test2prof@gmail.com', '$2y$10$1mUaPojOumF6Mo2BmURsoOjojslGmTiT1yF7DsH43xAtVi0p/UrjO', 'teacher', '2024-12-19 23:27:02', NULL, NULL, 'pending', '2024-12-19 23:27:02');

-- --------------------------------------------------------

--
-- Structure de la table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `carbon_data_points`
--
ALTER TABLE `carbon_data_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Index pour la table `carbon_sessions`
--
ALTER TABLE `carbon_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `course_parts`
--
ALTER TABLE `course_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_parts_ibfk_1` (`course_id`);

--
-- Index pour la table `course_progress`
--
ALTER TABLE `course_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progress` (`user_id`,`part_id`);

--
-- Index pour la table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Index pour la table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `carbon_data_points`
--
ALTER TABLE `carbon_data_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `carbon_sessions`
--
ALTER TABLE `carbon_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `course_parts`
--
ALTER TABLE `course_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `course_progress`
--
ALTER TABLE `course_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `carbon_data_points`
--
ALTER TABLE `carbon_data_points`
  ADD CONSTRAINT `carbon_data_points_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `carbon_sessions` (`id`);

--
-- Contraintes pour la table `carbon_sessions`
--
ALTER TABLE `carbon_sessions`
  ADD CONSTRAINT `carbon_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Contraintes pour la table `course_parts`
--
ALTER TABLE `course_parts`
  ADD CONSTRAINT `course_parts_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Contraintes pour la table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
