<?php require APPROOT . '/view/custom/header.php'; ?>

<!-- 1. AWS IVS Web Broadcast SDK -->
<script src="https://web-broadcast.live-video.net/1.14.0/amazon-ivs-web-broadcast.js"></script>
<!-- 2. Socket.io -->
<!-- Usamos SOCKET_URL de tu config.php. Si no está definida, fallback a IP:3000 -->
<script src="<?php echo defined('SOCKET_URL') ? SOCKET_URL : RUTA_URL . ':3000'; ?>/socket.io/socket.io.js"></script>
<!-- 3. Librería QR Code (Ligera, para generar el QR de Lovense) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    .lovense-active { border: 2px solid #ff0055 !important; box-shadow: 0 0 15px #ff0055; }
    .animate-vibrate { animation: vibrate 0.3s linear infinite; }
    @keyframes vibrate { 0% { transform: translate(0); } 20% { transform: translate(-2px, 2px); } 40% { transform: translate(-2px, -2px); } 60% { transform: translate(2px, 2px); } 80% { transform: translate(2px, -2px); } 100% { transform: translate(0); } }
</style>

<div class="container-fluid mt-4 fade-in">
    <div class="row">
        <!-- ÁREA DE VIDEO Y CONTROLES -->
        <div class="col-lg-9">
            <div class="ratio ratio-16x9 bg-black rounded shadow position-relative overflow-hidden" id="video-container">
                <canvas id="preview-canvas" style="width:100%; height:100%; object-fit:cover;"></canvas>
                
                <!-- Indicadores de Estado -->
                <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                    <span id="live-badge" class="badge bg-danger d-none animate-pulse shadow">🔴 EN VIVO</span>
                    <span class="badge bg-dark bg-opacity-75 shadow"><i class="fas fa-eye me-1"></i> <span id="viewer-count">0</span></span>
                    <span id="lovense-badge" class="badge bg-secondary shadow"><i class="fas fa-plug me-1"></i> Toy Offline</span>
                </div>

                <!-- Notificación de Regalo / Vibración -->
                <div id="gift-notification" class="position-absolute top-50 start-50 translate-middle text-center p-4 rounded bg-dark bg-opacity-90 d-none" style="z-index: 1000; min-width: 300px;">
                    <h1 class="display-4 mb-0">🎁</h1>
                    <h3 class="text-warning fw-bold" id="gift-amount">100</h3>
                    <p class="text-white mb-0" id="gift-user">Usuario</p>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar bg-warning" id="vibration-bar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Panel de Inicio (Pre-Live) -->
                <div id="start-controls" class="position-absolute top-50 start-50 translate-middle text-center w-50 p-5 bg-dark bg-opacity-90 rounded-4 shadow-lg border border-secondary">
                    <h2 class="text-white fw-bold mb-4">Configurar Transmisión</h2>
                    
                    <div class="form-floating mb-3">
                        <input type="text" id="stream-title" class="form-control bg-black text-white border-secondary" placeholder="Título" value="<?php echo $data['stream']->title ?? ''; ?>">
                        <label for="stream-title">Título del Live</label>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <select id="video-select" class="form-select bg-black text-white border-secondary"></select>
                        </div>
                        <div class="col-6">
                            <select id="audio-select" class="form-select bg-black text-white border-secondary"></select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button id="btn-start" class="btn btn-primary btn-lg fw-bold rounded-pill">
                            <i class="fas fa-satellite-dish me-2"></i> EMITIR AHORA
                        </button>
                        <button type="button" class="btn btn-outline-light rounded-pill" data-bs-toggle="modal" data-bs-target="#lovenseModal">
                            <i class="fas fa-heart me-2 text-danger"></i> Conectar Lovense
                        </button>
                    </div>
                </div>
            </div>

            <!-- Botones Post-Live -->
            <div class="mt-3 text-center d-flex justify-content-center gap-3">
                <button id="btn-stop" class="btn btn-danger btn-lg px-5 fw-bold d-none shadow">
                    <i class="fas fa-stop me-2"></i> FINALIZAR
                </button>
                <button class="btn btn-dark border-secondary" data-bs-toggle="modal" data-bs-target="#lovenseModal">
                    <i class="fas fa-cog me-2"></i> Configurar Juguetes
                </button>
            </div>
        </div>

        <!-- CHAT Y HERRAMIENTAS -->
        <div class="col-lg-3">
            <div class="card h-100 bg-dark text-white border-secondary shadow">
                <div class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="far fa-comments me-2"></i>Chat</span>
                    <small class="text-muted">Conectado</small>
                </div>
                <div id="chat-box" class="card-body overflow-auto" style="height: 500px; background: #0f0f0f;">
                    <div class="text-center text-muted mt-5">
                        <i class="fas fa-comment-dots fa-2x mb-2"></i>
                        <p>El chat aparecerá aquí...</p>
                    </div>
                </div>
                <div class="card-footer border-top border-secondary bg-dark">
                    <div class="input-group">
                        <input type="text" id="chat-msg" class="form-control bg-black text-white border-secondary" placeholder="Escribe algo...">
                        <button id="btn-send" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONEXIÓN LOVENSE -->
<div class="modal fade" id="lovenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="fas fa-heart text-danger me-2"></i>Conectar Lovense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-3">Escanea este código con tu App <strong>Lovense Connect</strong> en el móvil.</p>
                
                <div id="qrcode-container" class="bg-white p-3 d-inline-block rounded mb-3">
                    <!-- El QR se genera aquí -->
                </div>
                
                <div id="connection-status" class="alert alert-secondary mt-2">
                    Esperando conexión...
                </div>

                <hr class="border-secondary">
                
                <button class="btn btn-outline-warning w-100" onclick="testVibration()">
                    <i class="fas fa-vibration me-2"></i> Probar Vibración (Test)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ✅ ACTUALIZADO: Usamos las constantes de tu config.php
    const URLROOT = '<?php echo RUTA_URL; ?>'; // Usamos RUTA_URL en lugar de URLROOT
    const SOCKET_URL = '<?php echo defined("SOCKET_URL") ? SOCKET_URL : RUTA_URL . ":3000"; ?>'; 
    const USER_ID = <?php echo $_SESSION['logueando']; ?>;
    const USERNAME = '<?php echo $_SESSION['usuario']; ?>';
    
    let ivsClient;
    let streamId = <?php echo $data['stream']->idstream ?? 0; ?>;
    let socket;
    
    // VARIABLES LOVENSE
    let lovenseConnected = false;
    let toyUrl = null; // URL local del juguete (ej: http://192.168.1.5:3000)

    // ==========================================
    // 1. INICIALIZACIÓN IVS (VIDEO)
    // ==========================================
    async function initIVS() {
        if (!window.IVSBroadcastClient) return console.error("IVS SDK no cargado");
        
        const { IVSBroadcastClient, STANDARD_LANDSCAPE } = window.IVSBroadcastClient;
        ivsClient = IVSBroadcastClient.create({
            streamConfig: STANDARD_LANDSCAPE,
            ingestEndpoint: "" // Se llena al iniciar live
        });
        ivsClient.attachPreview(document.getElementById('preview-canvas'));

        // Obtener Cámaras/Micros
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const vSel = document.getElementById('video-select');
            const aSel = document.getElementById('audio-select');
            
            devices.forEach(d => {
                const o = document.createElement('option');
                o.value = d.deviceId; 
                o.text = d.label || d.kind;
                if(d.kind === 'videoinput') vSel.appendChild(o);
                if(d.kind === 'audioinput') aSel.appendChild(o);
            });

            startCamera(vSel.value, aSel.value);
            vSel.onchange = () => startCamera(vSel.value, aSel.value);
        } catch(e) { console.error("Error media devices:", e); }
    }

    async function startCamera(vid, aud) {
        try {
            const s = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: vid ? {exact:vid} : undefined },
                audio: { deviceId: aud ? {exact:aud} : undefined }
            });
            ivsClient.addVideoInputDevice(s, 'camera1', {index:0});
            ivsClient.addAudioInputDevice(s, 'mic1', {index:0});
        } catch(e) { console.error("Error cámara:", e); }
    }

    // ==========================================
    // 2. INICIAR / PARAR STREAM
    // ==========================================
    document.getElementById('btn-start').onclick = async function() {
        const title = document.getElementById('stream-title').value;
        if(!title) return alert("Por favor escribe un título para tu Live.");
        
        this.disabled = true; this.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Iniciando...';

        try {
            const fd = new FormData(); fd.append('title', title);
            const res = await fetch(`${URLROOT}/Live/start_ivs_stream`, { method: 'POST', body: fd });
            const data = await res.json();

            if(data.success) {
                streamId = data.stream_id;
                // Iniciar emisión a AWS
                await ivsClient.startBroadcast(data.stream_key, `rtmps://${data.ingest_endpoint}:443/app/`);
                
                // Actualizar UI
                document.getElementById('start-controls').classList.add('d-none');
                document.getElementById('live-badge').classList.remove('d-none');
                document.getElementById('btn-stop').classList.remove('d-none');
                
                // Conectar Chat y Eventos
                connectSocket(); 
            } else {
                alert("Error del servidor: " + data.message); this.disabled = false; this.innerHTML = "EMITIR AHORA";
            }
        } catch(e) { 
            console.error(e); alert("Error de conexión. Revisa tu internet."); 
            this.disabled = false; this.innerHTML = "EMITIR AHORA";
        }
    };

    document.getElementById('btn-stop').onclick = async () => {
        if(confirm("¿Seguro que quieres terminar el live?")) {
            if(ivsClient) await ivsClient.stopBroadcast();
            await fetch(`${URLROOT}/Live/stop_stream`, { method: 'POST' });
            window.location.reload();
        }
    };

    // ==========================================
    // 3. SOCKETS + LÓGICA LOVENSE
    // ==========================================
    function connectSocket() {
        socket = io(SOCKET_URL);
        
        // Unirse a la sala
        socket.emit('join_stream_room', { streamId: streamId, isCreator: true });

        // Actualizar contadores
        socket.on('update_viewer_count', d => {
            document.getElementById('viewer-count').innerText = d.count;
        });
        
        // Chat
        socket.on('new_chat_message', d => {
            const box = document.getElementById('chat-box');
            // Si es el primer mensaje, limpiamos el placeholder
            if(box.querySelector('.text-center')) box.innerHTML = '';
            
            box.innerHTML += `
                <div class="mb-2 fade-in">
                    <strong style="color: #0d6efd;">${d.username}:</strong> 
                    <span class="text-white">${d.message}</span>
                </div>`;
            box.scrollTop = box.scrollHeight;
        });

        // 🔥 EVENTO CRÍTICO: ACTIVAR JUGUETE 🔥
        socket.on('trigger_lovense', data => {
            console.log("🔥 COMANDO LOVENSE RECIBIDO:", data);
            
            // 1. Mostrar Notificación Visual
            showVisualAlert(data.amount);
            
            // 2. Ejecutar Vibración Real
            triggerToyVibration(data.level, 5); // 5 segundos por defecto o calcula según monto
        });

        // Alerta de Tip Visual
        socket.on('new_tip_alert', d => {
             const box = document.getElementById('chat-box');
             if(box.querySelector('.text-center')) box.innerHTML = '';
             box.innerHTML += `
                <div class="alert alert-warning p-2 mb-2 text-center border-warning">
                    <strong>${d.username}</strong> envió <strong>${d.amount} 💎</strong>
                    <br><small>${d.message}</small>
                </div>`;
             box.scrollTop = box.scrollHeight;
        });

        // Enviar mensajes
        document.getElementById('btn-send').onclick = sendChat;
        document.getElementById('chat-msg').onkeypress = (e) => { if(e.key === 'Enter') sendChat(); };
    }

    function sendChat() {
        const input = document.getElementById('chat-msg');
        if(input.value.trim()) {
            socket.emit('send_chat_message', { 
                streamId, userId: USER_ID, username: USERNAME, message: input.value 
            });
            input.value = '';
        }
    }

    // ==========================================
    // 4. INTEGRACIÓN LOVENSE (QR & API LAN)
    // ==========================================
    
    // Generar QR al abrir modal
    const modal = document.getElementById('lovenseModal');
    modal.addEventListener('show.bs.modal', async () => {
        const container = document.getElementById('qrcode-container');
        const status = document.getElementById('connection-status');
        
        container.innerHTML = ''; // Limpiar previo
        status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo token...';
        
        try {
            // Obtener Token Lovense desde tu controlador PHP
            const res = await fetch(`${URLROOT}/Live/getLovenseAuthToken`, {method: 'POST'});
            const data = await res.json();
            
            if(data.success || data.token) { // Asumiendo que devuelve 'token'
                const token = data.token || 'test_token'; // Fallback si es mock
                // URL estándar para QR Lovense LAN
                const qrUrl = `https://api.lovense.com/api/lan/v2/qr?token=${token}&uid=${USER_ID}&uname=${USERNAME}`;
                
                // Generar Gráfico QR
                new QRCode(container, {
                    text: qrUrl,
                    width: 200,
                    height: 200
                });
                
                status.className = "alert alert-info mt-2";
                status.innerHTML = "Escanea con Lovense Connect App.";
                
                // Aquí deberíamos iniciar un polling o esperar callback, 
                // pero para simplificar asumiremos conexión manual o detección futura
                // En un sistema real Lovense LAN, el navegador descubre la IP local
                checkLovenseLocal(); 

            } else {
                status.className = "alert alert-danger mt-2";
                status.innerText = "Error token: " + (data.message || 'Desconocido');
            }
        } catch(e) {
            status.innerText = "Error de conexión con Lovense API";
        }
    });

    // Función simple para enviar comando
    function triggerToyVibration(level, durationSec) {
        if (!lovenseConnected && !toyUrl) {
            console.warn("⚠️ No hay juguete conectado. Vibración simulada en UI.");
            return;
        }

        // Si tenemos URL local (LAN)
        if (toyUrl) {
            const commandUrl = `${toyUrl}/command`;
            fetch(commandUrl, {
                method: 'POST',
                body: JSON.stringify({
                    command: "Function",
                    action: "Vibrate:" + level,
                    timeSec: durationSec,
                    apiVer: 1
                })
            }).catch(e => console.error("Error enviando comando al juguete:", e));
        }
        
        // Si tienes la extensión de Chrome de Lovense instalada
        if (window.Lovense) {
            window.Lovense.vibrate(level); // Comando genérico extensión
        }
    }

    // Buscar juguete en localhost (Patrón común LAN)
    // Esto intenta encontrar la app Lovense Connect corriendo en la misma red
    function checkLovenseLocal() {
        // Intenta puertos comunes de Lovense Connect
        const ports = [20010, 30010, 34567]; 
        // Esta lógica es avanzada, para simplificar marcamos como "Listo para escanear"
        // Si el usuario escanea, la App móvil se encarga de la comunicación si se configura el Callback.
    }

    function testVibration() {
        alert("Enviando prueba de vibración...");
        showVisualAlert(50); // Test visual
        // Intenta vibrar si hay conexión
        triggerToyVibration(20, 2);
    }
    
    // UI Helpers
    function showVisualAlert(amount) {
        const notif = document.getElementById('gift-notification');
        const amt = document.getElementById('gift-amount');
        const badge = document.getElementById('live-badge');
        
        // Actualizar datos
        amt.innerText = amount + " 💎";
        
        // Mostrar
        notif.classList.remove('d-none');
        notif.classList.add('animate-vibrate');
        badge.classList.remove('bg-danger');
        badge.classList.add('bg-warning', 'text-dark');
        badge.innerText = "⚡ VIBRANDO";

        // Ocultar después de 3 seg
        setTimeout(() => {
            notif.classList.add('d-none');
            notif.classList.remove('animate-vibrate');
            badge.classList.add('bg-danger');
            badge.classList.remove('bg-warning', 'text-dark');
            badge.innerText = "🔴 EN VIVO";
        }, 3000);
    }

    // Inicializar
    initIVS();
</script>