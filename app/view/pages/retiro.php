<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/retiro.css">
<style>
    
    /* Estilos adicionales para componentes dinámicos como el dropdown personalizado */
    .custom-select-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: rgba(10, 20, 40, 0.9);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 10;
        max-height: 200px;
        overflow-y: auto;
        animation: fadeIn 0.2s ease-out;
    }
    .custom-select-option {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .custom-select-option:hover, .custom-select-option.selected {
        background-color: var(--accent-2);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Estilos para el modal de verificación */
    #verification-modal .input-code {
        width: 1.5em; height: 2em; text-align: center; font-size: 2rem;
        border: 2px solid var(--border-color); background: rgba(0,0,0,0.3);
        border-radius: 0.5rem; color: white; caret-color: var(--accent);
    }
    #verification-modal .input-code:focus { outline: none; border-color: var(--accent); }
    #resend-code-link.disabled { color: #6b7280; cursor: not-allowed; }
</style>

<body class="p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 flex items-center gap-3"><i class="fas fa-hand-holding-usd text-2xl" style="color: var(--accent-2);"></i> Retirar Fondos</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-8">
                <div class="card-glass rounded-2xl p-6">
                    <h2 class="text-lg font-medium text-slate-300">Saldo Disponible</h2>
                    <div class="mt-2">
                        <p class="text-5xl font-bold tracking-tighter text-white">💎 <span id="zafiro-balance">---</span></p>
                        <p class="text-slate-400">≈ $<span id="usd-equivalent">--.--</span> USD para retirar</p>
                    </div>
                </div>
                <div class="card-glass rounded-2xl p-6">
                    <h2 class="text-lg font-medium text-slate-300 mb-4">Ingresos (Últimos 7 Días)</h2>
                    <div class="relative h-64">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="card-glass rounded-2xl p-6">
                    <nav class="border-b border-slate-700 mb-6 -mb-px flex space-x-4" id="retiro-tabs">
                        <button class="tab-button active" data-target="withdraw">Solicitar Retiro</button>
                        <button class="tab-button" data-target="history">Historial</button>
                    </nav>
                    
                    <div>
                        <div id="tab-withdraw" class="tab-content active">
                             <form id="withdrawal-form" class="space-y-6">
                                <p id="min-withdrawal-alert" class="text-sm text-amber-400 bg-amber-500/10 p-3 rounded-lg hidden"><i class="fas fa-info-circle mr-2"></i>Necesitas un mínimo de $20.00 USD para solicitar un retiro.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="font-semibold text-white mb-3">1. Información de Pago</h3>
                                        <div class="space-y-4">
                                            <div>
                                                <label for="fullName" class="block text-sm font-medium text-slate-300 mb-1">Nombres Completos del Titular</label>
                                                <input type="text" name="fullName" class="w-full form-control-glass rounded-md" required>
                                            </div>
                                            <div>
                                                <label for="idNumber" class="block text-sm font-medium text-slate-300 mb-1">Nº de Cédula del Titular</label>
                                                <input type="text" name="idNumber" class="w-full form-control-glass rounded-md" required>
                                            </div>
                                            <div>
                                                <label for="bank" class="block text-sm font-medium text-slate-300 mb-1">Banco</label>
                                                <select name="bank" class="w-full form-control-glass rounded-md" required>
                                                    <option value="Banco Pichincha">Banco Pichincha</option>
                                                    <option value="Banco Guayaquil">Banco Guayaquil</option>
                                                    <option value="Banco Bolivariano">Banco Bolivariano</option>
                                                    <option value="Otro">Otro (Especificar)</option>
                                                </select>
                                            </div>
                                            <div id="other-bank-container" class="hidden">
                                                <input type="text" name="otherBankName" class="w-full form-control-glass rounded-md" placeholder="Nombre del otro banco">
                                            </div>
                                            <div>
                                                <label for="accountNumber" class="block text-sm font-medium text-slate-300 mb-1">Número de Cuenta</label>
                                                <input type="text" name="accountNumber" class="w-full form-control-glass rounded-md" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-white mb-3">2. Monto del Retiro</h3>
                                        <div class="space-y-4">
                                            <div>
                                                <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Monto a Retirar (USD)</label>
                                                <input type="number" name="amount" id="amount" min="20" step="1" class="w-full form-control-glass rounded-md" placeholder="$20.00 mínimo" required>
                                            </div>
                                            <div class="bg-slate-800/50 p-3 rounded-lg text-sm space-y-1">
                                                <div class="flex justify-between"><span>Zafiros a deducir:</span><span id="zafiros-to-deduct" class="font-bold">💎 0</span></div>
                                                <div class="flex justify-between"><span>Comisión (10%):</span><span id="platform-fee" class="font-bold text-red-400">-$0.00</span></div>
                                                <hr class="border-slate-600 my-1">
                                                <div class="flex justify-between text-base"><strong>Recibirás aprox:</strong><strong id="net-amount" class="text-green-400">$0.00</strong></div>
                                            </div>
                                            <button type="submit" id="submit-withdrawal" class="w-full btn-gradient py-3 rounded-lg text-base mt-2">
                                                <i class="fas fa-shield-alt mr-2"></i>Continuar para verificar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div id="tab-history" class="tab-content">
                            <h3 class="text-lg font-semibold mb-4">Historial de Retiros</h3>
                            <div id="withdrawal-history-list" class="space-y-3">
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="verification-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
        <div class="card-glass modal-panel rounded-2xl p-8 max-w-md w-11/12 text-center">
            <h3 class="text-2xl font-bold mb-2">Verificación de Seguridad</h3>
            <p class="text-slate-300 mb-6">Completa los siguientes pasos para proteger tu retiro.</p>
            
            <div class="space-y-4">
                <p class="text-sm">Hemos enviado un código de 6 dígitos a tu correo. Por favor, ingrésalo a continuación.</p>
                <div id="code-inputs" class="flex justify-center gap-2">
                    <input type="text" maxlength="1" class="input-code">
                    <input type="text" maxlength="1" class="input-code">
                    <input type="text" maxlength="1" class="input-code">
                    <input type="text" maxlength="1" class="input-code">
                    <input type="text" maxlength="1" class="input-code">
                    <input type="text" maxlength="1" class="input-code">
                </div>
                <div class="text-xs text-slate-400">¿No recibiste el código? <a href="#" id="resend-code-link" class="resend-link text-blue-400 hover:text-blue-300">Reenviar código</a></div>
            </div>

            <div class="space-y-4 mt-6">
                 <p class="text-sm">Por favor, completa el CAPTCHA para continuar.</p>
                 <div class="flex justify-center">
                    <div class="g-recaptcha" data-sitekey="6LdjY9crAAAAAAoPIy-4OAklDC0l6Fyhkt3vrDCZ"></div>
                 </div>
            </div>

            <div id="verification-feedback" class="text-red-400 text-sm mt-4 h-5"></div>

            <div class="flex gap-4 mt-8">
                <button id="cancel-verification-btn" class="flex-1 py-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">Cancelar</button>
                <button id="confirm-verification-btn" class="flex-1 py-2 rounded-full btn-gradient">Confirmar Retiro</button>
            </div>
        </div>
    </div>

    <div id="info-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
        <div class="card-glass modal-panel rounded-2xl p-8 max-w-sm w-11/12 text-center">
            <div id="info-modal-icon" class="text-5xl mb-4"></div>
            <h3 id="info-modal-title" class="text-2xl font-bold mb-2"></h3>
            <p id="info-modal-message" class="text-blue-200 mb-6"></p>
            <button id="info-modal-close-btn" class="w-full py-2 rounded-full btn-gradient">Entendido</button>
        </div>
    </div>


    <script src="<?php echo URL_PROJECT; ?>/public/js/retiro.js"></script>
</body>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>