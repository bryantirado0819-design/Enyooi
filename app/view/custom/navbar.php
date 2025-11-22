<?php
// Simplificamos el acceso a los datos
$isLoggedIn = isset($datos['navbar_usuario_obj']);
if ($isLoggedIn) {
    $user = $datos['navbar_usuario_obj'];
    $profile = $datos['navbar_perfil_obj'];
    
    // Suponemos que tienes un modelo de usuario para obtener estos datos
    $tempUserModel = new Usuario();
    $message_count = $tempUserModel->getMensajesNoLeidos($_SESSION['logueando']);
    $zafiros_balance = $datos['navbar_zafiros'];

    // Obtenemos el rol del usuario para usarlo en la lógica de la barra de navegación
    $user_rol = $user->rol ?? 'usuario';
}
?>

<style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body {
        padding-top: 80px; /* Altura del navbar (h-20 en Tailwind) */
        background-color: #020617;
    }
    .dropdown-panel, .search-results {
        transition: opacity 0.2s ease-out, transform 0.2s ease-out;
        transform: translateY(-10px);
        opacity: 0;
        pointer-events: none;
    }
    .dropdown-panel.open, .search-results.open {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(124, 92, 255, 0.5); border-radius: 5px; }
</style>

<nav class="fixed top-0 left-0 right-0 z-50" style="background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <div class="flex items-center">
                <a href="<?php echo URL_PROJECT; ?>home" class="flex-shrink-0">
                    <img class="h-12 w-auto" src="<?php echo URL_PROJECT; ?>public/img/logo_enyooi.png" alt="Logo Enyooi">
                </a>
                <div class="hidden md:block ml-10">
                    <div class="flex items-baseline space-x-4">
                        <a href="<?php echo URL_PROJECT; ?>home" class="flex items-center gap-2 text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"><i class="fas fa-home w-4"></i><span>Inicio</span></a>
                        <a href="<?php echo URL_PROJECT; ?>usuarios" class="flex items-center gap-2 text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"><i class="fas fa-users w-4"></i><span>Explorar</span></a>
                        <a href="<?php echo URL_PROJECT; ?>live" class="flex items-center gap-2 text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"><i class="fas fa-video w-4 text-red-500"></i><span>Live</span></a>
                        
                        <?php if ($isLoggedIn && $user_rol === 'creadora'): ?>
                            <a href="<?php echo URL_PROJECT; ?>live/stream" class="flex items-center gap-2 text-white bg-pink-600/80 hover:bg-pink-700/80 px-4 py-2 rounded-full text-sm font-bold transition-colors shadow-lg">
                                <i class="fas fa-broadcast-tower"></i>
                                <span>Hacer Live</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- BOTÓN DEL MENÚ MÓVIL (HAMBURGUESA) -->
            <div class="md:hidden">
                <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-slate-700/50 focus:outline-none">
                    <i id="mobile-menu-icon" class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <?php if ($isLoggedIn): ?>
            <div class="hidden md:flex items-center gap-5">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" id="searchInput" autocomplete="off" class="bg-slate-800/80 border border-slate-700 text-white text-sm rounded-full w-48 px-4 py-2 pr-10 focus:ring-2 focus:ring-pink-500 focus:outline-none transition-all duration-300 focus:w-64" placeholder="Buscar...">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                        <i class="fas fa-search"></i>
                    </button>
                    <div id="searchResults" class="search-results custom-scrollbar absolute left-0 mt-2 w-full origin-top-right rounded-xl shadow-lg max-h-80 overflow-y-auto" style="background: rgba(30, 41, 59, 0.95); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);"></div>
                </div>

                <!-- Mensajes -->
                <a href="<?php echo URL_PROJECT; ?>mensajes" title="Mensajes" class="relative text-gray-300 hover:text-white text-xl transition-colors">
                    <i class="far fa-envelope"></i>
                    <?php if ($message_count > 0): ?>
                    <span class="absolute -top-1 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white"><?php echo $message_count; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Notificaciones -->
                <div class="relative">
                    <button id="notification-bell-button" class="text-gray-300 hover:text-white text-xl transition-colors">
                        <i class="far fa-bell"></i>
                    </button>
                    <span id="notification-count-badge" class="absolute -top-1 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white hidden"></span>
                    
                    <div id="notification-dropdown-panel" class="dropdown-panel absolute right-0 mt-4 w-80 md:w-96 origin-top-right rounded-xl shadow-lg" style="background: rgba(30, 41, 59, 0.95); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);">
                        <div class="p-3 font-semibold text-white border-b border-slate-700/50 flex justify-between items-center">
                            <span>Notificaciones</span>
                        </div>
                        <div id="notification-list-container" class="custom-scrollbar max-h-96 overflow-y-auto">
                            <!-- JS inserta las notificaciones aquí -->
                        </div>
                        <a href="<?php echo URL_PROJECT; ?>notificacion/verTodas" class="block text-center p-2 text-sm text-blue-400 hover:bg-slate-700/50 rounded-b-xl transition-colors">Ver todas</a>
                    </div>
                </div>

                <!-- Zafiros -->
                <a href="<?php echo URL_PROJECT; ?>/wallet" class="px-3 py-1.5 rounded-full text-sm font-bold text-white transition-transform hover:scale-105" style="background: linear-gradient(to right, var(--accent), var(--accent-2));">
                    💎 <?php echo number_format($zafiros_balance); ?>
                </a>

                <!-- Menú de Usuario -->
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-white">
                        <img class="h-9 w-9 rounded-full object-cover" src="<?php echo URL_PROJECT . htmlspecialchars($profile->foto_perfil); ?>" alt="Avatar">
                    </button>
                    <div id="user-menu-panel" class="dropdown-panel absolute right-0 mt-4 w-64 origin-top-right rounded-xl py-1 shadow-lg" style="background: rgba(30, 41, 59, 0.95);">
                         <!-- Contenido del menú de usuario (sin cambios) -->
                        <div class="px-4 py-3 border-b border-slate-700">
                            <p class="text-sm text-white font-semibold">Hola, <?php echo htmlspecialchars(ucwords($user->usuario)); ?></p>
                            <p class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($user->correo); ?></p>
                        </div>
                        <div class="py-1">
                            <?php if ($user_rol === 'creadora'): ?>
                                <a href="<?php echo URL_PROJECT; ?>perfil/<?php echo $user->usuario; ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="far fa-user w-4"></i> Mi Perfil</a>
                                <a href="<?php echo URL_PROJECT; ?>CreatorDashboardController" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-chart-line w-4"></i> Dashboard</a>
                                <a href="<?php echo URL_PROJECT; ?>retiro" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50">💰 Cartera</a>
                                <a href="<?php echo URL_PROJECT; ?>Historial" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-history w-4"></i> Historial</a>
                                <a href="<?php echo URL_PROJECT; ?>settings" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-cog w-4"></i> Configuración</a>
                            <?php else: ?>
                                <a href="<?php echo URL_PROJECT; ?>perfil/<?php echo $user->usuario; ?>" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="far fa-user w-4"></i> Mi Perfil</a>
                                <a href="<?php echo URL_PROJECT; ?>settings" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-cog w-4"></i> Configuración</a>
                            <?php endif; ?>
                        </div>
                        <div class="py-1 border-t border-slate-700"><a href="<?php echo URL_PROJECT; ?>home/salir" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-sign-out-alt w-4"></i> Salir</a></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PANEL DEL MENÚ MÓVIL -->
    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-slate-700/50">
            <a href="<?php echo URL_PROJECT; ?>home" class="flex items-center gap-3 block text-gray-300 hover:bg-slate-700/50 px-3 py-2 rounded-md text-base font-medium"><i class="fas fa-home w-5"></i>Inicio</a>
            <a href="<?php echo URL_PROJECT; ?>usuarios" class="flex items-center gap-3 block text-gray-300 hover:bg-slate-700/50 px-3 py-2 rounded-md text-base font-medium"><i class="fas fa-users w-5"></i>Explorar</a>
            <a href="<?php echo URL_PROJECT; ?>live" class="flex items-center gap-3 block text-gray-300 hover:bg-slate-700/50 px-3 py-2 rounded-md text-base font-medium"><i class="fas fa-video w-5 text-red-500"></i>Live</a>
            
            <?php if ($isLoggedIn && $user_rol === 'creadora'): ?>
                <a href="<?php echo URL_PROJECT; ?>live/stream" class="flex items-center gap-3 block text-white bg-pink-600/80 px-3 py-2 rounded-md text-base font-medium"><i class="fas fa-broadcast-tower w-5"></i>Hacer Live</a>
            <?php endif; ?>
        </div>
        <?php if ($isLoggedIn): ?>
        <div class="pt-4 pb-3 border-t border-slate-700/50">
            <div class="flex items-center px-5 mb-3">
                <img class="h-10 w-10 rounded-full object-cover" src="<?php echo URL_PROJECT . htmlspecialchars($profile->foto_perfil); ?>" alt="Avatar">
                <div class="ml-3">
                    <div class="text-base font-medium text-white"><?php echo htmlspecialchars(ucwords($user->usuario)); ?></div>
                    <div class="text-sm font-medium text-gray-400">💎 <?php echo number_format($zafiros_balance); ?></div>
                </div>
            </div>
            <div class="mt-3 px-2 space-y-1">
                 <!-- Links del menú móvil (sin cambios) -->
                <a href="<?php echo URL_PROJECT; ?>mensajes" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="far fa-envelope w-5"></i>Mensajes</a>
                <a href="<?php echo URL_PROJECT; ?>notificacion/verTodas" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="far fa-bell w-5"></i>Notificaciones</a>
                <div class="border-t border-slate-700 my-2"></div>
                
                <?php if ($user_rol === 'creadora'): ?>
                     <a href="<?php echo URL_PROJECT; ?>perfil/<?php echo $user->usuario; ?>" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="far fa-user w-5"></i>Mi Perfil</a>
                    <a href="<?php echo URL_PROJECT; ?>CreatorDashboardController" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="fas fa-chart-line w-5"></i>Dashboard</a>
                    <a href="<?php echo URL_PROJECT; ?>wallet" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50">💰 Cartera</a>
                    <a href="<?php echo URL_PROJECT; ?>Historial" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:bg-slate-700/50"><i class="fas fa-history w-4"></i> Historial</a>
                <?php else: // Espectador ?>
                    <a href="<?php echo URL_PROJECT; ?>perfil/<?php echo $user->usuario; ?>" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="far fa-user w-5"></i>Mi Perfil</a>
                <?php endif; ?>

                <a href="<?php echo URL_PROJECT; ?>settings" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="fas fa-cog w-5"></i>Configuración</a>
                <a href="<?php echo URL_PROJECT; ?>home/salir" class="flex items-center gap-3 block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-slate-700/50"><i class="fas fa-sign-out-alt w-5"></i>Salir</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</nav>

