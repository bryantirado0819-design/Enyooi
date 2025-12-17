<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
?>

<link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/wallet.css">

<body>
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>

    <div class="wallet-container">
        <div class="glass-card">
            
            <div class="balance-display">
                <h4 class="text-blue-200 font-light">Tu Saldo Actual</h4>
                <h1 class="font-bold tracking-tighter"><?php echo number_format($datos['saldo_zafiros']); ?> <span class="zafiro-text">Zafiros</span></h1>
            </div>

            <?php if (empty($datos['checkoutId'])) : ?>
                <div id="packages-section">
                    <h2 class="text-center text-2xl font-bold text-white mb-2">Elige tu Paquete</h2>
                    <p class="text-center text-sm text-blue-200 mb-6">La transacción es 100% segura a través de Datafast.</p>
                    
                    <div class="recharge-packages">
                        <!-- Paquete 1 -->
                        <form action="<?php echo URL_PROJECT; ?>/wallet/preparePayment" method="POST" class="form-package">
                            <input type="hidden" name="zafiros" value="100">
                            <input type="hidden" name="monto" value="1.00">
                            <button type="submit" class="package-card">
                                <div class="package-gem">💎</div>
                                <div class="package-info">
                                    <span class="package-amount">100 Zafiros</span>
                                    <span class="package-price">$1.00 USD</span>
                                </div>
                            </button>
                        </form>

                        <!-- Paquete 2 -->
                        <form action="<?php echo URL_PROJECT; ?>/wallet/preparePayment" method="POST" class="form-package">
                            <input type="hidden" name="zafiros" value="550">
                            <input type="hidden" name="monto" value="5.00">
                            <button type="submit" class="package-card popular">
                                <div class="popular-badge">POPULAR</div>
                                <div class="package-gem">💎</div>
                                <div class="package-info">
                                    <span class="package-amount">550 Zafiros</span>
                                    <span class="package-price">$5.00 USD</span>
                                </div>
                            </button>
                        </form>

                        <!-- Paquete 3 -->
                        <form action="<?php echo URL_PROJECT; ?>/wallet/preparePayment" method="POST" class="form-package">
                            <input type="hidden" name="zafiros" value="1200">
                            <input type="hidden" name="monto" value="10.00">
                            <button type="submit" class="package-card">
                                <div class="package-gem">💎</div>
                                <div class="package-info">
                                    <span class="package-amount">1200 Zafiros</span>
                                    <span class="package-price">$10.00 USD</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <div id="payment-section" class="text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Completa tu pago de forma segura</h2>
                    <div class="datafast-wrapper">
                        <script src="https://test.datapago.com/v1/paymentWidgets.js?checkoutId=<?php echo $datos['checkoutId']; ?>"></script>
                        <form action="<?php echo URL_PROJECT; ?>/wallet/success" class="paymentWidgets" data-brands="VISA MASTER AMEX DISCOVER"></form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="payment-modal-backdrop" class="modal-backdrop hidden">
        <div id="payment-modal" class="modal-panel">
            <div id="modal-icon"></div>
            <h2 id="modal-title" class="text-2xl font-bold text-white mt-4"></h2>
            <p id="modal-message" class="text-blue-200 mt-2"></p>
            <button id="modal-close-btn" class="modal-button">Entendido</button>
        </div>
    </div>
</body>

<!-- SCRIPT ORIGINAL: MANEJO DE ESTADO DE PAGO -->
<?php if (isset($datos['payment_status'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const status = <?php echo json_encode($datos['payment_status']); ?>;
        
        const backdrop = document.getElementById('payment-modal-backdrop');
        const icon = document.getElementById('modal-icon');
        const title = document.getElementById('modal-title');
        const message = document.getElementById('modal-message');
        const closeBtn = document.getElementById('modal-close-btn');

        if (status.type === 'success') {
            icon.innerHTML = '✅';
            title.textContent = '¡Pago Exitoso!';
            message.textContent = status.message;
        } else {
            icon.innerHTML = '❌';
            title.textContent = 'Error en el Pago';
            message.textContent = status.message;
        }
        
        backdrop.classList.remove('hidden');
        
        closeBtn.addEventListener('click', () => backdrop.classList.add('hidden'));
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                backdrop.classList.add('hidden');
            }
        });
    });
</script>
<?php endif; ?>

<!-- 🧪 NUEVO SCRIPT: MODO PRUEBAS (COMPRA DIRECTA) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Definimos URL base para JS
    const URL_PROJECT = '<?php echo URL_PROJECT; ?>';

    // Seleccionamos todos los formularios de paquetes
    const forms = document.querySelectorAll('.form-package');

    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            // Prevenimos el envío normal (ir a Datafast)
            e.preventDefault();

            // Obtenemos la cantidad de zafiros del input hidden
            const zafirosInput = this.querySelector('input[name="zafiros"]');
            const amount = zafirosInput ? zafirosInput.value : 0;

            // Confirmación de prueba
            if(!confirm(`🧪 MODO PRUEBA: ¿Quieres acreditar ${amount} Zafiros gratis a tu cuenta?`)) {
                return; // Si cancela, no hacemos nada
            }

            try {
                // Feedback visual en el botón
                const btn = this.querySelector('button');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<span class="text-sm">Procesando...</span>';
                btn.style.opacity = '0.7';
                btn.disabled = true;

                // Llamada a la API de prueba
                const response = await fetch(`${URL_PROJECT}/wallet/api_test_buy`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount: amount })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`✅ ¡Éxito! Se han agregado ${amount} Zafiros.\nTu nuevo saldo es: ${data.new_balance}`);
                    window.location.reload(); // Recargar para ver el saldo actualizado
                } else {
                    alert("❌ Error: " + (data.message || 'Error desconocido'));
                    // Restaurar botón si falla
                    btn.innerHTML = originalContent;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }

            } catch (error) {
                console.error(error);
                alert("❌ Error de conexión con el servidor.");
                // Restaurar botón
                const btn = this.querySelector('button');
                btn.innerHTML = originalContent;
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        });
    });
});
</script>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>