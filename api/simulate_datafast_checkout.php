<?php
// api/simulate_datafast_checkout.php
// Este archivo simula la pasarela Datafast para pruebas locales. En producción, el usuario será redirigido a Datafast real.
require_once __DIR__ . '/db_connect.php';
$ref = $_GET['ref'] ?? '';
if (!$ref) { echo 'Referencia inválida'; exit; }
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Simulación Datafast</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head>
<body class="bg-gradient-to-b from-[#020617] to-[#081127] text-white font-sans min-h-screen flex items-center justify-center">
  <div class="bg-white/5 p-8 rounded-2xl max-w-lg w-full">
    <h2 class="text-2xl font-bold mb-3">Simulación de pago - Datafast (Sandbox)</h2>
    <p class="mb-4">Referencia: <strong><?php echo htmlspecialchars($ref); ?></strong></p>
    <form method="post" action="/api/simulate_datafast_callback.php">
      <input type="hidden" name="reference" value="<?php echo htmlspecialchars($ref); ?>"/>
      <div class="space-y-3">
        <label class="block"><span>Método</span><select name="method" class="w-full bg-black/30 rounded p-2"><option value="card">Tarjeta</option><option value="pse">PSE</option></select></label>
        <label class="block"><span>Resultado</span><select name="result" class="w-full bg-black/30 rounded p-2"><option value="approved">Aprobado</option><option value="rejected">Rechazado</option></select></label>
        <div class="flex gap-3"><button class="px-4 py-2 bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] rounded">Enviar resultado</button><a href="/" class="px-4 py-2 border rounded">Cancelar</a></div>
      </div>
    </form>
  </div>
</body>
</html>
