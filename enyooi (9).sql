-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2025 a las 03:33:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `enyooi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `achievements`
--

CREATE TABLE `achievements` (
  `idachievement` int(11) NOT NULL,
  `code` varchar(80) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `zafiros_reward` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activity_log`
--

CREATE TABLE `activity_log` (
  `idlog` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `detalles` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `activity_log`
--

INSERT INTO `activity_log` (`idlog`, `idusuario`, `accion`, `detalles`, `created_at`) VALUES
(1, 2, 'like', 'Dio like a publicación 1', '2025-09-01 01:27:11'),
(2, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-01 01:27:11'),
(3, 2, 'like', 'Dio like a publicación 1', '2025-09-01 01:27:12'),
(4, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-01 01:27:12'),
(5, 2, 'like', 'Dio like a publicación 2', '2025-09-01 01:34:17'),
(6, 2, 'unlike', 'Quitó like a publicación 2', '2025-09-01 01:34:17'),
(7, 2, 'like', 'Dio like a publicación 2', '2025-09-01 01:34:19'),
(8, 2, 'unlike', 'Quitó like a publicación 2', '2025-09-01 01:34:19'),
(9, 2, 'like', 'Dio like a publicación 3', '2025-09-01 02:14:04'),
(10, 2, 'unlike', 'Quitó like a publicación 3', '2025-09-01 02:14:04'),
(11, 2, 'like', 'Dio like a publicación 3', '2025-09-01 02:14:06'),
(12, 2, 'unlike', 'Quitó like a publicación 3', '2025-09-01 02:14:06'),
(13, 2, 'like', 'Dio like a publicación 2', '2025-09-01 02:14:19'),
(14, 2, 'unlike', 'Quitó like a publicación 2', '2025-09-01 02:14:20'),
(15, 2, 'like', 'Dio like a publicación 1', '2025-09-01 03:27:15'),
(16, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:01:20'),
(17, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:01:55'),
(18, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:02:00'),
(19, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:02:01'),
(20, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:02:01'),
(21, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:02:01'),
(22, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:14:25'),
(23, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:20:02'),
(24, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:21:09'),
(25, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:49:39'),
(26, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:49:53'),
(27, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:50:00'),
(28, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:50:08'),
(29, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:50:09'),
(30, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:50:20'),
(31, 2, 'like', 'Dio like a publicación 1', '2025-09-05 02:50:26'),
(32, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-05 02:51:18'),
(33, 2, 'like', 'Dio like a publicación 4', '2025-09-07 07:19:15'),
(34, 2, 'like', 'Dio like a publicación 1', '2025-09-07 07:22:41'),
(35, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-07 07:24:19'),
(36, 2, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:24:21'),
(37, 2, 'like', 'Dio like a publicación 1', '2025-09-07 07:24:26'),
(38, 2, 'like', 'Dio like a publicación 4', '2025-09-07 07:33:31'),
(39, 2, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:33:53'),
(40, 2, 'like', 'Dio like a publicación 4', '2025-09-07 07:33:54'),
(41, 2, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:34:25'),
(42, 2, 'like', 'Dio like a publicación 4', '2025-09-07 07:34:32'),
(43, 3, 'like', 'Dio like a publicación 4', '2025-09-07 07:34:41'),
(44, 3, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:34:42'),
(45, 3, 'like', 'Dio like a publicación 4', '2025-09-07 07:34:44'),
(46, 3, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:34:45'),
(47, 3, 'like', 'Dio like a publicación 1', '2025-09-07 07:34:45'),
(48, 3, 'unlike', 'Quitó like a publicación 1', '2025-09-07 07:34:46'),
(49, 3, 'like', 'Dio like a publicación 1', '2025-09-07 07:34:48'),
(50, 3, 'unlike', 'Quitó like a publicación 1', '2025-09-07 07:34:48'),
(51, 3, 'like', 'Dio like a publicación 4', '2025-09-07 07:34:49'),
(52, 3, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:34:51'),
(53, 3, 'like', 'Dio like a publicación 1', '2025-09-07 07:34:52'),
(54, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-07 07:35:01'),
(55, 2, 'like', 'Dio like a publicación 1', '2025-09-07 07:35:02'),
(56, 2, 'unlike', 'Quitó like a publicación 4', '2025-09-07 07:55:15'),
(57, 2, 'unlike', 'Quitó like a publicación 1', '2025-09-07 07:55:16'),
(58, 2, 'like', 'Dio like a publicación 4', '2025-09-07 07:55:17'),
(59, 2, 'like', 'Dio like a publicación 1', '2025-09-07 07:55:18'),
(60, 3, 'like', 'Dio like a publicación 5', '2025-09-07 20:29:52'),
(61, 6, 'like', 'Dio like a publicación 5', '2025-09-09 03:19:15'),
(62, 6, 'like', 'Dio like a publicación 4', '2025-09-09 03:19:18'),
(63, 6, 'like', 'Dio like a publicación 1', '2025-09-09 03:19:19'),
(64, 1, 'like', 'Dio like a publicación 6', '2025-09-15 01:01:40'),
(65, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-17 01:24:40'),
(66, 1, 'like', 'Dio like a publicación 6', '2025-09-17 01:24:40'),
(67, 1, 'like', 'Dio like a publicación 4', '2025-09-17 02:19:14'),
(68, 1, 'like', 'Dio like a publicación 1', '2025-09-17 02:19:15'),
(69, 1, 'like', 'Dio like a publicación 5', '2025-09-17 02:19:18'),
(70, 1, 'transaccion', 'Transacción aprobada tipo recharge por 5.00 USD / 550 zafiros', '2025-09-21 05:22:25'),
(71, 1, 'transaccion', 'Transacción aprobada tipo recharge por 5.00 USD / 550 zafiros', '2025-09-21 05:22:58'),
(72, 1, 'like', 'Dio like a publicación 7', '2025-09-22 00:57:03'),
(73, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-22 01:20:07'),
(74, 1, 'like', 'Dio like a publicación 7', '2025-09-22 01:20:07'),
(75, 1, 'unlike', 'Quitó like a publicación 5', '2025-09-22 05:50:00'),
(76, 1, 'like', 'Dio like a publicación 5', '2025-09-22 05:50:01'),
(77, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-23 03:21:29'),
(78, 1, 'like', 'Dio like a publicación 4', '2025-09-23 03:21:29'),
(79, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-23 03:21:36'),
(80, 1, 'like', 'Dio like a publicación 4', '2025-09-23 03:21:37'),
(81, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-23 03:29:51'),
(82, 1, 'like', 'Dio like a publicación 4', '2025-09-23 03:29:53'),
(83, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-23 03:43:33'),
(84, 1, 'like', 'Dio like a publicación 6', '2025-09-23 03:43:33'),
(85, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-23 03:43:35'),
(86, 1, 'like', 'Dio like a publicación 6', '2025-09-23 03:43:38'),
(87, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-23 03:43:51'),
(88, 1, 'like', 'Dio like a publicación 6', '2025-09-23 03:43:55'),
(89, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 01:39:30'),
(90, 1, 'like', 'Dio like a publicación 7', '2025-09-24 01:39:30'),
(91, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 01:39:31'),
(92, 1, 'like', 'Dio like a publicación 7', '2025-09-24 01:39:35'),
(93, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 01:39:54'),
(94, 1, 'like', 'Dio like a publicación 7', '2025-09-24 01:39:58'),
(95, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 02:21:52'),
(96, 1, 'like', 'Dio like a publicación 7', '2025-09-24 02:21:53'),
(97, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 02:21:59'),
(98, 1, 'like', 'Dio like a publicación 7', '2025-09-24 02:22:03'),
(99, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 02:22:05'),
(100, 1, 'like', 'Dio like a publicación 7', '2025-09-24 02:22:12'),
(101, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 02:29:29'),
(102, 1, 'like', 'Dio like a publicación 7', '2025-09-24 02:29:30'),
(103, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-24 03:17:17'),
(104, 1, 'like', 'Dio like a publicación 7', '2025-09-24 03:17:17'),
(105, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-26 01:40:01'),
(106, 1, 'like', 'Dio like a publicación 7', '2025-09-26 01:40:02'),
(107, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-26 01:59:27'),
(108, 1, 'like', 'Dio like a publicación 7', '2025-09-26 01:59:27'),
(109, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-26 01:59:29'),
(110, 1, 'like', 'Dio like a publicación 6', '2025-09-26 01:59:29'),
(111, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 01:59:34'),
(112, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:48:44'),
(113, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:48:53'),
(114, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:48:54'),
(115, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:49:07'),
(116, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:49:09'),
(117, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:49:11'),
(118, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:49:13'),
(119, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:49:58'),
(120, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:50:04'),
(121, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:52:21'),
(122, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:52:22'),
(123, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 02:52:23'),
(124, 1, 'like', 'Dio like a publicación 4', '2025-09-26 02:52:24'),
(125, 1, 'unlike', 'Quitó like a publicación 6', '2025-09-26 02:52:35'),
(126, 1, 'like', 'Dio like a publicación 6', '2025-09-26 02:52:35'),
(127, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-26 03:13:20'),
(128, 1, 'like', 'Dio like a publicación 4', '2025-09-26 03:13:21'),
(129, 1, 'unlike', 'Quitó like a publicación 4', '2025-09-27 20:59:08'),
(130, 1, 'like', 'Dio like a publicación 4', '2025-09-27 20:59:08'),
(131, 1, 'unlike', 'Quitó like a publicación 7', '2025-09-27 21:16:25'),
(132, 1, 'like', 'Dio like a publicación 7', '2025-09-27 21:16:26'),
(133, 4, 'like', 'Dio like a publicación 7', '2025-10-16 01:43:38'),
(134, 4, 'unlike', 'Quitó like a publicación 7', '2025-10-16 01:43:41'),
(135, 4, 'like', 'Dio like a publicación 7', '2025-10-16 01:43:43'),
(136, 4, 'unlike', 'Quitó like a publicación 7', '2025-10-16 01:43:56'),
(137, 4, 'like', 'Dio like a publicación 7', '2025-10-16 01:44:02'),
(138, 4, 'like', 'Dio like a publicación 6', '2025-10-16 01:44:05'),
(139, 4, 'like', 'Dio like a publicación 5', '2025-10-16 01:44:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats_desbloqueados`
--

CREATE TABLE `chats_desbloqueados` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_creadora` int(11) NOT NULL,
  `fecha_desbloqueo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_messages`
--

CREATE TABLE `chat_messages` (
  `idchat` int(11) NOT NULL,
  `idstream` int(11) NOT NULL,
  `idusuario` int(11) DEFAULT NULL,
  `anon_hash` varchar(64) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_verificacion_retiro`
--

CREATE TABLE `codigos_verificacion_retiro` (
  `id` int(11) NOT NULL,
  `id_creadora` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigos_verificacion_retiro`
--

INSERT INTO `codigos_verificacion_retiro` (`id`, `id_creadora`, `codigo`, `expires_at`, `created_at`) VALUES
(1, 1, '278388', '2025-09-27 18:11:23', '2025-09-27 22:56:23'),
(2, 1, '802042', '2025-09-27 18:11:27', '2025-09-27 22:56:27'),
(3, 1, '225042', '2025-09-27 22:16:25', '2025-09-28 03:01:25'),
(4, 1, '282072', '2025-09-27 22:19:16', '2025-09-28 03:04:16'),
(6, 1, '327386', '2025-09-28 15:43:03', '2025-09-28 20:28:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `idComentario` int(11) NOT NULL,
  `idPublicacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `contenidoComentario` longtext DEFAULT NULL,
  `fechaComentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`idComentario`, `idPublicacion`, `idUsuario`, `contenidoComentario`, `fechaComentario`) VALUES
(1, 1, 2, 'HOLAAA', '2025-09-01 01:05:37'),
(2, 1, 2, 'holaaa', '2025-09-01 02:14:43'),
(3, 1, 2, 'holaaa', '2025-09-01 02:14:50'),
(4, 1, 2, 'holaaa', '2025-09-02 02:15:10'),
(5, 1, 2, 'holaaa', '2025-09-02 02:15:11'),
(6, 1, 2, 'holaaa', '2025-09-02 02:15:11'),
(7, 1, 2, 'holaaa', '2025-09-02 02:15:11'),
(8, 1, 2, 'holaaa', '2025-09-02 02:15:11'),
(9, 1, 2, 'holaaa', '2025-09-02 02:15:12'),
(10, 1, 2, 'holaaa', '2025-09-02 02:15:12'),
(11, 1, 2, 'holaaa', '2025-09-02 02:15:12'),
(12, 1, 2, 'holaaa', '2025-09-02 02:15:12'),
(13, 1, 2, 'HOAL', '2025-09-02 02:18:00'),
(14, 1, 2, 'HOLA', '2025-09-02 02:18:05'),
(15, 1, 2, '????', '2025-09-05 02:01:09'),
(16, 1, 2, 'holaa', '2025-09-07 08:00:37'),
(17, 4, 2, 'holaa', '2025-09-07 08:06:07'),
(18, 4, 2, 'hola', '2025-09-07 08:14:09'),
(19, 4, 2, 'hola', '2025-09-07 08:23:12'),
(20, 4, 2, 'hola', '2025-09-07 08:46:19'),
(21, 4, 2, 'holaa', '2025-09-07 08:53:36'),
(22, 5, 3, 'jhgy', '2025-09-07 20:30:05'),
(23, 5, 5, 'jajaj', '2025-09-09 03:13:14'),
(24, 5, 6, 'kjkjk', '2025-09-10 01:13:08'),
(25, 7, 1, 'asdsa', '2025-09-22 01:20:13'),
(26, 5, 1, 'holaaa', '2025-09-23 01:14:33'),
(27, 7, 1, 'hola', '2025-09-24 01:39:45'),
(28, 7, 1, 'hola', '2025-09-24 01:39:51'),
(29, 7, 1, 'hola', '2025-09-24 02:29:37'),
(30, 4, 1, 'holaaa', '2025-09-26 03:13:32'),
(31, 4, 1, 'holaaa', '2025-09-26 03:13:40'),
(32, 4, 1, '????', '2025-09-26 03:13:45'),
(33, 4, 1, '????', '2025-09-26 03:14:09'),
(34, 7, 1, 'hola', '2025-09-27 20:59:24'),
(35, 7, 1, 'kjsdklasjdklasdhjaskdjaksdjaskdjaskñdjsakñdjasñkdjasñkdjasñkdjasñkdjaskñdjaskñdjaskñdjaskñdjaskdjaskldjasldjsakldjaskdlasjdklasjdklasjdkasldjaskldjaskldjsakldjaskldjaskldjksaldjaskldjaskldjaskldjaskldjkslajdksaldjkasldjaskldjslak', '2025-09-27 20:59:55'),
(36, 4, 1, '🙄', '2025-09-27 21:19:40'),
(37, 4, 1, '🤒', '2025-09-27 21:20:02'),
(38, 7, 1, 'gygkgkhghkghkghkgkgkhgfdfdfhdfhdfgdfghshshshhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', '2025-09-27 21:27:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `idcompra` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idcontenido` int(11) DEFAULT NULL,
  `idmenu` int(11) DEFAULT NULL,
  `idcreadora` int(11) NOT NULL,
  `zafiros` int(11) NOT NULL,
  `comision` int(11) NOT NULL,
  `creadora_recibe` int(11) NOT NULL,
  `estado` enum('ok','reembolsado') DEFAULT 'ok',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contenido`
--

CREATE TABLE `contenido` (
  `idcontenido` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_zafiros` int(11) NOT NULL DEFAULT 0,
  `ruta_archivo` varchar(255) DEFAULT NULL,
  `privado` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contenido_desbloqueado`
--

CREATE TABLE `contenido_desbloqueado` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_publicacion` int(11) NOT NULL,
  `fecha_desbloqueo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `content_reports`
--

CREATE TABLE `content_reports` (
  `idreport` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `idpublicacion` int(11) DEFAULT NULL,
  `idcomentario` int(11) DEFAULT NULL,
  `motivo` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `estado` enum('pendiente','revisado','accionado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `daily_stats`
--

CREATE TABLE `daily_stats` (
  `idstat` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `new_users` int(11) DEFAULT 0,
  `publicaciones` int(11) DEFAULT 0,
  `comentarios` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `zafiros_gastados` int(11) DEFAULT 0,
  `ingresos_usd` decimal(12,2) DEFAULT 0.00,
  `watch_seconds` bigint(20) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_verifications`
--

CREATE TABLE `email_verifications` (
  `idverif` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `idrate` int(11) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `rate_usd` decimal(12,6) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(64) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','issued','sent') DEFAULT 'draft',
  `sri_uuid` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leaderboard_cache`
--

CREATE TABLE `leaderboard_cache` (
  `id` int(11) NOT NULL,
  `period` enum('daily','weekly','monthly','alltime') NOT NULL,
  `metric` varchar(64) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `value` bigint(20) NOT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `levels`
--

CREATE TABLE `levels` (
  `idlevel` int(11) NOT NULL,
  `level_number` int(11) NOT NULL,
  `xp_required` int(11) NOT NULL,
  `zafiros_reward` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `levels`
--

INSERT INTO `levels` (`idlevel`, `level_number`, `xp_required`, `zafiros_reward`) VALUES
(1, 1, 0, 0),
(2, 2, 100, 50),
(3, 3, 250, 100),
(4, 4, 500, 150),
(5, 5, 1000, 200),
(6, 6, 2000, 250),
(7, 7, 3500, 300),
(8, 8, 5000, 400),
(9, 9, 7500, 500),
(10, 10, 10000, 1000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes`
--

CREATE TABLE `likes` (
  `idLike` int(11) NOT NULL,
  `idPublicacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `fechaLike` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `likes`
--

INSERT INTO `likes` (`idLike`, `idPublicacion`, `idUsuario`, `fechaLike`) VALUES
(29, 1, 3, '2025-09-07 07:34:52'),
(31, 4, 2, '2025-09-07 07:55:17'),
(32, 1, 2, '2025-09-07 07:55:18'),
(33, 5, 3, '2025-09-07 20:29:52'),
(34, 5, 6, '2025-09-09 03:19:15'),
(35, 4, 6, '2025-09-09 03:19:18'),
(36, 1, 6, '2025-09-09 03:19:19'),
(40, 1, 1, '2025-09-17 02:19:15'),
(44, 5, 1, '2025-09-22 05:50:01'),
(69, 6, 1, '2025-09-26 02:52:35'),
(71, 4, 1, '2025-09-27 20:59:08'),
(72, 7, 1, '2025-09-27 21:16:26'),
(75, 7, 4, '2025-10-16 01:44:02'),
(76, 6, 4, '2025-10-16 01:44:05'),
(77, 5, 4, '2025-10-16 01:44:10');

--
-- Disparadores `likes`
--
DELIMITER $$
CREATE TRIGGER `trg_like_delete` AFTER DELETE ON `likes` FOR EACH ROW BEGIN
  UPDATE publicaciones
  SET num_likes = GREATEST(num_likes - 1, 0)
  WHERE idPublicacion = OLD.idPublicacion;
  
  -- Registrar en activity_log
  INSERT INTO activity_log (idusuario, accion, detalles)
  VALUES (OLD.idUsuario, 'unlike', CONCAT('Quitó like a publicación ', OLD.idPublicacion));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_like_insert` AFTER INSERT ON `likes` FOR EACH ROW BEGIN
  UPDATE publicaciones
  SET num_likes = num_likes + 1
  WHERE idPublicacion = NEW.idPublicacion;
  
  -- Registrar en activity_log
  INSERT INTO activity_log (idusuario, accion, detalles)
  VALUES (NEW.idUsuario, 'like', CONCAT('Dio like a publicación ', NEW.idPublicacion));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lovense_tip_options`
--

CREATE TABLE `lovense_tip_options` (
  `id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `zafiros` int(11) NOT NULL,
  `duration_seconds` int(11) NOT NULL COMMENT 'Duración en segundos',
  `intensity_level` int(11) NOT NULL COMMENT 'Nivel de 1 a 20',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `media`
--

CREATE TABLE `media` (
  `idmedia` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('video','imagen') NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `ruta_thumb` varchar(255) DEFAULT NULL,
  `privado` tinyint(1) DEFAULT 0,
  `precio_zafiros` int(11) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `media_thumbs`
--

CREATE TABLE `media_thumbs` (
  `idthumb` int(11) NOT NULL,
  `idmedia` int(11) NOT NULL,
  `ruta_thumb` varchar(255) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `idMensaje` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `contenido` text DEFAULT NULL,
  `fechaMensaje` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `media_url` varchar(255) DEFAULT NULL,
  `media_tipo` enum('imagen','gif') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`idMensaje`, `remitente_id`, `destinatario_id`, `contenido`, `fechaMensaje`, `leido`, `media_url`, `media_tipo`) VALUES
(1, 2, 3, NULL, '2025-09-07 02:20:32', 1, 'public/img/chat_media/68bcebf00a2f6-sung-jin-woo-solo-leveling-arise-4k-wallpaper-uhdpaper.com-188@5@d.jpg', 'imagen'),
(2, 3, 2, NULL, '2025-09-07 02:38:34', 1, 'public/img/chat_media/68bcf02af1499-ChatGPT Image 26 ago 2025, 21_08_25.png', 'imagen'),
(3, 3, 2, NULL, '2025-09-07 02:50:16', 1, 'public/img/chat_media/68bcf2e886d12-ChatGPT Image 26 ago 2025, 21_08_25.png', 'imagen'),
(4, 2, 3, NULL, '2025-09-07 02:50:31', 1, 'public/img/chat_media/68bcf2f7d657d-cyberpunk-night-city-hd-wallpaper-uhdpaper.com-632@2@b.jpg', 'imagen'),
(5, 2, 3, 'hola', '2025-09-07 03:57:32', 1, NULL, NULL),
(6, 2, 3, 'como estas', '2025-09-07 03:57:41', 1, NULL, NULL),
(7, 2, 3, 'estas bien?', '2025-09-07 03:57:49', 1, NULL, NULL),
(8, 2, 3, 'funciona?', '2025-09-07 03:57:55', 1, NULL, NULL),
(9, 3, 2, 'si', '2025-09-07 03:58:16', 1, NULL, NULL),
(10, 2, 3, NULL, '2025-09-07 03:58:34', 1, 'public/img/chat_media/68bd02ea87f74-sung-jin-woo-solo-leveling-arise-4k-wallpaper-uhdpaper.com-188@5@d.jpg', 'imagen'),
(11, 3, 2, 'imagen', '2025-09-07 03:58:42', 1, NULL, NULL),
(12, 3, 2, 'hola', '2025-09-07 03:59:26', 1, NULL, NULL),
(13, 2, 3, 'holaa', '2025-09-07 04:52:28', 1, NULL, NULL),
(14, 3, 2, 'hola', '2025-09-07 08:45:01', 1, NULL, NULL),
(15, 3, 5, 'hola', '2025-09-07 20:50:10', 1, NULL, NULL),
(16, 5, 3, 'hiola', '2025-09-07 20:50:44', 1, NULL, NULL),
(17, 5, 3, '????', '2025-09-07 20:50:56', 1, NULL, NULL),
(18, 5, 3, 'jajaj', '2025-09-07 20:51:11', 1, NULL, NULL),
(19, 3, 5, 'juasdhsjdhs', '2025-09-07 20:51:16', 1, NULL, NULL),
(20, 5, 3, NULL, '2025-09-07 20:51:25', 1, 'public/img/chat_media/68bdf04d52cea-pexels-belych-8047169.jpg', 'imagen'),
(21, 6, 5, 'holaaa', '2025-09-09 03:19:37', 0, NULL, NULL),
(22, 1, 3, 'hola', '2025-09-24 01:19:15', 0, NULL, NULL),
(23, 6, 5, 'hola', '2025-09-24 01:19:42', 0, NULL, NULL),
(24, 6, 1, 'hola', '2025-09-24 01:19:54', 1, NULL, NULL),
(25, 1, 3, 'hola', '2025-09-24 01:37:35', 0, NULL, NULL),
(26, 6, 1, 'hola', '2025-09-24 01:37:44', 1, NULL, NULL),
(27, 1, 6, 'hola', '2025-09-24 01:38:03', 1, NULL, NULL),
(28, 1, 6, 'hola', '2025-09-24 01:38:23', 1, NULL, NULL),
(29, 6, 1, 'holaa', '2025-09-24 01:38:29', 1, NULL, NULL),
(30, 1, 6, 'holi', '2025-09-24 01:38:36', 1, NULL, NULL),
(31, 6, 1, 'holaa', '2025-09-24 01:39:00', 1, NULL, NULL),
(32, 6, 1, 'holaa', '2025-09-24 01:39:07', 1, NULL, NULL),
(33, 6, 1, 'hola', '2025-09-24 02:29:56', 1, NULL, NULL),
(34, 1, 6, 'holi}', '2025-09-24 02:30:05', 1, NULL, NULL),
(35, 4, 3, 'holaas', '2025-10-16 01:44:41', 0, NULL, NULL),
(36, 4, 3, 'como estassss', '2025-10-16 01:44:46', 0, NULL, NULL),
(37, 2, 3, 'hol', '2025-10-16 01:49:01', 0, NULL, NULL),
(38, 2, 3, 'holaaaa', '2025-10-16 01:49:07', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

CREATE TABLE `menus` (
  `idmenu` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_zafiros` int(11) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `idNotificacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `usuarioAccion` int(11) NOT NULL,
  `tipoNotificacion` int(11) NOT NULL,
  `idPublicacion` int(11) DEFAULT NULL,
  `fechaNotificacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`idNotificacion`, `idUsuario`, `usuarioAccion`, `tipoNotificacion`, `idPublicacion`, `fechaNotificacion`, `leido`, `payload`) VALUES
(1, 2, 2, 2, 1, '2025-09-01 01:05:37', 0, NULL),
(2, 2, 2, 1, 1, '2025-09-01 01:27:11', 0, NULL),
(3, 2, 2, 1, 1, '2025-09-01 01:27:12', 0, NULL),
(9, 2, 2, 2, 1, '2025-09-01 02:14:43', 0, NULL),
(10, 2, 2, 2, 1, '2025-09-01 02:14:50', 0, NULL),
(11, 2, 2, 1, 1, '2025-09-01 03:27:15', 0, NULL),
(12, 2, 2, 2, 1, '2025-09-02 02:15:10', 0, NULL),
(13, 2, 2, 2, 1, '2025-09-02 02:15:11', 0, NULL),
(14, 2, 2, 2, 1, '2025-09-02 02:15:11', 0, NULL),
(15, 2, 2, 2, 1, '2025-09-02 02:15:11', 0, NULL),
(16, 2, 2, 2, 1, '2025-09-02 02:15:11', 0, NULL),
(17, 2, 2, 2, 1, '2025-09-02 02:15:12', 0, NULL),
(18, 2, 2, 2, 1, '2025-09-02 02:15:12', 0, NULL),
(19, 2, 2, 2, 1, '2025-09-02 02:15:12', 0, NULL),
(20, 2, 2, 2, 1, '2025-09-02 02:15:12', 0, NULL),
(21, 2, 2, 2, 1, '2025-09-02 02:18:00', 0, NULL),
(22, 2, 2, 2, 1, '2025-09-02 02:18:05', 0, NULL),
(23, 2, 2, 2, 1, '2025-09-05 02:01:09', 0, NULL),
(24, 2, 2, 1, 1, '2025-09-05 02:01:55', 0, NULL),
(25, 2, 2, 1, 1, '2025-09-05 02:02:01', 0, NULL),
(26, 2, 2, 1, 1, '2025-09-05 02:02:01', 0, NULL),
(27, 2, 2, 1, 1, '2025-09-05 02:20:02', 0, NULL),
(28, 2, 2, 1, 1, '2025-09-05 02:49:39', 0, NULL),
(29, 2, 2, 1, 1, '2025-09-05 02:50:00', 0, NULL),
(30, 2, 2, 1, 1, '2025-09-05 02:50:09', 0, NULL),
(31, 2, 2, 1, 1, '2025-09-05 02:50:26', 0, NULL),
(32, 4, 3, 1, 5, '2025-09-07 20:29:52', 0, NULL),
(33, 4, 5, 2, 5, '2025-09-09 03:13:14', 0, NULL),
(34, 4, 6, 1, 5, '2025-09-09 03:19:15', 0, NULL),
(35, 3, 6, 1, 4, '2025-09-09 03:19:18', 0, NULL),
(36, 2, 6, 1, 1, '2025-09-09 03:19:19', 0, NULL),
(37, 4, 6, 2, 5, '2025-09-10 01:13:08', 0, NULL),
(38, 6, 1, 1, 6, '2025-09-15 01:01:40', 0, NULL),
(39, 6, 1, 1, 6, '2025-09-17 01:24:40', 0, NULL),
(40, 3, 1, 1, 4, '2025-09-17 02:19:14', 0, NULL),
(41, 2, 1, 1, 1, '2025-09-17 02:19:15', 0, NULL),
(42, 4, 1, 1, 5, '2025-09-17 02:19:19', 0, NULL),
(43, 4, 1, 1, 5, '2025-09-22 05:50:01', 0, NULL),
(44, 4, 1, 2, 5, '2025-09-23 01:14:33', 0, NULL),
(45, 3, 1, 1, 4, '2025-09-23 03:21:29', 0, NULL),
(46, 3, 1, 1, 4, '2025-09-23 03:21:37', 0, NULL),
(47, 3, 1, 1, 4, '2025-09-23 03:29:53', 0, NULL),
(48, 6, 1, 1, 6, '2025-09-23 03:43:33', 0, NULL),
(49, 6, 1, 1, 6, '2025-09-23 03:43:38', 0, NULL),
(50, 6, 1, 1, 6, '2025-09-23 03:43:55', 0, NULL),
(51, 6, 1, 1, 6, '2025-09-26 01:59:29', 0, NULL),
(52, 3, 1, 1, 4, '2025-09-26 02:48:44', 0, NULL),
(53, 3, 1, 1, 4, '2025-09-26 02:48:54', 0, NULL),
(54, 3, 1, 1, 4, '2025-09-26 02:49:09', 0, NULL),
(55, 3, 1, 1, 4, '2025-09-26 02:49:13', 0, NULL),
(56, 3, 1, 1, 4, '2025-09-26 02:50:04', 0, NULL),
(57, 3, 1, 1, 4, '2025-09-26 02:52:22', 0, NULL),
(58, 3, 1, 1, 4, '2025-09-26 02:52:24', 0, NULL),
(59, 6, 1, 1, 6, '2025-09-26 02:52:35', 0, NULL),
(60, 3, 1, 1, 4, '2025-09-26 03:13:21', 0, NULL),
(61, 3, 1, 2, 4, '2025-09-26 03:13:32', 0, NULL),
(62, 3, 1, 2, 4, '2025-09-26 03:13:40', 0, NULL),
(63, 3, 1, 2, 4, '2025-09-26 03:13:45', 0, NULL),
(64, 3, 1, 2, 4, '2025-09-26 03:14:09', 0, NULL),
(65, 3, 1, 1, 4, '2025-09-27 20:59:08', 0, NULL),
(66, 3, 1, 2, 4, '2025-09-27 21:19:40', 0, NULL),
(67, 3, 1, 2, 4, '2025-09-27 21:20:02', 0, NULL),
(68, 1, 4, 1, 7, '2025-10-16 01:43:38', 0, NULL),
(69, 1, 4, 1, 7, '2025-10-16 01:43:43', 0, NULL),
(70, 1, 4, 1, 7, '2025-10-16 01:44:02', 0, NULL),
(71, 6, 4, 1, 6, '2025-10-16 01:44:05', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `idreset` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil`
--

CREATE TABLE `perfil` (
  `idusuario` int(11) NOT NULL,
  `nickname_artistico` varchar(60) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT '/ENYOOI/public/img/defaults/default_avatar.png',
  `banner_portada` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `precio_suscripcion` int(11) UNSIGNED NOT NULL DEFAULT 35 COMMENT 'Precio en Zafiros. Mínimo 35 (equivalente a $5 USD).',
  `chat_precio` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `lovense_api_key` varchar(255) DEFAULT NULL,
  `ultima_conexion` timestamp NULL DEFAULT NULL,
  `xp` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `level` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil`
--

INSERT INTO `perfil` (`idusuario`, `nickname_artistico`, `foto_perfil`, `banner_portada`, `bio`, `precio_suscripcion`, `chat_precio`, `lovense_api_key`, `ultima_conexion`, `xp`, `level`) VALUES
(1, 'Lauraxd', 'public/uploads/foto_1_1756687203.png', 'public/uploads/banner_1_1756687203.jpg', 'Bienvenidos', 5, 0, NULL, NULL, 3, 1),
(2, 'Luna3372', 'public/img/defaults/default_avatar.png', NULL, '', 5, 0, NULL, NULL, 0, 1),
(3, 'Bryda20', 'public/uploads/foto_3_1757211287.jpg', 'public/uploads/banner_3_1757211287.jpg', 'Desarrolador', 5, 0, NULL, NULL, 0, 1),
(4, 'Lola', 'public/uploads/foto_4_1757276814.jpg', 'public/uploads/banner_4_1757276814.jpg', 'Amigable me gusta interactuar con uds', 5, 0, NULL, NULL, 0, 1),
(5, 'Delta2126', 'public/img/defaults/default_avatar.png', NULL, '', 5, 0, NULL, NULL, 0, 1),
(6, 'user17cd8e', 'public/img/defaults/default_avatar.png', NULL, '', 5, 0, NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promo_codes`
--

CREATE TABLE `promo_codes` (
  `idpromo` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `descuento` decimal(5,2) NOT NULL,
  `max_uses` int(11) DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promo_redemptions`
--

CREATE TABLE `promo_redemptions` (
  `idredemp` int(11) NOT NULL,
  `idpromo` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propinas`
--

CREATE TABLE `propinas` (
  `idpropina` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
  `idstream` int(11) DEFAULT NULL,
  `zafiros` int(11) NOT NULL,
  `comision` int(11) NOT NULL,
  `creadora_recibe` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `idPublicacion` int(11) NOT NULL,
  `idUsuarioPublico` int(11) NOT NULL,
  `contenidoPublicacion` longtext DEFAULT NULL,
  `fechaPublicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fotoPublicacion` varchar(255) NOT NULL,
  `tipo_archivo` varchar(10) DEFAULT NULL,
  `num_likes` int(11) DEFAULT 0,
  `precio_zafiros` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`idPublicacion`, `idUsuarioPublico`, `contenidoPublicacion`, `fechaPublicacion`, `fotoPublicacion`, `tipo_archivo`, `num_likes`, `precio_zafiros`) VALUES
(1, 2, 'holaaa', '2025-09-01 01:05:26', 'sin archivo', 'texto', 4, 0),
(4, 3, 'holaaa', '2025-09-07 05:25:36', 'http://localhost/ENYOOI/img/Imagenes_Publicaciones/1757222736_cyberpunk-night-city-hd-wallpaper-uhdpaper.com-632@2@b.jpg', 'imagen', 3, 0),
(5, 4, 'nfyusny', '2025-09-07 20:29:16', 'http://localhost/ENYOOI/img/Imagenes_Publicaciones/1757276956_ChatGPT Image 26 ago 2025, 21_08_25.png', 'imagen', 4, 0),
(6, 6, 'jhgjgf', '2025-09-10 01:13:41', 'sin archivo', 'texto', 2, 0),
(7, 1, 'xbakjxha', '2025-09-22 00:57:00', 'sin archivo', 'texto', 2, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_retiro`
--

CREATE TABLE `solicitudes_retiro` (
  `id` int(11) NOT NULL,
  `id_creadora` int(11) NOT NULL,
  `monto_zafiros` int(11) NOT NULL,
  `monto_usd` decimal(10,2) NOT NULL,
  `comision_usd` decimal(10,2) NOT NULL,
  `monto_final_usd` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','aprobado','denegado','anulado') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_procesado` timestamp NULL DEFAULT NULL,
  `datos_bancarios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`datos_bancarios`)),
  `nota_admin` text DEFAULT NULL,
  `comprobante_admin` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_retiro`
--

INSERT INTO `solicitudes_retiro` (`id`, `id_creadora`, `monto_zafiros`, `monto_usd`, `comision_usd`, `monto_final_usd`, `estado`, `fecha_solicitud`, `fecha_procesado`, `datos_bancarios`, `nota_admin`, `comprobante_admin`) VALUES
(1, 1, 553, 79.00, 7.90, 71.10, 'pendiente', '2025-09-28 03:09:41', NULL, '{\"fullName\":\"kdjcskdlcnd\",\"idNumber\":\"089u78y786\",\"bank\":\"Banco Pichincha\",\"otherBankName\":\"\",\"accountNumber\":\"98987856\",\"amount\":\"79\"}', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `streams`
--

CREATE TABLE `streams` (
  `idstream` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT 'En vivo',
  `descripcion` text DEFAULT NULL,
  `stream_key` varchar(128) NOT NULL,
  `estado` enum('offline','live') DEFAULT 'offline',
  `viewers_count` int(11) DEFAULT 0,
  `hls_path` varchar(255) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `streams`
--

INSERT INTO `streams` (`idstream`, `creator_id`, `titulo`, `descripcion`, `stream_key`, `estado`, `viewers_count`, `hls_path`, `started_at`, `ended_at`, `created_at`) VALUES
(1, 1, 'En vivo', NULL, '58c9604e45750d20c8a71b2d6d88c4e8', 'offline', 0, NULL, NULL, NULL, '2025-09-28 22:07:15'),
(2, 4, 'En vivo', NULL, 'b51139bf9552a69c814248d9aa7503e3', 'offline', 0, NULL, NULL, NULL, '2025-10-08 03:33:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stream_objectives`
--

CREATE TABLE `stream_objectives` (
  `id` int(11) NOT NULL,
  `idstream` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `goal_zafiros` int(11) NOT NULL DEFAULT 0,
  `current_zafiros` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stream_roulette_options`
--

CREATE TABLE `stream_roulette_options` (
  `id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `cost_zafiros` int(11) NOT NULL DEFAULT 10,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `stream_roulette_options`
--

INSERT INTO `stream_roulette_options` (`id`, `creator_id`, `option_text`, `cost_zafiros`, `is_enabled`, `created_at`) VALUES
(1, 4, 'z', 10, 1, '2025-10-16 01:40:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stream_sessions`
--

CREATE TABLE `stream_sessions` (
  `idsession` int(11) NOT NULL,
  `idstream` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `viewers_max` int(11) DEFAULT 0,
  `total_watch_seconds` bigint(20) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stream_tip_options`
--

CREATE TABLE `stream_tip_options` (
  `id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `zafiros` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `stream_tip_options`
--

INSERT INTO `stream_tip_options` (`id`, `creator_id`, `zafiros`, `descripcion`, `is_active`) VALUES
(0, 4, 2000, 'hola', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stream_viewers`
--

CREATE TABLE `stream_viewers` (
  `idviewer` int(11) NOT NULL,
  `idsession` int(11) NOT NULL,
  `idusuario` int(11) DEFAULT NULL,
  `anon_hash` varchar(64) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL,
  `watch_seconds` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `stream_viewers`
--
DELIMITER $$
CREATE TRIGGER `trg_stream_viewer_leave` AFTER UPDATE ON `stream_viewers` FOR EACH ROW BEGIN
  IF NEW.left_at IS NOT NULL AND NEW.watch_seconds > 0 THEN
    -- Sumar al total del usuario
    IF NEW.idusuario IS NOT NULL THEN
      UPDATE usuarios
      SET watch_seconds = watch_seconds + NEW.watch_seconds
      WHERE idUsuario = NEW.idusuario;
    END IF;
    
    -- Sumar al total de la sesión
    UPDATE stream_sessions
    SET total_watch_seconds = total_watch_seconds + NEW.watch_seconds
    WHERE idsession = NEW.idsession;
    
    -- Registrar en activity_log
    IF NEW.idusuario IS NOT NULL THEN
      INSERT INTO activity_log (idusuario, accion, detalles)
      VALUES (NEW.idusuario, 'watch_stream', CONCAT('Vio stream (ID sesión=', NEW.idsession, ') ', NEW.watch_seconds, ' segundos'));
    END IF;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscriptions`
--

CREATE TABLE `subscriptions` (
  `idsub` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `zafiros` int(11) NOT NULL,
  `status` enum('active','cancelled','expired') DEFAULT 'active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `renewal_date` date DEFAULT NULL,
  `cancellation_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subscriptions`
--

INSERT INTO `subscriptions` (`idsub`, `subscriber_id`, `creator_id`, `zafiros`, `status`, `started_at`, `renewal_date`, `cancellation_date`) VALUES
(1, 1, 3, 5, 'active', '2025-09-22 03:39:17', '2025-10-21', NULL),
(2, 1, 4, 5, 'active', '2025-09-22 03:41:48', '2025-10-21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposnotificaciones`
--

CREATE TABLE `tiposnotificaciones` (
  `idTiposNotificaciones` int(11) NOT NULL,
  `nombreTipo` varchar(60) NOT NULL,
  `mensajeNotificacion` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiposnotificaciones`
--

INSERT INTO `tiposnotificaciones` (`idTiposNotificaciones`, `nombreTipo`, `mensajeNotificacion`) VALUES
(1, 'Like', 'le ha dado like a tu publicación'),
(2, 'Comentario', 'ha comentado tu publicación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

CREATE TABLE `transacciones` (
  `idtransaccion` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `tipo` enum('recharge','purchase','tip','subscription','withdraw') NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `zafiros` int(11) NOT NULL DEFAULT 0,
  `currency` varchar(10) DEFAULT 'USD',
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado','ok','reembolsado') DEFAULT 'pendiente',
  `metodo` varchar(50) DEFAULT 'datafast',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transacciones`
--

INSERT INTO `transacciones` (`idtransaccion`, `idusuario`, `creator_id`, `tipo`, `monto`, `zafiros`, `currency`, `referencia`, `estado`, `metodo`, `metadata`, `created_at`) VALUES
(1, 1, NULL, 'recharge', 5.00, 550, 'USD', NULL, 'aprobado', 'datafast', NULL, '2025-09-19 03:00:32'),
(2, 1, NULL, 'recharge', 5.00, 550, 'USD', NULL, 'aprobado', 'datafast', NULL, '2025-09-19 03:24:16'),
(3, 1, 3, 'subscription', 0.00, 5, 'USD', NULL, 'aprobado', 'datafast', NULL, '2025-09-22 03:39:17'),
(4, 1, 4, 'subscription', 0.00, 5, 'USD', NULL, 'aprobado', 'datafast', NULL, '2025-09-22 03:41:48');

--
-- Disparadores `transacciones`
--
DELIMITER $$
CREATE TRIGGER `trg_transaccion_update` AFTER UPDATE ON `transacciones` FOR EACH ROW BEGIN
  IF NEW.estado = 'aprobado' AND OLD.estado <> 'aprobado' THEN
    
    -- Recarga de zafiros
    IF NEW.tipo = 'recharge' THEN
      UPDATE usuarios
      SET saldo_zafiros = saldo_zafiros + NEW.zafiros
      WHERE idUsuario = NEW.idusuario;
    END IF;
    
    -- Compra / propina / suscripción
    IF NEW.tipo IN ('purchase','tip','subscription') THEN
      -- Descontar al comprador
      UPDATE usuarios
      SET saldo_zafiros = saldo_zafiros - NEW.zafiros
      WHERE idUsuario = NEW.idusuario;
      
      -- Acreditar a la creadora (si aplica)
      IF NEW.creator_id IS NOT NULL THEN
        UPDATE usuarios
        SET saldo_zafiros = saldo_zafiros + NEW.zafiros
        WHERE idUsuario = NEW.creator_id;
      END IF;
    END IF;
    
    -- Retiro (descontar saldo_retirable)
    IF NEW.tipo = 'withdraw' THEN
      UPDATE usuarios
      SET saldo_retirable = GREATEST(saldo_retirable - NEW.monto, 0)
      WHERE idUsuario = NEW.idusuario;
    END IF;
    
    -- Registrar en activity_log
    INSERT INTO activity_log (idusuario, accion, detalles)
    VALUES (NEW.idusuario, 'transaccion', CONCAT('Transacción aprobada tipo ', NEW.tipo, ' por ', NEW.monto, ' USD / ', NEW.zafiros, ' zafiros'));
    
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_financieras`
--

CREATE TABLE `transacciones_financieras` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_usuario_origen` int(11) DEFAULT NULL,
  `tipo` enum('ingreso_suscripcion','ingreso_propina','ingreso_chat','retiro_aprobado','ajuste_admin_add','ajuste_admin_sub','compra_contenido') NOT NULL,
  `monto_zafiros` int(11) NOT NULL,
  `monto_usd_equivalente` decimal(10,2) NOT NULL,
  `comision_plataforma_usd` decimal(10,2) NOT NULL,
  `monto_neto_usd` decimal(10,2) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_referencia` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_achievements`
--

CREATE TABLE `user_achievements` (
  `idua` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idachievement` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_blocks`
--

CREATE TABLE `user_blocks` (
  `idblock` int(11) NOT NULL,
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_reports`
--

CREATE TABLE `user_reports` (
  `idreport` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_id` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `estado` enum('pendiente','revisado','accionado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_sessions`
--

CREATE TABLE `user_sessions` (
  `idsession` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` enum('admin','creadora','espectador','usuario') NOT NULL DEFAULT 'usuario',
  `onboarding_creadora` tinyint(1) NOT NULL DEFAULT 0,
  `cuenta_verificada` tinyint(1) NOT NULL DEFAULT 0,
  `nickname` varchar(80) DEFAULT NULL,
  `metodo_pago` varchar(40) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `documento_identidad` varchar(255) DEFAULT NULL,
  `prefer_dark` tinyint(1) NOT NULL DEFAULT 0,
  `twofa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `notify_email` tinyint(1) NOT NULL DEFAULT 1,
  `blocked_countries` text DEFAULT NULL,
  `billing_name` varchar(120) DEFAULT NULL,
  `billing_ruc` varchar(60) DEFAULT NULL,
  `watch_seconds` bigint(20) NOT NULL DEFAULT 0,
  `saldo_zafiros` int(11) NOT NULL DEFAULT 0,
  `saldo_retirable` decimal(12,2) NOT NULL DEFAULT 0.00,
  `xp` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `correo`, `usuario`, `contrasena`, `cedula`, `fecha_nac`, `genero`, `ciudad`, `pais`, `documento`, `fecha_registro`, `rol`, `onboarding_creadora`, `cuenta_verificada`, `nickname`, `metodo_pago`, `foto_perfil`, `documento_identidad`, `prefer_dark`, `twofa_enabled`, `notify_email`, `blocked_countries`, `billing_name`, `billing_ruc`, `watch_seconds`, `saldo_zafiros`, `saldo_retirable`, `xp`) VALUES
(1, 'raullinux.12@gmail.com', 'root', '$2y$10$xzSABhNywsYWfwvKTfvOG.Cr6hAVSDFGqqY9bkmTJtukop3LGa8oO', '0955262951', '2003-07-09', '', 'Guayaquil', 'Ecuador', NULL, '2025-08-31 20:01:17', 'creadora', 1, 0, NULL, 'transferencia', NULL, '/ENYOOI/public/uploads/doc_1_1756687203.png', 0, 0, 1, NULL, NULL, NULL, 0, 1090, 0.00, 0),
(2, 'bryan.tirado0819@gmail.com', 'Abby_21', '$2y$10$XKvYwEpAJiiwLUksbgirb.d8VKzSNI5rK81Y9APbJzO7mdocrU0m2', '0955262951', '2003-08-20', '', 'Guayaquil', 'Ecuador', NULL, '2025-09-01 00:53:34', 'espectador', 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 0, 0, 0.00, 0),
(3, 'gdavidb.82@gmail.com', 'Bryda', '$2y$10$5BRzNCG/hWTeTAh.Msn/Ve3.I4oVi/mX6ypfACsBI/sdwHmjGtLQa', '0955262951', '2003-03-29', '', 'Guayaquil', 'Ecuador', NULL, '2025-09-07 02:13:05', 'creadora', 1, 1, NULL, 'transferencia', NULL, '/ENYOOI/public/uploads/doc_3_1757211287.png', 0, 0, 1, NULL, NULL, NULL, 0, 5, 0.00, 0),
(4, 'gdb.82@hotmail.com', 'user2', '$2y$10$MXfxof/ThbWxWmxi6J0O0.6kq0xYNHLLFkKfAO.quzBPivXSN0n0K', '0918780685', '1986-06-02', '', 'Guayaquil', 'Ecuador', NULL, '2025-09-07 18:15:59', 'creadora', 1, 0, NULL, 'transferencia', NULL, '/ENYOOI/public/uploads/doc_4_1757276814.jpg', 0, 0, 1, NULL, NULL, NULL, 0, 5, 0.00, 0),
(5, 'desarrollador@enyooi.com', 'Federico', '$2y$10$r0bmljY.6EsXQfmly/v.gOpJxTO4L/dh1s35u0ZxX4cP83A3ICW2W', '0959348194', '2001-01-01', '', 'Guayaquil', 'Ecuador', NULL, '2025-09-07 20:32:53', 'espectador', 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 0, 0, 0.00, 0),
(6, 'erickgtigrero.21@gmail.com', 'usuario', '$2y$10$I9sBGFP9WOMC0svyypcFquFljHJo9Zz3.Hg/m1koMdT2g2g2zkV/O', '0918780685', '2001-09-08', '', 'Guayaquil', 'Ecuador', NULL, '2025-09-09 03:15:27', 'espectador', 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, NULL, NULL, NULL, 0, 0, 0.00, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `type` enum('email_change','password_reset','account_verify') NOT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `user_id`, `code`, `type`, `new_value`, `expires_at`, `created_at`) VALUES
(9, 2, '119834', 'password_reset', '$2y$10$NZVtYu9ofCHZW97y9imW4uLykEZoH9Ae2ksoJumPzwm193qCMERUy', '2025-09-05 22:29:31', '2025-09-06 03:14:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `withdrawals`
--

CREATE TABLE `withdrawals` (
  `idwithdraw` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `estado` enum('pendiente','procesando','aprobado','rechazado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ws_tokens`
--

CREATE TABLE `ws_tokens` (
  `token` varchar(128) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`idachievement`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `idx_activity_user` (`idusuario`),
  ADD KEY `idx_activity_accion` (`accion`),
  ADD KEY `idx_activity_fecha` (`created_at`);

--
-- Indices de la tabla `chats_desbloqueados`
--
ALTER TABLE `chats_desbloqueados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `desbloqueo_unico` (`id_usuario`,`id_creadora`),
  ADD KEY `id_creadora` (`id_creadora`);

--
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`idchat`),
  ADD KEY `idx_chat_stream` (`idstream`),
  ADD KEY `idx_chat_user` (`idusuario`),
  ADD KEY `idx_chat_fecha` (`created_at`);

--
-- Indices de la tabla `codigos_verificacion_retiro`
--
ALTER TABLE `codigos_verificacion_retiro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_codigo_creadora` (`id_creadora`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`idComentario`),
  ADD KEY `idx_comentarios_pub` (`idPublicacion`),
  ADD KEY `idx_comentarios_user` (`idUsuario`),
  ADD KEY `idx_comentarios_fecha` (`fechaComentario`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`idcompra`),
  ADD KEY `compras_contenido_fk` (`idcontenido`),
  ADD KEY `compras_menu_fk` (`idmenu`),
  ADD KEY `idx_compras_usuario` (`idusuario`),
  ADD KEY `idx_compras_creadora` (`idcreadora`),
  ADD KEY `idx_compras_fecha` (`created_at`);

--
-- Indices de la tabla `contenido`
--
ALTER TABLE `contenido`
  ADD PRIMARY KEY (`idcontenido`),
  ADD KEY `idx_contenido_creadora` (`idcreadora`),
  ADD KEY `idx_contenido_privado` (`privado`);

--
-- Indices de la tabla `contenido_desbloqueado`
--
ALTER TABLE `contenido_desbloqueado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `desbloqueo_unico` (`id_usuario`,`id_publicacion`),
  ADD KEY `id_publicacion` (`id_publicacion`);

--
-- Indices de la tabla `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`idreport`),
  ADD KEY `content_reports_reporter_fk` (`reporter_id`),
  ADD KEY `idx_content_reports_pub` (`idpublicacion`),
  ADD KEY `idx_content_reports_com` (`idcomentario`);

--
-- Indices de la tabla `daily_stats`
--
ALTER TABLE `daily_stats`
  ADD PRIMARY KEY (`idstat`),
  ADD UNIQUE KEY `uq_daily_stats_fecha` (`fecha`);

--
-- Indices de la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`idverif`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_emailverif_user` (`idusuario`),
  ADD KEY `idx_emailverif_expires` (`expires_at`);

--
-- Indices de la tabla `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`idrate`),
  ADD UNIQUE KEY `uq_currency` (`currency`);

--
-- Indices de la tabla `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_follow` (`follower_id`,`followed_id`),
  ADD KEY `idx_follow_followed` (`followed_id`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `invoices_tx_fk` (`transaction_id`),
  ADD KEY `idx_invoices_user` (`user_id`);

--
-- Indices de la tabla `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_leaderboard` (`period`,`metric`,`idusuario`),
  ADD KEY `idx_leaderboard_metric` (`metric`),
  ADD KEY `leaderboard_user_fk` (`idusuario`);

--
-- Indices de la tabla `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`idlevel`),
  ADD UNIQUE KEY `level_number` (`level_number`);

--
-- Indices de la tabla `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`idLike`),
  ADD UNIQUE KEY `uq_like_usuario_publicacion` (`idUsuario`,`idPublicacion`),
  ADD KEY `likes_publicacion_fk` (`idPublicacion`),
  ADD KEY `idx_likes_fecha` (`fechaLike`);

--
-- Indices de la tabla `lovense_tip_options`
--
ALTER TABLE `lovense_tip_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lovense_options_creadora` (`creator_id`);

--
-- Indices de la tabla `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`idmedia`),
  ADD KEY `idx_media_creadora` (`idcreadora`),
  ADD KEY `idx_media_tipo` (`tipo`),
  ADD KEY `idx_media_privado` (`privado`);

--
-- Indices de la tabla `media_thumbs`
--
ALTER TABLE `media_thumbs`
  ADD PRIMARY KEY (`idthumb`),
  ADD KEY `idx_thumbs_media` (`idmedia`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`idMensaje`),
  ADD KEY `idx_mensajes_remitente` (`remitente_id`),
  ADD KEY `idx_mensajes_destinatario` (`destinatario_id`),
  ADD KEY `idx_mensajes_fecha` (`fechaMensaje`);

--
-- Indices de la tabla `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`idmenu`),
  ADD KEY `idx_menus_creadora` (`idcreadora`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`idNotificacion`),
  ADD KEY `notificaciones_tipo_fk` (`tipoNotificacion`),
  ADD KEY `notificaciones_publicacion_fk` (`idPublicacion`),
  ADD KEY `idx_notif_usuario` (`idUsuario`),
  ADD KEY `idx_notif_actor` (`usuarioAccion`),
  ADD KEY `idx_notif_fecha` (`fechaNotificacion`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`idreset`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_pwreset_user` (`idusuario`),
  ADD KEY `idx_pwreset_expires` (`expires_at`);

--
-- Indices de la tabla `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`idusuario`);

--
-- Indices de la tabla `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`idpromo`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  ADD PRIMARY KEY (`idredemp`),
  ADD UNIQUE KEY `uq_redemption` (`idpromo`,`idusuario`),
  ADD KEY `promo_redemp_user_fk` (`idusuario`);

--
-- Indices de la tabla `propinas`
--
ALTER TABLE `propinas`
  ADD PRIMARY KEY (`idpropina`),
  ADD KEY `idx_propinas_usuario` (`idusuario`),
  ADD KEY `idx_propinas_creadora` (`idcreadora`),
  ADD KEY `idx_propinas_fecha` (`created_at`),
  ADD KEY `idx_propinas_stream` (`idstream`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`idPublicacion`),
  ADD KEY `idx_publicaciones_usuario` (`idUsuarioPublico`),
  ADD KEY `idx_publicaciones_fecha` (`fechaPublicacion`);

--
-- Indices de la tabla `solicitudes_retiro`
--
ALTER TABLE `solicitudes_retiro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_retiro_creadora` (`id_creadora`);

--
-- Indices de la tabla `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`idstream`),
  ADD UNIQUE KEY `stream_key` (`stream_key`),
  ADD KEY `idx_streams_creadora` (`creator_id`),
  ADD KEY `idx_streams_estado` (`estado`);

--
-- Indices de la tabla `stream_objectives`
--
ALTER TABLE `stream_objectives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stream_objectives_stream_fk` (`idstream`);

--
-- Indices de la tabla `stream_roulette_options`
--
ALTER TABLE `stream_roulette_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roulette_creadora_fk` (`creator_id`);

--
-- Indices de la tabla `stream_sessions`
--
ALTER TABLE `stream_sessions`
  ADD PRIMARY KEY (`idsession`),
  ADD KEY `idx_stream_sessions_stream` (`idstream`),
  ADD KEY `idx_stream_sessions_started` (`started_at`);

--
-- Indices de la tabla `stream_tip_options`
--
ALTER TABLE `stream_tip_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tip_options_creadora` (`creator_id`);

--
-- Indices de la tabla `stream_viewers`
--
ALTER TABLE `stream_viewers`
  ADD PRIMARY KEY (`idviewer`),
  ADD KEY `idx_stream_viewers_session` (`idsession`),
  ADD KEY `idx_stream_viewers_user` (`idusuario`);

--
-- Indices de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`idsub`),
  ADD UNIQUE KEY `uq_subscription` (`subscriber_id`,`creator_id`),
  ADD KEY `idx_subs_creator` (`creator_id`),
  ADD KEY `idx_subs_status` (`status`);

--
-- Indices de la tabla `tiposnotificaciones`
--
ALTER TABLE `tiposnotificaciones`
  ADD PRIMARY KEY (`idTiposNotificaciones`),
  ADD UNIQUE KEY `uq_tipos_nombre` (`nombreTipo`);

--
-- Indices de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`idtransaccion`),
  ADD KEY `transacciones_creator_fk` (`creator_id`),
  ADD KEY `idx_transacciones_user` (`idusuario`),
  ADD KEY `idx_transacciones_tipo` (`tipo`),
  ADD KEY `idx_transacciones_estado` (`estado`),
  ADD KEY `idx_transacciones_fecha` (`created_at`);

--
-- Indices de la tabla `transacciones_financieras`
--
ALTER TABLE `transacciones_financieras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaccion_usuario` (`id_usuario`);

--
-- Indices de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`idua`),
  ADD UNIQUE KEY `uq_user_achievement` (`idusuario`,`idachievement`),
  ADD KEY `ua_ach_fk` (`idachievement`),
  ADD KEY `idx_ua_granted` (`granted_at`);

--
-- Indices de la tabla `user_blocks`
--
ALTER TABLE `user_blocks`
  ADD PRIMARY KEY (`idblock`),
  ADD UNIQUE KEY `uq_block` (`blocker_id`,`blocked_id`),
  ADD KEY `user_blocks_blocked_fk` (`blocked_id`);

--
-- Indices de la tabla `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`idreport`),
  ADD KEY `idx_user_reports_reporter` (`reporter_id`),
  ADD KEY `idx_user_reports_reported` (`reported_id`);

--
-- Indices de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`idsession`),
  ADD KEY `idx_sessions_user` (`idusuario`),
  ADD KEY `idx_sessions_login` (`login_at`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`),
  ADD UNIQUE KEY `uq_usuarios_usuario` (`usuario`);

--
-- Indices de la tabla `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`idwithdraw`),
  ADD KEY `idx_withdraw_user` (`idusuario`),
  ADD KEY `idx_withdraw_estado` (`estado`);

--
-- Indices de la tabla `ws_tokens`
--
ALTER TABLE `ws_tokens`
  ADD PRIMARY KEY (`token`),
  ADD KEY `idx_ws_tokens_user` (`idusuario`),
  ADD KEY `idx_ws_tokens_expires` (`expires_at`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `achievements`
--
ALTER TABLE `achievements`
  MODIFY `idachievement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `idlog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de la tabla `chats_desbloqueados`
--
ALTER TABLE `chats_desbloqueados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `idchat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `codigos_verificacion_retiro`
--
ALTER TABLE `codigos_verificacion_retiro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `idComentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `idcompra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contenido`
--
ALTER TABLE `contenido`
  MODIFY `idcontenido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contenido_desbloqueado`
--
ALTER TABLE `contenido_desbloqueado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `idreport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `daily_stats`
--
ALTER TABLE `daily_stats`
  MODIFY `idstat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `idverif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `idrate` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `levels`
--
ALTER TABLE `levels`
  MODIFY `idlevel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `likes`
--
ALTER TABLE `likes`
  MODIFY `idLike` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `lovense_tip_options`
--
ALTER TABLE `lovense_tip_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `media`
--
ALTER TABLE `media`
  MODIFY `idmedia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `media_thumbs`
--
ALTER TABLE `media_thumbs`
  MODIFY `idthumb` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `idMensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `idmenu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `idreset` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `idpromo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  MODIFY `idredemp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propinas`
--
ALTER TABLE `propinas`
  MODIFY `idpropina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `idPublicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `solicitudes_retiro`
--
ALTER TABLE `solicitudes_retiro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `streams`
--
ALTER TABLE `streams`
  MODIFY `idstream` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `stream_objectives`
--
ALTER TABLE `stream_objectives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `stream_roulette_options`
--
ALTER TABLE `stream_roulette_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `stream_sessions`
--
ALTER TABLE `stream_sessions`
  MODIFY `idsession` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `stream_viewers`
--
ALTER TABLE `stream_viewers`
  MODIFY `idviewer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `idsub` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tiposnotificaciones`
--
ALTER TABLE `tiposnotificaciones`
  MODIFY `idTiposNotificaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `idtransaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `transacciones_financieras`
--
ALTER TABLE `transacciones_financieras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `idua` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_blocks`
--
ALTER TABLE `user_blocks`
  MODIFY `idblock` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `idreport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `idsession` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `idwithdraw` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chats_desbloqueados`
--
ALTER TABLE `chats_desbloqueados`
  ADD CONSTRAINT `chats_desbloqueados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_desbloqueados_ibfk_2` FOREIGN KEY (`id_creadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_stream_fk` FOREIGN KEY (`idstream`) REFERENCES `streams` (`idstream`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_publicacion_fk` FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_usuario_fk` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_contenido_fk` FOREIGN KEY (`idcontenido`) REFERENCES `contenido` (`idcontenido`) ON DELETE SET NULL,
  ADD CONSTRAINT `compras_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_menu_fk` FOREIGN KEY (`idmenu`) REFERENCES `menus` (`idmenu`) ON DELETE SET NULL,
  ADD CONSTRAINT `compras_usuario_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `contenido`
--
ALTER TABLE `contenido`
  ADD CONSTRAINT `contenido_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `contenido_desbloqueado`
--
ALTER TABLE `contenido_desbloqueado`
  ADD CONSTRAINT `contenido_desbloqueado_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `contenido_desbloqueado_ibfk_2` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `content_reports_comentario_fk` FOREIGN KEY (`idcomentario`) REFERENCES `comentarios` (`idComentario`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_publicacion_fk` FOREIGN KEY (`idpublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_reports_reporter_fk` FOREIGN KEY (`reporter_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `emailverif_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_followed_fk` FOREIGN KEY (`followed_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_follower_fk` FOREIGN KEY (`follower_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_tx_fk` FOREIGN KEY (`transaction_id`) REFERENCES `transacciones` (`idtransaccion`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_user_fk` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  ADD CONSTRAINT `leaderboard_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_publicacion_fk` FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_usuario_fk` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lovense_tip_options`
--
ALTER TABLE `lovense_tip_options`
  ADD CONSTRAINT `fk_lovense_options_creadora` FOREIGN KEY (`creator_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `media_thumbs`
--
ALTER TABLE `media_thumbs`
  ADD CONSTRAINT `media_thumbs_media_fk` FOREIGN KEY (`idmedia`) REFERENCES `media` (`idmedia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_destinatario_fk` FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_remitente_fk` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_actor_fk` FOREIGN KEY (`usuarioAccion`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_publicacion_fk` FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_tipo_fk` FOREIGN KEY (`tipoNotificacion`) REFERENCES `tiposnotificaciones` (`idTiposNotificaciones`),
  ADD CONSTRAINT `notificaciones_usuario_fk` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `pwreset_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `perfil`
--
ALTER TABLE `perfil`
  ADD CONSTRAINT `perfil_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `promo_redemptions`
--
ALTER TABLE `promo_redemptions`
  ADD CONSTRAINT `promo_redemp_promo_fk` FOREIGN KEY (`idpromo`) REFERENCES `promo_codes` (`idpromo`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_redemp_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `propinas`
--
ALTER TABLE `propinas`
  ADD CONSTRAINT `propinas_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `propinas_stream_fk` FOREIGN KEY (`idstream`) REFERENCES `streams` (`idstream`) ON DELETE SET NULL,
  ADD CONSTRAINT `propinas_usuario_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_usuario_fk` FOREIGN KEY (`idUsuarioPublico`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_retiro`
--
ALTER TABLE `solicitudes_retiro`
  ADD CONSTRAINT `fk_retiro_creadora` FOREIGN KEY (`id_creadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `streams`
--
ALTER TABLE `streams`
  ADD CONSTRAINT `streams_creadora_fk` FOREIGN KEY (`creator_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stream_objectives`
--
ALTER TABLE `stream_objectives`
  ADD CONSTRAINT `stream_objectives_stream_fk` FOREIGN KEY (`idstream`) REFERENCES `streams` (`idstream`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stream_roulette_options`
--
ALTER TABLE `stream_roulette_options`
  ADD CONSTRAINT `roulette_creadora_fk` FOREIGN KEY (`creator_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stream_sessions`
--
ALTER TABLE `stream_sessions`
  ADD CONSTRAINT `stream_sessions_stream_fk` FOREIGN KEY (`idstream`) REFERENCES `streams` (`idstream`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stream_viewers`
--
ALTER TABLE `stream_viewers`
  ADD CONSTRAINT `stream_viewers_session_fk` FOREIGN KEY (`idsession`) REFERENCES `stream_sessions` (`idsession`) ON DELETE CASCADE,
  ADD CONSTRAINT `stream_viewers_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subs_creator_fk` FOREIGN KEY (`creator_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `subs_subscriber_fk` FOREIGN KEY (`subscriber_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD CONSTRAINT `transacciones_creator_fk` FOREIGN KEY (`creator_id`) REFERENCES `usuarios` (`idUsuario`),
  ADD CONSTRAINT `transacciones_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `transacciones_financieras`
--
ALTER TABLE `transacciones_financieras`
  ADD CONSTRAINT `fk_transaccion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `ua_ach_fk` FOREIGN KEY (`idachievement`) REFERENCES `achievements` (`idachievement`) ON DELETE CASCADE,
  ADD CONSTRAINT `ua_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_blocks`
--
ALTER TABLE `user_blocks`
  ADD CONSTRAINT `user_blocks_blocked_fk` FOREIGN KEY (`blocked_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_blocks_blocker_fk` FOREIGN KEY (`blocker_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_reported_fk` FOREIGN KEY (`reported_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_reporter_fk` FOREIGN KEY (`reporter_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ws_tokens`
--
ALTER TABLE `ws_tokens`
  ADD CONSTRAINT `ws_tokens_user_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_daily_stats` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-25 07:05:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_generate_daily_stats(CURDATE() - INTERVAL 1 DAY)$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_leaderboard_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-25 07:10:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_refresh_leaderboard_daily(CURDATE() - INTERVAL 1 DAY)$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_cleanup_tokens_hourly` ON SCHEDULE EVERY 1 HOUR STARTS '2025-08-25 17:20:29' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_cleanup_expired_tokens_sessions()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_deactivate_subscriptions_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-25 07:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_deactivate_expired_subscriptions()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_finalize_stale_streams_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-25 08:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_finalize_stale_streams()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
