<?php
session_start();
require_once __DIR__ . '/../api/db_connect.php';
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { header('Location: /public/login.php'); exit; }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Admin - Logros</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;background:linear-gradient(180deg,#020617 0%,#081127 100%);color:#e6eef8}.card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(6px);padding:16px;border-radius:12px}</style>
</head><body class="p-6">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Panel de Logros (Admin)</h1>
    <div class="card mb-4">
      <h2 class="font-semibold mb-2">Crear nuevo logro</h2>
      <form id="formCreate">
        <div class="grid grid-cols-2 gap-2 mb-2">
          <input name="code" placeholder="Código (ej: FIRST_UPLOAD)" class="p-2 rounded bg-black/20" required>
          <input name="title" placeholder="Título" class="p-2 rounded bg-black/20" required>
        </div>
        <div class="mb-2"><input name="zafiros_reward" type="number" placeholder="Recompensa en zafiros (0)" class="p-2 rounded bg-black/20"></div>
        <div class="mb-2"><input name="icon" placeholder="Icon URL (opcional)" class="p-2 rounded bg-black/20"></div>
        <div class="mb-2"><textarea name="description" placeholder="Descripción" class="w-full p-2 rounded bg-black/20"></textarea></div>
        <button class="px-4 py-2 bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] rounded" type="submit">Crear logro</button>
      </form>
      <div id="msg" class="mt-2 text-sm"></div>
    </div>

    <div class="card">
      <h2 class="font-semibold mb-2">Logros existentes</h2>
      <div id="list"></div>
    </div>
  </div>

<script>
async function loadList(){
  const res = await fetch('/api/admin_list_achievements.php', {credentials:'include'});
  const j = await res.json();
  const wrap = document.getElementById('list'); wrap.innerHTML='';
  if(!j.ok) return wrap.textContent='Error';
  j.rows.forEach(r=>{
    const div = document.createElement('div'); div.className='p-2 mb-2 bg-white/3 rounded flex items-center justify-between';
    div.innerHTML = `<div><strong>${r.title}</strong> <span class="text-sm text-blue-200">(${r.code})</span><div class="text-sm text-blue-200">${r.description||''}</div></div>
    <div class="flex gap-2"><button onclick="edit(${r.idachievement})" class="px-2 py-1 bg-white/5 rounded">Editar</button><button onclick="del(${r.idachievement})" class="px-2 py-1 bg-red-600 rounded text-white">Eliminar</button></div>`;
    wrap.appendChild(div);
  });
}
document.getElementById('formCreate').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('/api/admin_create_achievement.php',{method:'POST',body:fd,credentials:'include'});
  const j = await res.json();
  document.getElementById('msg').textContent = j.ok ? 'Creado' : ('Error: '+(j.error||''));
  loadList();
});
async function edit(id){ const title = prompt('Nuevo título'); if(!title) return; const fd = new FormData(); fd.append('id',id); fd.append('title',title); const res = await fetch('/api/admin_update_achievement.php',{method:'POST',body:fd,credentials:'include'}); const j=await res.json(); if(j.ok) alert('Actualizado'); loadList(); }
async function del(id){ if(!confirm('Eliminar?')) return; const fd = new FormData(); fd.append('id',id); const res = await fetch('/api/admin_delete_achievement.php',{method:'POST',body:fd,credentials:'include'}); const j=await res.json(); if(j.ok) alert('Eliminado'); loadList(); }
loadList();
</script>
</body></html>
