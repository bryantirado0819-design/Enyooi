<?php
include_once __DIR__ . '/../custom/header.php';

$base = rtrim(URL_PROJECT, '/'); 
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ENYOOI — Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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
            padding: 1rem;
        }
        .blob { position: absolute; border-radius: 9999px; filter: blur(42px); opacity: 0.22; mix-blend-mode: screen; animation: float 14s ease-in-out infinite; z-index: -1; }
        .blob.b1 { width: 420px; height: 420px; left: -80px; top: -80px; background: linear-gradient(135deg, var(--accent), #d46fcb); }
        .blob.b2 { width: 300px; height: 300px; right: -40px; top: 40px; background: linear-gradient(135deg, var(--accent-2), #68a6ff); animation-delay: 3s; }
        .blob.b3 { width: 360px; height: 360px; left: 45%; bottom: -100px; background: linear-gradient(135deg, #ffa3d9, #a88bff); animation-delay: 6s; }
        @keyframes float { 0% { transform: translateY(0); } 50% { transform: translateY(-30px); } 100% { transform: translateY(0); } }
        .card { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(8px); }
    </style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>

<div class="card w-full max-w-4xl p-6 sm:p-8 rounded-2xl shadow-lg">
    <div class="grid md:grid-cols-2 md:gap-16 items-center">
        
        <div class="space-y-6 order-last md:order-first">
            <div class="text-center md:text-left">
                <h1 class="text-2xl sm:text-3xl font-bold">Iniciar Sesión</h1>
                <p class="text-blue-200 text-sm mt-1">¡Qué bueno verte de nuevo!</p>
            </div>
            
            <form action="<?php echo $base; ?>/home/entrar" method="POST" class="space-y-4">
                <input type="text" name="usuario" placeholder="Usuario o Email" required class="w-full px-4 py-3 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                <input type="password" name="contrasena" placeholder="Contraseña" required class="w-full px-4 py-3 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-[#ff7bbd] to-[#7c5cff] font-semibold transition-transform hover:scale-105">Ingresar</button>
            </form>

            <?php if (isset($_SESSION['errorLogin'])): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg text-sm" role="alert">
                    <?= htmlspecialchars($_SESSION['errorLogin']) ?>
                </div>
                <?php unset($_SESSION['errorLogin']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['loginComplete'])): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg text-sm" role="alert">
                    <?= htmlspecialchars($_SESSION['loginComplete']) ?>
                </div>
                <?php unset($_SESSION['loginComplete']); ?>
            <?php endif; ?>
            
            <div class="text-center text-sm text-blue-200">
                <span>¿No tienes una cuenta?</span>
                <a href="<?php echo $base; ?>/home/registrar" class="font-semibold text-pink-400 hover:underline">Registrarme</a>
            </div>
        </div>
        
        <div class="hidden md:flex flex-col items-center justify-center p-8">
             <img src="<?php echo $base; ?>/public/img/ico_enyooi.png" class="logo w-64" alt="logo">
             <p class="text-blue-200 text-center mt-6">Tu espacio para conectar, crear y monetizar.</p>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Tu animación de GSAP se mantiene
    gsap.from(".logo", {duration: 1.5, x: 100, opacity: 0, ease: "power2.out"});
});
</script>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>
</body>
</html>