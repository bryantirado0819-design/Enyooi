/**
 * live_explorer.js
 * * Gestiona dos cosas en la página /lives:
 * 1. Lógica de filtrado (búsqueda por texto y categoría).
 * 2. Conexión a Socket.IO para actualizar contadores de espectadores en tiempo real.
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- PARTE 1: LÓGICA DE FILTROS ---
    const searchInput = document.getElementById('filter-search');
    const categorySelect = document.getElementById('filter-category');
    const streamsGrid = document.getElementById('streams-grid');
    const noResultsMessage = document.getElementById('no-filter-results');
    
    // Solo ejecuta si los elementos de filtro existen
    if (searchInput && categorySelect && streamsGrid) {
        
        // Almacena todas las tarjetas en una variable
        const allCards = Array.from(streamsGrid.getElementsByClassName('live-card-wrapper'));

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const categoryTerm = categorySelect.value.toLowerCase().trim();
            let visibleCards = 0;

            allCards.forEach(card => {
                const title = card.dataset.title || '';
                const creator = card.dataset.creator || '';
                const tags = card.dataset.tags || '';

                // Condición 1: El texto de búsqueda coincide
                const searchMatch = (searchTerm === '') || 
                                    title.includes(searchTerm) || 
                                    creator.includes(searchTerm);
                
                // Condición 2: La categoría coincide
                const categoryMatch = (categoryTerm === '') || 
                                      tags.includes(categoryTerm);

                // Mostrar si ambas condiciones son verdaderas
                if (searchMatch && categoryMatch) {
                    card.classList.remove('hidden');
                    visibleCards++;
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Mostrar u ocultar el mensaje de "sin resultados"
            if (noResultsMessage) {
                noResultsMessage.classList.toggle('hidden', visibleCards > 0);
            }
        }

        // Añadir listeners
        searchInput.addEventListener('input', applyFilters);
        categorySelect.addEventListener('change', applyFilters);
    }

    // --- PARTE 2: LÓGICA DE SOCKET.IO ---

    if (typeof io === 'undefined') {
        console.error('Socket.IO client library not found.');
        document.querySelectorAll('[id^="viewers-"] span').forEach(span => span.textContent = 'Err');
        return;
    }

    if (typeof NODE_SERVER_URL === 'undefined') {
        console.error('NODE_SERVER_URL is not defined.');
        document.querySelectorAll('[id^="viewers-"] span').forEach(span => span.textContent = 'Err');
        return;
    }

    try {
        const socket = io(NODE_SERVER_URL);

        socket.on('connect', () => {
            // console.log('Socket.IO connected for exploring streams.');
            // Pide todos los contadores actuales al conectar
            socket.emit('get_all_viewer_counts');
        });

        socket.on('connect_error', (error) => {
            console.error('Socket.IO connection error:', error);
            document.querySelectorAll('[id^="viewers-"] span').forEach(span => span.textContent = 'N/A');
        });

        // Escucha el evento global con TODOS los contadores
        // Espera un objeto: { "streamId1": count1, "streamId2": count2, ... }
        socket.on('all_viewer_counts', (counts) => {
            // console.log('Received global viewer counts:', counts);
            updateAllViewerCounts(counts);
        });

        socket.on('disconnect', () => {
            // console.log('Socket.IO disconnected.');
            document.querySelectorAll('[id^="viewers-"] span').forEach(span => span.textContent = '--');
        });

    } catch (error) {
        console.error('Failed to initialize Socket.IO connection:', error);
        document.querySelectorAll('[id^="viewers-"] span').forEach(span => span.textContent = 'Err');
    }
});

/**
 * Actualiza todos los contadores de espectadores en la página.
 * @param {Object} counts - Objeto con { streamId: viewerCount, ... }
 */
function updateAllViewerCounts(counts) {
    if (typeof counts !== 'object' || counts === null) return;

    const viewerSpans = document.querySelectorAll('[id^="viewers-"] > span');

    viewerSpans.forEach(span => {
        const parentDiv = span.closest('[id^="viewers-"]');
        if (!parentDiv) return;

        const streamId = parentDiv.id.substring('viewers-'.length);
        
        // Usa el conteo si existe, de lo contrario 0
        const count = counts[String(streamId)] || 0; 
        
        span.textContent = count;
    });
}

