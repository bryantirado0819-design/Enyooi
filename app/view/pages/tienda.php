<?php
// app/view/pages/tienda.php
session_start();
require_once __DIR__ . '/../../api/db_connect.php';
require_once __DIR__ . '/../includes/csrf.php';
if (empty($_SESSION['logueando'])) { header('Location: /public/login.php'); exit; }
$uid = (int) $_SESSION['logueando'];
// fetch content and menus
$contents = $mysqli->query("SELECT c.idcontenido,c.titulo,c.precio_zafiros,u.nickname AS creadora FROM contenido c JOIN usuarios u ON u.idusuario = c.idcreadora ORDER BY c.creado_en DESC LIMIT 50");
$menus = $mysqli->query("SELECT m.idmenu,m.titulo,m.precio_zafiros,u.nickname AS creadora FROM menus m JOIN usuarios u ON u.idusuario = m.idcreadora ORDER BY m.creado_en DESC LIMIT 50");
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Tienda — ENYOOI</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{--accent:#ff4fa3;--accent-2:#7c5cff}
html,body{height:100%;margin:0;padding:0;font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),radial-gradient(1000px 600px at 90% 80%,rgba(255,79,163,0.08),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8}
.card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);border-radius:12px;padding:18px}
.btn{background:linear-gradient(135deg,var(--accent),var(--accent-2));padding:8px 12px;border-radius:10px;color:#071022;font-weight:700}
</style>
</head>
<body>
<main class="min-h-screen p-6">
  <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
    <section class="md:col-span-2">
      <div class="card mb-4">
        <h2 class="text-xl font-bold mb-2">Contenido disponible</h2>
        <div class="grid grid-cols-1 gap-3">
          <?php while($c = $contents->fetch_assoc()): ?>
            <div class="p-3 rounded-xl bg-white/3 flex items-center justify-between">
              <div>
                <div class="font-semibold"><?php echo htmlspecialchars($c['titulo']); ?></div>
                <div class="text-sm text-blue-200">Por: <?php echo htmlspecialchars($c['creadora']); ?></div>
              </div>
              <div class="flex items-center gap-3">
                <div class="text-sm">💎 <?php echo intval($c['precio_zafiros']); ?></div>
                <button class="btn" onclick="buyContent(<?php echo intval($c['idcontenido']); ?>)">Comprar</button>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <div class="card">
        <h2 class="text-xl font-bold mb-2">Menús personalizados</h2>
        <div class="grid grid-cols-1 gap-3">
          <?php while($m = $menus->fetch_assoc()): ?>
            <div class="p-3 rounded-xl bg-white/3 flex items-center justify-between">
              <div>
                <div class="font-semibold"><?php echo htmlspecialchars($m['titulo']); ?></div>
                <div class="text-sm text-blue-200">Por: <?php echo htmlspecialchars($m['creadora']); ?></div>
              </div>
              <div class="flex items-center gap-3">
                <div class="text-sm">💎 <?php echo intval($m['precio_zafiros']); ?></div>
                <button class="btn" onclick="buyMenu(<?php echo intval($m['idmenu']); ?>)">Comprar</button>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </section>

    <aside class="card">
      <h3 class="text-lg font-bold mb-2">Tu saldo</h3>
      <div id="wallet" class="text-2xl font-extrabold mb-4">Cargando...</div>
      <h4 class="font-semibold mb-1">Propina rápida</h4>
      <div class="grid grid-cols-2 gap-2">
        <button class="btn" onclick="sendTip(20)">💎20</button>
        <button class="btn" onclick="sendTip(50)">💎50</button>
        <button class="btn" onclick="sendTip(100)">💎100</button>
        <button class="btn" onclick="sendTip(250)">💎250</button>
      </div>
    </aside>
  </div>
</main>

<script>
const CSRF = "<?php echo $ENYOOI_CSRF ?>";
async function loadWallet(){
  const res = await fetch('/api/get_wallet.php', { credentials:'include' });
  const j = await res.json();
  if (j.ok) document.getElementById('wallet').textContent = j.saldo + ' Zafiros';
  else document.getElementById('wallet').textContent = 'Error';
}
async function buyContent(id){
  if (!confirm('Confirmar compra?')) return;
  const fd = new FormData(); fd.append('idcontenido', id); fd.append('csrf', CSRF);
  const res = await fetch('/api/buy_content.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  if (j.ok){ alert('Compra realizada'); if (j.ruta) window.open(j.ruta,'_blank'); loadWallet(); }
  else alert('Error: '+(j.error||j.msg||''));
}
async function buyMenu(id){
  if (!confirm('Confirmar compra?')) return;
  const fd = new FormData(); fd.append('idmenu', id); fd.append('csrf', CSRF);
  const res = await fetch('/api/buy_menu.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  if (j.ok){ alert('Compra realizada'); loadWallet(); } else alert('Error: '+(j.error||j.msg||''));
}
async function sendTip(amount){
  const idcreadora = prompt('ID de la creadora a la que deseas enviar la propina (consulta su perfil)');
  if (!idcreadora) return;
  const fd = new FormData(); fd.append('idcreadora', idcreadora); fd.append('zafiros', amount); fd.append('csrf', CSRF);
  const res = await fetch('/api/tip.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  if (j.ok){ alert('Propina enviada'); loadWallet(); } else alert('Error: '+(j.error||j.msg||''));
}
loadWallet();
</script>
</body>
</html>
