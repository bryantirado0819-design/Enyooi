<?php
// app/view/pages/content.php - muestra detalle de un contenido y permite comprar
session_start();
require_once __DIR__ . '/../../api/db_connect.php';
require_once __DIR__ . '/../includes/csrf.php';
if (empty($_SESSION['logueando'])) { header('Location: /public/login.php'); exit; }
$id = (int) ($_GET['id'] ?? 0);
if (!$id) { echo 'Contenido no válido'; exit; }
$stmt = $mysqli->prepare("SELECT c.*, u.nickname FROM contenido c JOIN usuarios u ON u.idusuario = c.idcreadora WHERE c.idcontenido = ? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { echo 'No encontrado'; exit; }
$c = $res->fetch_assoc();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title><?php echo htmlspecialchars($c['titulo']); ?> - ENYOOI</title>
<script src="https://cdn.tailwindcss.com"></script><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>:root{--accent:#ff4fa3;--accent-2:#7c5cff}body{font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8}</style>
</head><body>
<main class="min-h-screen flex items-center justify-center p-6"><div class="max-w-3xl w-full bg-white/5 rounded-xl p-6">
  <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($c['titulo']); ?></h1>
  <p class="text-sm text-blue-200 mb-3">Por <?php echo htmlspecialchars($c['nickname']); ?> • 💎 <?php echo intval($c['precio_zafiros']); ?></p>
  <p class="mb-4 text-blue-200"><?php echo nl2br(htmlspecialchars($c['descripcion'])); ?></p>
  <div class="flex gap-3">
    <button class="px-4 py-2 bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] rounded" onclick="buy(<?php echo $c['idcontenido']; ?>)">Comprar y ver</button>
    <a href="/app/view/pages/tienda.php" class="px-4 py-2 border rounded">Volver</a>
  </div>
  <div id="msg" class="mt-3 text-sm"></div>
</div></main>
<script>
const CSRF = "<?php echo $ENYOOI_CSRF ?>";
async function buy(id){
  const fd = new FormData(); fd.append('idcontenido', id); fd.append('csrf', CSRF);
  const res = await fetch('/api/buy_content.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  if (j.ok){ window.location.href = j.url; } else document.getElementById('msg').textContent = 'Error: ' + (j.error || j.msg || '');
}
</script>
</body></html>
