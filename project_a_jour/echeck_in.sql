-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 12 sep. 2025 à 12:11
-- Version du serveur : 8.0.31
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `echeck_in`
--

-- --------------------------------------------------------

--
-- Structure de la table `agent`
--

DROP TABLE IF EXISTS `agent`;
CREATE TABLE IF NOT EXISTS `agent` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `owner_id` int NOT NULL,
  `is_confirmed` tinyint(1) NOT NULL,
  `confirmation_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_268B9C9DE7927C74` (`email`),
  UNIQUE KEY `UNIQ_268B9C9DC05FB297` (`confirmation_token`),
  KEY `IDX_268B9C9D71F7E88B` (`event_id`),
  KEY `IDX_268B9C9D7E3C61F9` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `agent`
--

INSERT INTO `agent` (`id`, `nom`, `email`, `roles`, `password`, `status`, `created_at`, `updated_at`, `owner_id`, `is_confirmed`, `confirmation_token`, `event_id`) VALUES
(40, 'Franco Glorys', 'miguelsingcol@gmail.com', '[\"ROLE_AGENT\"]', '$2y$13$MiTn/xcs11Xcuzb5ChPck.hHsgUZiXFxojtTp.XPB.nEJi3bpP0zO', 'active', '2025-08-28 14:37:17', '2025-08-29 13:56:00', 19, 1, NULL, 18),
(44, 'Boost Bertrand', 'boost@gmail.com', '[\"ROLE_AGENT\"]', '$2y$13$dGMT5TZf.Pa1hMCf/8yrROBSivLLIyzaXTzlXB7bTjY7CvcuzDrk6', 'active', '2025-08-31 19:22:25', NULL, 20, 0, '60a3ac69b70fd4bf9733e51e3d84d46fc3b342ef670dd38d8772643fa1dd730a', 20),
(45, 'Voangy Lanto', 'bayane437@gmail.com', '[\"ROLE_AGENT\"]', '$2y$13$.B.gcwBYgWFdQIwmXcguAupF0e3Z42z4p/tHazSVHTMbiNeA.K25.', 'active', '2025-08-31 19:33:32', NULL, 20, 1, NULL, 20),
(51, 'Ashir Houssen', 'singcolmiguel9@gmail.com', '[\"ROLE_AGENT\"]', '$2y$13$G.vdKrdbUdqULXEfGjZxWeCCOmgpOohvcnZ5xTQWhm5bYvG3BQXqm', 'active', '2025-09-08 13:32:58', NULL, 20, 1, NULL, 20);

-- --------------------------------------------------------

--
-- Structure de la table `check_in`
--

DROP TABLE IF EXISTS `check_in`;
CREATE TABLE IF NOT EXISTS `check_in` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `checked_in_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `checked_in_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_90466CF971F7E88B` (`event_id`),
  KEY `IDX_90466CF99D1C3019` (`participant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `check_in`
--

INSERT INTO `check_in` (`id`, `event_id`, `participant_id`, `checked_in_at`, `checked_in_by`, `notes`) VALUES
(14, 18, 77, '2025-08-29 14:07:29', 'Franco Glorys Franco Glorys', 'ok'),
(15, 20, 85, '2025-08-31 19:55:01', 'Voangy Lanto Voangy Lanto', 'ok'),
(16, 20, 87, '2025-08-31 19:55:42', 'Voangy Lanto Voangy Lanto', 'bien reçu '),
(17, 22, 102, '2025-09-02 12:30:11', 'Aschir Houssen Aschir Houssen', 'ok'),
(18, 22, 93, '2025-09-02 12:34:16', 'Aschir Houssen Aschir Houssen', 'bien reçu '),
(20, 22, 88, '2025-09-02 12:51:19', 'Aschir Houssen Aschir Houssen', 'enregistré '),
(21, 20, 124, '2025-09-08 13:36:05', 'Ashir Houssen Ashir Houssen', 'ok voaray');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250703123400', '2025-07-03 12:34:43', 2183),
('DoctrineMigrations\\Version20250722140654', '2025-07-22 14:08:49', 1264),
('DoctrineMigrations\\Version20250724122645', '2025-07-24 12:26:53', 363),
('DoctrineMigrations\\Version20250728072038', '2025-07-28 08:06:37', 5),
('DoctrineMigrations\\Version20250728075402', '2025-07-28 08:06:37', 139),
('DoctrineMigrations\\Version20250814115253', '2025-08-14 11:53:05', 708),
('DoctrineMigrations\\Version20250822111000', '2025-08-22 08:28:17', 298);

