<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8 text-white">
    <h1 class="text-3xl font-bold mb-6">Todas tus Notificaciones</h1>

    <div class="card-glass rounded-2xl p-4 md:p-6">
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                <?php if (empty($datos['notificaciones'])): ?>
                    <li class="text-center text-gray-400 py-16">
                        <div class="relative pb-8">
                            <div class="relative flex items-center justify-center space-x-3">
                                <div>
                                    <span class="h-12 w-12 rounded-full bg-gray-700 flex items-center justify-center ring-8 ring-gray-800">
                                        <i class="fas fa-bell-slash text-xl text-gray-400"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-lg text-gray-300">No tienes notificaciones todavía.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php else: ?>
                    <?php foreach ($datos['notificaciones'] as $index => $notif): ?>
                    <li>
                        <div class="relative pb-8">
                            <?php if ($index !== count($datos['notificaciones']) - 1): ?>
                                <span class="absolute top-4 left-6 -ml-px h-full w-0.5 bg-gray-700" aria-hidden="true"></span>
                            <?php endif; ?>
                            <div class="relative flex items-center space-x-3">
                                <div>
                                    <span class="h-12 w-12 rounded-full bg-gray-700 flex items-center justify-center ring-8 ring-gray-800">
                                        <?php 
                                            $icon = 'fa-bell'; // Icono por defecto
                                            if ($notif->tipoNotificacion == 1) $icon = 'fa-heart text-red-400';
                                            if ($notif->tipoNotificacion == 2) $icon = 'fa-comment text-blue-400';
                                        ?>
                                        <i class="fas <?php echo $icon; ?> text-xl"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-300">
                                            <a href="<?php echo RUTA_URL . 'perfil/' . $notif->usuarioAccionNombre; ?>" class="font-bold text-white"><?php echo htmlspecialchars($notif->usuarioAccionNombre); ?></a>
                                            <?php echo htmlspecialchars($notif->mensajeNotificacion); ?>
                                        </p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time datetime="<?php echo $notif->fechaNotificacion; ?>"><?php echo format_time_ago($notif->fechaNotificacion); ?></time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>
