<?php
// app/view/pages/recargar.php
session_start();
require_once __DIR__ . '/../../api/db_connect.php';
if (empty($_SESSION['logueando'])) { header('Location: /public/login.php'); exit; }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Recargar Zafiros — ENYOOI</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{--accent:#ff4fa3;--accent-2:#7c5cff}
html,body{height:100%;margin:0;padding:0;font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),radial-gradient(1000px 600px at 90% 80%,rgba(255,79,163,0.08),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8}
.blob{position:absolute;border-radius:9999px;filter:blur(42px);opacity:0.22;mix-blend-mode:screen;animation:float 14s ease-in-out infinite;z-index:-1}
.blob.b1{width:420px;height:420px;left:-80px;top:-80px;background:linear-gradient(135deg,var(--accent),#d46fcb)}
.blob.b2{width:300px;height:300px;right:-40px;top:40px;background:linear-gradient(135deg,var(--accent-2),#68a6ff);animation-delay:3s}
.blob.b3{width:360px;height:360px;left:45%;bottom:-100px;background:linear-gradient(135deg,#ffa3d9,#a88bff);animation-delay:6s}
.card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);border-radius:12px;padding:18px}
.btn{background:linear-gradient(135deg,var(--accent),var(--accent-2));padding:10px 14px;border-radius:10px;color:#071022;font-weight:700}
</style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
<?php include __DIR__ . '/../includes/navbar_zafiros.php'; ?>
<main class="min-h-screen flex items-center justify-center p-6">
  <div class="max-w-2xl w-full card">
    <h1 class="text-2xl font-bold mb-2">Recargar Zafiros</h1>
    <p class="text-blue-200 mb-4">Elige una recarga y sigue al proceso de pago seguro con Datafast.</p>
    <form id="recargaForm" class="grid grid-cols-1 gap-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <label class="p-3 rounded-xl bg-white/3 border border-white/6 cursor-pointer">
          <input type="radio" name="package" value="100" data-price="10" class="hidden">
          <div class="font-semibold">100 Zafiros</div>
          <div class="text-sm text-blue-200">$10</div>
        </label>
        <label class="p-3 rounded-xl bg-white/3 border border-white/6 cursor-pointer">
          <input type="radio" name="package" value="250" data-price="20" class="hidden">
          <div class="font-semibold">250 Zafiros</div>
          <div class="text-sm text-blue-200">$20</div>
        </label>
        <label class="p-3 rounded-xl bg-white/3 border border-white/6 cursor-pointer">
          <input type="radio" name="package" value="600" data-price="45" class="hidden">
          <div class="font-semibold">600 Zafiros</div>
          <div class="text-sm text-blue-200">$45</div>
        </label>
      </div>

      <div class="flex items-center gap-3">
        <label class="label">Monedero</label>
        <div id="selected" class="ml-auto text-sm">Selecciona un paquete</div>
      </div>

      <button id="payBtn" class="btn w-full">Pagar con Datafast</button>
    </form>
    <div id="msg" class="text-sm mt-3"></div>
  </div>
</main>

<script>
document.querySelectorAll('input[name="package"]').forEach(r=>{
  r.addEventListener('change', ()=>{
    const sel = document.querySelector('input[name="package"]:checked');
    if (sel) {
      document.getElementById('selected').textContent = sel.value + ' Zafiros — $' + sel.dataset.price;
    }
  });
});

document.getElementById('recargaForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const sel = document.querySelector('input[name="package"]:checked');
  const msg = document.getElementById('msg');
  if (!sel) { msg.textContent = 'Selecciona un paquete'; return; }
  const zafiros = sel.value;
  const monto = sel.dataset.price;

  msg.textContent = 'Iniciando pago...';
  const fd = new FormData();
  fd.append('zafiros', zafiros);
  fd.append('monto', monto);

  try {
    const res = await fetch('/api/create_payment.php', { method: 'POST', body: fd, credentials:'include' });
    const j = await res.json();
    if (!j.ok) { msg.textContent = 'Error: ' + (j.error || 'no se pudo crear pago'); return; }
    // redirigir al checkout real (o al simulador en sandbox)
    window.location.href = j.redirect;
  } catch (err) { msg.textContent = 'Error de red'; }
});
</script>
</body>
</html>
