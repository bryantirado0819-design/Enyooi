<?php require RUTA_APP . '/view/custom/header.php'; // Incluye tu cabecera 
include_once __DIR__ . '/../custom/navbar.php';?>

<!-- Estilos CSS del ejemplo, integrados aquí para asegurar que se carguen -->
<style>
    /* Variables de color del ejemplo */
    :root { 
        --accent: #ff4fa3; 
        --accent-2: #7c5cff;
        --dark-bg: #081127;
    }
    
    /* Estilo de tarjeta de vidrio del ejemplo */
    .glass-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: all 0.3s ease;
    }
    
    /* Animación de hover de la tarjeta */
    .live-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        border-color: var(--accent);
    }
    
    /* Animación de la insignia LIVE */
    .live-badge {
        background-color: var(--accent);
        box-shadow: 0 0 15px var(--accent);
        animation: pulse-live 2s infinite;
    }
    @keyframes pulse-live {
        0%, 100% { transform: scale(1); box-shadow: 0 0 15px var(--accent); }
        50% { transform: scale(1.1); box-shadow: 0 0 25px var(--accent); }
    }
    
    /* Estilos para los filtros */
    .filter-input {
        background-color: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    .filter-input:focus {
        background-color: rgba(0,0,0,0.5);
        border-color: var(--accent);
        box-shadow: 0 0 15px rgba(255, 79, 163, 0.3);
        outline: none;
    }
</style>

<!-- Contenido de la página -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    <!-- Encabezado y Filtros (del ejemplo) -->
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-white text-center mb-6">Explorar Cuartos en Vivo</h1>

        <div class="glass-card rounded-xl p-4 flex flex-wrap items-center justify-center gap-4">
            <!-- Filtro de Búsqueda -->
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="filter-search" placeholder="Buscar por nombre o título..." class="w-full sm:w-56 p-2.5 pl-10 rounded-full filter-input text-sm">
            </div>
            
            <!-- Filtro de Categoría -->
            <div>
                <select id="filter-category" class="p-2.5 rounded-full filter-input text-sm appearance-none pr-8">
                    <option value="">Todas las Categorías</option>
                    <!-- Puedes popular esto dinámicamente si quieres, por ahora usamos las del ejemplo -->
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
        <!-- Mensaje si no hay streams en vivo -->
        <div class="text-center text-slate-400 py-20 glass-card rounded-2xl">
            <i class="fas fa-video-slash fa-4x mb-4 opacity-50"></i>
            <h2 class="text-2xl font-semibold text-white">No hay transmisiones en vivo</h2>
            <p class="mt-2 text-lg text-slate-300">¡Vuelve más tarde para ver quién está en línea!</p>
        </div>
        
    <?php else : ?>
        <!-- Grid responsivo para las tarjetas de stream -->
        <main id="streams-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            <?php foreach ($datos['streams'] as $stream) : ?>
                <?php
                    // --- Fallbacks y URLs ---
                    $thumbnail = !empty($stream->thumbnail_url)
                        ? (filter_var($stream->thumbnail_url, FILTER_VALIDATE_URL) ? $stream->thumbnail_url : RUTA_URL . ltrim($stream->thumbnail_url, '/'))
                        : 'https://placehold.co/600x400/0f172a/7c5cff?text=' . urlencode(htmlspecialchars($stream->stream_title));

                    // ✅ --- CORRECCIÓN ---
                    // Construimos la URL del avatar manualmente
                    // Asume que RUTA_URL termina en '/' y que la ruta default es correcta
                    $avatar = (!empty($stream->creator_avatar) ? RUTA_URL . ltrim($stream->creator_avatar, '/') : RUTA_URL . 'public/img/defaults/default_avatar.png'); 

                    $streamLink = RUTA_URL . 'live/watch/' . $stream->stream_id;
                    
                    // Prepara los datos para los filtros de JS
                    $filter_tags = htmlspecialchars(strtolower($stream->tags ?? ''));
                    $filter_title = htmlspecialchars(strtolower($stream->stream_title ?? ''));
                    $filter_creator = htmlspecialchars(strtolower($stream->creator_nickname ?? ''));
                ?>

                <!-- 
                  Wrapper de tarjeta para filtros.
                  Usamos atributos data-* para que el JS pueda filtrar.
                -->
                <div class="live-card-wrapper" 
                     data-title="<?php echo $filter_title; ?>" 
                     data-creator="<?php echo $filter_creator; ?>" 
                     data-tags="<?php echo $filter_tags; ?>">
                     
                    <a href="<?php echo $streamLink; ?>" class="group block">
                        <div class="live-card glass-card rounded-2xl overflow-hidden h-full flex flex-col">
                            
                            <div class="relative">
                                <img src="<?php echo $thumbnail; ?>" alt="Thumbnail de <?php echo htmlspecialchars($stream->stream_title); ?>" class="w-full h-48 object-cover">
                                <div class="absolute top-3 left-3 live-badge text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-wider">Live</div>
                                
                                <!-- Contador de Espectadores (ID único para JS) -->
                                <div id="viewers-<?php echo $stream->stream_id; ?>" class="absolute top-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1 backdrop-blur-sm">
                                    <i class="fas fa-eye"></i>
                                    <span>--</span> <!-- JS lo actualiza -->
                                </div>
                                
                                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/70 to-transparent"></div>
                                <div class="absolute bottom-3 left-3 flex items-center gap-3">
                                    <img src="<?php echo $avatar; ?>" alt="Avatar de <?php echo htmlspecialchars($stream->creator_nickname ?? 'Usuario'); ?>" class="w-10 h-10 rounded-full border-2 border-[var(--accent-2)] object-cover">
                                    <h4 class="font-semibold text-white" style="text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                                        <?php echo htmlspecialchars($stream->creator_nickname ?? 'Usuario'); ?>
                                    </h4>
                                </div>
                            </div>

                            <div class="p-4 flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-semibold truncate text-white mb-2" title="<?php echo htmlspecialchars($stream->stream_title); ?>">
                                        <?php echo htmlspecialchars($stream->stream_title); ?>
                                    </h3>
                                </div>

                                <?php if (!empty($stream->tags)) : ?>
                                    <div class="flex flex-wrap gap-1 mt-auto">
                                        <?php
                                            $tagsArray = array_map('trim', explode(',', $stream->tags));
                                            $tagsToShow = array_slice($tagsArray, 0, 3);
                                        ?>
                                        <?php foreach ($tagsToShow as $tag) : ?>
                                            <span class="text-xs bg-purple-500/30 text-purple-300 px-2 py-0.5 rounded-full">
                                                <?php echo htmlspecialchars($tag); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($tagsArray) > 3): ?>
                                             <span class="text-xs text-purple-400 px-1 py-0.5">+<?php echo count($tagsArray) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div> <!-- Fin de .live-card-wrapper -->
                
            <?php endforeach; ?>
            
            <!-- Mensaje para "Sin resultados" de filtros (controlado por JS) -->
             <div id="no-filter-results" class="hidden text-center text-slate-400 py-20 glass-card rounded-2xl sm:col-span-2 lg:col-span-3 xl:col-span-4">
                <i class="fas fa-search fa-4x mb-4 opacity-50"></i>
                <h2 class="text-2xl font-semibold text-white">Sin resultados</h2>
                <p class="mt-2 text-lg text-slate-300">Intenta con otros filtros o términos de búsqueda.</p>
            </div>

        </main>
    <?php endif; ?>

</div>

<!-- Scripts (Asegúrate que footer.php cargue jQuery si es necesario, aunque este JS no lo usa) -->

<!-- 1. Cliente Socket.IO (Cárgalo desde tu footer.php o aquí) -->
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

<!-- 2. Pasa la URL de Node.js a JS -->
<script>
    // Define la URL del servidor Node.js (leída desde config.php)
    const NODE_SERVER_URL = '<?php echo defined("NODE_SERVER_URL") ? NODE_SERVER_URL : "http://localhost:3000"; ?>';
</script>

<!-- 3. Carga el nuevo JS para esta página -->
<script src="<?php echo RUTA_URL; ?>js/live_explorer.js?v=<?php echo time(); ?>"></script>


<?php require RUTA_APP . '/view/custom/footer.php'; // Incluye tu pie de página ?>
