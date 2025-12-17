<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ENYOOI — Onboarding Creadora</title>
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
        /* Estilo para los inputs de archivo */
        input[type="file"]::file-selector-button {
            background: #2d3748; /* gris oscuro */
            color: #e2e8f0; /* gris claro */
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem; /* 6px */
            cursor: pointer;
            transition: background-color 0.2s;
        }
        input[type="file"]::file-selector-button:hover {
            background: #4a5568; /* gris más claro */
        }
    </style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>

<div class="card max-w-xl w-full p-6 sm:p-8 rounded-2xl shadow-lg relative z-10">
    <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">¡Bienvenida a ENYOOI!</h1>
    <p class="text-blue-200 text-sm text-center mb-6">Completa tu perfil para empezar a monetizar.</p>
    
    <form id="onboarding-form" action="<?php echo URL_PROJECT; ?>home/save_onboarding" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div>
            <label for="nickname" class="block text-sm font-medium mb-2">Nickname artístico</label>
            <input id="nickname" type="text" name="nickname" class="w-full px-3 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500" required>
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium mb-2">Biografía / Descripción</label>
            <textarea id="bio" name="bio" rows="3" class="w-full px-3 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500 resize-none"></textarea>
        </div>

        <div>
            <label for="banner" class="block text-sm font-medium mb-2">Foto de portada (banner)</label>
            <input id="banner" type="file" name="banner" accept="image/*" class="w-full text-sm text-slate-400">
        </div>

        <div>
            <label for="foto" class="block text-sm font-medium mb-2">Foto de perfil (opcional)</label>
            <input id="foto" type="file" name="foto" accept="image/*" class="w-full text-sm text-slate-400">
            <p class="text-xs text-gray-400 mt-1">Si no subes una, se usará un avatar por defecto.</p>
        </div>

        <div>
            <label for="documento" class="block text-sm font-medium mb-2">Documento de identidad</label>
            <input id="documento" type="file" name="documento" accept="image/*,application/pdf" class="w-full text-sm text-slate-400" required>
        </div>

        <div>
            <label for="pago" class="block text-sm font-medium mb-2">Método de pago preferido</label>
            <select id="pago" name="pago" class="w-full px-3 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                <option value="transferencia">Transferencia bancaria</option>
                <option value="stripe">Stripe</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>

        <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-[#ff7bbd] to-[#7c5cff] font-semibold transition-transform hover:scale-105">Finalizar Onboarding</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ahora el JavaScript encontrará el formulario correctamente.
    const onboardingForm = document.getElementById('onboarding-form');

    if (onboardingForm) {
        onboardingForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const submitButton = onboardingForm.querySelector('button[type="submit"]');
            submitButton.textContent = 'Guardando...';
            submitButton.disabled = true;

            const formData = new FormData(onboardingForm);

            fetch('/home/save_onboarding', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok && data.redirect) {
                    // ¡Ahora sí! La redirección se ejecutará.
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + (data.error || 'No se pudo guardar la información.'));
                    submitButton.textContent = 'Finalizar Onboarding';
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error en el fetch:', error);
                alert('Hubo un problema de conexión. Inténtalo de nuevo.');
                submitButton.textContent = 'Finalizar Onboarding';
                submitButton.disabled = false;
            });
        });
    }
});
</script>

</body>
</html>