-- --------------------------------------------------------

--
-- Structure de la table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE IF NOT EXISTS `event` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organizer_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `menu` longtext COLLATE utf8mb4_unicode_ci,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3BAE0AA7876C4DDA` (`organizer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `event`
--

INSERT INTO `event` (`id`, `organizer_id`, `title`, `description`, `start_date`, `end_date`, `location`, `status`, `created_at`, `updated_at`, `menu`, `locale`) VALUES
(18, 19, 'Anniversaire de Miguel', 'Célébration conviviale pour marquer un nouvel anniversaire entouré d’amis et de la famille.s\r\nAu programme : gâteau, musique, danse et bonne ambiance !x', '2025-09-15 18:00:00', '2025-09-15 23:06:00', 'Salle des fêtes Alasora', 'active', '2025-08-26 14:03:13', '2025-08-29 12:39:07', 'Entrée : Salade composée (contient lait, œuf)\r\nPlat principal : Poulet rôti, riz parfumé, légumes sautés\r\nDessert : Gâteau d’anniversaire chocolat (contient gluten, lait)\r\nBoissons : Jus naturel, sodas, vin rouge', 'en'),
(20, 20, 'Anniverssaire de Fatima', 'Fête conviviale avec famille et amis, gâteau, animations et musique.', '2025-10-09 18:00:00', '2025-09-15 23:00:00', 'Salle Polyvalente Ivandry', 'completed', '2025-08-31 18:11:02', '2025-09-08 13:37:08', 'Entrée : Salade composée\r\nPlat : Poulet rôti, riz parfumé\r\nDessert : Gâteau d’anniversaire, fruits\r\nBoissons : Jus, sodas, champagne', 'en'),
(21, 20, 'Mariage civil et religieux de Heritiana & Sandra', 'Mariage civil à la mairie, suivi d’une bénédiction religieuse à l’église et d’une réception festive avec danse et animation.', '2025-12-21 09:00:00', '2025-12-21 23:00:00', 'Mairie d’Antananarivo (civil) / Église Andohalo (religieux) / Espace Rasseta Ivandry (réception)', 'draft', '2025-08-31 18:13:50', '2025-09-01 08:31:15', 'Entrée : Cocktail de crevettes, salade exotique\r\nPlat : Viande de zébu sauce champignon, poulet rôti, riz parfumé\r\nDessert : Pièce montée, glace, fruits frais\r\nBoissons : Jus, vin, eau minérale, champagne', 'en'),
(22, 20, 'Remise de diplôme de l\'ESMIA– Promotion 2025', 'Célébration officielle avec discours, remise des diplômes et cocktail.', '2025-10-05 09:00:00', '2025-10-05 15:00:00', 'CCI Ivato', 'active', '2025-08-31 18:16:13', '2025-09-02 12:49:04', 'Buffet varié : mini-pizzas, brochettes, quiches\r\nDessert : Cupcakes, fruits frais\r\nBoissons : Jus, eau, sodas', 'en'),
(23, 20, 'Baptême de Franco', 'Cérémonie religieuse suivie d’un repas convivial en famille et entre amis.', '2026-01-12 10:00:00', '2026-01-12 16:00:00', 'Église Ambohijatovo, puis Jardin Andohalo (réception)', 'completed', '2025-08-31 18:19:56', '2025-08-31 18:26:00', 'Entrée : Salade de crudités\r\nPlat : Poisson grillé, riz nature\r\nDessert : Gâteau, fruits frais\r\nBoissons : Jus, eau, limonade', 'en'),
(24, 20, 'Fête de Noël en familles', 'Soirée chaleureuse avec repas, partage de cadeaux et animations pour les enfants.', '2025-12-24 19:00:00', '2025-12-25 01:00:00', 'Maison familiale – Andoharanofotsy', 'cancelled', '2025-08-31 18:21:49', '2025-09-08 04:01:58', 'Entrée : Foie gras, salade de fête\r\nPlat : Dinde rôtie, gratin dauphinois\r\nDessert : Bûche de Noël, fruits confits\r\nBoissons : Vin, champagne, jus', 'en'),
(25, 20, 'Team Building – Entreprise TechnoDev', 'Journée de cohésion d’équipe avec jeux, formation et activités sportives.', '2025-11-11 08:00:00', '2025-11-11 18:00:00', 'Parc de Mandraka – Centre de loisir', 'draft', '2025-08-31 18:24:20', NULL, 'Buffet libre : grillades, salades, pâtes\r\nDessert : Gâteaux variés, fruits\r\nBoissons : Jus, eau, sodas', 'en');

