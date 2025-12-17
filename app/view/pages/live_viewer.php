<?php require APPROOT . '/view/custom/header.php'; ?>
<script src="https://player.live-video.net/1.24.0/amazon-ivs-player.min.js"></script>
<!-- CORREGIDO: Usamos SOCKET_URL o RUTA_URL si no está definido -->
<script src="<?php echo defined('SOCKET_URL') ? SOCKET_URL : RUTA_URL . ':3000'; ?>/socket.io/socket.io.js"></script>

<div class="container-fluid p-0">
    <div class="row g-0" style="height: calc(100vh - 60px);">
        <!-- PLAYER -->
        <div class="col-lg-9 bg-black d-flex align-items-center justify-content-center position-relative">
            <video id="video-player" playsinline controls style="width:100%; height:100%; max-height:100vh;"></video>
            <div class="position-absolute top-0 start-0 p-4 text-white w-100" style="background:linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);">
                <h4><?php echo $data['stream']->title; ?></h4>
                <small>@<?php echo $data['stream']->creator_nickname; ?></small>
            </div>
        </div>

        <!-- CHAT -->
        <div class="col-lg-3 bg-dark border-start border-secondary d-flex flex-column">
            <div class="p-3 border-bottom border-secondary text-white d-flex justify-content-between">
                <span>Chat</span>
                <small><i class="fas fa-eye"></i> <span id="viewer-count">0</span></small>
            </div>
            <div id="chat-box" class="flex-grow-1 p-3 overflow-auto text-white"></div>
            
            <!-- Propinas -->
            <div class="p-2 bg-secondary bg-opacity-25 d-flex gap-2 justify-content-center flex-wrap">
                <?php foreach($data['tip_options'] as $tip): ?>
                    <button class="btn btn-sm btn-outline-warning" onclick="sendTip(<?php echo $tip->zafiros; ?>)">💎 <?php echo $tip->zafiros; ?></button>
                <?php endforeach; ?>
                <button class="btn btn-sm btn-warning" onclick="sendTip(10)">10</button>
            </div>

            <div class="p-3 bg-black bg-opacity-50">
                <div class="input-group">
                    <input type="text" id="chat-msg" class="form-control bg-dark text-white border-secondary">
                    <button id="btn-send" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const PLAYBACK_URL = "<?php echo $data['stream']->ivs_playback_url; ?>";
    const STREAM_ID = <?php echo $data['stream']->idstream; ?>;
    const CREATOR_ID = <?php echo $data['stream']->creator_id; ?>;
    const USER_ID = <?php echo $_SESSION['logueando'] ?? 0; ?>;
    const USERNAME = "<?php echo $_SESSION['usuario'] ?? 'Invitado'; ?>";
    // CORREGIDO: Usamos la constante segura para el socket
    const SOCKET_URL = '<?php echo defined("SOCKET_URL") ? SOCKET_URL : RUTA_URL . ":3000"; ?>';

    // 1. PLAYER
    if (IVSPlayer.isPlayerSupported) {
        const p = IVSPlayer.create();
        p.attachHTMLVideoElement(document.getElementById('video-player'));
        p.load(PLAYBACK_URL);
        p.play();
    }

    // 2. SOCKET
    const socket = io(SOCKET_URL);
    socket.emit('join_stream_room', { streamId: STREAM_ID, isCreator: false });
    
    socket.on('update_viewer_count', d => document.getElementById('viewer-count').innerText = d.count);
    
    socket.on('new_chat_message', d => {
        const b = document.getElementById('chat-box');
        b.innerHTML += `<div class="mb-1"><strong class="text-info">${d.username}:</strong> ${d.message}</div>`;
        b.scrollTop = b.scrollHeight;
    });

    socket.on('new_tip_alert', d => {
        const b = document.getElementById('chat-box');
        b.innerHTML += `<div class="alert alert-warning p-1 mb-1 text-center"><strong>${d.username}</strong> envió ${d.amount} 💎</div>`;
        b.scrollTop = b.scrollHeight;
    });

    // Enviar Chat
    document.getElementById('btn-send').onclick = () => {
        if(!USER_ID) return alert("Inicia sesión");
        const i = document.getElementById('chat-msg');
        if(i.value.trim()) {
            socket.emit('send_chat_message', { streamId: STREAM_ID, userId: USER_ID, username: USERNAME, message: i.value });
            i.value = '';
        }
    };

    // Enviar Tip (Llama a tu Controller -> Wallet -> Socket)
    window.sendTip = async (amount) => {
        if(!USER_ID) return alert("Inicia sesión");
        if(!confirm(`¿Enviar ${amount} zafiros?`)) return;

        try {
            // CORREGIDO: Usamos RUTA_URL en lugar de URLROOT
            const res = await fetch('<?php echo RUTA_URL; ?>/Live/processTip', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ amount, streamId: STREAM_ID, creatorId: CREATOR_ID })
            });
            const data = await res.json();
            if(data.success) {
                // Emitir alerta al socket para activar Lovense
                socket.emit('send_tip_alert', { streamId: STREAM_ID, username: USERNAME, amount: amount, message: "¡Propina!" });
            } else {
                alert("Error: " + data.message);
            }
        } catch(e) { console.error(e); }
    };
</script>

<?php require APPROOT . '/view/custom/footer.php'; ?>