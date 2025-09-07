<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Enyooi — Dashboard Creador</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body{font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),radial-gradient(1000px 600px at 90% 80%,rgba(255,79,163,0.08),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8;}
    .card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);}
    .input{background:#fff;color:#111;border-radius:10px;padding:.5rem .75rem}
  </style>
</head>
<body>
  <header class="p-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-white">🎨 Dashboard Creador</h1>
    <div class="text-sm">Usuario ID: <span id="uid" class="font-semibold">1</span></div>
  </header>

  <main class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="card rounded-2xl p-6">
      <h2 class="text-lg font-semibold">💎 Zafiros y ganancias</h2>
      <p class="mt-2">Zafiros: <b id="zaf">—</b></p>
      <p>USD bruto (1 USD = 7 zafiros): <b id="usd_gross">—</b></p>
      <p>USD neto (descontado % plataforma): <b id="usd_net">—</b></p>
      <button id="export" class="mt-4 px-4 py-2 rounded-lg bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] font-semibold shadow-lg">Exportar PDF</button>
    </section>

    <section class="card rounded-2xl p-6 lg:col-span-2">
      <h2 class="text-lg font-semibold">📈 Ingresos por transmisión</h2>
      <canvas id="chartStreams" class="mt-3"></canvas>
    </section>

    <section class="card rounded-2xl p-6 lg:col-span-2">
      <h2 class="text-lg font-semibold">🌍 Audiencia por país / ciudad</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <canvas id="chartGeo"></canvas>
        <div class="overflow-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="text-left text-blue-200"><th class="py-2 pr-4">Ciudad</th><th class="py-2 pr-4">Visitas</th></tr></thead>
            <tbody id="tbodyCity"></tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="card rounded-2xl p-6">
      <h2 class="text-lg font-semibold">💵 Solicitar Retiro</h2>
      <div class="space-y-2 mt-2">
        <input id="nombre" class="input w-full" placeholder="Nombre">
        <input id="apellido" class="input w-full" placeholder="Apellido">
        <input id="cedula" class="input w-full" placeholder="Cédula">
        <input id="cuenta" class="input w-full" placeholder="Cuenta Bancaria">
        <input id="monto" type="number" min="10" step="0.01" class="input w-full" placeholder="Monto (mínimo $10)">
        <button id="retirar" class="w-full px-4 py-2 rounded-lg bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] font-semibold shadow-lg">Solicitar</button>
        <div id="toast" class="fixed right-4 bottom-4"></div>
      </div>
    </section>

  </main>

<script>
const API = '/app/controllers/CreatorDashboardController.php';
const WITHDRAW_API = '/app/controllers/WithdrawalController.php';
const USER_ID = 1; // reemplaza por ID de sesión real
let CH1, CH2, cache;

function toast(msg,type='ok'){
  const t = document.getElementById('toast');
  const el = document.createElement('div');
  el.className = 'mb-2 px-4 py-3 rounded-lg shadow-lg text-white ' + (type==='err'?'bg-red-500/80':'bg-green-500/80');
  el.textContent = msg; t.appendChild(el); setTimeout(()=>el.remove(),3000);
}

async function load(){
  const j = await (await fetch(API+'?action=getAnalytics&user_id='+USER_ID)).json(); cache = j;
  document.getElementById('zaf').innerText = (j.zafiros||0).toLocaleString();
  document.getElementById('usd_gross').innerText = '$'+(j.usd_gross||0).toFixed(2);
  document.getElementById('usd_net').innerText = '$'+(j.usd_net||0).toFixed(2);

  const labels = (j.streams||[]).map(s=> s.title || ('Stream #'+s.id));
  const data = (j.streams||[]).map(s=> parseFloat((s.earned_zafiros||0)/7.0).toFixed(2));
  CH1 && CH1.destroy();
  CH1 = new Chart(document.getElementById('chartStreams'), { type:'bar', data:{ labels, datasets:[{label:'USD por stream', data}] } });

  const labels2 = (j.geoCountry||[]).map(g=> g.country);
  const data2 = (j.geoCountry||[]).map(g=> g.viewers);
  CH2 && CH2.destroy();
  CH2 = new Chart(document.getElementById('chartGeo'), { type:'doughnut', data:{ labels: labels2, datasets:[{label:'Visitas', data: data2}] } });

  const tb = document.getElementById('tbodyCity'); tb.innerHTML='';
  (j.geoCity||[]).forEach(r=>{
    const tr = document.createElement('tr');
    tr.innerHTML = '<td class="py-2 pr-4">'+r.city+'</td><td class="py-2 pr-4">'+r.viewers+'</td>';
    tb.appendChild(tr);
  });
}

document.getElementById('export').addEventListener('click', async ()=>{
  const chart_geo = document.getElementById('chartGeo').toDataURL('image/png');
  const chart_streams = document.getElementById('chartStreams').toDataURL('image/png');
  const res = await fetch(API+'?action=exportPdf', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ chart_geo, chart_streams, user_id: USER_ID })});
  const blob = await res.blob(); const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href=url; a.download='creator_report.pdf'; a.click();
});

document.getElementById('retirar').addEventListener('click', async ()=>{
  const monto = parseFloat(document.getElementById('monto').value||'0');
  if(!monto || monto<10) return toast('Mínimo $10 para retirar','err');
  const titular = (document.getElementById('nombre').value + ' ' + document.getElementById('apellido').value).trim();
  const cuenta = document.getElementById('cuenta').value.trim();
  const body = { user_id: USER_ID, amount: monto, bank:{ banco:'Banco Pichincha', tipo_cuenta:'Cuenta Corriente', cuenta_bancaria:cuenta, titular } };
  const j = await (await fetch(WITHDRAW_API+'?action=request',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)})).json();
  toast(j.ok?'Solicitud enviada al administrador':'Error: '+(j.error||''), j.ok?'ok':'err');
});

load();
</script>
</body>
</html>