-- --------------------------------------------------------

--
-- Structure de la table `event_photo`
--

DROP TABLE IF EXISTS `event_photo`;
CREATE TABLE IF NOT EXISTS `event_photo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_event_photo_event_id` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `event_photo`
--

INSERT INTO `event_photo` (`id`, `filename`, `event_id`) VALUES
(25, 'eventphoto_68b069ff10bd8.jpg', 18),
(26, 'eventphoto_68b49f6b0e831.jpg', 20),
(27, 'eventphoto_68b49f73a73d2.jpg', 20),
(28, 'eventphoto_68b49f7c3c8bd.jpg', 20),
(29, 'eventphoto_68b49f820f1a5.jpg', 20),
(30, 'eventphoto_68b49f87a91bb.jpg', 20),
(32, 'eventphoto_68b49f95e4a7b.jpg', 20),
(33, 'eventphoto_68b49f9d696b2.jpg', 20),
(39, 'eventphoto_68b4a032b9719.webp', 20),
(40, 'eventphoto_68b4a03ddb216.jpg', 20),
(41, 'eventphoto_68b4a04a90ce2.webp', 20),
(42, 'eventphoto_68b4a0527eca5.jpg', 20),
(43, 'eventphoto_68b4a05f1a796.jpg', 20);

-- --------------------------------------------------------

--
-- Structure de la table `invitation`
--

DROP TABLE IF EXISTS `invitation`;
CREATE TABLE IF NOT EXISTS `invitation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `confirmed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F11D61A271F7E88B` (`event_id`),
  KEY `IDX_F11D61A29D1C3019` (`participant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `invitation`
--

