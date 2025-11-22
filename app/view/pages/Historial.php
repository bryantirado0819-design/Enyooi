<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
// Helper para dar formato a los números y fechas
function formatNumber($num) {
    return number_format($num, 0, ',', '.');
}
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8 text-white">
    <h1 class="text-3xl font-bold mb-6">Historial de Actividad</h1>

    <!-- Pestañas para Navegación -->
    <div class="mb-6">
        <div class="border-b border-gray-700">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button id="tab-ingresos" class="tab-btn active text-indigo-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Ingresos
                </button>
                <button id="tab-retiros" class="tab-btn text-gray-400 hover:text-white hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Retiros
                </button>
            </nav>
        </div>
    </div>

    <!-- Contenido de Ingresos (Visible por defecto) -->
    <div id="content-ingresos" class="tab-content">
        <!-- Resumen y Exportación -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="card-glass p-6 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-200">Ingresos este mes</p>
                    <p class="text-2xl font-bold">
                        <?php 
                            $ingresosMes = array_reduce($datos['ingresos'], function($carry, $item) {
                                if (strtotime($item->fecha_transaccion) > strtotime('-30 days')) {
                                    $carry += $item->monto_usd_creador;
                                }
                                return $carry;
                            }, 0);
                            echo '$' . number_format($ingresosMes, 2);
                        ?>
                    </p>
                </div>
                <a href="<?php echo RUTA_URL; ?>/historial/exportarPDF/mes" target="_blank" class="btn-gradient-sm py-2 px-4 rounded-full text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar Mes
                </a>
            </div>
            <div class="card-glass p-6 rounded-2xl flex items-center justify-between">
                 <div>
                    <p class="text-sm text-blue-200">Ingresos esta semana</p>
                    <p class="text-2xl font-bold">
                        <?php 
                            $ingresosSemana = array_reduce($datos['ingresos'], function($carry, $item) {
                                if (strtotime($item->fecha_transaccion) > strtotime('-7 days')) {
                                    $carry += $item->monto_usd_creador;
                                }
                                return $carry;
                            }, 0);
                            echo '$' . number_format($ingresosSemana, 2);
                        ?>
                    </p>
                </div>
                <a href="<?php echo RUTA_URL; ?>/historial/exportarPDF/semana" target="_blank" class="btn-gradient-sm py-2 px-4 rounded-full text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar Semana
                </a>
            </div>
        </div>

        <!-- Tabla de Ingresos -->
        <div class="card-glass rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-white/10">
                        <tr>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4 hidden md:table-cell">Espectador</th>
                            <th class="p-4 text-right">Monto (Gemas)</th>
                            <th class="p-4 text-right">Monto (USD)</th>
                            <th class="p-4 text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($datos['ingresos'] as $ingreso): ?>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <td class="p-4"><?php echo formatDate($ingreso->fecha_transaccion); ?></td>
                            <td class="p-4"><?php echo ucfirst(str_replace('_', ' ', $ingreso->tipo_transaccion)); ?></td>
                            <td class="p-4 hidden md:table-cell"><?php echo $ingreso->espectador_usuario ?? 'N/A'; ?></td>
                            <td class="p-4 text-right text-cyan-400 font-semibold"><?php echo formatNumber($ingreso->monto_zafiros_creador); ?></td>
                            <td class="p-4 text-right text-green-400 font-semibold">$<?php echo number_format($ingreso->monto_usd_creador, 2); ?></td>
                            <td class="p-4 text-center">
                                <button class="btn-detail" data-id="<?php echo $ingreso->id_transaccion; ?>" data-details='<?php echo json_encode($ingreso); ?>'>
                                    <i class="fas fa-eye text-indigo-400"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Contenido de Retiros (Oculto por defecto) -->
    <div id="content-retiros" class="tab-content hidden">
        <div class="card-glass rounded-2xl overflow-hidden">
             <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-white/10">
                        <tr>
                            <th class="p-4">Fecha Solicitud</th>
                            <!-- ✅ CORRECCIÓN: Columna 'Método' eliminada porque no existe en la tabla 'solicitudes_retiro' -->
                            <th class="p-4 text-right">Monto (USD)</th>
                            <th class="p-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($datos['retiros'] as $retiro): ?>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <td class="p-4"><?php echo formatDate($retiro->fecha_solicitud); ?></td>
                            <td class="p-4 text-right text-green-400 font-semibold">$<?php echo number_format($retiro->monto_usd, 2); ?></td>
                            <td class="p-4 text-center">
                                <?php 
                                    $estadoClass = '';
                                    if ($retiro->estado == 'aprobado') $estadoClass = 'bg-green-500/20 text-green-400';
                                    elseif ($retiro->estado == 'pendiente') $estadoClass = 'bg-yellow-500/20 text-yellow-400';
                                    else $estadoClass = 'bg-red-500/20 text-red-400';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $estadoClass; ?>">
                                    <?php echo ucfirst($retiro->estado); ?>
                                </span>
                            </td>
                        </tr>
                         <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalles de Transacción -->
<div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4 hidden z-50">
    <div class="card-glass rounded-2xl w-full max-w-md p-6 relative">
        <button id="close-modal" class="absolute top-4 right-4 text-gray-400 hover:text-white">
            <i class="fas fa-times fa-lg"></i>
        </button>
        <h3 class="text-xl font-bold mb-4">Detalle de la Transacción</h3>
        <div class="space-y-3 text-sm">
            <p><strong>ID Transacción:</strong> <span id="modal-id"></span></p>
            <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
            <p><strong>Tipo:</strong> <span id="modal-tipo"></span></p>
            <p><strong>Espectador:</strong> <span id="modal-espectador"></span></p>
            <hr class="border-white/20 my-3">
            <div class="flex justify-between items-center">
                <span>Gemas para creadora:</span>
                <span id="modal-zafiros" class="font-bold text-cyan-400"></span>
            </div>
             <div class="flex justify-between items-center">
                <span>Comisión Plataforma (USD):</span>
                <span id="modal-comision" class="font-bold text-red-400"></span>
            </div>
            <div class="flex justify-between items-center text-lg">
                <span>Tus Ganancias (USD):</span>
                <span id="modal-ganancia" class="font-bold text-green-400"></span>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo RUTA_URL; ?>/js/historial.js"></script>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>
