<?php require RUTA_APP . '/view/custom/header.php'; ?>
<link rel="stylesheet" href="<?php echo RUTA_URL; ?>/public/css/wallet.css">

<div class="wallet-container container">
    <div class="glass-card">
        <div class="balance-display">
            <h4>Saldo Actual</h4>
            <h1><?php echo $data['saldo_zafiros']; ?> Zafiros <i class="fas fa-gem zafiro-icon"></i></h1>
        </div>

        <hr style="border-color: rgba(255,255,255,0.2);">

        <?php if (empty($data['checkoutId'])) : ?>
            <div id="packages-section">
                <h4 class="text-center">Selecciona un Paquete de Recarga</h4>
                <div class="recharge-packages row mt-4 justify-content-center">
                    <div class="col-md-4 mb-3">
                        <form action="<?php echo RUTA_URL; ?>/wallet/preparePayment" method="POST">
                            <input type="hidden" name="zafiros" value="100">
                            <input type="hidden" name="monto" value="1.00">
                            <button type="submit" class="package-btn">
                                <h5>100 💎</h5>
                                <p class="price">$1.00 USD</p>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 mb-3">
                        <form action="<?php echo RUTA_URL; ?>/wallet/preparePayment" method="POST">
                            <input type="hidden" name="zafiros" value="550">
                            <input type="hidden" name="monto" value="5.00">
                            <button type="submit" class="package-btn">
                                <h5>550 💎</h5>
                                <p class="price">$5.00 USD</p>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 mb-3">
                        <form action="<?php echo RUTA_URL; ?>/wallet/preparePayment" method="POST">
                            <input type="hidden" name="zafiros" value="1200">
                            <input type="hidden" name="monto" value="10.00">
                            <button type="submit" class="package-btn">
                                <h5>1200 💎</h5>
                                <p class="price">$10.00 USD</p>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div id="payment-section" class="text-center">
                <h4 class="mb-4">Completa tu pago de forma segura</h4>
                
                <script src="https://test.datapago.com/v1/paymentWidgets.js?checkoutId=<?php echo $data['checkoutId']; ?>"></script>
                
                <form action="<?php echo RUTA_URL; ?>/wallet/success" class="paymentWidgets" data-brands="VISA MASTER AMEX DISCOVER"></form>

                <div id="datafast-container"></div>
                <script type="text/javascript">
                    // Esta parte es del documento de Datafast para añadir su logo.
                    var datafast = '<div style="text-align: center; margin-top: 10px;"><img src="https://www.datafast.com.ec/images/powered_small.png" style="width:100%;"></div>';
                    // Pequeño hack para esperar a que el form se renderice
                    setTimeout(function() {
                        if (document.querySelector('form.wpwl-form-card')) {
                             document.querySelector('form.wpwl-form-card .wpwl-button').insertAdjacentHTML('beforebegin', datafast);
                        }
                    }, 1000);
                </script>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .package-btn {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        cursor: pointer;
        width: 100%;
        color: white;
    }
    .package-btn:hover {
        transform: translateY(-5px);
        border-color: #f09;
        box-shadow: 0 0 15px rgba(240, 0, 153, 0.5);
    }
    .package-btn h5 { font-weight: 700; font-size: 1.5rem; }
    .package-btn .price { font-size: 1.2rem; color: #c0c0c0; margin: 0; }
</style>

<?php require RUTA_APP . '/view/custom/footer.php'; ?>