INSERT INTO `invitation` (`id`, `event_id`, `participant_id`, `status`, `sent_at`, `confirmed_at`, `token`) VALUES
(45, 18, 77, 'sent', '2025-08-28 13:34:06', NULL, '85a6ac32158e50303ba0a34523fec751a7aac0ba830700271002becc1a39a8ab'),
(46, 18, 75, 'sent', '2025-08-28 13:34:18', NULL, '2587c3a437ab1dee1d892436f737a7b1c1014db35324540b9fac64a312fb004b'),
(48, 20, 87, 'confirmed', '2025-08-31 19:24:41', '2025-08-31 19:29:26', '1ac2e9b9a262b458726e6d6bab9ced65fb448e742b4b1c8c3927d608f14e6586'),
(49, 20, 85, 'confirmed', '2025-08-31 19:24:52', '2025-08-31 19:31:13', '27a0a11bb85f75b5ffb4b160a6824085a4048275fa2f1d0d1221c1b9fa28eec0'),
(50, 20, 84, 'sent', '2025-08-31 19:25:05', NULL, '2ee1ee680a16213da38d306af587d0683bccb1c82383bcdb1d29a32bd182e82a'),
(51, 20, 82, 'sent', '2025-08-31 19:25:25', NULL, '9b0d88f2a35578c202c50ef133bbd6b8490dc54367da0221b86aab4fccecf514'),
(53, 22, 93, 'confirmed', '2025-09-02 12:22:05', '2025-09-02 12:24:50', '5aafb2ebe29405a6679c6e37299b5573c002da63353505abff688bd10ce496fe'),
(54, 22, 102, 'confirmed', '2025-09-02 12:22:14', '2025-09-02 12:23:33', 'ea2df8fd52c02a199eaca26d76c54ce0517d4b6174dcdf495e5f296546f621d6'),
(55, 22, 88, 'sent', '2025-09-02 12:22:25', NULL, 'd5bd77276f8a9ef2138971807ed115b0a034bb0ae52f91975bca27de8c89d39d'),
(56, 22, 89, 'sent', '2025-09-02 12:22:34', NULL, '101e1d9e23e35f8904f13ac1b7959167e5f48899c6f9612bbe294c1b642d3544'),
(57, 22, 90, 'sent', '2025-09-02 12:22:44', NULL, 'e68124e574e644afb6f19643d8b9e88e8937cade6e11cbe04f509a7f0241af8a'),
(58, 22, 92, 'sent', '2025-09-02 12:22:54', NULL, '6fd4901f4642e488fc641140e1847baa58a8689750ca599e2f9529ad02127053'),
(59, 22, 98, 'sent', '2025-09-02 12:23:03', NULL, '7cf7174c2ce5ef6cf5200664f365a19187c22515c3e8e19d20dcb70a9ab33614'),
(60, 22, 94, 'sent', '2025-09-02 12:23:15', NULL, '8e6a88eb8f5e04df229fe7ee6008740fa5369462b44b0a66536beaf4c5d574eb'),
(62, 20, 124, 'confirmed', '2025-09-08 13:22:58', '2025-09-08 13:24:16', 'a1132038997196d7d013fdfb5ab4caee897c87fdc4aaf8ddce185ca0aa25ef56'),
(63, 25, 125, 'sent', '2025-09-08 13:25:31', NULL, 'f537dbfaa7965d55dc4add9f5cb84a26ba163f3e60a9dd94cf59da1641e9566b'),
(64, 25, 126, 'sent', '2025-09-08 13:25:36', NULL, 'f566a13c7f29c8b0e1c9c1b56c176d31d6867190ed7125034fb3d8a4c4b62f87'),
(65, 25, 127, 'sent', '2025-09-08 13:25:39', NULL, 'a980db34824fb74c96acf2edd48e2aa05f0b76324e18bb9d4cd38eb57061e3fd'),
(66, 25, 128, 'sent', '2025-09-08 13:25:41', NULL, '09c2e825d9b40a31baa8077b9f8a9e3b180efc549c067228713dddde67bf3454'),
(67, 25, 129, 'sent', '2025-09-08 13:25:43', NULL, 'd307359019b2a405791fc0cd39d84be55baba1e200dfb320afea3abf508a198c'),
(68, 25, 130, 'sent', '2025-09-08 13:25:45', NULL, '81954bfe9715611373e873f1e20d596f87acf325fada72606e3a9cb6167403f7'),
(69, 25, 131, 'sent', '2025-09-08 13:25:47', NULL, '5f3a6d00a34ad1ffdcd6e9f18a8697b1e1976bc6a41feda0b02c82af211f4aa6'),
(70, 25, 132, 'sent', '2025-09-08 13:25:49', NULL, '421ec6e18505e0d3ec254a7e87155a9ef701dc591d17d2482b50818a02df865d'),
(71, 25, 133, 'sent', '2025-09-08 13:25:51', NULL, 'd485081cc5c2e0c1ecad1f784511e90c7c3526fc1f080f222ad040e6d40e088f'),
(72, 25, 134, 'sent', '2025-09-08 13:25:53', NULL, 'f15c41098a38ce1465cac03442000463460febcf105ee7a4da51d387e3e8543d'),
(73, 25, 135, 'sent', '2025-09-08 13:25:55', NULL, '8e18705bcfcd92955c87279b53b90e9680d7a385489613845d4fc85a7ae3a92a'),
(74, 25, 136, 'sent', '2025-09-08 13:25:57', NULL, '4cf1d3995c12564145b9cc5a77b31dc744091a8878e562855355d4093a9e56d9'),
(75, 25, 137, 'sent', '2025-09-08 13:25:59', NULL, '368873d2d9148cdfddc684dc6e7e47a6e5a12ac10a391bd3b466dc219ae5b734'),
(76, 25, 138, 'sent', '2025-09-08 13:26:01', NULL, 'a63b028882640c31c81c0065b1775b9d30e95ab69eca66b75a1811dc19707bb5'),
(77, 25, 139, 'sent', '2025-09-08 13:26:04', NULL, '5e87a98eb82b40978eedc9feae1450942a59c5ec37ce1c0e709fde8e0141d0e8'),
(78, 25, 140, 'sent', '2025-09-08 13:26:06', NULL, '713c90a6171edd576e83e92964c032fe6be5857541a1542cec4010479b3297fc'),
(79, 25, 141, 'sent', '2025-09-08 13:26:07', NULL, '1fe344ad457c953ed9553e18e1a3cc9107668d4ffa779ace1f2473f96b848015'),
(80, 25, 142, 'sent', '2025-09-08 13:26:09', NULL, 'f38d3e9e4bd37aa2a13ae49ed779f88186f2854733a255d15a1a4a8c6d55d626');

