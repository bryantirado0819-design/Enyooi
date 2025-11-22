<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
<style>
    :root { 
        --accent: #ff4fa3; 
        --accent-2: #7c5cff;
        --dark-bg: #020617;
        --card-bg: rgba(15, 23, 42, 0.7);
        --border-color: rgba(255, 255, 255, 0.08);
    }
    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--dark-bg);
        background-image: radial-gradient(1200px 600px at 10% 10%, rgba(124, 92, 255, 0.1), transparent 8%),
                          radial-gradient(1000px 600px at 90% 80%, rgba(255, 79, 163, 0.08), transparent 8%);
        color: #e2e8f0; 
    }
    .card { 
        background: var(--card-bg); 
        border: 1px solid var(--border-color); 
        backdrop-filter: blur(12px); 
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
    .kpi-card .icon { background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 0 20px rgba(124, 92, 255, 0.5); }
    @keyframes slide-up-fade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .card-animated { animation: slide-up-fade 0.5s ease-out forwards; opacity: 0; }
    .level-badge { background: linear-gradient(to right, #f59e0b, #ef4444); text-shadow: 1px 1px 2px rgba(0,0,0,0.4); }
    .progress-bar-container { background-color: rgba(0,0,0,0.3); border-radius: 9999px; overflow: hidden; }
    .progress-bar { background: linear-gradient(to right, var(--accent-2), var(--accent)); height: 100%; border-radius: 9999px; transition: width 1s ease-in-out; }
    .achievement-unlocked { background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; }
    .time-filter button.active { background-color: var(--accent); color: white; }
</style>

<div class="p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto">
        <header class="flex flex-col sm:flex-row justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Dashboard de Creadora</h1>
                <p class="text-blue-200">¡Bienvenida de nuevo, <span id="creator-name">...</span>!</p>
            </div>
            <div class="flex items-center gap-4 mt-4 sm:mt-0">
                 <a href="<?php echo URL_PROJECT; ?>perfil/<?php echo $_SESSION['usuario']; ?>" class="px-4 py-2 text-sm rounded-full text-white font-semibold transition-colors bg-white/10 hover:bg-white/20">Ver Mi Perfil</a>
                 <a href="<?php echo URL_PROJECT; ?>retiro" class="px-4 py-2 text-sm rounded-full text-white font-semibold transition-transform hover:scale-105" style="background: linear-gradient(to right, var(--accent), var(--accent-2));">Retirar Fondos</a>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card kpi-card rounded-2xl p-6 flex items-center gap-5 card-animated" style="animation-delay: 0.1s;">
                <div class="icon w-16 h-16 rounded-full flex items-center justify-center text-3xl"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <p class="text-sm text-slate-300">Ingresos (30 días)</p>
                    <p class="text-3xl font-bold tracking-tighter text-white">$<span id="kpi-revenue">0.00</span></p>
                </div>
            </div>
            <div class="card kpi-card rounded-2xl p-6 flex items-center gap-5 card-animated" style="animation-delay: 0.2s;">
                <div class="icon w-16 h-16 rounded-full flex items-center justify-center text-3xl"><i class="fas fa-users"></i></div>
                <div>
                    <p class="text-sm text-slate-300">Suscriptores Activos</p>
                    <p class="text-3xl font-bold tracking-tighter text-white" id="kpi-subs">0</p>
                </div>
            </div>
            <div class="card kpi-card rounded-2xl p-6 flex items-center gap-5 card-animated" style="animation-delay: 0.3s;">
                <div class="icon w-16 h-16 rounded-full flex items-center justify-center text-3xl"><i class="fas fa-user-plus"></i></div>
                <div>
                    <p class="text-sm text-slate-300">Nuevos Subs (30d)</p>
                    <p class="text-3xl font-bold tracking-tighter text-white" id="kpi-new-subs">0</p>
                </div>
            </div>
            <div class="card kpi-card rounded-2xl p-6 flex items-center gap-5 card-animated" style="animation-delay: 0.4s;">
                <div class="icon w-16 h-16 rounded-full flex items-center justify-center text-3xl">💎</div>
                <div>
                    <p class="text-sm text-slate-300">Saldo Actual</p>
                    <p class="text-3xl font-bold tracking-tighter text-white" id="kpi-balance">0</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card rounded-2xl p-6 card-animated" style="animation-delay: 0.5s;">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Rendimiento de Ingresos</h2>
                        <div class="time-filter bg-black/20 p-1 rounded-full text-sm">
                            <button class="px-3 py-1 rounded-full active" data-period="30d">30d</button>
                            <button class="px-3 py-1 rounded-full" data-period="7d">7d</button>
                        </div>
                    </div>
                    <div class="h-80"><canvas id="revenueChart"></canvas></div>
                </div>
                 <div class="card rounded-2xl p-6 card-animated" style="animation-delay: 0.6s;">
                    <h2 class="text-xl font-semibold mb-4">Top Contenido</h2>
                    <div id="top-content-list" class="space-y-3"></div>
                </div>
            </div>
            <div class="lg:col-span-1 space-y-8">
                <div class="card rounded-2xl p-6 text-center card-animated" style="animation-delay: 0.7s;">
                    <h2 class="text-xl font-semibold mb-3">Nivel de Creadora</h2>
                    <div id="level-badge" class="level-badge inline-block px-4 py-1 rounded-full text-white font-bold text-lg mb-3 shadow-lg"></div>
                    <p class="text-sm text-blue-200 mb-2" id="next-level-text"></p>
                    <div class="progress-bar-container h-3 w-full">
                        <div id="level-progress-bar" class="progress-bar" style="width: 0%;"></div>
                    </div>
                </div>
                <div class="card rounded-2xl p-6 card-animated" style="animation-delay: 0.8s;">
                    <h2 class="text-xl font-semibold mb-4">Fuentes de Ingresos</h2>
                    <div class="h-64"><canvas id="revenueSourceChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let fullData;
    let revenueChartInstance;

    function animateCountUp(el, end, decimals = 0, isCurrency = false) {
        let start = 0;
        const duration = 1500;
        const frameRate = 1000 / 60;
        const totalFrames = Math.round(duration / frameRate);
        const easeOutQuad = t => t * (2 - t);
        let currentFrame = 0;
        const counter = setInterval(() => {
            currentFrame++;
            const progress = easeOutQuad(currentFrame / totalFrames);
            const currentVal = start + (end - start) * progress;
            el.textContent = isCurrency ? currentVal.toFixed(decimals) : Math.round(currentVal).toLocaleString();
            if (currentFrame === totalFrames) clearInterval(counter);
        }, frameRate);
    }
    
    function populateData(data) {
        document.getElementById('creator-name').textContent = data.creatorName;
        animateCountUp(document.getElementById('kpi-revenue'), data.kpi.revenue, 2, true);
        animateCountUp(document.getElementById('kpi-subs'), data.kpi.subs);
        animateCountUp(document.getElementById('kpi-new-subs'), data.kpi.newSubs);
        animateCountUp(document.getElementById('kpi-balance'), data.kpi.balance);

        const contentList = document.getElementById('top-content-list');
        contentList.innerHTML = '';
        if (data.topContent && data.topContent.length > 0) {
            data.topContent.forEach(item => {
                const icon = item.tipo_archivo === 'imagen' ? 'fa-image' : 'fa-video';
                contentList.innerHTML += `<div class="flex items-center gap-4 p-2 rounded-lg hover:bg-white/5"><div class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center text-lg text-pink-400"><i class="fas ${icon}"></i></div><div class="flex-1"><p class="text-sm font-medium text-white truncate">${item.contenidoPublicacion || 'Media sin texto'}</p></div><div class="text-sm flex items-center gap-4 text-slate-300"><span><i class="fas fa-heart text-red-500"></i> ${item.likes}</span><span><i class="fas fa-comment text-blue-400"></i> ${item.comentarios}</span></div></div>`;
            });
        } else {
            contentList.innerHTML = '<p class="text-sm text-slate-400">No hay contenido para mostrar.</p>';
        }

        if (data.gamification) {
            document.getElementById('level-badge').textContent = `Nivel ${data.gamification.level}: ${data.gamification.levelName}`;
            document.getElementById('next-level-text').textContent = `Siguiente: ${data.gamification.xpForNextLevel} XP para Nivel ${data.gamification.level + 1}`;
            setTimeout(() => {
                document.getElementById('level-progress-bar').style.width = `${data.gamification.progressPercentage}%`;
            }, 100);
        }
        
        renderRevenueSourceChart(data.revenueSource);
        updateRevenueChart('30d');
    }

    function renderRevenueSourceChart(sourceData) {
        const sourceCtx = document.getElementById('revenueSourceChart').getContext('2d');
        new Chart(sourceCtx, { type: 'doughnut', data: { labels: sourceData.labels, datasets: [{ data: sourceData.data, backgroundColor: ['#7c5cff', '#ff4fa3', '#38bdf8', '#f59e0b'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1' } } } } });
    }

    function updateRevenueChart(period) {
        const chartData = fullData.revenueChart[period];
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        if (revenueChartInstance) revenueChartInstance.destroy();
        
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Ingresos (USD)',
                    data: chartData.map(d => d.total_net_usd),
                    borderColor: 'var(--accent)', backgroundColor: 'rgba(255, 79, 163, 0.1)',
                    fill: true, tension: 0.4, pointRadius: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#94a3b8' } }, y: { beginAtZero: true, ticks: { color: '#94a3b8' } } } }
        });
    }

    document.querySelectorAll('.time-filter button').forEach(button => {
        button.addEventListener('click', (e) => {
            document.querySelectorAll('.time-filter button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            updateRevenueChart(e.target.dataset.period);
        });
    });

    async function loadDashboardData() {
        try {
            const response = await fetch('<?php echo URL_PROJECT; ?>creatorDashboardController/getDashboardData');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            if (data.success) {
                fullData = data;
                populateData(data);
            } else { console.error('Error from API:', data.message); }
        } catch (error) { console.error('Fetch error:', error); }
    }

    loadDashboardData();
});
</script>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>
