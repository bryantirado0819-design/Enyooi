<?php
// admin/transactions.php - lista compras y propinas, permite reembolsar compras (restaurar saldos)
session_start();
require_once __DIR__ . '/../api/db_connect.php';
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { header('Location: /public/login.php'); exit; }

$compras = $mysqli->query("SELECT co.idcompra,co.created_at,co.zafiros,co.comision,co.creadora_recibe,co.estado,co.idusuario,u.usuario AS comprador, c.titulo FROM compras co LEFT JOIN usuarios u ON u.idusuario = co.idusuario LEFT JOIN contenido c ON c.idcontenido = co.idcontenido ORDER BY co.created_at DESC LIMIT 200");
$propinas = $mysqli->query("SELECT p.idpropina,p.created_at,p.zafiros,p.comision,p.creadora_recibe,u.usuario AS donante, uc.nickname AS creadora FROM propinas p LEFT JOIN usuarios u ON u.idusuario = p.idusuario LEFT JOIN usuarios uc ON uc.idusuario = p.idcreadora ORDER BY p.created_at DESC LIMIT 200");
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Admin - Transacciones</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 text-gray-900 p-6">
<div class="max-w-6xl mx-auto">
<h1 class="text-2xl font-bold mb-4">Transacciones - Compras y Propinas</h1>
<section class="mb-6">
  <h2 class="text-xl font-semibold mb-2">Compras</h2>
  <div class="bg-white shadow rounded p-4">
    <table class="w-full text-sm">
      <thead><tr><th>ID</th><th>Fecha</th><th>Comprador</th><th>Título</th><th>Zafiros</th><th>Comisión</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php while($r = $compras->fetch_assoc()): ?>
        <tr class="border-t"><td><?php echo $r['idcompra']; ?></td><td><?php echo $r['created_at']; ?></td><td><?php echo htmlspecialchars($r['comprador']); ?></td><td><?php echo htmlspecialchars($r['titulo']); ?></td><td><?php echo intval($r['zafiros']); ?></td><td><?php echo intval($r['comision']); ?></td><td><?php echo $r['estado']; ?></td>
        <td><?php if($r['estado'] === 'ok'): ?><button onclick="refund(<?php echo $r['idcompra']; ?>)" class="px-2 py-1 bg-red-500 text-white rounded">Reembolsar</button><?php else: ?>-<?php endif; ?></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<section>
  <h2 class="text-xl font-semibold mb-2">Propinas</h2>
  <div class="bg-white shadow rounded p-4">
    <table class="w-full text-sm">
      <thead><tr><th>ID</th><th>Fecha</th><th>Donante</th><th>Creadora</th><th>Zafiros</th><th>Comisión</th></tr></thead>
      <tbody>
      <?php while($p = $propinas->fetch_assoc()): ?>
        <tr class="border-t"><td><?php echo $p['idpropina']; ?></td><td><?php echo $p['created_at']; ?></td><td><?php echo htmlspecialchars($p['donante']); ?></td><td><?php echo htmlspecialchars($p['creadora']); ?></td><td><?php echo intval($p['zafiros']); ?></td><td><?php echo intval($p['comision']); ?></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>
</div>

<script>
async function refund(id){
  if(!confirm('¿Confirmar reembolso? Esto revertirá la compra y devolverá zafiros al comprador.')) return;
  const fd = new FormData(); fd.append('idcompra', id);
  const res = await fetch('/api/admin_refund.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  if (j.ok) { alert('Reembolsado'); location.reload(); } else alert('Error: '+(j.error||''));
}
</script>

</body></html>
