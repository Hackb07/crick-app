-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 07:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cricapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_action_logs`
--

CREATE TABLE `admin_action_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `resource_type` varchar(50) NOT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_action_logs`
--

INSERT INTO `admin_action_logs` (`log_id`, `admin_id`, `action_type`, `resource_type`, `resource_id`, `reason`, `ip_address`, `timestamp`) VALUES
(1, 1, 'create', 'match', 3, '{\"teams\":[1,2]}', '::1', '2025-11-13 16:49:43'),
(2, 1, 'create', 'match', 4, '{\"teams\":[1,2]}', '::1', '2025-11-13 20:00:47'),
(3, 1, 'create', 'match', 5, '{\"teams\":[1,2]}', '::1', '2025-11-13 20:22:57'),
(4, 1, 'create', 'match', 6, '{\"teams\":[1,2]}', '::1', '2025-11-14 17:41:23'),
(5, 1, 'assign_players', 'match', 6, '{\"team_id\":1,\"team_name\":\"Team A\",\"player_count\":3,\"player_ids\":[\"3\",\"1\",\"2\"]}', '::1', '2025-11-14 17:43:40'),
(6, 1, 'assign_players', 'match', 6, '{\"team_id\":2,\"team_name\":\"Team B\",\"player_count\":3,\"player_ids\":[\"3\",\"1\",\"2\"]}', '::1', '2025-11-14 17:43:43'),
(7, 1, 'update', 'match', 5, '{\"changes\":{\"team1_id\":1,\"team2_id\":2,\"series_id\":1,\"match_date\":\"2025-11-14T12:26\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"scheduled\"}}', '::1', '2025-11-14 17:46:43'),
(8, 1, 'assign_players', 'match', 5, '{\"team_id\":1,\"team_name\":\"Team A\",\"player_count\":3,\"player_ids\":[\"3\",\"1\",\"2\"]}', '::1', '2025-11-14 17:46:48'),
(9, 1, 'assign_players', 'match', 5, '{\"team_id\":2,\"team_name\":\"Team B\",\"player_count\":3,\"player_ids\":[\"3\",\"1\",\"2\"]}', '::1', '2025-11-14 17:46:50'),
(10, 1, 'update', 'match', 5, '{\"changes\":{\"team1_id\":1,\"team2_id\":2,\"series_id\":1,\"match_date\":\"2025-11-14T12:26\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"scheduled\"}}', '::1', '2025-11-14 17:51:37'),
(11, 1, 'create', 'match', 7, '{\"teams\":[1,2]}', '::1', '2025-11-14 17:54:21'),
(12, 1, 'assign_players', 'match', 7, '{\"team_id\":1,\"team_name\":\"Team A\",\"player_count\":3,\"player_ids\":[\"3\",\"1\",\"2\"]}', '::1', '2025-11-14 17:54:32'),
(13, 1, 'assign_players', 'match', 7, '{\"team_id\":1,\"team_name\":\"Team A\",\"player_count\":2,\"player_ids\":[\"3\",\"2\"]}', '::1', '2025-11-14 17:54:35'),
(14, 1, 'assign_players', 'match', 7, '{\"team_id\":2,\"team_name\":\"Team B\",\"player_count\":1,\"player_ids\":[\"1\"]}', '::1', '2025-11-14 17:54:44'),
(15, 1, 'create', 'match', 8, '{\"teams\":[1,2]}', '::1', '2025-11-14 18:11:04'),
(16, 1, 'delete', 'match', 6, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:23'),
(17, 1, 'delete', 'match', 3, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:27'),
(18, 1, 'delete', 'match', 8, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:30'),
(19, 1, 'delete', 'match', 7, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:34'),
(20, 1, 'delete', 'match', 2, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:37'),
(21, 1, 'delete', 'match', 4, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:40'),
(22, 1, 'delete', 'match', 5, '{\"match_name\":\"Team A vs Team B\"}', '::1', '2025-11-14 18:16:44'),
(23, 1, 'delete', 'player', 3, '{\"name\":\"Alex Seeman\"}', '::1', '2025-11-14 18:17:04'),
(24, 1, 'delete', 'player', 1, '{\"name\":\"Kavin S\"}', '::1', '2025-11-14 18:17:08'),
(25, 1, 'create', 'team', 5, '{\"name\":\"Kavin S\"}', '::1', '2025-11-14 22:00:15'),
(26, 1, 'create', 'team', 6, '{\"name\":\"Alex\"}', '::1', '2025-11-14 22:01:10'),
(27, 1, 'delete', 'player', 2, '{\"name\":\"Khaled Hosseini\"}', '::1', '2025-11-14 22:01:16'),
(28, 1, 'create', 'player', 4, '{\"name\":\"Kavin S\"}', '::1', '2025-11-14 22:01:27'),
(29, 1, 'create', 'player', 5, '{\"name\":\"Alex Seeman\"}', '::1', '2025-11-14 22:01:40'),
(30, 1, 'create', 'player', 6, '{\"name\":\"Deepak\"}', '::1', '2025-11-14 22:01:54'),
(31, 1, 'create', 'player', 7, '{\"name\":\"Roshik\"}', '::1', '2025-11-14 22:02:10'),
(32, 1, 'create', 'match', 9, '{\"teams\":[6,5]}', '::1', '2025-11-14 22:04:42'),
(33, 1, 'delete', 'match', 9, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-14 23:07:53'),
(34, 1, 'create', 'match', 10, '{\"teams\":[6,5]}', '::1', '2025-11-14 23:10:59'),
(35, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"6\"]}', '::1', '2025-11-14 23:11:04'),
(36, 1, 'assign_players', 'match', 10, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":2,\"player_ids\":[\"4\",\"7\"]}', '::1', '2025-11-14 23:11:09'),
(37, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"4\"]}', '::1', '2025-11-14 23:11:17'),
(38, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"6\"]}', '::1', '2025-11-14 23:11:33'),
(39, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"6\"]}', '::1', '2025-11-14 23:11:36'),
(40, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"6\"]}', '::1', '2025-11-14 23:11:40'),
(41, 1, 'assign_players', 'match', 10, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":2,\"player_ids\":[\"4\",\"7\"]}', '::1', '2025-11-14 23:11:45'),
(42, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":4,\"player_ids\":[\"5\",\"6\",\"4\",\"7\"]}', '::1', '2025-11-14 23:12:27'),
(43, 1, 'assign_players', 'match', 10, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":4,\"player_ids\":[\"5\",\"6\",\"4\",\"7\"]}', '::1', '2025-11-14 23:12:32'),
(44, 1, 'assign_players', 'match', 10, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":2,\"player_ids\":[\"5\",\"6\"]}', '::1', '2025-11-14 23:13:51'),
(45, 1, 'assign_players', 'match', 10, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":2,\"player_ids\":[\"4\",\"7\"]}', '::1', '2025-11-14 23:13:59'),
(46, 1, 'delete', 'match', 10, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-15 00:02:11'),
(47, 1, 'create', 'match', 11, '{\"teams\":[6,5]}', '::1', '2025-11-15 00:02:26'),
(48, 1, 'change_innings', 'match', 11, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-15 00:03:58'),
(49, 1, 'delete', 'match', 11, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-15 21:58:08'),
(50, 1, 'create', 'match', 12, '{\"teams\":[6,5]}', '::1', '2025-11-15 21:58:30'),
(51, 1, 'change_innings', 'match', 12, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-15 22:00:23'),
(52, 1, 'delete', 'match', 12, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-15 22:03:58'),
(53, 1, 'create', 'match', 13, '{\"teams\":[6,5]}', '::1', '2025-11-15 22:04:31'),
(54, 1, 'change_innings', 'match', 13, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-15 22:06:29'),
(55, 1, 'create', 'match', 14, '{\"teams\":[6,5]}', '::1', '2025-11-15 22:10:03'),
(56, 1, 'create', 'match', 15, '{\"teams\":[6,5]}', '::1', '2025-11-15 22:17:43'),
(57, 1, 'change_innings', 'match', 15, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-15 22:19:16'),
(58, 1, 'create', 'series', 2, '{\"name\":\"Khaled Hosseini\"}', '127.0.0.1', '2025-11-15 23:21:07'),
(59, 1, 'delete', 'match', 14, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 00:02:25'),
(60, 1, 'delete', 'match', 13, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 00:02:32'),
(61, 1, 'create', 'player', 8, '{\"name\":\"Edwin\"}', '::1', '2025-11-16 00:04:42'),
(62, 1, 'create', 'player', 9, '{\"name\":\"Edwin\"}', '::1', '2025-11-16 00:05:00'),
(63, 1, 'update', 'player', 5, '{\"changes\":{\"name\":\"Alex Seeman SK\",\"date_of_birth\":null,\"batting_hand\":\"\",\"bowling_style\":\"\",\"profile_image\":\"\"}}', '::1', '2025-11-16 00:05:25'),
(64, 1, 'create', 'series', 3, '{\"name\":\"Kavin S\"}', '::1', '2025-11-16 00:08:07'),
(65, 1, 'create', 'team', 7, '{\"name\":\"Khaled Hosseini\"}', '::1', '2025-11-16 00:09:18'),
(66, 1, 'delete', 'player', 5, '{\"name\":\"Alex Seeman SK\"}', '::1', '2025-11-16 00:17:09'),
(67, 1, 'delete', 'player', 8, '{\"name\":\"Edwin\"}', '::1', '2025-11-16 00:17:17'),
(68, 1, 'create', 'match', 16, '{\"teams\":[6,5]}', '::1', '2025-11-16 00:17:58'),
(69, 1, 'create', 'player', 10, '{\"name\":\"Alex Seeman\"}', '::1', '2025-11-16 00:23:37'),
(70, 1, 'create', 'player', 11, '{\"name\":\"Shephin\"}', '::1', '2025-11-16 00:24:58'),
(71, 1, 'create', 'player', 12, '{\"name\":\"Sathis\"}', '::1', '2025-11-16 00:25:16'),
(72, 1, 'create', 'player', 13, '{\"name\":\"Dhilipan\"}', '::1', '2025-11-16 00:25:40'),
(73, 1, 'create', 'player', 14, '{\"name\":\"Dilsen\"}', '::1', '2025-11-16 00:26:09'),
(74, 1, 'create', 'player', 15, '{\"name\":\"Jonath\"}', '::1', '2025-11-16 00:26:40'),
(75, 1, 'create', 'player', 16, '{\"name\":\"Jelikshan\"}', '::1', '2025-11-16 00:27:38'),
(76, 1, 'create', 'player', 17, '{\"name\":\"Adriel\"}', '::1', '2025-11-16 00:27:59'),
(77, 1, 'create', 'player', 18, '{\"name\":\"Gnanakumar J\"}', '::1', '2025-11-16 00:28:37'),
(78, 1, 'create', 'player', 19, '{\"name\":\"Benny\"}', '::1', '2025-11-16 00:29:09'),
(79, 1, 'create', 'player', 20, '{\"name\":\"Gabi\"}', '::1', '2025-11-16 00:29:25'),
(80, 1, 'create', 'player', 21, '{\"name\":\"Simbu\"}', '::1', '2025-11-16 00:29:35'),
(81, 1, 'create', 'series', 4, '{\"name\":\"16 NOV 2025\"}', '::1', '2025-11-16 00:30:07'),
(82, 1, 'create', 'match', 17, '{\"teams\":[6,5]}', '::1', '2025-11-16 00:30:35'),
(83, 1, 'change_innings', 'match', 17, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-16 00:33:35'),
(84, 1, 'delete', 'match', 16, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 00:38:39'),
(85, 1, 'create', 'match', 18, '{\"teams\":[6,5]}', '::1', '2025-11-16 00:51:22'),
(86, 1, 'change_innings', 'match', 18, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-16 00:53:49'),
(87, 1, 'delete', 'match', 15, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 01:27:17'),
(88, 1, 'delete', 'match', 17, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 01:27:25'),
(89, 1, 'delete', 'match', 18, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-16 01:27:33'),
(90, 1, 'create', 'match', 19, '{\"teams\":[6,5]}', '::1', '2025-11-16 01:28:43'),
(91, 1, 'assign_players', 'match', 19, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":5,\"player_ids\":[\"17\",\"10\",\"19\",\"6\",\"13\"]}', '::1', '2025-11-16 01:28:55'),
(92, 1, 'assign_players', 'match', 19, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":5,\"player_ids\":[\"14\",\"9\",\"20\",\"18\",\"16\"]}', '::1', '2025-11-16 01:29:00'),
(93, 1, 'change_innings', 'match', 19, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-16 01:30:13'),
(94, 1, 'create', 'match', 20, '{\"teams\":[6,5]}', '::1', '2025-11-16 11:33:15'),
(95, 1, 'update', 'match_player_assignments', 20, '{\"team_id\":6,\"player_count\":2}', '::1', '2025-11-16 11:33:25'),
(96, 1, 'update', 'match_player_assignments', 20, '{\"team_id\":5,\"player_count\":4}', '::1', '2025-11-16 11:33:33'),
(97, 1, 'update', 'match_player_assignments', 20, '{\"team_id\":6,\"player_count\":4}', '::1', '2025-11-16 11:33:42'),
(98, 1, 'update', 'match_toss', 20, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-16 11:33:49'),
(99, 1, 'change_innings', 'match', 20, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-16 11:34:06'),
(100, 1, 'update', 'match_player_assignments', 20, '{\"team_id\":6,\"player_count\":2}', '::1', '2025-11-16 11:43:10'),
(101, 1, 'update', 'player', 17, '{\"changes\":{\"name\":\"Adriel\",\"date_of_birth\":null,\"batting_hand\":\"\",\"bowling_style\":\"Right-arm Fast\",\"profile_image\":\"\"}}', '::1', '2025-11-16 12:05:28'),
(102, 1, 'delete', 'match', 20, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-20 02:37:15'),
(103, 1, 'create', 'match', 21, '{\"teams\":[6,5]}', '::1', '2025-11-20 02:40:54'),
(104, 1, 'update', 'match_player_assignments', 21, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-20 02:41:07'),
(105, 1, 'update', 'match_player_assignments', 21, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-20 02:41:14'),
(106, 1, 'update', 'match_player_assignments', 21, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-20 02:41:24'),
(107, 1, 'update', 'match_player_assignments', 21, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-20 02:51:58'),
(108, 1, 'update', 'match_player_assignments', 21, '{\"team_id\":6,\"player_count\":4}', '::1', '2025-11-20 02:52:10'),
(109, 1, 'create', 'match', 22, '{\"teams\":[6,5]}', '::1', '2025-11-20 23:15:04'),
(110, 1, 'update', 'match_player_assignments', 22, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-20 23:17:56'),
(111, 1, 'update', 'match_player_assignments', 22, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-20 23:18:01'),
(112, 1, 'update', 'match_toss', 21, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-20 23:39:45'),
(113, 1, 'change_innings', 'match', 21, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-20 23:45:36'),
(114, 1, 'update', 'player', 17, '{\"changes\":{\"name\":\"Adriell\",\"date_of_birth\":null,\"batting_hand\":\"\",\"bowling_style\":\"\",\"profile_image\":\"\"}}', '::1', '2025-11-21 15:49:27'),
(115, 1, 'create', 'match', 23, '{\"teams\":[6,5]}', '::1', '2025-11-21 15:49:48'),
(116, 1, 'assign_players', 'match', 23, '{\"team_id\":6,\"team_name\":\"Alex\",\"player_count\":5,\"player_ids\":[\"17\",\"10\",\"19\",\"6\",\"13\"]}', '::1', '2025-11-21 15:50:02'),
(117, 1, 'assign_players', 'match', 23, '{\"team_id\":5,\"team_name\":\"Kavin S\",\"player_count\":5,\"player_ids\":[\"14\",\"9\",\"20\",\"18\",\"16\"]}', '::1', '2025-11-21 15:50:08'),
(118, 2, 'delete', 'match', 21, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-21 23:00:31'),
(119, 2, 'delete', 'match', 23, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-21 23:00:36'),
(120, 2, 'delete', 'match', 22, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-21 23:00:41'),
(121, 2, 'delete', 'match', 19, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-21 23:00:45'),
(122, 2, 'create', 'match', 24, '{\"teams\":[6,5]}', '::1', '2025-11-21 23:01:15'),
(123, 2, 'update', 'match_player_assignments', 24, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-21 23:01:29'),
(124, 2, 'update', 'match_player_assignments', 24, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-21 23:01:41'),
(125, 2, 'update', 'match_toss', 24, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-21 23:01:52'),
(126, 2, 'change_innings', 'match', 24, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":2}', '::1', '2025-11-21 23:07:05'),
(127, 1, 'create', 'series', 5, '{\"name\":\"Test Series 2025\"}', '::1', '2025-11-21 23:30:14'),
(128, 2, 'create', 'match', 25, '{\"teams\":[6,5]}', '::1', '2025-11-21 23:30:40'),
(129, 2, 'update', 'match_player_assignments', 25, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-21 23:30:57'),
(130, 2, 'update', 'match_player_assignments', 25, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-21 23:31:02'),
(131, 2, 'update', 'match_toss', 25, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-21 23:31:13'),
(132, 2, 'update', 'match_player_assignments', 26, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-21 23:50:28'),
(133, 2, 'update', 'match_player_assignments', 26, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-21 23:50:32'),
(134, 2, 'update', 'match_toss', 26, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-21 23:50:39'),
(135, 2, 'create', 'match', 27, '{\"teams\":[6,5]}', '::1', '2025-11-22 00:11:29'),
(136, 2, 'update', 'match_player_assignments', 27, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-22 00:11:37'),
(137, 2, 'update', 'match_player_assignments', 27, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-22 00:11:41'),
(138, 2, 'update', 'match_toss', 27, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-22 00:11:47'),
(139, 1, 'create', 'match', 28, '{\"teams\":[6,5]}', '::1', '2025-11-22 00:43:30'),
(140, 1, 'update', 'match_player_assignments', 28, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-22 00:43:51'),
(141, 1, 'update', 'match_player_assignments', 28, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-22 00:43:55'),
(142, 1, 'update', 'match_toss', 28, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-22 00:44:20'),
(143, 1, 'change_innings', 'match', 28, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-22 00:45:56'),
(144, 1, 'delete', 'match', 28, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 00:59:44'),
(145, 1, 'delete', 'match', 27, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 00:59:47'),
(146, 1, 'delete', 'match', 26, '{\"match_name\":\"Kavin S vs Alex\"}', '::1', '2025-11-22 00:59:52'),
(147, 1, 'create', 'match', 29, '{\"teams\":[6,5]}', '::1', '2025-11-22 13:51:06'),
(148, 1, 'update', 'match_player_assignments', 29, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-22 13:51:29'),
(149, 1, 'update', 'match_player_assignments', 29, '{\"team_id\":5,\"player_count\":6}', '::1', '2025-11-22 13:51:42'),
(150, 1, 'update', 'match_toss', 29, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-22 13:51:57'),
(151, 1, 'change_innings', 'match', 25, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-22 14:08:04'),
(152, 1, 'create', 'match', 30, '{\"teams\":[6,5]}', '::1', '2025-11-22 15:14:32'),
(153, 1, 'delete', 'match', 25, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 17:34:35'),
(154, 1, 'delete', 'match', 29, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 17:34:39'),
(155, 1, 'delete', 'match', 24, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 17:34:42'),
(156, 1, 'delete', 'match', 30, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 17:34:45'),
(157, 1, 'create', 'match', 31, '{\"teams\":[6,5]}', '::1', '2025-11-22 17:35:06'),
(158, 1, 'update', 'match_player_assignments', 31, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-22 17:36:46'),
(159, 1, 'update', 'match_player_assignments', 31, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-22 17:37:07'),
(160, 1, 'update', 'match_toss', 31, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-22 17:37:17'),
(161, 1, 'delete', 'match', 31, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-22 23:48:38'),
(162, 1, 'create', 'match', 32, '{\"teams\":[6,5]}', '::1', '2025-11-22 23:48:53'),
(163, 1, 'update', 'match_player_assignments', 32, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-22 23:49:16'),
(164, 1, 'update', 'match_player_assignments', 32, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-22 23:49:24'),
(165, 1, 'update', 'match_toss', 32, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-22 23:49:34'),
(166, 1, 'change_innings', 'match', 32, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-22 23:58:16'),
(167, 1, 'delete', 'match', 32, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 00:16:11'),
(168, 1, 'create', 'match', 33, '{\"teams\":[6,5]}', '::1', '2025-11-23 00:16:26'),
(169, 1, 'update', 'match_player_assignments', 33, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-23 00:16:39'),
(170, 1, 'update', 'match_player_assignments', 33, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 00:16:53'),
(171, 1, 'update', 'match_toss', 33, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 00:17:13'),
(172, 1, 'change_innings', 'match', 33, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 00:19:24'),
(173, 1, 'create', 'match', 34, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:02:44'),
(174, 1, 'delete', 'match', 33, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:03:54'),
(175, 1, 'delete', 'match', 34, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:03:57'),
(176, 1, 'create', 'match', 35, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:04:13'),
(177, 1, 'update', 'match_player_assignments', 35, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-23 01:04:35'),
(178, 1, 'update', 'match_player_assignments', 35, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:04:41'),
(179, 1, 'update', 'match_toss', 35, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 01:04:45'),
(180, 1, 'update', 'match', 35, '{\"changes\":{\"team1_id\":6,\"team2_id\":5,\"series_id\":5,\"match_date\":\"\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"draft\"}}', '::1', '2025-11-23 01:05:25'),
(181, 1, 'update', 'match', 35, '{\"changes\":{\"team1_id\":6,\"team2_id\":5,\"series_id\":5,\"match_date\":\"\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"draft\"}}', '::1', '2025-11-23 01:05:29'),
(182, 1, 'create', 'match', 36, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:07:14'),
(183, 1, 'update', 'match_player_assignments', 36, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 01:07:31'),
(184, 1, 'update', 'match_player_assignments', 36, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:07:55'),
(185, 1, 'update', 'match_toss', 36, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 01:07:57'),
(186, 1, 'delete', 'match', 36, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:09:17'),
(187, 1, 'delete', 'match', 35, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:09:20'),
(188, 1, 'create', 'match', 37, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:09:27'),
(189, 1, 'update', 'match_player_assignments', 37, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-23 01:09:37'),
(190, 1, 'update', 'match_player_assignments', 37, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 01:09:42'),
(191, 1, 'update', 'match_player_assignments', 37, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:09:54'),
(192, 1, 'update', 'match_toss', 37, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 01:09:56'),
(193, 1, 'create', 'match', 38, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:12:25'),
(194, 1, 'update', 'match_player_assignments', 38, '{\"team_id\":6,\"player_count\":5}', '::1', '2025-11-23 01:15:36'),
(195, 1, 'update', 'match_player_assignments', 38, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:15:42'),
(196, 1, 'update', 'match_toss', 38, '{\"toss_winner_id\":5,\"toss_decision\":\"bowl\"}', '::1', '2025-11-23 01:15:45'),
(197, 1, 'create', 'match', 39, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:17:59'),
(198, 1, 'assign_players', 'match', 39, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 01:25:00'),
(199, 1, 'assign_players', 'match', 39, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:25:11'),
(200, 1, 'update', 'match_toss', 39, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 01:25:13'),
(201, 1, 'change_innings', 'match', 39, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 01:32:34'),
(202, 1, 'delete', 'match', 39, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:38:03'),
(203, 1, 'delete', 'match', 38, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:38:06'),
(204, 1, 'delete', 'match', 37, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 01:38:09'),
(205, 1, 'create', 'match', 40, '{\"teams\":[6,5]}', '::1', '2025-11-23 01:38:44'),
(206, 1, 'assign_players', 'match', 40, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 01:39:13'),
(207, 1, 'assign_players', 'match', 40, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 01:39:23'),
(208, 1, 'update', 'match_toss', 40, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 01:39:28'),
(209, 1, 'change_innings', 'match', 40, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 01:53:59'),
(210, 1, 'update', 'player', 17, '{\"changes\":{\"name\":\"Adriell\",\"date_of_birth\":null,\"batting_hand\":\"\",\"bowling_style\":\"\",\"profile_image\":\"\"}}', '::1', '2025-11-23 02:52:50'),
(211, 1, 'delete', 'match', 40, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 02:53:01'),
(212, 1, 'create', 'player', 22, '{\"name\":\"Naveen\"}', '::1', '2025-11-23 02:55:41'),
(213, 1, 'create', 'match', 41, '{\"teams\":[6,5]}', '::1', '2025-11-23 03:02:46'),
(214, 1, 'assign_players', 'match', 41, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 03:03:02'),
(215, 1, 'assign_players', 'match', 41, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 03:03:12'),
(216, 1, 'update', 'match_toss', 41, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 03:03:15'),
(217, 1, 'change_innings', 'match', 41, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 03:16:04'),
(218, 1, 'delete', 'match', 41, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 04:04:41'),
(219, 1, 'create', 'series', 6, '{\"name\":\"Nov 23\"}', '::1', '2025-11-23 04:05:14'),
(220, 1, 'create', 'match', 42, '{\"teams\":[6,5]}', '::1', '2025-11-23 04:05:45'),
(221, 1, 'assign_players', 'match', 42, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 04:05:59'),
(222, 1, 'assign_players', 'match', 42, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 04:06:12'),
(223, 1, 'update', 'match_toss', 42, '{\"toss_winner_id\":5,\"toss_decision\":\"bowl\"}', '::1', '2025-11-23 04:06:15'),
(224, 1, 'change_innings', 'match', 42, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 04:13:08'),
(225, 1, 'delete', 'match', 42, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 04:36:53'),
(226, 1, 'delete', 'match', 44, '{\"match_name\":\"Royal Challengers vs Kolkata Knights\"}', '::1', '2025-11-23 04:36:57'),
(227, 1, 'delete', 'match', 45, '{\"match_name\":\"Mumbai Indians vs Royal Challengers\"}', '::1', '2025-11-23 04:37:00'),
(228, 1, 'delete', 'match', 43, '{\"match_name\":\"Mumbai Indians vs Chennai Super Kings\"}', '::1', '2025-11-23 04:37:37'),
(229, 1, 'create', 'match', 46, '{\"teams\":[6,5]}', '::1', '2025-11-23 04:37:44'),
(230, 1, 'assign_players', 'match', 46, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 04:37:55'),
(231, 1, 'assign_players', 'match', 46, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 04:38:02'),
(232, 1, 'update', 'match_toss', 46, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 04:38:05'),
(233, 1, 'change_innings', 'match', 46, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-23 04:44:49'),
(234, 1, 'delete', 'match', 46, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-23 11:17:22'),
(235, 1, 'create', 'match', 47, '{\"teams\":[6,5]}', '::1', '2025-11-23 11:17:29'),
(236, 1, 'assign_players', 'match', 47, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-23 11:17:44'),
(237, 1, 'assign_players', 'match', 47, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-23 11:17:50'),
(238, 1, 'update', 'match_toss', 47, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-23 11:17:53'),
(239, 1, 'create', 'player', 67, '{\"name\":\"Sakthi\"}', '::1', '2025-11-25 16:22:58'),
(240, 1, 'change_innings', 'match', 47, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-25 16:56:12'),
(241, 1, 'delete', 'match', 47, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-25 17:09:48'),
(242, 1, 'create', 'match', 48, '{\"teams\":[6,5]}', '::1', '2025-11-25 17:12:24'),
(243, 1, 'assign_players', 'match', 48, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-25 17:12:33'),
(244, 1, 'assign_players', 'match', 48, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-25 17:12:39'),
(245, 1, 'update', 'match_toss', 48, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-25 17:12:41'),
(246, 1, 'change_innings', 'match', 48, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-25 17:14:56'),
(247, 1, 'create', 'match', 49, '{\"teams\":[6,5]}', '::1', '2025-11-26 00:09:36'),
(248, 1, 'assign_players', 'match', 49, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 00:09:52'),
(249, 1, 'assign_players', 'match', 49, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 00:09:58'),
(250, 1, 'update', 'match_toss', 49, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-26 00:10:13'),
(251, 1, 'change_innings', 'match', 49, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-26 00:21:15'),
(252, 1, 'update', 'match', 49, '{\"changes\":{\"team1_id\":6,\"team2_id\":5,\"series_id\":7,\"match_date\":\"2025-11-30T16:09\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"live\"}}', '::1', '2025-11-26 00:26:30'),
(253, 1, 'delete', 'match', 48, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-26 00:34:03'),
(254, 1, 'create', 'match', 50, '{\"teams\":[6,5]}', '::1', '2025-11-26 00:34:36'),
(255, 1, 'assign_players', 'match', 50, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 00:34:47'),
(256, 1, 'assign_players', 'match', 50, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 00:34:53'),
(257, 1, 'update', 'match_toss', 50, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-26 00:34:56'),
(258, 1, 'change_innings', 'match', 50, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-26 00:35:56'),
(259, 1, 'create', 'match', 51, '{\"teams\":[6,5]}', '::1', '2025-11-26 14:43:40'),
(260, 1, 'assign_players', 'match', 51, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 14:43:56'),
(261, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:44:11'),
(262, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:44:30'),
(263, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:44:52'),
(264, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:45:20'),
(265, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:45:34'),
(266, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 14:45:52'),
(267, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 16:01:43'),
(268, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 16:01:43'),
(269, 1, 'update', 'match', 51, '{\"changes\":{\"team1_id\":6,\"team2_id\":5,\"series_id\":7,\"match_date\":\"2025-11-27T17:45\",\"venue\":\"Bagalur\",\"overs_per_innings\":2,\"state\":\"draft\"}}', '::1', '2025-11-26 16:02:33'),
(270, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":4}', '::1', '2025-11-26 16:07:37'),
(271, 1, 'assign_players', 'match', 51, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 16:14:59'),
(272, 1, 'assign_players', 'match', 51, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 16:15:12'),
(273, 1, 'create', 'match', 52, '{\"teams\":[6,5]}', '::1', '2025-11-26 16:16:31'),
(274, 1, 'assign_players', 'match', 52, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 16:16:40'),
(275, 1, 'assign_players', 'match', 52, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 16:16:46'),
(276, 1, 'assign_players', 'match', 52, '{\"team_id\":5,\"player_count\":1}', '::1', '2025-11-26 16:17:15'),
(277, 1, 'update', 'match_toss', 51, '{\"toss_winner_id\":6,\"toss_decision\":\"bowl\"}', '::1', '2025-11-26 16:19:29'),
(278, 1, 'delete', 'match', 51, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-26 16:24:07'),
(279, 1, 'delete', 'match', 52, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-26 16:24:12'),
(280, 1, 'create', 'match', 53, '{\"teams\":[6,5]}', '::1', '2025-11-26 16:24:31'),
(281, 1, 'assign_players', 'match', 53, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 16:24:39'),
(282, 1, 'assign_players', 'match', 53, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 16:24:47'),
(283, 1, 'update', 'match_toss', 53, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-26 17:40:47'),
(284, 1, 'change_innings', 'match', 53, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-26 17:42:40'),
(285, 1, 'create', 'match', 54, '{\"teams\":[6,5]}', '::1', '2025-11-26 22:17:22'),
(286, 1, 'assign_players', 'match', 54, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 22:17:45'),
(287, 1, 'assign_players', 'match', 54, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-26 22:17:58'),
(288, 1, 'update', 'match_toss', 54, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-26 22:18:10'),
(289, 1, 'change_innings', 'match', 54, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-26 22:21:27'),
(290, 1, 'create', 'match', 55, '{\"teams\":[6,5]}', '::1', '2025-11-26 23:01:36'),
(291, 1, 'assign_players', 'match', 55, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-26 23:01:51'),
(292, 1, 'delete', 'match', 53, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-27 15:27:41'),
(293, 1, 'delete', 'match', 49, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-27 15:27:45'),
(294, 1, 'delete', 'match', 55, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-27 15:27:49'),
(295, 1, 'delete', 'match', 54, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-27 15:27:51'),
(296, 1, 'delete', 'match', 50, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-11-27 15:27:59'),
(297, 1, 'create', 'match', 56, '{\"teams\":[6,5]}', '::1', '2025-11-27 15:28:30'),
(298, 1, 'assign_players', 'match', 56, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-27 15:28:50'),
(299, 1, 'assign_players', 'match', 56, '{\"team_id\":5,\"player_count\":6}', '::1', '2025-11-27 15:29:05'),
(300, 1, 'update', 'match_toss', 56, '{\"toss_winner_id\":5,\"toss_decision\":\"bat\"}', '::1', '2025-11-27 15:29:17'),
(301, 1, 'change_innings', 'match', 56, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-27 15:35:52'),
(302, 1, 'create', 'match', 57, '{\"teams\":[6,5]}', '::1', '2025-11-27 18:20:39'),
(303, 1, 'create', 'match', 58, '{\"teams\":[6,5]}', '::1', '2025-11-27 22:41:24'),
(304, 1, 'assign_players', 'match', 58, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-27 22:41:37'),
(305, 1, 'assign_players', 'match', 58, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-27 22:41:47'),
(306, 1, 'update', 'match_toss', 58, '{\"toss_winner_id\":6,\"toss_decision\":\"bat\"}', '::1', '2025-11-27 22:41:51'),
(307, 1, 'create', 'match', 59, '{\"teams\":[6,5]}', '::1', '2025-11-28 22:41:10'),
(308, 1, 'assign_players', 'match', 59, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-11-28 22:41:30'),
(309, 1, 'assign_players', 'match', 59, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-11-28 22:41:52'),
(310, 1, 'update', 'match_toss', 59, '{\"toss_winner_id\":6,\"toss_decision\":\"bowl\"}', '::1', '2025-11-28 22:42:07'),
(311, 1, 'change_innings', 'match', 59, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-11-28 22:49:23'),
(312, 1, 'assign_players', 'match', 57, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-12-05 01:08:44'),
(313, 1, 'assign_players', 'match', 57, '{\"team_id\":6,\"player_count\":6}', '::1', '2025-12-05 01:08:53'),
(314, 1, 'create', 'match', 60, '{\"teams\":[6,5]}', '::1', '2025-12-05 01:09:06'),
(315, 1, 'assign_players', 'match', 60, '{\"team_id\":5,\"player_count\":5}', '::1', '2025-12-05 01:09:19'),
(316, 1, 'update', 'player', 46, '{\"changes\":{\"name\":\"AB de Villiers\",\"date_of_birth\":null,\"batting_hand\":\"Right\",\"bowling_style\":\"Left-arm Fast\",\"profile_image\":\"\"}}', '::1', '2025-12-05 02:09:34'),
(317, 1, 'delete', 'match', 56, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 12:20:46'),
(318, 1, 'delete', 'match', 58, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 12:20:54'),
(319, 1, 'delete', 'match', 57, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 12:21:00'),
(320, 1, 'delete', 'match', 59, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 12:21:10'),
(321, 1, 'delete', 'match', 60, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 12:21:19'),
(322, 1, 'create', 'match', 61, '{\"teams\":[6,5]}', '::1', '2025-12-05 12:22:07'),
(323, 1, 'create', 'match', 62, '{\"teams\":[9,8]}', '::1', '2025-12-05 13:42:46'),
(324, 1, 'create', 'match', 63, '{\"teams\":[6,5]}', '::1', '2025-12-05 14:11:18'),
(325, 1, 'delete', 'match', 63, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 17:39:43'),
(326, 1, 'delete', 'match', 62, '{\"match_name\":\"Chennai Super Kings vs Mumbai Indians\"}', '::1', '2025-12-05 17:40:53'),
(327, 1, 'delete', 'match', 61, '{\"match_name\":\"Alex vs Kavin S\"}', '::1', '2025-12-05 17:40:57'),
(328, 1, 'create', 'match', 64, '{\"teams\":[6,5]}', '::1', '2025-12-05 17:41:06'),
(329, 1, 'delete', 'player', 46, '{\"name\":\"AB de Villiers\"}', '::1', '2025-12-06 13:48:40'),
(330, 1, 'create', 'match', 70, '{\"teams\":[13,12]}', '::1', '2025-12-06 14:56:32'),
(331, 1, 'create', 'match', 71, '{\"teams\":[6,5]}', '::1', '2025-12-06 14:57:56'),
(332, 1, 'create', 'match', 72, '{\"teams\":[6,13]}', '::1', '2025-12-06 14:58:32'),
(333, 1, 'create', 'match', 73, '{\"teams\":[6,5]}', '::1', '2025-12-06 15:08:16'),
(334, 1, 'create', 'match', 74, '{\"teams\":[6,5]}', '::1', '2025-12-06 16:07:35'),
(335, 1, 'create', 'match', 75, '{\"teams\":[6,9]}', '::1', '2025-12-08 14:13:09'),
(336, 1, 'delete', 'match', 69, '{\"match_name\":\"South Africa vs India\"}', '::1', '2025-12-08 14:48:25'),
(337, 1, 'create', 'match', 76, '{\"teams\":[12,5]}', '::1', '2025-12-08 14:49:25'),
(338, 1, 'create', 'match', 85, '{\"teams\":[6,11]}', '::1', '2025-12-09 02:10:06'),
(339, 1, 'create', 'match', 87, '{\"teams\":[9,6]}', '::1', '2025-12-09 02:20:30'),
(340, 1, 'change_innings', 'match', 87, '{\"from_innings\":1,\"to_innings\":2,\"user_id\":1}', '::1', '2025-12-09 02:47:49'),
(341, 1, 'create', 'match', 90, '{\"teams\":[6,13]}', '::1', '2025-12-09 02:50:29'),
(342, 1, 'delete', 'player', 37, '{\"name\":\"Ambati Rayudu\"}', '::1', '2025-12-09 13:24:43'),
(343, 1, 'delete', 'player', 59, '{\"name\":\"Andre Russell\"}', '::1', '2025-12-09 13:24:52'),
(344, 1, 'delete', 'player', 65, '{\"name\":\"Lockie Ferguson\"}', '::1', '2025-12-09 13:39:32'),
(345, 1, 'delete', 'player', 54, '{\"name\":\"Dan Christian\"}', '::1', '2025-12-09 13:39:38'),
(346, 1, 'delete', 'player', 48, '{\"name\":\"Devdutt Padikkal\"}', '::1', '2025-12-09 13:39:43'),
(347, 1, 'delete', 'player', 44, '{\"name\":\"Imran Tahir\"}', '::1', '2025-12-09 13:39:48'),
(348, 1, 'delete', 'player', 27, '{\"name\":\"Kieron Pollard\"}', '::1', '2025-12-09 13:39:58'),
(349, 1, 'create', 'team', 1, '{\"name\":\"Alex\"}', '::1', '2025-12-09 14:49:00'),
(350, 1, 'create', 'team', 2, '{\"name\":\"Kavin\"}', '::1', '2025-12-09 14:49:10'),
(351, 1, 'create', 'match', 1, '{\"teams\":[1,2]}', '::1', '2025-12-09 14:49:31'),
(352, 1, 'delete', 'match', 1, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-17 00:48:23'),
(353, 1, 'create', 'match', 2, '{\"teams\":[1,2]}', '::1', '2025-12-17 00:48:38'),
(354, 1, 'create', 'match', 3, '{\"teams\":[1,2]}', '::1', '2025-12-17 11:18:15'),
(355, 1, 'delete', 'match', 3, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-19 23:40:22'),
(356, 1, 'delete', 'match', 2, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-19 23:40:27'),
(357, 1, 'create', 'match', 4, '{\"teams\":[1,2]}', '::1', '2025-12-19 23:46:31'),
(358, 1, 'delete', 'match', 4, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-19 23:57:24'),
(359, 1, 'create', 'match', 5, '{\"teams\":[1,2]}', '::1', '2025-12-19 23:57:41'),
(360, 1, 'create', 'match', 6, '{\"teams\":[1,2]}', '::1', '2025-12-19 23:57:54'),
(361, 1, 'delete', 'match', 5, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-20 11:21:21'),
(362, 1, 'delete', 'match', 6, '{\"match_name\":\"Alex vs Kavin\"}', '::1', '2025-12-20 11:21:31'),
(363, 1, 'create', 'match', 7, '{\"teams\":[1,2]}', '::1', '2025-12-20 11:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `batting_stats`
--

CREATE TABLE `batting_stats` (
  `batting_stat_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `runs` int(11) DEFAULT 0,
  `balls` int(11) DEFAULT 0,
  `fours` int(11) DEFAULT 0,
  `sixes` int(11) DEFAULT 0,
  `strike_rate` decimal(5,2) DEFAULT 0.00,
  `is_out` tinyint(1) DEFAULT 0,
  `how_out` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bowling_stats`
--

CREATE TABLE `bowling_stats` (
  `bowling_stat_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `overs` decimal(3,1) DEFAULT 0.0,
  `balls` int(11) DEFAULT 0,
  `runs` int(11) DEFAULT 0,
  `wickets` int(11) DEFAULT 0,
  `economy` decimal(4,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clone_links`
--

CREATE TABLE `clone_links` (
  `clone_id` int(11) NOT NULL,
  `source_match_id` int(11) NOT NULL,
  `target_match_id` int(11) NOT NULL,
  `options_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options_json`)),
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commentary`
--

CREATE TABLE `commentary` (
  `commentary_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `innings` tinyint(4) NOT NULL,
  `commentary_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_uuid` varchar(36) NOT NULL,
  `match_id` int(11) NOT NULL,
  `appearance_id` int(11) DEFAULT NULL,
  `client_id` varchar(100) DEFAULT NULL,
  `client_ts` datetime DEFAULT NULL,
  `client_base_seq` int(11) NOT NULL DEFAULT 0,
  `assigned_server_seq` int(11) NOT NULL,
  `ball_index` tinyint(4) NOT NULL DEFAULT 0,
  `payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload_json`)),
  `created_at` datetime NOT NULL,
  `processed_flag` tinyint(1) NOT NULL DEFAULT 0,
  `fielder_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_suspense`
--

CREATE TABLE `events_suspense` (
  `suspense_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fielding_stats`
--

CREATE TABLE `fielding_stats` (
  `fielding_stat_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `catches` int(11) DEFAULT 0,
  `run_outs` int(11) DEFAULT 0,
  `stumpings` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `job_type` varchar(50) NOT NULL,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `cursor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cursor`)),
  `last_heartbeat` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `series_id` int(11) DEFAULT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `match_date` datetime DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `overs_per_innings` decimal(4,1) NOT NULL DEFAULT 20.0,
  `state` enum('draft','scheduled','live','completed','abandoned','cancelled') NOT NULL DEFAULT 'draft',
  `toss_winner_id` int(11) DEFAULT NULL,
  `toss_decision` enum('bat','bowl') DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `current_innings` tinyint(1) DEFAULT NULL,
  `last_seq` int(11) NOT NULL DEFAULT 0,
  `auto_start_innings2` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `ball_type` varchar(20) DEFAULT 'leather',
  `pitch_type` varchar(20) DEFAULT 'turf',
  `umpire1_name` varchar(100) DEFAULT NULL,
  `umpire2_name` varchar(100) DEFAULT NULL,
  `scorer_name` varchar(100) DEFAULT NULL,
  `match_type` varchar(20) DEFAULT 'limited_overs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`match_id`, `series_id`, `team1_id`, `team2_id`, `match_date`, `venue`, `overs_per_innings`, `state`, `toss_winner_id`, `toss_decision`, `winner_id`, `current_innings`, `last_seq`, `auto_start_innings2`, `created_by`, `created_at`, `updated_at`, `ball_type`, `pitch_type`, `umpire1_name`, `umpire2_name`, `scorer_name`, `match_type`) VALUES
