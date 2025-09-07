<?php
// app/includes/navbar_zafiros.php - include this in your header where $mysqli and session are available
if (session_status() === PHP_SESSION_NONE) session_start();
$uid = $_SESSION['logueando'] ?? null;
$saldo_zafiros = 0;
if ($uid && isset($mysqli)) {
    $q = $mysqli->prepare('SELECT saldo_zafiros FROM usuarios WHERE idusuario=? LIMIT 1');
    $q->bind_param('i',$uid); $q->execute(); $r = $q->get_result();
    if ($r && $row = $r->fetch_assoc()) $saldo_zafiros = (int)$row['saldo_zafiros'];
}
?>
<div class="flex items-center gap-4">
  <a href="/app/view/pages/recargar.php" class="text-sm px-3 py-1 rounded-full bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] shadow">
    💎 <?php echo number_format($saldo_zafiros); ?> Zafiros
  </a>
</div>
