<?php 
include_once __DIR__ . '/../custom/header.php';

// --- DATA SANITIZATION ---
$stream = $datos['stream'] ?? null;
$level_info = $datos['level_info'] ?? ['current_level' => 1, 'progress_percentage' => 0, 'next_level_reward' => 50, 'current_xp' => 0, 'xp_needed_for_next' => 100];
$tip_options = $datos['tip_options'] ?? [];
$roulette_options = $datos['roulette_options'] ?? [];
$lovense_options = $datos['lovense_options'] ?? [];
$creator_id = htmlspecialchars($_SESSION['logueando'] ?? '0');
$username = htmlspecialchars($_SESSION['usuario'] ?? 'Creator');

// Avatar para el placeholder
$avatar = !empty($datos['perfil']->foto_perfil) ? URL_PROJECT . $datos['perfil']->foto_perfil : URL_PROJECT . 'public/img/defaults/default_avatar.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comando Central - Enyooi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Tu CSS Original -->
    <link rel="stylesheet" href="<?php echo RUTA_URL; ?>/public/css/live.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- LIBRERÍAS VITALES (SweetAlert, Socket.io, Mediasoup) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mediasoup-client@3/dist/mediasoup-client.min.js"></script>

    <style>
        /* Ajustes para el video local */
        #local-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Modo espejo */
        }
        /* Animación Toast */
        .alert-toast { animation: slideInRight 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>

<body data-creator-id="<?php echo $creator_id; ?>" data-username="<?php echo $username; ?>" data-lovense-uid="enyooi_user_<?php echo $creator_id; ?>">
    
    <!-- Contenedor de Toasts (Alertas de Donación) -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>
    
    <div class="main-background"></div>
    <div id="mobile-overlay"></div>
    
    <!-- Modal Tutorial (Tu código original) -->
    <div id="tutorial-modal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="modal-content card-glass-neon p-6 max-w-lg w-full rounded-2xl relative">
            <div class="modal-header flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-3"><i class="fas fa-magic-wand text-fuchsia-400"></i> Tutorial del Panel</h2>
                <button id="close-modal-btn" class="text-2xl text-gray-400 hover:text-white transition-colors">&times;</button>
            </div>
            <div class="modal-body custom-scrollbar text-sm text-gray-300 space-y-3">
                <p>¡Bienvenido/a a tu Comando Central! Resumen rápido:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong class="text-fuchsia-400">Izquierda:</strong> Configura título y fuente (Cámara). Pulsa "Empezar Stream" para salir al aire.</li>
                    <li><strong class="text-fuchsia-400">Centro:</strong> Tu monitor de video y el feed de alertas en tiempo real.</li>
                    <li><strong class="text-fuchsia-400">Derecha:</strong> Chat y Herramientas (Propinas, Ruleta, Lovense).</li>
                </ul>
            </div>
        </div>
    </div>

    <main class="main-container flex h-screen overflow-hidden p-2 gap-2">

        <!-- COLUMNA IZQUIERDA: CONFIGURACIÓN -->
        <section id="column-left" class="column-container custom-scrollbar w-1/4 flex flex-col gap-2 overflow-y-auto">
            <div class="card-glass-neon p-4 rounded-xl bg-black/40 border border-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-white">Comando Central</h1>
                        <div id="stream-status" class="status-indicator offline text-xs font-bold text-gray-500 mt-1 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-gray-500"></span> OFFLINE
                        </div>
                    </div>
                    <button id="show-tutorial-btn" class="text-fuchsia-400 hover:text-fuchsia-300 text-xl"><i class="fas fa-question-circle"></i></button>
                </div>
                <a href="<?php echo RUTA_URL; ?>/creator_dashboard" class="text-xs text-fuchsia-400 hover:text-fuchsia-300 transition-colors mt-3 inline-block flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-glass-neon p-4 rounded-xl bg-black/40 border border-white/10">
                <h3 class="card-header text-white font-bold mb-3 flex items-center gap-2"><i class="fas fa-satellite-dish text-fuchsia-400"></i> Info del Stream</h3>
                <div class="space-y-3">
                    <input type="text" id="stream-title" class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white text-sm focus:border-fuchsia-500 outline-none" placeholder="Título de tu stream..." value="<?php echo htmlspecialchars($stream->titulo ?? ''); ?>">
                    <textarea id="stream-desc" class="w-full bg-white/5 border border-white/10 rounded px-3 py-2 text-white text-sm focus:border-fuchsia-500 outline-none h-20" placeholder="Describe tu stream..."><?php echo htmlspecialchars($stream->descripcion ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="card-glass-neon p-4 rounded-xl bg-black/40 border border-white/10 mt-auto">
                 <h3 class="card-header text-white font-bold mb-3 flex items-center gap-2"><i class="fas fa-rocket text-fuchsia-400"></i> Acciones</h3>
                 <div class="space-y-3">
                    <button id="save-settings-btn" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded font-bold transition"><i class="fas fa-save mr-2"></i>Guardar Info</button>
                    
                    <!-- BOTONES DE STREAMING -->
                    <button id="start-webrtc-btn" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-400 text-white py-3 rounded font-bold shadow-lg shadow-green-500/20 transition flex items-center justify-center gap-2">
                        <i class="fas fa-video"></i> Empezar Stream
                    </button>
                    
                    <button id="stop-webrtc-btn" class="hidden w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded font-bold shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-stop-circle"></i> Terminar Stream
                    </button>
                 </div>
            </div>
        </section>

        <!-- COLUMNA CENTRAL: VIDEO Y FEED -->
        <section id="column-center" class="column-container flex-grow flex flex-col gap-2 relative">
            <!-- CONTENEDOR DE VIDEO -->
            <div id="video-preview-container" class="card-glass-neon flex-grow rounded-xl overflow-hidden bg-black relative group">
                
                <!-- Elemento VIDEO REAL (Insertado aquí) -->
                <video id="local-video" autoplay playsinline muted></video>

                <div id="video-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 pointer-events-none z-10 bg-gray-900">
                    <i class="fas fa-video-slash text-4xl mb-2"></i>
                    <span>Cámara apagada</span>
                </div>
                
                <!-- Controles sobre video -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-3 opacity-0 group-hover:opacity-100 transition z-20 bg-black/50 p-2 rounded-full backdrop-blur-md">
                    <button id="btn-toggle-cam" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center"><i class="fas fa-video"></i></button>
                    <button id="btn-toggle-mic" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center"><i class="fas fa-microphone"></i></button>
                </div>
            </div>
            
            <!-- FEED DE ACTIVIDAD -->
            <div class="card-glass-neon h-48 flex-shrink-0 rounded-xl bg-black/40 border border-white/10 flex flex-col">
                <div class="card-header p-3 border-b border-white/10 flex justify-between items-center">
                    <div class="flex items-center gap-2 font-bold text-white"><i class="fas fa-bolt text-fuchsia-400"></i> Actividad</div>
                    <div id="session-earnings" class="text-yellow-400 font-bold text-sm"><i class="fas fa-gem"></i> 0</div>
                </div>
                <div id="activity-feed" class="p-3 flex-grow overflow-y-auto space-y-2 custom-scrollbar text-sm">
                    <!-- Aquí JS inyectará eventos -->
                </div>
            </div>
        </section>

        <!-- COLUMNA DERECHA: PESTAÑAS -->
        <section id="column-right" class="column-container custom-scrollbar w-1/4 flex flex-col gap-2">
            <div class="card-glass-neon flex-grow flex flex-col h-full bg-black/40 border border-white/10 rounded-xl overflow-hidden">
                
                <!-- Headers Pestañas -->
                <div class="tabs-header flex border-b border-white/10">
                    <button class="tab-button active flex-1 py-3 text-gray-400 hover:text-white hover:bg-white/5 transition" data-tab="tab-monetization" title="Monetización"><i class="fas fa-sack-dollar"></i></button>
                    <button class="tab-button flex-1 py-3 text-gray-400 hover:text-white hover:bg-white/5 transition relative" data-tab="tab-chat" title="Chat">
                        <i class="fas fa-comments"></i>
                        <span id="viewer-count-badge" class="absolute top-2 right-4 bg-red-600 text-white text-[10px] px-1.5 rounded-full hidden">0</span>
                    </button>
                </div>
                
                <div class="tabs-content flex-grow relative overflow-hidden">
                    
                    <!-- TAB 1: MONETIZACIÓN (CONFIG) -->
                    <div id="tab-monetization" class="tab-content active h-full overflow-y-auto p-4 space-y-4 custom-scrollbar">
                        
                        <!-- Nivel -->
                        <div class="sub-card p-3 bg-white/5 rounded-lg">
                            <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Nivel Actual</h4>
                            <div class="flex justify-between items-baseline mb-1">
                                <span class="font-bold text-white">Lvl <?php echo $level_info['current_level']; ?></span>
                                <span class="text-xs text-fuchsia-400">Próx: <?php echo $level_info['next_level_reward']; ?>💎</span>
                            </div>
                            <div class="w-full bg-black/50 rounded-full h-2"><div class="bg-fuchsia-500 h-2 rounded-full" style="width: <?php echo $level_info['progress_percentage']; ?>%;"></div></div>
                        </div>

                        <!-- Meta -->
                        <div class="sub-card p-3 bg-white/5 rounded-lg">
                            <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Meta de Propinas</h4>
                            <div class="flex gap-2 mb-2">
                                <input type="number" id="tip-goal-target" class="w-full bg-black/30 border border-white/10 rounded px-2 py-1 text-white text-xs" placeholder="Meta 💎" value="<?php echo $datos['active_tip_goal']['goal_amount'] ?? 500; ?>">
                                <button onclick="updateGoal()" class="bg-fuchsia-600 text-white px-3 rounded text-xs font-bold">Set</button>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span>Actual: <span id="current-goal-val"><?php echo $datos['active_tip_goal']['current_amount'] ?? 0; ?></span></span>
                            </div>
                        </div>

                        <!-- LOVENSE (QR y Tests) -->
                        <div class="sub-card p-3 bg-pink-900/20 border border-pink-500/20 rounded-lg">
                             <h4 class="text-xs font-bold text-pink-400 uppercase mb-2 flex items-center gap-2"><i class="fas fa-heartbeat"></i> Lovense</h4>
                             
                             <div id="lovense-connect-view">
                                <button id="get-lovense-qr-btn" class="w-full bg-pink-600 hover:bg-pink-500 text-white py-2 rounded text-xs font-bold mb-2">
                                    <i class="fas fa-qrcode mr-1"></i> Generar QR
                                </button>
                                <div id="lovense-qrcode" class="flex justify-center items-center min-h-[120px] bg-white rounded-lg hidden p-2"></div>
                             </div>

                             <div class="mt-3 border-t border-white/10 pt-2">
                                <h5 class="text-[10px] font-bold text-gray-400 mb-1">PRUEBAS RÁPIDAS</h5>
                                <div class="grid grid-cols-3 gap-1">
                                   <button class="btn-test-lovense bg-white/10 hover:bg-white/20 text-white py-1 rounded text-xs" data-action="Vibrate:5" data-time="1">Suave</button>
                                   <button class="btn-test-lovense bg-white/10 hover:bg-white/20 text-white py-1 rounded text-xs" data-action="Vibrate:15" data-time="2">Medio</button>
                                   <button class="btn-test-lovense bg-white/10 hover:bg-white/20 text-white py-1 rounded text-xs" data-action="Vibrate:20" data-time="3">Fuerte</button>
                                </div>
                             </div>
                        </div>

                        <!-- Propinas CRUD -->
                        <div class="sub-card p-3 bg-white/5 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Menú Propinas</h4>
                                <button id="add-tip-btn" class="text-xs text-fuchsia-400 hover:text-white"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="tip-list" class="space-y-1 max-h-40 overflow-y-auto custom-scrollbar">
                                <?php foreach($tip_options as $item): ?>
                                    <div class="flex justify-between items-center p-2 bg-black/30 rounded text-xs group" id="tip-<?php echo $item->id; ?>">
                                        <span class="text-gray-300">💎<?php echo $item->zafiros; ?>: <?php echo htmlspecialchars($item->descripcion); ?></span>
                                        <button onclick="deleteTip(<?php echo $item->id; ?>)" class="text-red-500 opacity-0 group-hover:opacity-100"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Ruleta CRUD -->
                        <div class="sub-card p-3 bg-white/5 rounded-lg">
                             <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-bold text-gray-400 uppercase">Ruleta</h4>
                                <button id="add-roulette-btn" class="text-xs text-cyan-400 hover:text-white"><i class="fas fa-plus"></i></button>
                            </div>
                            <div id="roulette-list" class="space-y-1 max-h-40 overflow-y-auto custom-scrollbar">
                                <?php foreach($roulette_options as $item): ?>
                                    <div class="flex justify-between items-center p-2 bg-black/30 rounded text-xs group" id="roulette-<?php echo $item->id; ?>">
                                        <span class="text-gray-300"><?php echo htmlspecialchars($item->option_text); ?></span>
                                        <button onclick="deleteRoulette(<?php echo $item->id; ?>)" class="text-red-500 opacity-0 group-hover:opacity-100"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: CHAT -->
                    <div id="tab-chat" class="tab-content hidden h-full flex flex-col">
                        <div id="chat-messages" class="flex-grow p-4 space-y-2 overflow-y-auto custom-scrollbar">
                            <p class="text-center text-gray-500 text-xs mt-4">El chat aparecerá aquí.</p>
                        </div>
                        <div class="p-3 bg-black/40 border-t border-white/10 flex-shrink-0">
                            <form id="chat-form" class="flex gap-2">
                                <input type="text" id="chat-input" class="flex-grow bg-white/5 border border-white/10 rounded px-3 py-2 text-white text-sm focus:border-fuchsia-500 outline-none" placeholder="Escribe...">
                                <button type="submit" class="w-10 h-10 bg-fuchsia-600 hover:bg-fuchsia-500 text-white rounded-full flex items-center justify-center transition"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>
    
    <!-- Toggle Mobile -->
    <div id="mobile-toggles" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-black/80 backdrop-blur-md p-2 rounded-full border border-white/10 flex gap-4 lg:hidden z-40">
        <button class="mobile-toggle-btn text-white p-2" onclick="document.getElementById('column-left').classList.toggle('hidden')"><i class="fas fa-cogs"></i></button>
        <button class="mobile-toggle-btn text-white p-2" onclick="document.getElementById('column-right').classList.toggle('hidden')"><i class="fas fa-comments"></i></button>
    </div>

    <script>var RUTA_URL = '<?php echo RUTA_URL; ?>';</script>
    <!-- JS Lógica Completa (Actualizado) -->
    <script src="<?php echo RUTA_URL; ?>/public/js/live_creator.js"></script>
    
<script src="https://api.lovense-api.com/basic-sdk/core.min.js"></script>
</body>
</html>