(7, 8, 1, 2, '0000-00-00 00:00:00', 'Bagalur', 2.0, 'live', 1, 'bowl', NULL, 1, 0, 0, 1, '2025-12-20 11:21:45', '2025-12-20 11:22:34', 'leather', 'turf', '', '', 'Kavin', 'limited_overs');

-- --------------------------------------------------------

--
-- Table structure for table `match_locks`
--

CREATE TABLE `match_locks` (
  `lock_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `lock_type` varchar(50) NOT NULL,
  `locked_by` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `batting_hand` enum('right','left') DEFAULT NULL,
  `bowling_style` enum('fast','fast-medium','medium','off-spin','leg-spin','left-arm-spin','left-arm-orthodox') DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`player_id`, `name`, `date_of_birth`, `profile_image`, `batting_hand`, `bowling_style`, `photo_url`, `created_at`, `updated_at`) VALUES
(4, 'Kavin S', NULL, '', 'right', '', NULL, '2025-11-14 22:01:27', '2025-11-14 22:01:27'),
(6, 'Deepak', NULL, '', 'right', '', NULL, '2025-11-14 22:01:54', '2025-11-14 22:01:54'),
(7, 'Roshik', NULL, '', 'right', '', NULL, '2025-11-14 22:02:10', '2025-11-14 22:02:10'),
(9, 'Edwin', NULL, '', 'right', '', NULL, '2025-11-16 00:05:00', '2025-11-16 00:05:00'),
(10, 'Alex Seeman', NULL, '', 'right', '', NULL, '2025-11-16 00:23:37', '2025-11-16 00:23:37'),
(11, 'Shephin', NULL, '', 'right', '', NULL, '2025-11-16 00:24:58', '2025-11-16 00:24:58'),
(12, 'Sathis', NULL, '', 'right', '', NULL, '2025-11-16 00:25:16', '2025-11-16 00:25:16'),
(13, 'Dhilipan', NULL, '', 'right', '', NULL, '2025-11-16 00:25:40', '2025-11-16 00:25:40'),
(14, 'Dilsen', NULL, '', 'left', '', NULL, '2025-11-16 00:26:09', '2025-11-16 00:26:09'),
(15, 'Jonath', NULL, '', 'right', '', NULL, '2025-11-16 00:26:40', '2025-11-16 00:26:40'),
(16, 'Jelikshan', NULL, '', 'right', '', NULL, '2025-11-16 00:27:38', '2025-11-16 00:27:38'),
(17, 'Adriell', NULL, '', '', '', NULL, '2025-11-16 00:27:59', '2025-11-23 02:52:50'),
(18, 'Gnanakumar J', NULL, '', 'right', '', NULL, '2025-11-16 00:28:37', '2025-11-16 00:28:37'),
(19, 'Benny', NULL, '', 'right', '', NULL, '2025-11-16 00:29:09', '2025-11-16 00:29:09'),
(20, 'Gabi', NULL, '', 'right', '', NULL, '2025-11-16 00:29:25', '2025-11-16 00:29:25'),
(21, 'Simbu', NULL, '', 'right', '', NULL, '2025-11-16 00:29:35', '2025-11-16 00:29:35'),
(22, 'Naveen', NULL, '', 'right', '', NULL, '2025-11-23 02:55:41', '2025-11-23 02:55:41'),
(24, 'Ishan Kishan', NULL, NULL, 'left', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(25, 'Suryakumar Yadav', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(26, 'Hardik Pandya', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(28, 'Jasprit Bumrah', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(29, 'Trent Boult', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(30, 'Rahul Chahar', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(31, 'Krunal Pandya', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(32, 'Quinton de Kock', NULL, NULL, 'left', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(33, 'Nathan Coulter-Nile', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(34, 'MS Dhoni', NULL, NULL, 'right', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(35, 'Faf du Plessis', NULL, NULL, 'right', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(36, 'Ruturaj Gaikwad', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(38, 'Ravindra Jadeja', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(39, 'Dwayne Bravo', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(40, 'Deepak Chahar', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(41, 'Shardul Thakur', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(42, 'Moeen Ali', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(43, 'Sam Curran', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(45, 'Virat Kohli', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(47, 'Glenn Maxwell', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(49, 'Yuzvendra Chahal', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(50, 'Mohammed Siraj', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(51, 'Kyle Jamieson', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(52, 'Washington Sundar', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(53, 'Harshal Patel', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(55, 'Shahbaz Ahmed', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(56, 'Eoin Morgan', NULL, NULL, 'left', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(57, 'Shubman Gill', NULL, NULL, 'right', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(58, 'Nitish Rana', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(60, 'Sunil Narine', NULL, NULL, 'left', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(61, 'Pat Cummins', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(62, 'Varun Chakravarthy', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(63, 'Rahul Tripathi', NULL, NULL, 'right', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(64, 'Dinesh Karthik', NULL, NULL, 'right', NULL, NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(66, 'Prasidh Krishna', NULL, NULL, 'right', '', NULL, '2025-11-23 04:11:49', '0000-00-00 00:00:00'),
(67, 'Sakthi', NULL, '', 'right', '', NULL, '2025-11-25 16:22:58', '2025-11-25 16:22:58');

-- --------------------------------------------------------

--
-- Table structure for table `player_appearances`
--

CREATE TABLE `player_appearances` (
  `appearance_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `is_guest` tinyint(1) DEFAULT 0 COMMENT 'Whether this player is a guest (can play for either team)',
  `is_captain` tinyint(1) DEFAULT 0,
  `role_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`role_tags`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `player_appearances`
--

INSERT INTO `player_appearances` (`appearance_id`, `player_id`, `match_id`, `team_id`, `is_guest`, `is_captain`, `role_tags`, `created_at`, `updated_at`) VALUES
(58, 17, 7, 1, 0, 0, '[\"WK\"]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(59, 10, 7, 1, 0, 0, '[]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(60, 19, 7, 1, 0, 1, '[]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(61, 6, 7, 1, 0, 0, '[]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(62, 40, 7, 1, 0, 0, '[]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(63, 13, 7, 1, 1, 0, '[]', '2025-12-20 11:22:00', '2025-12-20 11:22:00'),
(64, 14, 7, 2, 0, 0, '[]', '2025-12-20 11:22:17', '2025-12-20 11:22:17'),
(65, 64, 7, 2, 0, 1, '[]', '2025-12-20 11:22:17', '2025-12-20 11:22:17'),
(66, 39, 7, 2, 1, 0, '[]', '2025-12-20 11:22:17', '2025-12-20 11:22:17'),
(67, 9, 7, 2, 0, 0, '[\"WK\"]', '2025-12-20 11:22:17', '2025-12-20 11:22:17'),
(68, 56, 7, 2, 0, 0, '[]', '2025-12-20 11:22:17', '2025-12-20 11:22:17'),
(69, 35, 7, 2, 0, 0, '[]', '2025-12-20 11:22:17', '2025-12-20 11:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `player_edits`
--

CREATE TABLE `player_edits` (
  `edit_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `appearance_id` int(11) NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `reason` text NOT NULL,
  `admin_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `potm_decisions`
--

CREATE TABLE `potm_decisions` (
  `potm_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `computed_player_id` int(11) DEFAULT NULL,
  `final_player_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pots_aggregate`
--

CREATE TABLE `pots_aggregate` (
  `pots_id` int(11) NOT NULL,
  `series_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `consistency_bonus` int(11) NOT NULL DEFAULT 0,
  `rank` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `rate_limit_id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT 1,
  `window_start` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`rate_limit_id`, `identifier`, `endpoint`, `count`, `window_start`, `expires_at`) VALUES
(1, '::1:/matches:minute:1763028960', '', 1, '2025-11-13 15:46:50', '2025-11-13 15:47:50'),
(2, '::1:/matches:hour:1763028000', '', 1, '2025-11-13 15:46:50', '2025-11-13 16:46:50'),
(3, '1:/events:minute:1763031540', '', 2, '2025-11-13 16:29:12', '2025-11-13 16:30:12'),
(4, '1:/events:hour:1763028000', '', 2, '2025-11-13 16:29:12', '2025-11-13 17:29:12'),
(7, '1:/events:minute:1763031660', '', 5, '2025-11-13 16:31:53', '2025-11-13 16:32:53'),
(8, '1:/events:hour:1763031600', '', 50, '2025-11-13 16:31:53', '2025-11-13 17:31:53'),
(17, '1:/events:minute:1763031720', '', 20, '2025-11-13 16:32:02', '2025-11-13 16:33:02'),
(57, '1:/events:minute:1763032020', '', 7, '2025-11-13 16:37:09', '2025-11-13 16:38:09'),
(71, '1:/events:minute:1763032800', '', 5, '2025-11-13 16:50:42', '2025-11-13 16:51:42'),
(81, '1:/events:minute:1763032860', '', 8, '2025-11-13 16:51:04', '2025-11-13 16:52:04'),
(97, '1:/events:minute:1763033340', '', 5, '2025-11-13 16:59:33', '2025-11-13 17:00:33'),
(107, '127.0.0.1:/matches:minute:1763042040', '', 8, '2025-11-13 19:24:10', '2025-11-13 19:25:10'),
(108, '127.0.0.1:/matches:hour:1763038800', '', 19, '2025-11-13 19:24:10', '2025-11-13 20:24:10'),
(109, '127.0.0.1:/players:minute:1763042040', '', 4, '2025-11-13 19:24:10', '2025-11-13 19:25:10'),
(110, '127.0.0.1:/players:hour:1763038800', '', 12, '2025-11-13 19:24:10', '2025-11-13 20:24:10'),
(131, '127.0.0.1:/matches:minute:1763042100', '', 2, '2025-11-13 19:25:02', '2025-11-13 19:26:02'),
(133, '127.0.0.1:/players:minute:1763042100', '', 1, '2025-11-13 19:25:03', '2025-11-13 19:26:03'),
(137, '127.0.0.1:/matches:minute:1763042160', '', 4, '2025-11-13 19:26:13', '2025-11-13 19:27:13'),
(139, '127.0.0.1:/players:minute:1763042160', '', 2, '2025-11-13 19:26:13', '2025-11-13 19:27:13'),
(149, '127.0.0.1:/matches:minute:1763042220', '', 2, '2025-11-13 19:27:23', '2025-11-13 19:28:23'),
(151, '127.0.0.1:/players:minute:1763042220', '', 2, '2025-11-13 19:27:23', '2025-11-13 19:28:23'),
(157, '127.0.0.1:/matches:minute:1763042340', '', 3, '2025-11-13 19:29:16', '2025-11-13 19:30:16'),
(159, '127.0.0.1:/players:minute:1763042340', '', 3, '2025-11-13 19:29:17', '2025-11-13 19:30:17'),
(169, '127.0.0.1:/matches:minute:1763042400', '', 2, '2025-11-13 19:30:27', '2025-11-13 19:31:27'),
(170, '127.0.0.1:/matches:hour:1763042400', '', 14, '2025-11-13 19:30:27', '2025-11-13 20:30:27'),
(171, '127.0.0.1:/players:minute:1763042400', '', 2, '2025-11-13 19:30:27', '2025-11-13 19:31:27'),
(172, '127.0.0.1:/players:hour:1763042400', '', 14, '2025-11-13 19:30:27', '2025-11-13 20:30:27'),
(177, '127.0.0.1:/matches:minute:1763042460', '', 1, '2025-11-13 19:31:22', '2025-11-13 19:32:22'),
(179, '127.0.0.1:/players:minute:1763042460', '', 1, '2025-11-13 19:31:23', '2025-11-13 19:32:23'),
(181, '127.0.0.1:/matches:minute:1763042520', '', 2, '2025-11-13 19:32:45', '2025-11-13 19:33:45'),
(183, '127.0.0.1:/players:minute:1763042520', '', 2, '2025-11-13 19:32:45', '2025-11-13 19:33:45'),
(189, '127.0.0.1:/matches:minute:1763042580', '', 3, '2025-11-13 19:33:02', '2025-11-13 19:34:02'),
(191, '127.0.0.1:/players:minute:1763042580', '', 3, '2025-11-13 19:33:02', '2025-11-13 19:34:02'),
(201, '127.0.0.1:/matches:minute:1763042640', '', 2, '2025-11-13 19:34:14', '2025-11-13 19:35:14'),
(203, '127.0.0.1:/players:minute:1763042640', '', 2, '2025-11-13 19:34:15', '2025-11-13 19:35:15'),
(209, '127.0.0.1:/matches:minute:1763042700', '', 1, '2025-11-13 19:35:08', '2025-11-13 19:36:08'),
(211, '127.0.0.1:/players:minute:1763042700', '', 1, '2025-11-13 19:35:09', '2025-11-13 19:36:09'),
(213, '127.0.0.1:/matches:minute:1763042760', '', 1, '2025-11-13 19:36:13', '2025-11-13 19:37:13'),
(215, '127.0.0.1:/players:minute:1763042760', '', 1, '2025-11-13 19:36:13', '2025-11-13 19:37:13'),
(217, '127.0.0.1:/matches:minute:1763043120', '', 1, '2025-11-13 19:42:47', '2025-11-13 19:43:47'),
(219, '127.0.0.1:/players:minute:1763043120', '', 1, '2025-11-13 19:42:47', '2025-11-13 19:43:47'),
(221, '127.0.0.1:/matches:minute:1763043960', '', 1, '2025-11-13 19:56:51', '2025-11-13 19:57:51'),
(223, '127.0.0.1:/players:minute:1763043960', '', 1, '2025-11-13 19:56:52', '2025-11-13 19:57:52'),
(225, '1:/events:minute:1763044020', '', 3, '2025-11-13 19:57:50', '2025-11-13 19:58:50'),
(226, '1:/events:hour:1763042400', '', 3, '2025-11-13 19:57:50', '2025-11-13 20:57:50'),
(231, '127.0.0.1:/matches:minute:1763050980', '', 2, '2025-11-13 21:53:34', '2025-11-13 21:54:34'),
(232, '127.0.0.1:/matches:hour:1763049600', '', 2, '2025-11-13 21:53:34', '2025-11-13 22:53:34'),
(233, '127.0.0.1:/players:minute:1763050980', '', 2, '2025-11-13 21:53:35', '2025-11-13 21:54:35'),
(234, '127.0.0.1:/players:hour:1763049600', '', 2, '2025-11-13 21:53:35', '2025-11-13 22:53:35'),
(239, '1:/events:minute:1763103420', '', 11, '2025-11-14 12:27:27', '2025-11-14 12:28:27'),
(240, '1:/events:hour:1763100000', '', 12, '2025-11-14 12:27:27', '2025-11-14 13:27:27'),
(261, '1:/events:minute:1763103480', '', 1, '2025-11-14 12:28:16', '2025-11-14 12:29:16'),
(263, '1:/events:minute:1763120880', '', 6, '2025-11-14 17:18:39', '2025-11-14 17:19:39'),
(264, '1:/events:hour:1763118000', '', 36, '2025-11-14 17:18:39', '2025-11-14 18:18:39'),
(275, '1:/events:minute:1763120940', '', 6, '2025-11-14 17:19:19', '2025-11-14 17:20:19'),
(287, '1:/events:minute:1763121120', '', 7, '2025-11-14 17:22:33', '2025-11-14 17:23:33'),
(301, '1:/events:minute:1763121180', '', 5, '2025-11-14 17:23:00', '2025-11-14 17:24:00'),
(311, '1:/events:minute:1763121420', '', 12, '2025-11-14 17:27:02', '2025-11-14 17:28:02'),
(335, '1:/events:minute:1763121660', '', 9, '2025-11-14 17:31:34', '2025-11-14 17:32:34'),
(336, '1:/events:hour:1763121600', '', 67, '2025-11-14 17:31:34', '2025-11-14 18:31:34'),
(353, '1:/events:minute:1763121720', '', 7, '2025-11-14 17:32:00', '2025-11-14 17:33:00'),
(367, '1:/events:minute:1763121900', '', 13, '2025-11-14 17:35:11', '2025-11-14 17:36:11'),
(393, '1:/events:minute:1763122500', '', 4, '2025-11-14 17:45:54', '2025-11-14 17:46:54'),
(401, '1:/events:minute:1763122560', '', 8, '2025-11-14 17:46:04', '2025-11-14 17:47:04'),
(417, '1:/events:minute:1763123280', '', 19, '2025-11-14 17:58:42', '2025-11-14 17:59:42'),
(455, '1:/events:minute:1763123340', '', 7, '2025-11-14 17:59:02', '2025-11-14 18:00:02'),
(469, '1:/events:minute:1763139540', '', 2, '2025-11-14 22:29:33', '2025-11-14 22:30:33'),
(470, '1:/events:hour:1763136000', '', 2, '2025-11-14 22:29:33', '2025-11-14 23:29:33'),
(473, '1:/events:minute:1763139780', '', 6, '2025-11-14 22:33:33', '2025-11-14 22:34:33'),
(474, '1:/events:hour:1763139600', '', 30, '2025-11-14 22:33:33', '2025-11-14 23:33:33'),
(485, '1:/events:minute:1763139840', '', 6, '2025-11-14 22:34:09', '2025-11-14 22:35:09'),
(497, '1:/events:minute:1763140200', '', 1, '2025-11-14 22:40:07', '2025-11-14 22:41:07'),
(499, '1:/events:minute:1763140740', '', 5, '2025-11-14 22:49:37', '2025-11-14 22:50:37'),
(509, '1:/events:minute:1763142240', '', 8, '2025-11-14 23:14:37', '2025-11-14 23:15:37'),
(525, '1:/events:minute:1763142300', '', 4, '2025-11-14 23:15:01', '2025-11-14 23:16:01'),
(533, '1:/events:minute:1763145180', '', 12, '2025-11-15 00:03:31', '2025-11-15 00:04:31'),
(534, '1:/events:hour:1763143200', '', 12, '2025-11-15 00:03:31', '2025-11-15 01:03:31'),
(557, '1:/events:minute:1763224140', '', 8, '2025-11-15 21:59:12', '2025-11-15 22:00:12'),
(558, '1:/events:hour:1763222400', '', 53, '2025-11-15 21:59:12', '2025-11-15 22:59:12'),
(573, '1:/events:minute:1763224200', '', 6, '2025-11-15 22:00:11', '2025-11-15 22:01:11'),
(585, '1:/events:minute:1763224500', '', 7, '2025-11-15 22:05:27', '2025-11-15 22:06:27'),
(599, '1:/events:minute:1763224560', '', 6, '2025-11-15 22:06:14', '2025-11-15 22:07:14'),
(611, '1:/events:minute:1763225280', '', 11, '2025-11-15 22:18:23', '2025-11-15 22:19:23'),
(633, '1:/events:minute:1763225340', '', 14, '2025-11-15 22:19:01', '2025-11-15 22:20:01'),
(661, '1:/events:minute:1763225400', '', 1, '2025-11-15 22:20:37', '2025-11-15 22:21:37'),
(663, '1:/events:minute:1763233260', '', 6, '2025-11-16 00:31:47', '2025-11-16 00:32:47'),
(664, '1:/events:hour:1763233200', '', 53, '2025-11-16 00:31:47', '2025-11-16 01:31:47'),
(675, '1:/events:minute:1763233320', '', 4, '2025-11-16 00:32:14', '2025-11-16 00:33:14'),
(683, '1:/events:minute:1763233380', '', 9, '2025-11-16 00:33:07', '2025-11-16 00:34:07'),
(701, '1:/events:minute:1763233440', '', 4, '2025-11-16 00:34:02', '2025-11-16 00:35:02'),
(709, '1:/events:minute:1763233500', '', 4, '2025-11-16 00:35:05', '2025-11-16 00:36:05'),
(717, '1:/events:minute:1763234520', '', 6, '2025-11-16 00:52:31', '2025-11-16 00:53:31'),
(729, '1:/events:minute:1763234580', '', 8, '2025-11-16 00:53:08', '2025-11-16 00:54:08'),
(745, '1:/events:minute:1763234640', '', 2, '2025-11-16 00:54:00', '2025-11-16 00:55:00'),
(749, '1:/events:minute:1763236740', '', 10, '2025-11-16 01:29:34', '2025-11-16 01:30:34'),
(769, '1:/events:minute:1763236800', '', 2, '2025-11-16 01:30:04', '2025-11-16 01:31:04'),
(770, '1:/events:hour:1763236800', '', 15, '2025-11-16 01:30:04', '2025-11-16 02:30:04'),
(773, '1:/events:minute:1763236860', '', 13, '2025-11-16 01:31:18', '2025-11-16 01:32:18'),
(799, '1:/events:minute:1763585640', '', 7, '2025-11-20 02:24:46', '2025-11-20 02:25:46'),
(800, '1:/events:hour:1763582400', '', 15, '2025-11-20 02:24:46', '2025-11-20 03:24:46'),
(813, '1:/events:minute:1763585700', '', 8, '2025-11-20 02:25:02', '2025-11-20 02:26:02'),
(829, '1:/events:minute:1763587560', '', 2, '2025-11-20 02:56:55', '2025-11-20 02:57:55'),
(830, '1:/events:hour:1763586000', '', 2, '2025-11-20 02:56:55', '2025-11-20 03:56:55'),
(833, '1:/events:minute:1763662500', '', 19, '2025-11-20 23:45:00', '2025-11-20 23:46:00'),
(834, '1:/events:hour:1763661600', '', 24, '2025-11-20 23:45:00', '2025-11-21 00:45:00'),
(871, '1:/events:minute:1763662560', '', 5, '2025-11-20 23:46:03', '2025-11-20 23:47:03'),
(881, '1:/events:minute:1763720400', '', 5, '2025-11-21 15:50:33', '2025-11-21 15:51:33'),
(882, '1:/events:hour:1763719200', '', 10, '2025-11-21 15:50:33', '2025-11-21 16:50:33'),
(891, '1:/events:minute:1763720460', '', 2, '2025-11-21 15:51:48', '2025-11-21 15:52:48'),
(895, '1:/events:minute:1763720520', '', 2, '2025-11-21 15:52:11', '2025-11-21 15:53:11'),
(899, '1:/events:minute:1763720700', '', 1, '2025-11-21 15:55:14', '2025-11-21 15:56:14'),
(901, '2:/events:minute:1763746320', '', 12, '2025-11-21 23:02:20', '2025-11-21 23:03:20'),
(902, '2:/events:hour:1763744400', '', 77, '2025-11-21 23:02:20', '2025-11-22 00:02:20'),
(925, '2:/events:minute:1763746380', '', 6, '2025-11-21 23:03:05', '2025-11-21 23:04:05'),
(937, '2:/events:minute:1763746440', '', 10, '2025-11-21 23:04:01', '2025-11-21 23:05:01'),
(957, '2:/events:minute:1763746560', '', 6, '2025-11-21 23:06:11', '2025-11-21 23:07:11'),
(969, '2:/events:minute:1763746620', '', 18, '2025-11-21 23:07:00', '2025-11-21 23:08:00'),
(1005, '2:/events:minute:1763746800', '', 20, '2025-11-21 23:10:06', '2025-11-21 23:11:06'),
(1045, '2:/events:minute:1763746860', '', 5, '2025-11-21 23:11:04', '2025-11-21 23:12:04'),
(1055, '2:/events:minute:1763748060', '', 7, '2025-11-21 23:31:29', '2025-11-21 23:32:29'),
(1056, '2:/events:hour:1763748000', '', 14, '2025-11-21 23:31:29', '2025-11-22 00:31:29'),
(1069, '2:/events:minute:1763750520', '', 7, '2025-11-22 00:12:03', '2025-11-22 00:13:03'),
(1083, '1:/events:minute:1763752500', '', 12, '2025-11-22 00:45:15', '2025-11-22 00:46:15'),
(1084, '1:/events:hour:1763751600', '', 25, '2025-11-22 00:45:15', '2025-11-22 01:45:15'),
(1107, '1:/events:minute:1763752560', '', 13, '2025-11-22 00:46:04', '2025-11-22 00:47:04'),
(1133, '1:/events:minute:1763799720', '', 1, '2025-11-22 13:52:31', '2025-11-22 13:53:31'),
(1134, '1:/events:hour:1763798400', '', 14, '2025-11-22 13:52:31', '2025-11-22 14:52:31'),
(1135, '1:/events:minute:1763799780', '', 7, '2025-11-22 13:53:16', '2025-11-22 13:54:16'),
(1149, '1:/events:minute:1763799840', '', 6, '2025-11-22 13:54:24', '2025-11-22 13:55:24'),
(1161, '1:/events:minute:1763809740', '', 3, '2025-11-22 16:39:55', '2025-11-22 16:40:55'),
(1162, '1:/events:hour:1763809200', '', 9, '2025-11-22 16:39:56', '2025-11-22 17:39:56'),
(1167, '1:/events:minute:1763809800', '', 6, '2025-11-22 16:40:00', '2025-11-22 16:41:00'),
(1179, '1:/events:minute:1763813400', '', 3, '2025-11-22 17:40:26', '2025-11-22 17:41:26'),
(1180, '1:/events:hour:1763812800', '', 3, '2025-11-22 17:40:26', '2025-11-22 18:40:26'),
(1185, '1:/events:minute:1763835540', '', 6, '2025-11-22 23:49:53', '2025-11-22 23:50:53'),
(1186, '1:/events:hour:1763834400', '', 51, '2025-11-22 23:49:53', '2025-11-23 00:49:53'),
(1197, '1:/events:minute:1763835600', '', 6, '2025-11-22 23:50:21', '2025-11-22 23:51:21'),
(1209, '1:/events:minute:1763836080', '', 4, '2025-11-22 23:58:52', '2025-11-22 23:59:52'),
(1217, '1:/events:minute:1763836140', '', 1, '2025-11-22 23:59:00', '2025-11-23 00:00:00'),
(1219, '1:/events:minute:1763836680', '', 4, '2025-11-23 00:08:29', '2025-11-23 00:09:29'),
(1227, '1:/events:minute:1763836740', '', 1, '2025-11-23 00:09:25', '2025-11-23 00:10:25'),
(1229, '1:/events:minute:1763837100', '', 2, '2025-11-23 00:15:35', '2025-11-23 00:16:35'),
(1233, '1:/events:minute:1763837220', '', 8, '2025-11-23 00:17:26', '2025-11-23 00:18:26'),
(1249, '1:/events:minute:1763837280', '', 11, '2025-11-23 00:18:35', '2025-11-23 00:19:35'),
(1271, '1:/events:minute:1763837340', '', 1, '2025-11-23 00:19:58', '2025-11-23 00:20:58'),
(1273, '1:/events:minute:1763837400', '', 3, '2025-11-23 00:20:02', '2025-11-23 00:21:02'),
(1279, '1:/events:minute:1763837520', '', 4, '2025-11-23 00:22:22', '2025-11-23 00:23:22'),
(1287, '1:/events:minute:1763838060', '', 2, '2025-11-23 00:31:01', '2025-11-23 00:32:01'),
(1288, '1:/events:hour:1763838000', '', 28, '2025-11-23 00:31:01', '2025-11-23 01:31:01'),
(1291, '1:/events:minute:1763838240', '', 4, '2025-11-23 00:34:05', '2025-11-23 00:35:05'),
(1299, '1:/events:minute:1763838360', '', 3, '2025-11-23 00:36:18', '2025-11-23 00:37:18'),
(1305, '1:/events:minute:1763838420', '', 2, '2025-11-23 00:37:03', '2025-11-23 00:38:03'),
(1309, '1:/events:minute:1763839080', '', 2, '2025-11-23 00:48:01', '2025-11-23 00:49:01'),
(1313, '1:/events:minute:1763840400', '', 3, '2025-11-23 01:10:10', '2025-11-23 01:11:10'),
(1319, '1:/events:minute:1763840700', '', 1, '2025-11-23 01:15:58', '2025-11-23 01:16:58'),
(1321, '1:/events:minute:1763840760', '', 5, '2025-11-23 01:16:03', '2025-11-23 01:17:03'),
(1331, '1:/events:minute:1763841300', '', 2, '2025-11-23 01:25:29', '2025-11-23 01:26:29'),
(1335, '1:/events:minute:1763841420', '', 2, '2025-11-23 01:27:19', '2025-11-23 01:28:19'),
(1339, '1:/events:minute:1763841480', '', 2, '2025-11-23 01:28:42', '2025-11-23 01:29:42'),
(1343, '1:/events:minute:1763841660', '', 5, '2025-11-23 01:31:14', '2025-11-23 01:32:14'),
(1344, '1:/events:hour:1763841600', '', 36, '2025-11-23 01:31:14', '2025-11-23 02:31:14'),
(1353, '1:/events:minute:1763841720', '', 2, '2025-11-23 01:32:06', '2025-11-23 01:33:06'),
(1357, '1:/events:minute:1763841780', '', 7, '2025-11-23 01:33:06', '2025-11-23 01:34:06'),
(1371, '1:/events:minute:1763841840', '', 1, '2025-11-23 01:34:19', '2025-11-23 01:35:19'),
(1373, '1:/events:minute:1763842140', '', 1, '2025-11-23 01:39:59', '2025-11-23 01:40:59'),
(1375, '1:/events:minute:1763842200', '', 5, '2025-11-23 01:40:06', '2025-11-23 01:41:06'),
(1385, '1:/events:minute:1763842680', '', 3, '2025-11-23 01:48:39', '2025-11-23 01:49:39'),
(1391, '1:/events:minute:1763842740', '', 3, '2025-11-23 01:49:11', '2025-11-23 01:50:11'),
(1397, '1:/events:minute:1763843040', '', 7, '2025-11-23 01:54:14', '2025-11-23 01:55:14'),
(1411, '1:/events:minute:1763843100', '', 2, '2025-11-23 01:55:33', '2025-11-23 01:56:33'),
(1415, '1:/events:minute:1763847180', '', 3, '2025-11-23 03:03:43', '2025-11-23 03:04:43'),
(1416, '1:/events:hour:1763845200', '', 18, '2025-11-23 03:03:43', '2025-11-23 04:03:43'),
(1421, '1:/events:minute:1763847240', '', 4, '2025-11-23 03:04:03', '2025-11-23 03:05:03'),
(1429, '1:/events:minute:1763847360', '', 1, '2025-11-23 03:06:22', '2025-11-23 03:07:22'),
(1431, '1:/events:minute:1763847900', '', 6, '2025-11-23 03:15:31', '2025-11-23 03:16:31'),
(1443, '1:/events:minute:1763847960', '', 1, '2025-11-23 03:16:42', '2025-11-23 03:17:42'),
(1445, '1:/events:minute:1763848020', '', 3, '2025-11-23 03:17:07', '2025-11-23 03:18:07'),
(1451, '1:/events:minute:1763850960', '', 6, '2025-11-23 04:06:36', '2025-11-23 04:07:36'),
(1452, '1:/events:hour:1763848800', '', 32, '2025-11-23 04:06:36', '2025-11-23 05:06:36'),
(1463, '1:/events:minute:1763851020', '', 1, '2025-11-23 04:07:14', '2025-11-23 04:08:14'),
(1465, '1:/events:minute:1763851080', '', 7, '2025-11-23 04:08:10', '2025-11-23 04:09:10'),
(1479, '1:/events:minute:1763851140', '', 6, '2025-11-23 04:09:45', '2025-11-23 04:10:45'),
(1491, '1:/events:minute:1763851380', '', 7, '2025-11-23 04:13:22', '2025-11-23 04:14:22'),
(1505, '1:/events:minute:1763851440', '', 5, '2025-11-23 04:14:04', '2025-11-23 04:15:04'),
(1515, '1:/events:minute:1763852880', '', 1, '2025-11-23 04:38:21', '2025-11-23 04:39:21'),
(1516, '1:/events:hour:1763852400', '', 25, '2025-11-23 04:38:21', '2025-11-23 05:38:21'),
(1517, '1:/events:minute:1763853240', '', 12, '2025-11-23 04:44:13', '2025-11-23 04:45:13'),
(1541, '1:/events:minute:1763853300', '', 12, '2025-11-23 04:45:04', '2025-11-23 04:46:04'),
(1565, '1:/events:minute:1764069360', '', 5, '2025-11-25 16:46:21', '2025-11-25 16:47:21'),
(1566, '1:/events:hour:1764068400', '', 49, '2025-11-25 16:46:21', '2025-11-25 17:46:21'),
(1575, '1:/events:minute:1764069420', '', 10, '2025-11-25 16:47:02', '2025-11-25 16:48:02'),
(1595, '1:/events:minute:1764069480', '', 2, '2025-11-25 16:48:08', '2025-11-25 16:49:08'),
(1599, '1:/events:minute:1764069960', '', 2, '2025-11-25 16:56:34', '2025-11-25 16:57:34'),
(1603, '1:/events:minute:1764070020', '', 5, '2025-11-25 16:57:07', '2025-11-25 16:58:07'),
(1613, '1:/events:minute:1764070920', '', 1, '2025-11-25 17:12:58', '2025-11-25 17:13:58'),
(1615, '1:/events:minute:1764070980', '', 8, '2025-11-25 17:13:00', '2025-11-25 17:14:00'),
(1631, '1:/events:minute:1764071040', '', 5, '2025-11-25 17:14:26', '2025-11-25 17:15:26'),
(1641, '1:/events:minute:1764071100', '', 3, '2025-11-25 17:15:21', '2025-11-25 17:16:21'),
(1647, '1:/events:minute:1764071160', '', 5, '2025-11-25 17:16:04', '2025-11-25 17:17:04'),
(1657, '1:/events:minute:1764071220', '', 3, '2025-11-25 17:17:02', '2025-11-25 17:18:02'),
(1663, '1:/events:minute:1764072480', '', 1, '2025-11-25 17:38:47', '2025-11-25 17:39:47'),
(1664, '1:/events:hour:1764072000', '', 3, '2025-11-25 17:38:48', '2025-11-25 18:38:48'),
(1665, '1:/events:minute:1764072660', '', 2, '2025-11-25 17:41:05', '2025-11-25 17:42:05'),
(1669, '1:/events:minute:1764096300', '', 1, '2025-11-26 00:15:39', '2025-11-26 00:16:39'),
(1670, '1:/events:hour:1764093600', '', 18, '2025-11-26 00:15:39', '2025-11-26 01:15:39'),
(1671, '1:/events:minute:1764096360', '', 9, '2025-11-26 00:16:02', '2025-11-26 00:17:02'),
(1689, '1:/events:minute:1764096420', '', 2, '2025-11-26 00:17:05', '2025-11-26 00:18:05'),
(1693, '1:/events:minute:1764096480', '', 1, '2025-11-26 00:18:16', '2025-11-26 00:19:16'),
(1695, '1:/events:minute:1764096660', '', 1, '2025-11-26 00:21:34', '2025-11-26 00:22:34'),
(1697, '1:/events:minute:1764096780', '', 1, '2025-11-26 00:23:37', '2025-11-26 00:24:37'),
(1699, '1:/events:minute:1764096840', '', 1, '2025-11-26 00:24:58', '2025-11-26 00:25:58'),
(1701, '1:/events:minute:1764096900', '', 2, '2025-11-26 00:25:08', '2025-11-26 00:26:08'),
(1705, '1:/events:minute:1764097500', '', 12, '2025-11-26 00:35:13', '2025-11-26 00:36:13'),
(1706, '1:/events:hour:1764097200', '', 21, '2025-11-26 00:35:13', '2025-11-26 01:35:13'),
(1729, '1:/events:minute:1764097620', '', 6, '2025-11-26 00:37:05', '2025-11-26 00:38:05'),
(1741, '1:/events:minute:1764097680', '', 3, '2025-11-26 00:38:15', '2025-11-26 00:39:15'),
(1747, '1:/events:minute:1764159060', '', 9, '2025-11-26 17:41:10', '2025-11-26 17:42:10'),
(1748, '1:/events:hour:1764158400', '', 27, '2025-11-26 17:41:11', '2025-11-26 18:41:11'),
(1765, '1:/events:minute:1764159120', '', 6, '2025-11-26 17:42:06', '2025-11-26 17:43:06'),
(1777, '1:/events:minute:1764159180', '', 6, '2025-11-26 17:43:01', '2025-11-26 17:44:01'),
(1789, '1:/events:minute:1764159240', '', 1, '2025-11-26 17:44:37', '2025-11-26 17:45:37'),
(1791, '1:/events:minute:1764159300', '', 3, '2025-11-26 17:45:06', '2025-11-26 17:46:06'),
(1797, '1:/events:minute:1764159360', '', 2, '2025-11-26 17:46:17', '2025-11-26 17:47:17'),
(1801, '1:/events:minute:1764172980', '', 1, '2025-11-26 21:33:53', '2025-11-26 21:34:53'),
(1802, '1:/events:hour:1764172800', '', 16, '2025-11-26 21:33:53', '2025-11-26 22:33:53'),
(1803, '1:/events:minute:1764174960', '', 1, '2025-11-26 22:06:24', '2025-11-26 22:07:24'),
(1805, '1:/events:minute:1764175020', '', 1, '2025-11-26 22:07:45', '2025-11-26 22:08:45'),
(1807, '1:/events:minute:1764175680', '', 5, '2025-11-26 22:18:52', '2025-11-26 22:19:52'),
(1817, '1:/events:minute:1764175740', '', 6, '2025-11-26 22:19:20', '2025-11-26 22:20:20'),
(1829, '1:/events:minute:1764175800', '', 2, '2025-11-26 22:20:45', '2025-11-26 22:21:45'),
(1833, '1:/events:minute:1764179040', '', 2, '2025-11-26 23:14:47', '2025-11-26 23:15:47'),
(1834, '1:/events:hour:1764176400', '', 2, '2025-11-26 23:14:47', '2025-11-27 00:14:47'),
(1837, '1:/events:minute:1764237300', '', 3, '2025-11-27 15:25:33', '2025-11-27 15:26:33'),
(1838, '1:/events:hour:1764234000', '', 8, '2025-11-27 15:25:33', '2025-11-27 16:25:33'),
(1843, '1:/events:minute:1764237360', '', 2, '2025-11-27 15:26:46', '2025-11-27 15:27:46'),
(1847, '1:/events:minute:1764237540', '', 3, '2025-11-27 15:29:31', '2025-11-27 15:30:31'),
(1853, '1:/events:minute:1764237600', '', 2, '2025-11-27 15:30:20', '2025-11-27 15:31:20'),
(1854, '1:/events:hour:1764237600', '', 2, '2025-11-27 15:30:20', '2025-11-27 16:30:20'),
(1857, '1:/events:minute:1764349560', '', 1, '2025-11-28 22:36:43', '2025-11-28 22:37:43'),
(1858, '1:/events:hour:1764349200', '', 30, '2025-11-28 22:36:43', '2025-11-28 23:36:43'),
(1859, '1:/events:minute:1764349740', '', 1, '2025-11-28 22:39:40', '2025-11-28 22:40:40'),
(1861, '1:/events:minute:1764349800', '', 1, '2025-11-28 22:40:19', '2025-11-28 22:41:19'),
(1863, '1:/events:minute:1764349920', '', 6, '2025-11-28 22:42:22', '2025-11-28 22:43:22'),
(1875, '1:/events:minute:1764349980', '', 3, '2025-11-28 22:43:10', '2025-11-28 22:44:10'),
(1881, '1:/events:minute:1764350220', '', 4, '2025-11-28 22:47:21', '2025-11-28 22:48:21'),
(1889, '1:/events:minute:1764350340', '', 5, '2025-11-28 22:49:35', '2025-11-28 22:50:35'),
(1899, '1:/events:minute:1764350520', '', 1, '2025-11-28 22:52:07', '2025-11-28 22:53:07'),
(1901, '1:/events:minute:1764350580', '', 8, '2025-11-28 22:53:08', '2025-11-28 22:54:08'),
(1917, '1:/events:minute:1765228320', '', 2, '2025-12-09 02:42:06', '2025-12-09 02:43:06'),
(1918, '1:/events:hour:1765227600', '', 6, '2025-12-09 02:42:06', '2025-12-09 03:42:06'),
(1921, '1:/events:minute:1765228440', '', 1, '2025-12-09 02:44:34', '2025-12-09 02:45:34'),
(1923, '1:/events:minute:1765228620', '', 1, '2025-12-09 02:47:51', '2025-12-09 02:48:51'),
(1925, '1:/events:minute:1765228740', '', 1, '2025-12-09 02:49:44', '2025-12-09 02:50:44'),
(1927, '1:/events:minute:1765228860', '', 1, '2025-12-09 02:51:10', '2025-12-09 02:52:10'),
(1929, '1:/events:minute:1765265580', '', 2, '2025-12-09 13:03:33', '2025-12-09 13:04:33'),
(1930, '1:/events:hour:1765263600', '', 17, '2025-12-09 13:03:33', '2025-12-09 14:03:33'),
(1933, '1:/events:minute:1765265640', '', 2, '2025-12-09 13:04:55', '2025-12-09 13:05:55'),
(1937, '1:/events:minute:1765265700', '', 5, '2025-12-09 13:05:30', '2025-12-09 13:06:30'),
(1947, '1:/events:minute:1765265820', '', 8, '2025-12-09 13:07:13', '2025-12-09 13:08:13');

-- --------------------------------------------------------

--
-- Table structure for table `series`
--

CREATE TABLE `series` (
  `series_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `series`
--

INSERT INTO `series` (`series_id`, `name`, `start_date`, `end_date`, `description`, `created_at`) VALUES
(6, 'Nov 23', '2025-11-23', '2025-11-23', '', '2025-11-23 04:05:13'),
(7, 'Test Tournament 2024', '2024-01-01', '2024-01-31', NULL, '2025-11-23 04:11:49'),
(8, 'Freedom Series 2025', '2025-12-05', '2026-01-05', 'Test Series', '2025-12-05 23:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `stats_cache`
--

CREATE TABLE `stats_cache` (
  `cache_id` int(11) NOT NULL,
  `appearance_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `runs` int(11) NOT NULL DEFAULT 0,
  `wickets` int(11) NOT NULL DEFAULT 0,
  `balls_faced` int(11) NOT NULL DEFAULT 0,
  `fours` int(11) NOT NULL DEFAULT 0,
  `sixes` int(11) NOT NULL DEFAULT 0,
  `dismissals` int(11) NOT NULL DEFAULT 0,
  `runs_conceded` int(11) NOT NULL DEFAULT 0,
  `overs_bowled` decimal(5,2) NOT NULL DEFAULT 0.00,
  `strike_rate` decimal(5,2) DEFAULT NULL,
  `economy_rate` decimal(5,2) DEFAULT NULL,
  `last_event_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stats_cache`
--

INSERT INTO `stats_cache` (`cache_id`, `appearance_id`, `player_id`, `match_id`, `runs`, `wickets`, `balls_faced`, `fours`, `sixes`, `dismissals`, `runs_conceded`, `overs_bowled`, `strike_rate`, `economy_rate`, `last_event_at`, `updated_at`) VALUES
(2606, 661, 17, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2607, 662, 10, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2610, 665, 18, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2611, 666, 26, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2612, 667, 53, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2614, 669, 24, 64, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-05 18:05:51', '2025-12-05 18:05:51'),
(2674, 680, 17, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:43', '2025-12-06 16:43:43'),
(2675, 681, 10, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:43', '2025-12-06 16:43:43'),
(2678, 684, 19, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:43', '2025-12-06 16:43:43'),
(2679, 685, 17, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:43', '2025-12-06 16:43:43'),
(2680, 686, 10, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:43', '2025-12-06 16:43:43'),
(2683, 689, 19, 74, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-06 16:43:44', '2025-12-06 16:43:44'),
(2920, 690, 17, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:41', '2025-12-08 14:38:41'),
(2921, 691, 10, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:41', '2025-12-08 14:38:41'),
(2924, 694, 19, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2925, 695, 20, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2926, 696, 47, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2927, 697, 18, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2928, 698, 26, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2929, 699, 53, 75, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-08 14:38:42', '2025-12-08 14:38:42'),
(2994, 700, 17, 76, 0, 0, 0, 0, 0, 0, 15, 1.33, 0.00, 11.25, '2025-12-09 13:17:27', '2025-12-09 13:17:27'),
(2995, 701, 10, 76, 0, 0, 0, 0, 0, 0, 24, 1.00, 0.00, 24.00, '2025-12-09 13:17:27', '2025-12-09 13:17:27'),
(2997, 703, 19, 76, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 13:17:28', '2025-12-09 13:17:28'),
(2999, 705, 6, 76, 28, 0, 8, 0, 3, 0, 0, 0.00, 350.00, 0.00, '2025-12-09 13:17:28', '2025-12-09 13:17:28'),
(3000, 706, 40, 76, 11, 0, 9, 1, 0, 0, 0, 0.00, 122.22, 0.00, '2025-12-09 13:17:28', '2025-12-09 13:17:28'),
(3002, 708, 13, 76, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 13:17:28', '2025-12-09 13:17:28'),
(3003, 709, 14, 76, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 13:17:28', '2025-12-09 13:17:28'),
(3045, 726, 17, 83, 1, 0, 1, 0, 0, 0, 0, 0.00, 100.00, 0.00, '2025-12-09 02:07:43', '2025-12-09 02:07:43'),
(3046, 727, 10, 83, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:07:43', '2025-12-09 02:07:43'),
(3057, 738, 17, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3058, 739, 10, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3061, 742, 19, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3062, 743, 6, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3063, 744, 40, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3065, 746, 13, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3066, 747, 14, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3067, 748, 64, 87, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:49:42', '2025-12-09 02:49:42'),
(3145, 750, 17, 90, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 02:51:08', '2025-12-09 02:51:08'),
(3146, 751, 10, 90, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 03:08:01', '2025-12-09 03:08:01'),
(3148, 754, 19, 90, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 03:08:01', '2025-12-09 03:08:01'),
(3150, 756, 6, 90, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 03:08:01', '2025-12-09 03:08:01'),
(3151, 757, 40, 90, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 03:08:01', '2025-12-09 03:08:01'),
(3153, 759, 17, 90, 0, 0, 1, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-09 03:08:01', '2025-12-09 03:08:01'),
(5521, 58, 17, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5522, 59, 10, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5523, 60, 19, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5524, 61, 6, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5525, 62, 40, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5526, 63, 13, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5527, 64, 14, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5528, 65, 64, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5529, 66, 39, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5530, 67, 9, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5531, 68, 56, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40'),
(5532, 69, 35, 7, 0, 0, 0, 0, 0, 0, 0, 0.00, 0.00, 0.00, '2025-12-20 13:23:40', '2025-12-20 13:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `short_name` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `name`, `short_name`, `logo`, `created_at`) VALUES
(1, 'Alex', '', NULL, '2025-12-09 14:49:00'),
(2, 'Kavin', '', NULL, '2025-12-09 14:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','scorer','user') NOT NULL DEFAULT 'user',
  `full_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `full_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@cricapp.local', '$2y$10$kmPI23YpTD.TqBEengxeJew7JkG9WPX6e1GS6gbEZqMyrOmg6K29O', 'admin', 'Administrator', 1, '2025-11-13 10:15:45', '2025-11-13 15:48:57'),
(2, 'Benny', '', '$2y$10$5VS1qppj/RQHimjljuLpu.uRvoejwolL2SOgRAvc1gSjtSQWFEqmq', 'scorer', '', 1, '2025-11-21 22:58:37', '2025-11-21 22:58:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `resource` (`resource_type`,`resource_id`);

--
-- Indexes for table `batting_stats`
--
ALTER TABLE `batting_stats`
  ADD PRIMARY KEY (`batting_stat_id`),
  ADD UNIQUE KEY `unique_player_match_batting` (`player_id`,`match_id`),
  ADD KEY `idx_player_batting` (`player_id`),
  ADD KEY `idx_match_batting` (`match_id`),
  ADD KEY `idx_runs` (`runs`);

--
-- Indexes for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  ADD PRIMARY KEY (`bowling_stat_id`),
  ADD UNIQUE KEY `unique_player_match_bowling` (`player_id`,`match_id`),
  ADD KEY `idx_player_bowling` (`player_id`),
  ADD KEY `idx_match_bowling` (`match_id`),
  ADD KEY `idx_wickets` (`wickets`);

--
-- Indexes for table `clone_links`
--
ALTER TABLE `clone_links`
  ADD PRIMARY KEY (`clone_id`),
  ADD KEY `source_match_id` (`source_match_id`),
  ADD KEY `target_match_id` (`target_match_id`);

--
-- Indexes for table `commentary`
--
ALTER TABLE `commentary`
  ADD PRIMARY KEY (`commentary_id`),
  ADD KEY `idx_match_innings` (`match_id`,`innings`),
  ADD KEY `idx_event` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `event_uuid` (`event_uuid`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `appearance_id` (`appearance_id`),
  ADD KEY `match_seq` (`match_id`,`assigned_server_seq`),
  ADD KEY `idx_fielder` (`fielder_id`);

--
-- Indexes for table `events_suspense`
--
ALTER TABLE `events_suspense`
  ADD PRIMARY KEY (`suspense_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `fielding_stats`
--
ALTER TABLE `fielding_stats`
  ADD PRIMARY KEY (`fielding_stat_id`),
  ADD UNIQUE KEY `unique_player_match` (`player_id`,`match_id`),
  ADD KEY `idx_player` (`player_id`),
  ADD KEY `idx_match` (`match_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `status_heartbeat` (`status`,`last_heartbeat`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `series_id` (`series_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`),
  ADD KEY `toss_winner_id` (`toss_winner_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `state` (`state`),
  ADD KEY `idx_winner_id` (`winner_id`);

--
-- Indexes for table `match_locks`
--
ALTER TABLE `match_locks`
  ADD PRIMARY KEY (`lock_id`),
  ADD UNIQUE KEY `match_lock` (`match_id`,`lock_type`),
  ADD KEY `locked_by` (`locked_by`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`);

--
-- Indexes for table `player_appearances`
--
ALTER TABLE `player_appearances`
  ADD PRIMARY KEY (`appearance_id`),
  ADD UNIQUE KEY `player_match_team` (`player_id`,`match_id`,`team_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `idx_is_guest` (`is_guest`),
  ADD KEY `idx_captain` (`match_id`,`is_captain`);

--
-- Indexes for table `player_edits`
--
ALTER TABLE `player_edits`
  ADD PRIMARY KEY (`edit_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `appearance_id` (`appearance_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `potm_decisions`
--
ALTER TABLE `potm_decisions`
  ADD PRIMARY KEY (`potm_id`),
  ADD UNIQUE KEY `match_id` (`match_id`),
  ADD KEY `computed_player_id` (`computed_player_id`),
  ADD KEY `final_player_id` (`final_player_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `pots_aggregate`
--
ALTER TABLE `pots_aggregate`
  ADD PRIMARY KEY (`pots_id`),
  ADD UNIQUE KEY `series_player` (`series_id`,`player_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`rate_limit_id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`series_id`);

--
-- Indexes for table `stats_cache`
--
ALTER TABLE `stats_cache`
  ADD PRIMARY KEY (`cache_id`),
  ADD UNIQUE KEY `appearance_match` (`appearance_id`,`match_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `appearance_id` (`appearance_id`),
  ADD KEY `idx_fours` (`fours`),
  ADD KEY `idx_sixes` (`sixes`),
  ADD KEY `idx_dismissals` (`dismissals`),
  ADD KEY `idx_runs_conceded` (`runs_conceded`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `batting_stats`
--
ALTER TABLE `batting_stats`
  MODIFY `batting_stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  MODIFY `bowling_stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `clone_links`
--
ALTER TABLE `clone_links`
  MODIFY `clone_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commentary`
--
ALTER TABLE `commentary`
  MODIFY `commentary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events_suspense`
--
ALTER TABLE `events_suspense`
  MODIFY `suspense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fielding_stats`
--
ALTER TABLE `fielding_stats`
  MODIFY `fielding_stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `match_locks`
--
ALTER TABLE `match_locks`
  MODIFY `lock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `player_appearances`
--
ALTER TABLE `player_appearances`
  MODIFY `appearance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `player_edits`
--
ALTER TABLE `player_edits`
  MODIFY `edit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potm_decisions`
--
ALTER TABLE `potm_decisions`
  MODIFY `potm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pots_aggregate`
--
ALTER TABLE `pots_aggregate`
  MODIFY `pots_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `rate_limit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1963;

--
-- AUTO_INCREMENT for table `series`
--
ALTER TABLE `series`
  MODIFY `series_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stats_cache`
--
ALTER TABLE `stats_cache`
  MODIFY `cache_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5593;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_action_logs`
--
ALTER TABLE `admin_action_logs`
  ADD CONSTRAINT `admin_action_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `batting_stats`
--
ALTER TABLE `batting_stats`
  ADD CONSTRAINT `batting_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batting_stats_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE;

--
-- Constraints for table `bowling_stats`
--
ALTER TABLE `bowling_stats`
  ADD CONSTRAINT `bowling_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bowling_stats_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE;

--
-- Constraints for table `clone_links`
--
ALTER TABLE `clone_links`
  ADD CONSTRAINT `clone_links_ibfk_1` FOREIGN KEY (`source_match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `clone_links_ibfk_2` FOREIGN KEY (`target_match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE;

--
-- Constraints for table `commentary`
--
ALTER TABLE `commentary`
  ADD CONSTRAINT `commentary_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentary_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`appearance_id`) REFERENCES `player_appearances` (`appearance_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_fielder` FOREIGN KEY (`fielder_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL;

--
-- Constraints for table `events_suspense`
--
ALTER TABLE `events_suspense`
  ADD CONSTRAINT `events_suspense_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `events_suspense_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE;

--
-- Constraints for table `fielding_stats`
--
ALTER TABLE `fielding_stats`
  ADD CONSTRAINT `fielding_stats_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fielding_stats_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`series_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`team_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`team_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`toss_winner_id`) REFERENCES `teams` (`team_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `matches_ibfk_winner` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`team_id`) ON UPDATE CASCADE;

--
-- Constraints for table `match_locks`
--
ALTER TABLE `match_locks`
  ADD CONSTRAINT `match_locks_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `match_locks_ibfk_2` FOREIGN KEY (`locked_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `player_appearances`
--
ALTER TABLE `player_appearances`
  ADD CONSTRAINT `player_appearances_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `player_appearances_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `player_appearances_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON UPDATE CASCADE;

--
-- Constraints for table `player_edits`
--
ALTER TABLE `player_edits`
  ADD CONSTRAINT `player_edits_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `player_edits_ibfk_2` FOREIGN KEY (`appearance_id`) REFERENCES `player_appearances` (`appearance_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `player_edits_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `potm_decisions`
--
ALTER TABLE `potm_decisions`
  ADD CONSTRAINT `potm_decisions_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `potm_decisions_ibfk_2` FOREIGN KEY (`computed_player_id`) REFERENCES `players` (`player_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `potm_decisions_ibfk_3` FOREIGN KEY (`final_player_id`) REFERENCES `players` (`player_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `potm_decisions_ibfk_4` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pots_aggregate`
--
ALTER TABLE `pots_aggregate`
  ADD CONSTRAINT `pots_aggregate_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`series_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pots_aggregate_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON UPDATE CASCADE;

--
-- Constraints for table `stats_cache`
--
ALTER TABLE `stats_cache`
  ADD CONSTRAINT `stats_cache_ibfk_1` FOREIGN KEY (`appearance_id`) REFERENCES `player_appearances` (`appearance_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stats_cache_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stats_cache_ibfk_3` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
