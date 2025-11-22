document.addEventListener('DOMContentLoaded', () => {
    // Manejo de Pestañas
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Desactivar todas las pestañas y contenidos
            tabs.forEach(item => item.classList.remove('active', 'text-indigo-400'));
            contents.forEach(content => content.classList.add('hidden'));

            // Activar la pestaña clickeada
            tab.classList.add('active', 'text-indigo-400');
            const targetId = tab.id.replace('tab-', 'content-');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });

    // Manejo del Modal
    const modal = document.getElementById('detail-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const detailButtons = document.querySelectorAll('.btn-detail');

    const openModal = (details) => {
        // Formatear fecha
        const date = new Date(details.fecha_transaccion);
        const formattedDate = `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;

        document.getElementById('modal-id').textContent = details.id_transaccion;
        document.getElementById('modal-fecha').textContent = formattedDate;
        document.getElementById('modal-tipo').textContent = details.tipo_transaccion.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        document.getElementById('modal-espectador').textContent = details.espectador_usuario || 'N/A';
        document.getElementById('modal-zafiros').textContent = parseInt(details.monto_zafiros_creador).toLocaleString();
        document.getElementById('modal-comision').textContent = `$${parseFloat(details.monto_usd_plataforma).toFixed(2)}`;
        document.getElementById('modal-ganancia').textContent = `$${parseFloat(details.monto_usd_creador).toFixed(2)}`;
        
        modal.classList.remove('hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
    };

    detailButtons.forEach(button => {
        button.addEventListener('click', () => {
            const details = JSON.parse(button.dataset.details);
            openModal(details);
        });
    });

    closeModalBtn.addEventListener('click', closeModal);

    // Cerrar modal al hacer clic fuera de él
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === "Escape" && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
