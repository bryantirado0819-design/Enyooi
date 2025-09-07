<?php
// app/includes/csrf.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$ENYOOI_CSRF = $_SESSION['csrf'];
?>