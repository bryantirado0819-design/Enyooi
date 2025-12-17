/**
 * 🚀 LIVE EXPLORER - ENYOOI
 * Maneja filtros de búsqueda y actualizaciones de audiencia en tiempo real
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // 1. CONEXIÓN SOCKET (Para contadores en vivo)
    // ==========================================
    // NODE_SERVER_URL se define en lives.php antes de cargar este script
    if (typeof io !== 'undefined' && typeof NODE_SERVER_URL !== 'undefined') {
        const socket = io(NODE_SERVER_URL);

        // Escuchar actualizaciones globales de contadores
        socket.on('all_viewer_counts', (counts) => {
            for (const [streamId, count] of Object.entries(counts)) {
                updateCardCounter(streamId, count);
            }
        });

        // Escuchar actualización de un stream específico
        socket.on('update_viewer_count', (data) => {
            // data puede venir como { count: 10 } (contexto sala) o con ID
            // En el explorador dependemos más de 'all_viewer_counts'
        });
    }

    function updateCardCounter(streamId, count) {
        const badge = document.getElementById(`viewers-${streamId}`);
        if (badge) {
            const span = badge.querySelector('span');
            if (span) span.innerText = formatNumber(count);
        }
    }

    function formatNumber(num) {
        return num > 999 ? (num/1000).toFixed(1) + 'k' : num;
    }

    // ==========================================
    // 2. LÓGICA DE FILTROS (Búsqueda y Categoría)
    // ==========================================
    const searchInput = document.getElementById('filter-search');
    const categorySelect = document.getElementById('filter-category');
    const cards = document.querySelectorAll('.live-card-wrapper');
    const noResults = document.getElementById('no-filter-results');

    function filterStreams() {
        const term = searchInput.value.toLowerCase().trim();
        const cat = categorySelect.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const title = card.dataset.title || '';
            const creator = card.dataset.creator || '';
            const tags = card.dataset.tags || '';

            // Lógica de coincidencia
            const matchSearch = term === '' || title.includes(term) || creator.includes(term);
            const matchCategory = cat === '' || tags.includes(cat);

            if (matchSearch && matchCategory) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Mostrar/Ocultar mensaje de "Sin resultados"
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            noResults.classList.toggle('hidden', visibleCount !== 0); // Compatibilidad Tailwind
        }
    }

    // Event Listeners para filtros
    if (searchInput) searchInput.addEventListener('input', filterStreams);
    if (categorySelect) categorySelect.addEventListener('change', filterStreams);
});