<?php
session_start();
require_once __DIR__ . '/../../api/db_connect.php';
$key = $_GET['key'] ?? '';
if (!$key) { echo 'No key provided'; exit; }
$stream = $mysqli->query("SELECT s.*, u.nickname FROM streams s JOIN usuarios u ON u.idusuario = s.idcreadora WHERE s.stream_key='".$mysqli->real_escape_string($key)."' LIMIT 1")->fetch_assoc();
if (!$stream) { echo 'Stream no encontrado'; exit; }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title><?php echo htmlspecialchars($stream['titulo']); ?> — ENYOOI Live</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
<style>:root{--accent:#ff4fa3;--accent-2:#7c5cff}html,body{height:100%;margin:0;padding:0;font-family:'Poppins',sans-serif;background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8}.card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);border-radius:12px;padding:18px}.video-wrap{background:#000;border-radius:12px;overflow:hidden}.chat{height:420px;overflow:auto;background:rgba(0,0,0,0.25);padding:12px;border-radius:8px}</style>
</head><body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
<main class="min-h-screen p-6"><div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6"><div class="lg:col-span-2"><div class="card"><div class="flex items-center justify-between mb-3"><div><h2 class="text-xl font-bold"><?php echo htmlspecialchars($stream['titulo']); ?></h2><div class="text-sm text-blue-200">En vivo — <?php echo htmlspecialchars($stream['nickname']); ?></div></div><div class="text-sm text-blue-100">ID: <?php echo intval($stream['idstream']); ?></div></div><div class="video-wrap"><video id="player" class="w-full" controls playsinline></video></div><div class="mt-3 flex items-center justify-between"><div><span id="viewCount">Espectadores: <?php echo intval($stream['viewers_count']); ?></span></div><div class="flex items-center gap-2"><button class="px-3 py-1 bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] rounded" onclick="openTip()">💎 Propinar</button></div></div></div><div class="mt-4 card"><h3 class="font-semibold mb-2">Descripción</h3><p class="text-blue-200"><?php echo nl2br(htmlspecialchars($stream['descripcion'])); ?></p></div></div><aside><div class="card mb-4"><h3 class="font-semibold mb-2">Chat</h3><div id="chat" class="chat" tabindex="0"><div class="text-sm text-blue-200">Conecta con otros espectadores</div></div><div class="mt-3 flex gap-2"><input id="msg" class="flex-1 rounded p-2 bg-black/20" placeholder="Escribe..."/><button class="px-3 py-2 bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] rounded" onclick="sendMsg()">Enviar</button></div></div><div class="card"><h3 class="font-semibold mb-2">Creadora</h3><div class="flex items-center gap-3"><div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center font-bold"><?php echo strtoupper(substr($stream['nickname']?:'U',0,1)); ?></div><div><div class="font-semibold"><?php echo htmlspecialchars($stream['nickname']); ?></div><div class="text-sm text-blue-200">En línea</div></div></div></div></aside></div></main>
<div id="tipModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center"><div class="bg-white/5 rounded-xl p-6 max-w-sm w-full"><h3 class="font-bold mb-2">Enviar propina</h3><div class="grid grid-cols-2 gap-2 mb-3"><button class="p-2 bg-white/3 rounded" onclick="doTip(20)">💎20</button><button class="p-2 bg-white/3 rounded" onclick="doTip(50)">💎50</button><button class="p-2 bg-white/3 rounded" onclick="doTip(100)">💎100</button><button class="p-2 bg-white/3 rounded" onclick="doTip(250)">💎250</button></div><div class="flex justify-end"><button class="px-3 py-1" onclick="closeTip()">Cancelar</button></div></div></div>
<script>
const streamKey = "<?php echo htmlspecialchars($stream['stream_key']); ?>";
const hlsPath = "<?php echo htmlspecialchars($stream['hls_path'] ?: '/hls/'.$stream['stream_key'].'.m3u8'); ?>";
const player = document.getElementById('player');
if (player.canPlayType('application/vnd.apple.mpegurl')) { player.src = hlsPath; } else if (Hls.isSupported()) { const hls = new Hls(); hls.loadSource(hlsPath); hls.attachMedia(player);} else { player.innerHTML = 'Tu navegador no soporta HLS.'; }
async function refreshViewers(){ try{ const res = await fetch('/api/get_live_streams.php', {credentials:'include'}); const j = await res.json(); if (!j.ok) return; const live = j.live || []; const me = live.find(s=> (s.hls_path && s.hls_path.includes(streamKey)) || (s.stream_key===streamKey)); if (me) document.getElementById('viewCount').textContent = 'Espectadores: '+(me.viewers_count||0); }catch(e){} }
setInterval(refreshViewers, 5000); refreshViewers();
let reportInterval = 15;
async function report(){ try{ const sidRes = await fetch('/api/get_current_session.php?stream_key='+encodeURIComponent(streamKey), {credentials:'include'}); const sidJson = await sidRes.json(); if (!sidJson.ok) return; const idsession = sidJson.idsession; const fd = new FormData(); fd.append('idsession', idsession); fd.append('seconds', reportInterval); await fetch('/api/report_viewer.php', {method:'POST', body: fd, credentials:'include'}); }catch(e){} }
setInterval(report, reportInterval*1000); report();
// WebSocket chat integration
async function initWS(){
  const tres = await fetch('/api/get_ws_token.php', {method:'POST', credentials:'include'});
  const tj = await tres.json(); if (!tj.ok) { console.warn('No ws token'); return; }
  const token = tj.token;
  const wsUrl = (tj.ws_url || (location.protocol === 'https:' ? 'wss://' : 'ws://') + location.hostname + ':8082') + '?token=' + token + '&room=' + encodeURIComponent(streamKey);
  const ws = new WebSocket(wsUrl);
  ws.addEventListener('open', ()=>{ console.log('ws open'); });
  ws.addEventListener('message', ev=>{ try{ const d = JSON.parse(ev.data); if (d.type === 'chat_message') { const p = document.createElement('div'); p.className='text-sm text-blue-100'; p.innerHTML = '<strong>'+ (d.nickname||d.uid) +':</strong> '+ d.text; document.getElementById('chat').appendChild(p); document.getElementById('chat').scrollTop = document.getElementById('chat').scrollHeight; } else if (d.type === 'tip_event') { const p = document.createElement('div'); p.className='text-sm text-yellow-200'; p.innerHTML = '✨ Propina: <strong>'+ (d.from_nick || d.from_uid) +'</strong> envió 💎'+d.amount; document.getElementById('chat').appendChild(p); document.getElementById('chat').scrollTop = document.getElementById('chat').scrollHeight; } } catch(e){ console.error(e); } });
  ws.addEventListener('close', ()=>{ console.log('ws closed'); });
  window.ENYOOI_WS = ws;
}
function sendMsg(){ const t=document.getElementById('msg'); if(!t.value) return; const payload = {type:'chat', text: t.value, idstream: <?php echo intval($stream['idstream']); ?>}; if (window.ENYOOI_WS && window.ENYOOI_WS.readyState === WebSocket.OPEN) window.ENYOOI_WS.send(JSON.stringify(payload)); t.value=''; }
async function doTip(amount){ const payload = {type:'tip', idcreadora: <?php echo intval($stream['idcreadora']); ?>, zafiros: amount, idstream: <?php echo intval($stream['idstream']); ?>}; if (window.ENYOOI_WS && window.ENYOOI_WS.readyState === WebSocket.OPEN) { window.ENYOOI_WS.send(JSON.stringify(payload)); } else { alert('Conexión no establecida, inténtalo de nuevo'); } }
initWS();
function openTip(){ document.getElementById('tipModal').classList.remove('hidden'); document.getElementById('tipModal').classList.add('flex'); }
function closeTip(){ document.getElementById('tipModal').classList.add('hidden'); document.getElementById('tipModal').classList.remove('flex'); }
</script>
</body></html>