-- --------------------------------------------------------

--
-- Structure de la table `participant`
--

DROP TABLE IF EXISTS `participant`;
CREATE TABLE IF NOT EXISTS `participant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `confirmation_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D79F6B117D8B1FB5` (`qr_code`),
  UNIQUE KEY `UNIQ_D79F6B11C05FB297` (`confirmation_token`),
  KEY `IDX_D79F6B1171F7E88B` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `participant`
--

INSERT INTO `participant` (`id`, `event_id`, `first_name`, `last_name`, `email`, `phone`, `company`, `position`, `qr_code`, `status`, `created_at`, `confirmation_token`) VALUES
(75, 18, 'Sarah', 'Rakotoarisoa', 'sarah.rakotoarisoa@gmail.com', '0341234567', 'Mada Event', 'Amie proche', 'QR_68b059e375ede_50d54d63d6e797e5', 'invited', '2025-08-28 13:30:11', 'bf06f2ad7d7119c7a5bfcbf65598c8776139a38c244deecbd4b6af78ddcad9c1'),
(76, 18, 'Kevin', 'Rabeandriana', 'kevin.rabeandriana@gmail.com', '0342345678', 'Orange Madagascar', 'Collègue', 'QR_68b05a1ddea2f_8c6f254220c21792', 'pending', '2025-08-28 13:31:09', '435b0142a65586a1e5c3c3ef1be3ad22510b0581ace526b9b0cc261c4e566910'),
(77, 18, 'Lova', 'Andrianina', 'bayane437@gmail.com', '0343456789', 'Telma', 'Ami d’enfance', 'QR_68b05a53842cc_4e6a0a3e635ee582', 'checked_in', '2025-08-28 13:32:03', '6d285c8d6135f1f0668ab4615f6c3ba5a61c4d4f8d4fe7e09a74194c1d813094'),
(78, 18, 'Hery', 'Rajaonarivelo', 'hery.rajaonarivelo@gmail.com', '0344567890', 'BNI Madagascar', 'Cousin', 'QR_68b05a8f96a8c_a48701aeecb05411', 'pending', '2025-08-28 13:33:03', 'ab1135f8d7023ad66f83bd1910a99479e7268d2596ce9fb58869da7d4f0897d7'),
(79, 18, 'Fara', 'Rasoanaivo', 'fara.rasoanaivo@gmail.com', '0345678901', 'BOA', 'Amie', 'QR_68b05ac86bc6f_4b682b7ae23b668f', 'pending', '2025-08-28 13:34:00', 'bebbcaedb34e9850e0a678695048545033364e90cfce2fb1f1110f42f0081ea8'),
(81, 20, 'Timote', 'Rabenandrasana', 'Timote@gmail.com', '0326547896', 'Telma', 'Cousin', 'QR_68b49456236b5_18847e38c76d5497', 'pending', '2025-08-31 18:28:38', 'fb973260c3c57786f6ac7f34cb3828b8a70a479edd3b1be5836075ff98b6919b'),
(82, 20, 'Angelus', 'Rakotobe', 'Angel@gmail.com', '0336547896', 'BOA', 'Collègue', 'QR_68b494a18b1e1_4deb7877d35dccf8', 'invited', '2025-08-31 18:29:53', '2042bf8f18e87ec6ad3227ee9d22e282aa3262deb203950fb2a297e99f49552c'),
(84, 20, 'Alfa', 'Glory', 'alfa@gmail.com', '0378945632', NULL, 'Soeur', 'QR_68b494fba8435_002ed69be36bb793', 'invited', '2025-08-31 18:31:23', '4e4fe5dd46c2471f3a91409923a1c1df28da6757a211999e808833f085475572'),
(85, 20, 'Miguel', 'BAYANE', 'miguelsingcol@gmail.com', '0322365478', 'Air MAd', 'Amie proche', 'QR_68b49553a5ece_b1820d2685c1d9e9', 'checked_in', '2025-08-31 18:32:51', 'be8b1b90bf95cf4b766d78757ab374f0b5a01ee0fe154bcc9d6065f19997fe84'),
(86, 20, 'Esther', 'Déborah David', 'esther@gmail.com', '0326547896', NULL, 'Famille', 'QR_68b495970b453_0399dadfb647c0f9', 'pending', '2025-08-31 18:33:59', '8bdcc610ac71243ccd551cf151d4edec9990bad0670519cc4bd6cf7bcda0d1e6'),
(87, 20, 'Nizar', 'Ansar Goulzar', 'singcolmiguel9@gmail.com', '0345612398', 'Rundev Team', 'Frère', 'QR_68b495fedf324_0f6e46b5fb21b4a5', 'checked_in', '2025-08-31 18:35:42', 'c525349687fd5e9ee84d17dba3f1dd3a3dff1db8344fafed54b6a1baf0486fe7'),
(88, 22, 'Goulzaraly', 'Adèle Fatima', 'miguelsingcol@gmail.com', '+33612345678', 'BNI', 'Amie d\'enfance', 'QR_68b6e04dd8179_ec77e7d3f2b7eeb7', 'checked_in', '2025-09-02 12:17:17', NULL),
(89, 22, 'Marie', 'Leblanc', 'marie.leblanc@example.com', '', NULL, NULL, 'QR_68b6e04dd86da_e7040c589a109bfe', 'invited', '2025-09-02 12:17:17', NULL),
(90, 22, 'Paul', 'Martin', 'paul.martin@example.com', '+33687654321', NULL, NULL, 'QR_68b6e04dd8a95_480634080c032fa6', 'invited', '2025-09-02 12:17:17', NULL),
(91, 22, 'Sarah', 'Rakotoarisoa', 'sarah.rakotoarisoa@example.com', '+261341234567', NULL, NULL, 'QR_68b6e04dd8ea2_694950baac4278e7', 'pending', '2025-09-02 12:17:17', NULL),
(92, 22, 'Kevin', 'Rabeandriana', 'kevin.rabeandriana@example.com', '+261342345678', NULL, NULL, 'QR_68b6e04dd951b_6d28983088553f23', 'invited', '2025-09-02 12:17:17', NULL),
(93, 22, 'Lova', 'Andrianina', 'bayane437@gmail.com', '+261343456789', 'Orange', 'Cousin', 'QR_68b6e04dda54b_7eebf164babc5f5e', 'checked_in', '2025-09-02 12:17:17', NULL),
(94, 22, 'Hery', 'Rajaonarivelo', 'hery.rajaonarivelo@example.com', '+261344567890', NULL, NULL, 'QR_68b6e04ddafdb_96f207d760cad5ab', 'invited', '2025-09-02 12:17:17', NULL),
(95, 22, 'Fara', 'Rasoanaivo', 'fara.rasoanaivo@example.com', '+261345678901', NULL, NULL, 'QR_68b6e04ddb648_015978c1ea9b7d4b', 'pending', '2025-09-02 12:17:17', NULL),
(96, 22, 'Mamy', 'Rakotobe', 'mamy.rakotobe@example.com', '+261346789012', NULL, NULL, 'QR_68b6e04ddbc67_24f62a612a8d75de', 'pending', '2025-09-02 12:17:17', NULL),
(98, 22, 'Patrick', 'Raharison', 'patrick.raharison@example.com', '+261348901234', NULL, NULL, 'QR_68b6e04ddc6d8_4b3c09ce3f303e3e', 'invited', '2025-09-02 12:17:17', NULL),
(99, 22, 'Elie', 'Razafindrakoto', 'elie.razafindrakoto@example.com', '+261349012345', NULL, NULL, 'QR_68b6e04ddcb75_60765afd5c37879d', 'pending', '2025-09-02 12:17:17', NULL),
(100, 22, 'Cynthia', 'Raveloson', 'cynthia.raveloson@example.com', '+261349123456', NULL, NULL, 'QR_68b6e04ddd058_0c148eb9f51e1157', 'pending', '2025-09-02 12:17:17', NULL),
(101, 22, 'Tiana', 'Ravelo', 'tiana.ravelo@example.com', '+261350123456', NULL, NULL, 'QR_68b6e04ddd41b_519f4325cc28bd41', 'pending', '2025-09-02 12:17:17', NULL),
(102, 22, 'Joel', 'Andrianjafy', 'singcolmiguel9@gmail.com', '+261351234567', NULL, NULL, 'QR_68b6e04ddd7b3_6f280bbd322187dc', 'checked_in', '2025-09-02 12:17:17', NULL),
(103, 22, 'Miora', 'Rasoarimanana', 'miora.rasoarimanana@example.com', '+261352345678', NULL, NULL, 'QR_68b6e04dde9b8_24b1024a1320fc2b', 'pending', '2025-09-02 12:17:17', NULL),
(104, 22, 'Daniel', 'Rakotomalala', 'daniel.rakotomalala@example.com', '+261353456789', NULL, NULL, 'QR_68b6e04ddef62_1a3dbe40bc554693', 'pending', '2025-09-02 12:17:17', NULL),
(105, 22, 'Noro', 'Ranaivo', 'noro.ranaivo@example.com', '+261354567890', NULL, NULL, 'QR_68b6e04ddf63c_6665b4887c6b45be', 'pending', '2025-09-02 12:17:17', NULL),
(124, 20, 'Layana', 'Andriatsimarofy', 'bayane437@gmail.com', '0345698745', 'Orange', 'Amie proche', 'QR_68bed8a5b7ace_d095de81eef5f5d4', 'checked_in', '2025-09-08 13:22:45', 'c6bf39f17756be2c720d66d3497faff26db86b8b3ccb530071cbf4f82b349896'),
(125, 25, 'Jean', 'Durand', 'jean.durand@example.com', '+33612345678', NULL, NULL, 'QR_68bed93d6d3a7_dd1e35a46748c9fa', 'invited', '2025-09-08 13:25:17', NULL),
(126, 25, 'Marie', 'Leblanc', 'marie.leblanc@example.com', '', NULL, NULL, 'QR_68bed93d6d92d_7a128907bce163c1', 'invited', '2025-09-08 13:25:17', NULL),
(127, 25, 'Paul', 'Martin', 'paul.martin@example.com', '+33687654321', NULL, NULL, 'QR_68bed93d6de50_90c4121dc3ac33c8', 'invited', '2025-09-08 13:25:17', NULL),
(128, 25, 'Sarah', 'Rakotoarisoa', 'sarah.rakotoarisoa@example.com', '+261341234567', NULL, NULL, 'QR_68bed93d6e42f_3a28e38460d07d29', 'invited', '2025-09-08 13:25:17', NULL),
(129, 25, 'Kevin', 'Rabeandriana', 'kevin.rabeandriana@example.com', '+261342345678', NULL, NULL, 'QR_68bed93d6eada_24cfd958592e2e78', 'invited', '2025-09-08 13:25:17', NULL),
(130, 25, 'Lova', 'Andrianina', 'lova.andrianina@example.com', '+261343456789', NULL, NULL, 'QR_68bed93d6f2a8_e5358618ecab50cf', 'invited', '2025-09-08 13:25:17', NULL),
(131, 25, 'Hery', 'Rajaonarivelo', 'hery.rajaonarivelo@example.com', '+261344567890', NULL, NULL, 'QR_68bed93d6f93d_88559ae8e24819b1', 'invited', '2025-09-08 13:25:17', NULL),
(132, 25, 'Fara', 'Rasoanaivo', 'fara.rasoanaivo@example.com', '+261345678901', NULL, NULL, 'QR_68bed93d6ff10_ec70f1e26aaaabab', 'invited', '2025-09-08 13:25:17', NULL),
(133, 25, 'Mamy', 'Rakotobe', 'mamy.rakotobe@example.com', '+261346789012', NULL, NULL, 'QR_68bed93d70526_c041cc9e9bbb3256', 'invited', '2025-09-08 13:25:17', NULL),
(134, 25, 'Anja', 'Andriambelo', 'anja.andriambelo@example.com', '+261347890123', NULL, NULL, 'QR_68bed93d70aa0_12b7fadb4a7dce82', 'invited', '2025-09-08 13:25:17', NULL),
(135, 25, 'Patrick', 'Raharison', 'patrick.raharison@example.com', '+261348901234', NULL, NULL, 'QR_68bed93d70fa4_8d5dca61d46ebce8', 'invited', '2025-09-08 13:25:17', NULL),
(136, 25, 'Elie', 'Razafindrakoto', 'elie.razafindrakoto@example.com', '+261349012345', NULL, NULL, 'QR_68bed93d71594_f414f009b45c652e', 'invited', '2025-09-08 13:25:17', NULL),
(137, 25, 'Cynthia', 'Raveloson', 'cynthia.raveloson@example.com', '+261349123456', NULL, NULL, 'QR_68bed93d71ba3_d493b02c56e93ed5', 'invited', '2025-09-08 13:25:17', NULL),
(138, 25, 'Tiana', 'Ravelo', 'tiana.ravelo@example.com', '+261350123456', NULL, NULL, 'QR_68bed93d720c0_b1a8960d080ec0be', 'invited', '2025-09-08 13:25:17', NULL),
(139, 25, 'Joel', 'Andrianjafy', 'joel.andrianjafy@example.com', '+261351234567', NULL, NULL, 'QR_68bed93d72626_324f5130c6cfe406', 'invited', '2025-09-08 13:25:17', NULL),
(140, 25, 'Miora', 'Rasoarimanana', 'miora.rasoarimanana@example.com', '+261352345678', NULL, NULL, 'QR_68bed93d72d64_2e4296654c60a562', 'invited', '2025-09-08 13:25:17', NULL),
(141, 25, 'Daniel', 'Rakotomalala', 'daniel.rakotomalala@example.com', '+261353456789', NULL, NULL, 'QR_68bed93d73329_2d2fb1175dd683be', 'invited', '2025-09-08 13:25:17', NULL),
(142, 25, 'Noro', 'Ranaivo', 'noro.ranaivo@example.com', '+261354567890', NULL, NULL, 'QR_68bed93d73803_8fbe10d5414098d5', 'invited', '2025-09-08 13:25:17', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `first_name`, `last_name`, `created_at`, `reset_token`, `reset_token_expires_at`) VALUES
(19, 'miguelsingcol@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$BiaFH9pUPfLl1zuz2x258.t6jtpGmttvAoS2zr.xvBV3fJ/7VZgs6', 'Miguel', 'Nomenjanahary', '2025-08-26 16:42:19', NULL, NULL),
(20, 'bayane437@gmail.com', '[]', '$2y$13$KNCfs0G.NXLhLCJdt7FGh.ouaM1OiEUBrFNQ/Ddiq1wWeb7Yo5dj.', 'Kevine', 'Princy', '2025-08-31 06:37:48', NULL, NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `agent`
--
ALTER TABLE `agent`
  ADD CONSTRAINT `FK_268B9C9D71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_268B9C9D7E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `check_in`
--
ALTER TABLE `check_in`
  ADD CONSTRAINT `FK_90466CF971F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_90466CF99D1C3019` FOREIGN KEY (`participant_id`) REFERENCES `participant` (`id`);

--
-- Contraintes pour la table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_3BAE0AA7876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `event_photo`
--
ALTER TABLE `event_photo`
  ADD CONSTRAINT `FK_event_photo_event_id` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

--
-- Contraintes pour la table `invitation`
--
ALTER TABLE `invitation`
  ADD CONSTRAINT `FK_F11D61A271F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_F11D61A29D1C3019` FOREIGN KEY (`participant_id`) REFERENCES `participant` (`id`);

--
-- Contraintes pour la table `participant`
--
ALTER TABLE `participant`
  ADD CONSTRAINT `FK_D79F6B1171F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
