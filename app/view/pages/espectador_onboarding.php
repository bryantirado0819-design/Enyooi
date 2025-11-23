<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ENYOOI — Onboarding Espectador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(1200px 600px at 10% 10%, rgba(124, 92, 255, 0.12), transparent 8%),
                        radial-gradient(1000px 600px at 90% 80%, rgba(255, 79, 163, 0.08), transparent 8%),
                        linear-gradient(180deg, #020617 0%, #081127 100%);
            color: #e6eef8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem; /* 16px */
        }
        .blob { position: absolute; border-radius: 9999px; filter: blur(42px); opacity: 0.22; mix-blend-mode: screen; animation: float 14s ease-in-out infinite; z-index: -1; }
        .blob.b1 { width: 420px; height: 420px; left: -80px; top: -80px; background: linear-gradient(135deg, var(--accent), #d46fcb); }
        .blob.b2 { width: 300px; height: 300px; right: -40px; top: 40px; background: linear-gradient(135deg, var(--accent-2), #68a6ff); animation-delay: 3s; }
        .blob.b3 { width: 360px; height: 360px; left: 45%; bottom: -100px; background: linear-gradient(135deg, #ffa3d9, #a88bff); animation-delay: 6s; }
        @keyframes float { 0% { transform: translateY(0); } 50% { transform: translateY(-30px); } 100% { transform: translateY(0); } }
        .card { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(6px); }
        /* Animación para el dado */
        @keyframes bounce-sm { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .animate-bounce-sm { animation: bounce-sm 0.5s ease-in-out; }
    </style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>

<div class="card max-w-md w-full p-6 sm:p-8 rounded-2xl shadow-lg relative z-10">
    <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">¡Bienvenido a ENYOOI!</h1>
    <p class="text-blue-200 text-sm text-center mb-8">Elige tu apodo público para empezar la diversión.</p>
    
    <form action="<?php echo URL_PROJECT; ?>home/save_espectador" method="POST" class="space-y-6">
        <div>
            <label for="nickname" class="block text-sm font-medium mb-2">Tu Nickname</label>
            <input id="nickname" name="nickname" type="text"
                   class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-shadow" 
                   placeholder="Ej: FanDigital42" maxlength="60"
                   value="<?= htmlspecialchars($_SESSION['nickname'] ?? '') ?>">
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            <button type="button" id="dice" class="w-full sm:w-auto flex-1 py-3 px-4 rounded-lg bg-slate-700/50 hover:bg-slate-600/60 font-semibold transition">
                🎲 Generar Aleatorio
            </button>
            <button type="submit" class="w-full sm:w-auto flex-1 py-3 px-4 rounded-lg bg-gradient-to-r from-[#ff7bbd] to-[#7c5cff] font-semibold transition-transform hover:scale-105">
                Guardar y Continuar
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nickInput = document.getElementById('nickname');
    const diceButton = document.getElementById('dice');

    if (diceButton && nickInput) {
        diceButton.addEventListener('click', () => {
            const words = ['Luna','Pixel','Nube','Sol','Fenix','Neon','Astro','Delta','Cosmo','Zeta','Vortex','Mercury'];
            const rand = words[Math.floor(Math.random() * words.length)] + Math.floor(Math.random() * 9000 + 1000);
            nickInput.value = rand;
            
            // Usamos una animación CSS personalizada más sutil
            nickInput.classList.add('animate-bounce-sm');
            setTimeout(() => nickInput.classList.remove('animate-bounce-sm'), 500);
        });
    }
});
</script>

</body>
</html>