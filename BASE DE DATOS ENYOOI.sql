-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-08-2025 a las 00:11:11
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
-- Base de datos: `jade`
CREATE DATABASE IF NOT EXISTS enyooi
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE enyooi;


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
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `idComentario` int(11) NOT NULL,
  `idPublicacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `contenidoComentario` longtext DEFAULT NULL,
  `fechaComentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Estructura de tabla para la tabla `likes`
--

CREATE TABLE `likes` (
  `idLike` int(11) NOT NULL,
  `idPublicacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `fechaLike` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `contenido` longtext NOT NULL,
  `fechaMensaje` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `num_likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `streams`
--

CREATE TABLE `streams` (
  `idstream` int(11) NOT NULL,
  `idcreadora` int(11) NOT NULL,
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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` enum('admin','creadora','espectador','usuario') NOT NULL DEFAULT 'usuario',
  `onboarding_creadora` tinyint(1) NOT NULL DEFAULT 0,
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
  `saldo_retirable` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`idchat`),
  ADD KEY `idx_chat_stream` (`idstream`),
  ADD KEY `idx_chat_user` (`idusuario`),
  ADD KEY `idx_chat_fecha` (`created_at`);

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
-- Indices de la tabla `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`idLike`),
  ADD UNIQUE KEY `uq_like_usuario_publicacion` (`idUsuario`,`idPublicacion`),
  ADD KEY `likes_publicacion_fk` (`idPublicacion`),
  ADD KEY `idx_likes_fecha` (`fechaLike`);

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
  ADD KEY `idx_propinas_fecha` (`created_at`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`idPublicacion`),
  ADD KEY `idx_publicaciones_usuario` (`idUsuarioPublico`),
  ADD KEY `idx_publicaciones_fecha` (`fechaPublicacion`);

--
-- Indices de la tabla `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`idstream`),
  ADD UNIQUE KEY `stream_key` (`stream_key`),
  ADD KEY `idx_streams_creadora` (`idcreadora`),
  ADD KEY `idx_streams_estado` (`estado`);

--
-- Indices de la tabla `stream_sessions`
--
ALTER TABLE `stream_sessions`
  ADD PRIMARY KEY (`idsession`),
  ADD KEY `idx_stream_sessions_stream` (`idstream`),
  ADD KEY `idx_stream_sessions_started` (`started_at`);

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
  MODIFY `idlog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `idchat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `idComentario` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `likes`
--
ALTER TABLE `likes`
  MODIFY `idLike` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `idMensaje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `idmenu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `idPublicacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `streams`
--
ALTER TABLE `streams`
  MODIFY `idstream` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `idsub` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tiposnotificaciones`
--
ALTER TABLE `tiposnotificaciones`
  MODIFY `idTiposNotificaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `idtransaccion` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `propinas_usuario_fk` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_usuario_fk` FOREIGN KEY (`idUsuarioPublico`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `streams`
--
ALTER TABLE `streams`
  ADD CONSTRAINT `streams_creadora_fk` FOREIGN KEY (`idcreadora`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

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
