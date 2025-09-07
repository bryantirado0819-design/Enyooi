// enyooi_unified.js - Limpieza y robustez para MVC/XAMPP

(async function(){
  // --- Validación de variables globales necesarias ---
  if(typeof API_BASE === 'undefined' || typeof USER_ID === 'undefined' || typeof SOCKET_HOST === 'undefined') {
    alert('Error: Variables globales API_BASE, USER_ID o SOCKET_HOST no definidas.');
    return;
  }

  // --- Utilidades ---
  function el(id){ return document.getElementById(id); }
  function tplToast(html, timeout=4000){
    const root = el('toastRoot');
    if(!root) return;
    const node = document.createElement('div');
    node.className = 'mb-2 px-4 py-3 rounded-lg shadow-lg bg-white/5 text-white';
    node.innerHTML = html;
    root.appendChild(node);
    setTimeout(()=> node.remove(), timeout);
  }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }

  // --- Referencias DOM ---
  const contactsList = el('contactsList');
  const searchUser = el('searchUser');
  const messagesContainer = el('messagesContainer');
  const chatTitle = el('chatTitle');
  const chatSubtitle = el('chatSubtitle');
  const messageInput = el('messageInput');
  const sendBtn = el('sendBtn');
  const writeToId = el('writeToId');
  const typingIndicator = el('typingIndicator');
  const emptyState = el('emptyState');
  const notifBtn = el('notifBtn');
  const notifDropdown = el('notifDropdown');
  const notifCount = el('notifCount');
  const userIdDisplay = el('userIdDisplay');
  const chartPanelCanvas = el('chartPanel');
  const refreshMetricsBtn = el('refreshMetrics');

  // --- Estado ---
  let contacts = [];
  let currentContactId = null;
  let socket = null;
  let typingTimeout = null;
  let chart = null;

  // --- Cargar contactos ---
  async function loadContacts(q='') {
    try {
      const res = await fetch(`${API_BASE}/users_search.php?q=${encodeURIComponent(q)}`);
      const j = await res.json();
      contacts = (j.ok && Array.isArray(j.users)) ? j.users : [];
    } catch(e){
      contacts = [];
      tplToast('Error cargando usuarios.');
    }
    renderContacts();
  }

  function renderContacts(){
    if(!contactsList) return;
    contactsList.innerHTML = '';
    if(contacts.length===0) {
      contactsList.innerHTML = '<div class="text-sm text-blue-200">No hay usuarios.</div>';
      return;
    }
    contacts.forEach(c=>{
      const d = document.createElement('div');
      d.className = 'p-2 rounded hover:bg-white/2 cursor-pointer flex items-center gap-3';
      d.innerHTML = `<div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-xs font-semibold">${escapeHtml((c.display_name||c.username||'U').charAt(0).toUpperCase())}</div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-center">
          <div class="text-sm font-semibold truncate">${escapeHtml(c.display_name || c.username)}</div>
          <div class="text-xs text-blue-200">ID ${c.id}</div>
        </div>
      </div>`;
      d.addEventListener('click', ()=> openConversation(c.id, c.display_name || c.username));
      contactsList.appendChild(d);
    });
  }

  // --- Abrir conversación ---
  async function openConversation(contactId, name){
    currentContactId = contactId;
    if(writeToId) writeToId.value = contactId;
    if(chatTitle) chatTitle.textContent = name || ('Usuario ' + contactId);
    if(chatSubtitle) chatSubtitle.textContent = 'Conversación con ID ' + contactId;
    if(emptyState) emptyState.style.display = 'none';
    if(messagesContainer) messagesContainer.innerHTML = '<div class="text-sm text-blue-200">Cargando mensajes...</div>';
    try {
      const res = await fetch(`${API_BASE}/messages.php?u1=${USER_ID}&u2=${contactId}`);
      const j = await res.json();
      if(j.ok && j.conversation) renderMessages(j.conversation);
      else if(messagesContainer) messagesContainer.innerHTML = '<div class="text-sm text-blue-200">No hay mensajes.</div>';
    } catch(e){
      if(messagesContainer) messagesContainer.innerHTML = '<div class="text-sm text-red-400">Error cargando mensajes.</div>';
    }
  }

  function renderMessages(list){
    if(!messagesContainer) return;
    messagesContainer.innerHTML = '';
    if(!list || list.length===0){
      messagesContainer.innerHTML = '<div class="text-sm text-blue-200">Comienza la conversación.</div>';
      return;
    }
    list.forEach(m=>{
      const me = parseInt(m.sender_id) === parseInt(USER_ID);
      const wrapper = document.createElement('div');
      wrapper.className = 'max-w-xl ' + (me ? 'ml-auto' : 'mr-auto');
      wrapper.innerHTML = `<div class="text-xs text-blue-200 mb-1">${me ? 'Tú' : escapeHtml(m.sender_name||('Usuario '+m.sender_id))} • ${new Date(m.created_at).toLocaleString()}</div>
        <div class="${me ? 'bubble-me' : 'bubble-other'} p-3 rounded-lg text-sm">${escapeHtml(m.message)}</div>`;
      messagesContainer.appendChild(wrapper);
      // marcar como leído si es entrante
      if(!me && m.is_read==0){
        markMessageRead(m.id);
      }
    });
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // --- Enviar mensaje ---
  async function sendMessage(){
    const to = parseInt(writeToId && writeToId.value || 0);
    const text = String(messageInput && messageInput.value || '').trim();
    if(!to || !text) return tplToast('Selecciona un contacto y escribe un mensaje.');
    appendLocalMessage({from: USER_ID, to: to, message: text, created_at: new Date().toISOString()});
    if(messageInput) messageInput.value = '';
    try {
      const res = await fetch(`${API_BASE}/messages.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({from: USER_ID, to: to, message: text})
      });
      const j = await res.json();
      if(!j.ok) tplToast('Error enviando mensaje');
    } catch(e){ tplToast('Error en envío'); }
  }

  function appendLocalMessage(m){
    if(!messagesContainer) return;
    if(currentContactId && parseInt(m.to)!==parseInt(currentContactId) && parseInt(m.from)!==parseInt(currentContactId)){
      return;
    }
    const me = parseInt(m.from) === parseInt(USER_ID);
    const wrapper = document.createElement('div');
    wrapper.className = 'max-w-xl ' + (me ? 'ml-auto' : 'mr-auto');
    wrapper.innerHTML = `<div class="text-xs text-blue-200 mb-1">${me ? 'Tú' : m.from} • ${new Date(m.created_at).toLocaleString()}</div>
      <div class="${me ? 'bubble-me' : 'bubble-other'} p-3 rounded-lg text-sm">${escapeHtml(m.message)}</div>`;
    messagesContainer.appendChild(wrapper);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // --- Marcar mensaje como leído ---
  async function markMessageRead(messageId){
    try {
      await fetch(`${API_BASE}/messages_mark_read.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({message_id: messageId})
      });
    } catch(e){}
  }

  // --- Notificaciones ---
  async function loadNotifications(){
    try {
      const res = await fetch(`${API_BASE}/notifications.php?user_id=${USER_ID}`);
      const j = await res.json();
      if(j.notifications && notifDropdown){
        notifDropdown.innerHTML = '';
        let unread = 0;
        j.notifications.forEach(n=>{
          const div = document.createElement('div');
          div.className = 'p-2 border-b border-white/5 text-sm';
          div.innerHTML = `<div class="flex justify-between items-center"><div>${escapeHtml(n.type)}</div><div class="text-xs text-blue-200">${new Date(n.created_at).toLocaleString()}</div></div>
            <div class="mt-1">${escapeHtml(n.message)}</div>`;
          div.addEventListener('click', ()=> {
            fetch(`${API_BASE}/notifications_mark_read.php`, {
              method:'POST',
              headers:{'Content-Type':'application/json'},
              body: JSON.stringify({notification_id: n.id})
            });
            div.style.opacity = '0.6';
          });
          notifDropdown.appendChild(div);
          if(n.is_read==0) unread++;
        });
        if(notifCount) notifCount.innerText = unread;
      }
    } catch(e){}
  }

  if(notifBtn && notifDropdown){
    notifBtn.addEventListener('click', ()=> {
      if(notifDropdown.classList.contains('hidden')) {
        notifDropdown.classList.remove('hidden');
        loadNotifications();
      } else {
        notifDropdown.classList.add('hidden');
      }
    });
  }

  // --- Socket.io ---
  function initSocket(){
    if(typeof io === 'undefined') { console.error('socket.io not loaded'); return; }
    socket = io(SOCKET_HOST, { transports:['websocket','polling'] });
    socket.on('connect', ()=> {
      socket.emit('join', { room: 'user_' + USER_ID });
    });
    socket.on('new_message', (data)=> {
      const payload = data.payload || data;
      appendLocalMessage({from: payload.from, to: payload.to, message: payload.message, created_at: payload.created_at});
      tplToast('Nuevo mensaje de ' + (payload.from));
    });
    socket.on('notification', (data)=> {
      const p = data.payload || data;
      tplToast('Notificación: ' + (p.message || ''));
      loadNotifications();
    });
    socket.on('typing', (d)=> {
      const p = d.payload || d;
      if(currentContactId && parseInt(p.from) === parseInt(currentContactId) && typingIndicator) typingIndicator.classList.remove('hidden');
    });
    socket.on('stop_typing', (d)=> {
      const p = d.payload || d;
      if(currentContactId && parseInt(p.from) === parseInt(currentContactId) && typingIndicator) typingIndicator.classList.add('hidden');
    });
  }

  // --- Indicador de escritura ---
  function onTyping(){
    if(!socket || !currentContactId) return;
    socket.emit('typing', { room: 'user_' + currentContactId, from: USER_ID });
    if(typingTimeout) clearTimeout(typingTimeout);
    typingTimeout = setTimeout(()=> socket.emit('stop_typing', { room: 'user_' + currentContactId, from: USER_ID }), 1500);
  }

  if(messageInput){
    messageInput.addEventListener('keydown', e=>{
      if(e.key==='Enter'){ e.preventDefault(); sendMessage(); return; }
      onTyping();
    });
  }
  if(sendBtn) sendBtn.addEventListener('click', sendMessage);

  // --- Búsqueda de usuarios ---
  let searchTimer = null;
  if(searchUser){
    searchUser.addEventListener('input', ()=>{
      clearTimeout(searchTimer);
      searchTimer = setTimeout(()=> loadContacts(searchUser.value.trim()), 300);
    });
  }

  // --- Métricas (gráfica) ---
  async function loadMetrics(){
    if(!chartPanelCanvas) return;
    try {
      const res = await fetch(`${API_BASE}/reports.php`);
      const j = await res.json();
      const labels = j.data ? j.data.slice(0,7).map(r=> r.username || ('U'+r.id)) : [];
      const data = j.data ? j.data.slice(0,7).map(r=> parseFloat(r.total_amount || 0)) : [];
      if(!chart){
        chart = new Chart(chartPanelCanvas, {type:'bar', data:{labels: labels, datasets:[{label:'Ventas', data:data, backgroundColor: '#ff4fa3'}]}});
      } else {
        chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update();
      }
    } catch(e){}
  }

  // --- Inicialización ---
  await loadContacts();
  initSocket();
  loadNotifications();
  loadMetrics();

  // --- Botón de refresco de métricas ---
  if(refreshMetricsBtn) refreshMetricsBtn.addEventListener('click', loadMetrics);

  // --- Abrir primer contacto automáticamente ---
  if(contacts.length>0) openConversation(contacts[0].id, contacts[0].display_name || contacts[0].username);

})();
