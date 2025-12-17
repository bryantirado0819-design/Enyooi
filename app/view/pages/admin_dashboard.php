<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Enyooi — Dashboard Administrador</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body{font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),radial-gradient(1000px 600px at 90% 80%,rgba(255,79,163,0.08),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8;}
    .blob{position:absolute;border-radius:9999px;filter:blur(42px);opacity:0.22;mix-blend-mode:screen;animation:float 14s ease-in-out infinite;z-index:-1;}
    .blob.b1{width:420px;height:420px;left:-80px;top:-80px;background:linear-gradient(135deg,var(--accent),#d46fcb);} .blob.b2{width:300px;height:300px;right:-40px;top:40px;background:linear-gradient(135deg,var(--accent-2),#68a6ff);animation-delay:3s}
    .blob.b3{width:360px;height:360px;left:45%;bottom:-100px;background:linear-gradient(135deg,#ffa3d9,#a88bff);animation-delay:6s}
    @keyframes float{0%{transform:translateY(0)}50%{transform:translateY(-30px)}100%{transform:translateY(0)}}
    .card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);}
    .input{background:#fff;color:#111;border-radius:10px;padding:.5rem .75rem}
  </style>
</head>
<body>
  <div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>

  <header class="p-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-white">🛠️ Dashboard Administrador</h1>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] font-semibold shadow-lg" id="btnExport">Exportar PDF</button>
    </div>
  </header>

  <main class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="card rounded-2xl p-6">
      <h2 class="text-lg font-semibold">🧑‍🤝‍🧑 Registros (7 días)</h2>
      <canvas id="chartReg" class="mt-4"></canvas>
    </div>
    <div class="card rounded-2xl p-6">
      <h2 class="text-lg font-semibold">🌍 Logins de hoy por país</h2>
      <canvas id="chartLogin" class="mt-4"></canvas>
    </div>
    <div class="card rounded-2xl p-6">
      <h2 class="text-lg font-semibold">💎 Zafiros globales</h2>
      <p class="text-4xl font-extrabold mt-2"><span id="totalZ">—</span></p>
      <p class="text-sm text-blue-200 mt-1">(7 zafiros = 1 USD)</p>
      <div class="mt-6">
        <h3 class="font-semibold mb-2">⚖️ Split Creador/Plataforma</h3>
        <div class="flex items-center gap-2">
          <input id="split" type="number" class="input w-24" min="0" max="100" step="1">
          <span>% para el Creador</span>
          <button id="saveSplit" class="ml-auto px-3 py-2 rounded bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff]">Guardar</button>
        </div>
        <p class="text-xs text-blue-200 mt-1">La plataforma retiene (100 - split)%</p>
      </div>
    </div>

    <div class="card rounded-2xl p-6 md:col-span-2 lg:col-span-3">
      <h2 class="text-lg font-semibold">👥 Configuración de cuentas</h2>
      <div class="grid md:grid-cols-5 gap-3 mt-3">
        <input id="uid" class="input" type="number" placeholder="ID Usuario">
        <input id="email" class="input" type="email" placeholder="Nuevo correo">
        <input id="username" class="input" type="text" placeholder="Nuevo usernick">
        <input id="password" class="input" type="password" placeholder="Nueva contraseña">
        <select id="badge" class="input">
          <option value="none">Sin verificación</option>
          <option value="check">Verificado ✓</option>
          <option value="fire">Verificado 🔥</option>
        </select>
      </div>
      <div class="flex gap-3 mt-3">
        <input id="zafiros" class="input w-48" type="number" placeholder="Saldo de Zafiros">
        <button id="btnUpdateUser" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] font-semibold shadow-lg">Actualizar Cuenta</button>
        <button id="btnSetZafiros" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] font-semibold shadow-lg">Guardar Saldo</button>
      </div>
      <div id="toast" class="fixed right-4 bottom-4"></div>
    </div>
  </main>

<script>
const API = '/app/controllers/AdminDashboardController.php';

function toast(msg,type='ok'){
  const t = document.getElementById('toast');
  const el = document.createElement('div');
  el.className = 'mb-2 px-4 py-3 rounded-lg shadow-lg text-white ' + (type==='err'?'bg-red-500/80':'bg-green-500/80');
  el.textContent = msg; t.appendChild(el); setTimeout(()=>el.remove(),3000);
}

let chartReg, chartLogin, cached;

async function loadData(){
  const res = await fetch(API+'?action=getAnalytics');
  const j = await res.json(); cached = j;
  document.getElementById('totalZ').innerText = (j.globalZafiros||0).toLocaleString();
  document.getElementById('split').value = j.revenueSplitCreator ?? 60;

  const labels = (j.registrations||[]).map(r=>r.d);
  const data = (j.registrations||[]).map(r=>parseInt(r.c));
  chartReg && chartReg.destroy();
  chartReg = new Chart(document.getElementById('chartReg'), { type:'line', data:{ labels, datasets:[{label:'Registros', data, borderColor:'#ff4fa3', backgroundColor:'rgba(255,79,163,0.2)', fill:true, tension:.3}] }});

  const labels2 = (j.todaysLogins||[]).map(r=>r.country);
  const data2 = (j.todaysLogins||[]).map(r=>parseInt(r.c));
  chartLogin && chartLogin.destroy();
  chartLogin = new Chart(document.getElementById('chartLogin'), { type:'bar', data:{ labels: labels2, datasets:[{label:'Usuarios', data: data2, backgroundColor:'#7c5cff'}] }});
}

document.getElementById('btnUpdateUser').addEventListener('click', async ()=>{
  const body = {
    id: parseInt(document.getElementById('uid').value||0),
    email: document.getElementById('email').value||undefined,
    username: document.getElementById('username').value||undefined,
    password: document.getElementById('password').value||undefined,
    verify_badge: document.getElementById('badge').value
  };
  const res = await fetch(API+'?action=updateUser', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
  const j = await res.json(); toast(j.ok?'Cuenta actualizada':'Error',''+(j.ok?'ok':'err'));
});

document.getElementById('btnSetZafiros').addEventListener('click', async ()=>{
  const body = { id: parseInt(document.getElementById('uid').value||0), balance: parseInt(document.getElementById('zafiros').value||0) };
  const res = await fetch(API+'?action=setZafiros', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
  const j = await res.json(); toast(j.ok?'Saldo actualizado':'Error',''+(j.ok?'ok':'err'));
});

document.getElementById('saveSplit').addEventListener('click', async ()=>{
  const pct = parseFloat(document.getElementById('split').value||60);
  const res = await fetch(API+'?action=setRevenueSplit', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({pct})});
  const j = await res.json(); toast(j.ok?'Split guardado':'Error',''+(j.ok?'ok':'err'));
});

document.getElementById('btnExport').addEventListener('click', async ()=>{
  const c1 = document.getElementById('chartReg').toDataURL('image/png');
  const c2 = document.getElementById('chartLogin').toDataURL('image/png');
  const res = await fetch(API+'?action=exportPdf', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({
    chart_reg: c1, chart_login: c2, globalZafiros: cached.globalZafiros, revenueSplitCreator: cached.revenueSplitCreator
  })});
  const blob = await res.blob(); const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href=url; a.download='admin_report.pdf'; a.click();
});

loadData();
</script>
</body>
</html>