<!-- ✅ SCRIPT ORIGINAL RESTAURADO Y MEJORADO -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- LÓGICA DEL MENÚ MÓVIL (RESTAURADA) ---
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuIcon = document.getElementById('mobile-menu-icon');

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            mobileMenuIcon.classList.toggle('fa-bars', isHidden);
            mobileMenuIcon.classList.toggle('fa-times', !isHidden);
        });
    }

    // --- LÓGICA GENERAL PARA DROPDOWNS ---
    const setupDropdown = (buttonId, panelId) => {
        const button = document.getElementById(buttonId);
        const panel = document.getElementById(panelId);
        if (!button || !panel) return;

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            // Cierra otros paneles abiertos antes de abrir el actual
            document.querySelectorAll('.dropdown-panel.open, .search-results.open').forEach(openPanel => {
                if (openPanel !== panel) openPanel.classList.remove('open');
            });
            panel.classList.toggle('open');
        });
    };
    
    // Cierra todos los dropdowns si se hace clic fuera
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.dropdown-panel.open, .search-results.open').forEach(panel => {
                panel.classList.remove('open');
            });
        }
    });

    // Inicializa los dropdowns si el usuario ha iniciado sesión
    <?php if ($isLoggedIn): ?>
    setupDropdown('user-menu-button', 'user-menu-panel');
    setupDropdown('notification-bell-button', 'notification-dropdown-panel');
    <?php endif; ?>

    // --- LÓGICA DE BÚSQUEDA (SIN CAMBIOS) ---
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if(searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();
            if (query.length < 2) {
                searchResults.classList.remove('open');
                searchResults.innerHTML = '';
                return;
            }
            searchTimeout = setTimeout(() => {
                fetch(`<?php echo URL_PROJECT; ?>search/users?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            const userHtml = `
                                <a href="<?php echo URL_PROJECT; ?>perfil/${user.usuario}" class="flex items-center gap-3 p-2 hover:bg-slate-700/50 rounded-lg m-1 transition-colors">
                                    <img src="<?php echo URL_PROJECT; ?>${user.foto_perfil || 'public/img/defaults/default_avatar.png'}" class="h-8 w-8 rounded-full object-cover">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-200">${user.nickname_artistico || user.usuario}</p>
                                        <p class="text-xs text-gray-400">@${user.usuario}</p>
                                    </div>
                                </a>`;
                            searchResults.insertAdjacentHTML('beforeend', userHtml);
                        });
                    } else {
                        searchResults.innerHTML = '<p class="text-sm text-center text-gray-400 p-3">No se encontraron resultados.</p>';
                    }
                    searchResults.classList.add('open');
                }).catch(err => console.error("Error en búsqueda:", err));
            }, 300);
        });
    }
});
</script>

