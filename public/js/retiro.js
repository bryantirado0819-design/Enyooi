document.addEventListener('DOMContentLoaded', function() {
    // --- CONFIGURACIÓN Y SELECTORES ---
    const el = (selector) => document.querySelector(selector);
    const TASA_CONVERSION = 7;
    const COMISION = 0.10;
    let withdrawalData = null; 

    // --- Elementos del DOM del Modal de Información ---
    const infoModal = el('#info-modal');
    const infoModalIcon = el('#info-modal-icon');
    const infoModalTitle = el('#info-modal-title');
    const infoModalMessage = el('#info-modal-message');
    const infoModalCloseBtn = el('#info-modal-close-btn');

    // ✅ --- NUEVA FUNCIÓN PARA MOSTRAR MODAL DE ALERTA ---
    function showInfoModal(title, message, isSuccess = true) {
        if (!infoModal) return;
        
        infoModalTitle.textContent = title;
        infoModalMessage.textContent = message;
        infoModalIcon.textContent = isSuccess ? '✅' : '❌';

        infoModal.classList.remove('hidden');
        infoModal.classList.add('flex');
        
        void infoModal.offsetWidth;
        infoModal.querySelector('.modal-panel').style.opacity = 1;
        infoModal.querySelector('.modal-panel').style.transform = 'scale(1) translateY(0)';
    }

    if(infoModalCloseBtn) {
        infoModalCloseBtn.addEventListener('click', () => {
            infoModal.classList.add('hidden');
            infoModal.classList.remove('flex');
            infoModal.querySelector('.modal-panel').style.opacity = 0;
            infoModal.querySelector('.modal-panel').style.transform = 'scale(0.95) translateY(-10px)';
        });
    }

    // --- Lógica de Pestañas (Sin cambios) ---
    const tabs = document.querySelectorAll('#retiro-tabs .tab-button');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            contents.forEach(c => c.classList.remove('active'));
            el(`#tab-${tab.dataset.target}`).classList.add('active');
        });
    });

    // --- Carga de Datos Financieros y Gráfico (Sin cambios) ---
    let earningsChart = null;
    async function loadFinancialData() {
        try {
            const response = await fetch(`${URL_PROJECT}retiro/getFinancialData`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();

            el('#zafiro-balance').textContent = (data.saldo_zafiros || 0).toLocaleString();
            el('#usd-equivalent').textContent = (data.saldo_usd_disponible || 0).toFixed(2);
            el('#amount').max = data.saldo_usd_disponible || 0;
            
            if (data.saldo_usd_disponible < 20) {
                el('#min-withdrawal-alert').classList.remove('hidden');
                el('#submit-withdrawal').disabled = true;
            } else {
                 el('#min-withdrawal-alert').classList.add('hidden');
                 el('#submit-withdrawal').disabled = false;
            }

            renderWithdrawalHistory(data.historial_retiros);
            renderEarningsChart(data.ingresos_ultimos_7_dias);

        } catch (error) {
            console.error('Error al cargar datos financieros:', error);
        }
    }
    
    function renderWithdrawalHistory(history) {
        const list = el('#withdrawal-history-list');
        list.innerHTML = '';
        if (!history || history.length === 0) {
            list.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">No tienes retiros recientes.</p>';
            return;
        }
        history.forEach(item => {
            const statusClasses = { pendiente: 'status-pendiente', aprobado: 'status-aprobado', denegado: 'status-denegado', anulado: 'status-anulado' };
            const date = new Date(item.fecha_solicitud).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            list.innerHTML += `
                <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                    <div>
                        <p class="font-semibold text-white">$${parseFloat(item.monto_final_usd).toFixed(2)} USD</p>
                        <p class="text-xs text-slate-400">Solicitado: ${date}</p>
                    </div>
                    <span class="status-badge ${statusClasses[item.estado]}">${item.estado.charAt(0).toUpperCase() + item.estado.slice(1)}</span>
                </div>`;
        });
    }

    function renderEarningsChart(dailyData) {
        const ctx = el('#earningsChart').getContext('2d');
        const labels = [];
        const earnings = [];
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            const dateString = date.toISOString().split('T')[0];
            labels.push(date.toLocaleDateString('es-ES', { weekday: 'short' }));
            const dayData = dailyData ? dailyData.find(d => d.dia === dateString) : null;
            earnings.push(dayData ? parseFloat(dayData.total_ingresos) : 0);
        }

        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(124, 92, 255, 0.6)');
        gradient.addColorStop(1, 'rgba(255, 79, 163, 0.3)');

        if (earningsChart) earningsChart.destroy();
        earningsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Ingresos (USD)', data: earnings,
                    borderColor: 'var(--accent)', backgroundColor: gradient,
                    fill: true, tension: 0.4, pointBackgroundColor: '#fff'
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }, 
                scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } } 
            }
        });
    }

    // --- Lógica del Formulario de Retiro (Sin cambios, excepto el modal) ---
    el('#withdrawal-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        withdrawalData = data;
        el('#resend-code-link').textContent = 'Enviando...';
        await fetch(`${URL_PROJECT}retiro/enviarCodigoVerificacion`, { method: 'POST' });
        const verificationModal = el('#verification-modal');
        verificationModal.classList.remove('hidden');
        verificationModal.classList.add('flex');
        void verificationModal.offsetWidth; 
        el('#verification-modal .modal-panel').style.opacity = 1;
        el('#verification-modal .modal-panel').style.transform = 'scale(1) translateY(0)';
        startResendTimer();
    });

    el('select[name="bank"]').addEventListener('change', (e) => {
        el('#other-bank-container').classList.toggle('hidden', e.target.value !== 'Otro');
    });

    el('input[name="amount"]').addEventListener('input', (e) => {
        const usdAmount = parseFloat(e.target.value) || 0;
        const fee = usdAmount * COMISION;
        const net = usdAmount - fee;
        el('#zafiros-to-deduct').textContent = `💎 ${Math.ceil(usdAmount * TASA_CONVERSION).toLocaleString()}`;
        el('#platform-fee').textContent = `-$${fee.toFixed(2)}`;
        el('#net-amount').textContent = `$${net.toFixed(2)}`;
    });

    // --- Lógica del Modal de Verificación ---
    const codeInputs = document.querySelectorAll('#code-inputs .input-code');
    const verificationModal = el('#verification-modal');
    
    codeInputs.forEach((input, index) => {
        input.addEventListener('keydown', (e) => {
            if (e.key >= 0 && e.key <= 9) {
                input.value = '';
                setTimeout(() => { if (index < codeInputs.length - 1) codeInputs[index + 1].focus() }, 10);
            } else if (e.key === 'Backspace') {
                 setTimeout(() => { if (index > 0) codeInputs[index - 1].focus() }, 10);
            }
        });
    });

    let timerInterval;
    function startResendTimer() {
        let timeLeft = 180;
        const resendLink = el('#resend-code-link');
        clearInterval(timerInterval);
        resendLink.classList.add('disabled');
        
        timerInterval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            resendLink.textContent = `Reenviar en ${minutes}:${seconds}`;
            if (--timeLeft < 0) {
                clearInterval(timerInterval);
                resendLink.classList.remove('disabled');
                resendLink.textContent = 'Reenviar código';
            }
        }, 1000);
    }

    el('#resend-code-link').addEventListener('click', async (e) => {
        e.preventDefault();
        if (e.target.classList.contains('disabled')) return;
        await fetch(`${URL_PROJECT}retiro/enviarCodigoVerificacion`, { method: 'POST' });
        startResendTimer();
    });

    el('#cancel-verification-btn').addEventListener('click', () => {
        verificationModal.classList.remove('flex');
        verificationModal.classList.add('hidden');
        el('#verification-modal .modal-panel').style.opacity = 0;
        el('#verification-modal .modal-panel').style.transform = 'scale(0.95) translateY(-10px)';
    });

    el('#confirm-verification-btn').addEventListener('click', async () => {
        const code = Array.from(codeInputs).map(input => input.value).join('');
        const recaptchaResponse = grecaptcha.getResponse();

        if (code.length !== 6 || !recaptchaResponse) {
            el('#verification-feedback').textContent = 'Por favor, completa todos los campos.';
            return;
        }
        
        el('#verification-feedback').textContent = '';
        el('#confirm-verification-btn').disabled = true;
        el('#confirm-verification-btn').textContent = 'Procesando...';

        try {
            const response = await fetch(`${URL_PROJECT}retiro/solicitar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    codigo_verificacion: code,
                    'g-recaptcha-response': recaptchaResponse,
                    datos_formulario: withdrawalData
                })
            });
            const result = await response.json();
            
            // ✅ **CORRECCIÓN**: Usar el nuevo modal en lugar de alert()
            el('#cancel-verification-btn').click(); // Oculta el modal de verificación
            showInfoModal(
                result.success ? 'Solicitud Enviada' : 'Error',
                result.message,
                result.success
            );

            if(result.success) {
                // En lugar de recargar, solo actualizamos los datos
                loadFinancialData();
                el('#withdrawal-form').reset();
            }

        } catch(e) {
            showInfoModal('Error de Conexión', 'No se pudo completar la solicitud. Inténtalo de nuevo.', false);
        } finally {
            el('#confirm-verification-btn').disabled = false;
            el('#confirm-verification-btn').textContent = 'Confirmar Retiro';
            grecaptcha.reset();
        }
    });

    // --- INICIALIZACIÓN ---
    loadFinancialData();
});