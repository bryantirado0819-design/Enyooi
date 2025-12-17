<?php require RUTA_APP . '/view/custom/header.php'; ?>
<?php require RUTA_APP . '/view/custom/navbar.php'; ?>

<!-- Estilos CSS Específicos para Lives -->
<style>
    :root { 
        --accent: #ff4fa3; 
        --accent-2: #7c5cff;
        --dark-bg: #081127;
    }
    
    /* Efecto Glassmorphism */
    .glass-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: all 0.3s ease;
    }
    
    .live-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        border-color: var(--accent);
    }
    
    /* Insignia EN VIVO pulsante */
    .live-badge {
        background-color: var(--accent);
        box-shadow: 0 0 15px var(--accent);
        animation: pulse-live 2s infinite;
    }
    @keyframes pulse-live {
        0%, 100% { transform: scale(1); box-shadow: 0 0 15px var(--accent); }
        50% { transform: scale(1.1); box-shadow: 0 0 25px var(--accent); }
    }
    
    .filter-input {
        background-color: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .filter-input:focus {
        background-color: rgba(0,0,0,0.5);
        border-color: var(--accent);
        box-shadow: 0 0 15px rgba(255, 79, 163, 0.3);
        outline: none;
    }
    .filter-input option {
        background-color: #0f172a;
        color: white;
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 fade-in">

    <!-- Encabezado y Filtros -->
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-white text-center mb-6">Explorar Transmisiones</h1>

        <div class="glass-card rounded-xl p-4 flex flex-wrap items-center justify-center gap-4">
            <!-- Búsqueda -->
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors"></i>
                <input type="text" id="filter-search" placeholder="Buscar creador o título..." class="w-full sm:w-64 p-2.5 pl-10 rounded-full filter-input text-sm">
            </div>
            
            <!-- Categorías -->
            <div>
                <select id="filter-category" class="p-2.5 rounded-full filter-input text-sm appearance-none pr-8 cursor-pointer">
                    <option value="">Todas las Categorías</option>
                    <option value="Conversación">Conversación</option>
                    <option value="Juegos">Juegos</option>
                    <option value="Música">Música</option>
                    <option value="Latina">Latina</option>
                    <option value="Lovense">Lovense</option>
                    <option value="Fitness">Fitness</option>
                </select>
            </div>
        </div>
    </header>

    <?php if (empty($datos['streams'])) : ?>
        <!-- Estado Vacío -->
        <div class="text-center text-slate-400 py-20 glass-card rounded-2xl">
            <div class="mb-4 relative inline-block">
                <i class="fas fa-satellite-dish fa-4x opacity-20"></i>
                <i class="fas fa-times absolute bottom-0 right-0 text-red-500 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-semibold text-white">No hay transmisiones en vivo ahora</h2>
            <p class="mt-2 text-lg text-slate-300">¡Sé la primera en transmitir hoy!</p>
            <?php if(($_SESSION['rol'] ?? '') === 'creadora'): ?>
                <a href="<?php echo RUTA_URL; ?>live/stream" class="mt-6 inline-block px-6 py-2 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold hover:scale-105 transition-transform">
                    Iniciar Live
                </a>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        <!-- Grid de Streams -->
        <main id="streams-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            <?php foreach ($datos['streams'] as $stream) : ?>
                <?php
                    // Lógica segura para URLs de imágenes
                    $thumbnail = !empty($stream->thumbnail_url)
                        ? (filter_var($stream->thumbnail_url, FILTER_VALIDATE_URL) ? $stream->thumbnail_url : RUTA_URL . ltrim($stream->thumbnail_url, '/'))
                        : 'https://placehold.co/600x400/0f172a/7c5cff?text=' . urlencode($stream->stream_title);

                    $avatar = !empty($stream->creator_avatar) 
                        ? (filter_var($stream->creator_avatar, FILTER_VALIDATE_URL) ? $stream->creator_avatar : RUTA_URL . ltrim($stream->creator_avatar, '/'))
                        : RUTA_URL . 'public/img/defaults/default_avatar.png';

                    $streamLink = RUTA_URL . 'live/watch/' . $stream->stream_id;
                    
                    // Datos para filtrado JS
                    $filter_tags = htmlspecialchars(strtolower($stream->tags ?? ''));
                    $filter_title = htmlspecialchars(strtolower($stream->stream_title ?? ''));
                    $filter_creator = htmlspecialchars(strtolower($stream->creator_nickname ?? ''));
                ?>

                <div class="live-card-wrapper" 
                     data-title="<?php echo $filter_title; ?>" 
                     data-creator="<?php echo $filter_creator; ?>" 
                     data-tags="<?php echo $filter_tags; ?>">
                     
                    <a href="<?php echo $streamLink; ?>" class="group block h-full">
                        <div class="live-card glass-card rounded-2xl overflow-hidden h-full flex flex-col relative">
                            
                            <!-- Thumbnail & Overlay -->
                            <div class="relative aspect-video">
                                <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($stream->stream_title); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                
                                <!-- Badges -->
                                <div class="absolute top-3 left-3 live-badge text-white text-[10px] font-bold px-2 py-1 rounded-sm uppercase tracking-wider">
                                    EN VIVO
                                </div>
                                
                                <!-- Viewer Count (Actualizado por Socket) -->
                                <div id="viewers-<?php echo $stream->stream_id; ?>" class="absolute top-3 right-3 bg-black/60 backdrop-blur-md text-white text-xs px-2 py-1 rounded-full flex items-center gap-1 border border-white/10">
                                    <i class="fas fa-eye text-pink-500"></i>
                                    <span class="font-mono">0</span>
                                </div>
                                
                                <!-- Gradiente Inferior -->
                                <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#081127] to-transparent"></div>
                            </div>

                            <!-- Info Content -->
                            <div class="p-4 flex-grow flex flex-col -mt-4 relative z-10">
                                <div class="flex items-start gap-3">
                                    <img src="<?php echo $avatar; ?>" class="w-10 h-10 rounded-full border-2 border-pink-500 object-cover shrink-0">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-white truncate text-sm leading-tight group-hover:text-pink-400 transition-colors" title="<?php echo htmlspecialchars($stream->stream_title); ?>">
                                            <?php echo htmlspecialchars($stream->stream_title); ?>
                                        </h3>
                                        <p class="text-xs text-slate-400 mt-1 truncate">
                                            @<?php echo htmlspecialchars($stream->creator_nickname); ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($stream->tags)) : ?>
                                    <div class="flex flex-wrap gap-1 mt-3 pl-12">
                                        <?php 
                                            $tags = array_slice(array_map('trim', explode(',', $stream->tags)), 0, 2);
                                            foreach($tags as $tag): 
                                        ?>
                                            <span class="text-[10px] bg-white/5 text-slate-300 px-2 py-0.5 rounded-full border border-white/5">
                                                <?php echo htmlspecialchars($tag); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            
            <!-- Sin resultados de filtro -->
             <div id="no-filter-results" class="hidden col-span-full text-center py-10">
                <p class="text-slate-400 text-lg">No encontramos lives con esa búsqueda.</p>
                <button onclick="document.getElementById('filter-search').value=''; document.getElementById('filter-search').dispatchEvent(new Event('input'));" class="mt-2 text-pink-500 hover:underline">Limpiar filtros</button>
            </div>

        </main>
    <?php endif; ?>

</div>

<!-- 1. Configuración de Sockets -->
<script>
    // Usamos SOCKET_URL de tu config.php, o fallback a puerto 3000
    const SOCKET_URL = '<?php echo defined("SOCKET_URL") ? SOCKET_URL : RUTA_URL . ":3000"; ?>';
    // Alias para compatibilidad con scripts antiguos
    const NODE_SERVER_URL = SOCKET_URL;
</script>

<!-- 2. Cliente Socket.IO (Cargado desde el servidor Node para asegurar versión) -->
<script src="<?php echo defined('SOCKET_URL') ? SOCKET_URL : RUTA_URL . ':3000'; ?>/socket.io/socket.io.js"></script>

<!-- 3. Lógica de Explorador -->
<script src="<?php echo RUTA_URL; ?>public/js/live_explorer.js?v=<?php echo time(); ?>"></script>

<?php require RUTA_APP . '/view/custom/footer.php'; ?>