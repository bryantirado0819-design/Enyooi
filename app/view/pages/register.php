<?php
include_once __DIR__ . '/../custom/header.php';
$base = defined('URL_PROJECT') ? URL_PROJECT : '/ENYOOI';
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ENYOOI — Crear una Cuenta</title>
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
                <h1 class="text-2xl sm:text-3xl font-bold">Crear una Cuenta</h1>
                <p class="text-blue-200 text-sm mt-1">Únete a la comunidad de ENYOOI.</p>
            </div>
            
            <form action="<?php echo $base; ?>/home/registrar" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <input type="text" name="usuario" placeholder="Usuario" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                <input type="password" name="contrasena" placeholder="Contraseña" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                
                <div>
                    <label for="fecha_nacimiento" class="text-xs text-blue-200 ml-1">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <select name="genero" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Género</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Gay">Gay</option>
                        <option value="Trans">Trans</option>
                        <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                    </select>
                    <input type="text" name="ciudad" placeholder="Ciudad" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
                
                <select name="pais" id="pais" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="Ecuador">Ecuador</option>
                    <option value="Otro">Otro País</option>
                </select>

                <input type="text" name="cedula" id="cedula" placeholder="Cédula / Nro. Documento" required class="w-full px-4 py-2 rounded-lg bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">

                <div id="cedulaEcuador">
                    <p class="text-xs text-center text-blue-300">Se validará la cédula ecuatoriana.</p>
                </div>

                <div id="subirDocumento" style="display:none;">
                    <label class="block text-sm font-medium mb-2">Sube una imagen de tu documento</label>
                    <input type="file" name="documento" accept="image/*" class="w-full text-sm text-slate-400">
                </div>

                <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-[#ff7bbd] to-[#7c5cff] font-semibold transition-transform hover:scale-105">Registrarme</button>
            </form>

            <?php if (isset($_SESSION['usuarioError'])): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg text-sm" role="alert">
                    <?= htmlspecialchars($_SESSION['usuarioError']) ?>
                </div>
                <?php unset($_SESSION['usuarioError']); ?>
            <?php endif; ?>

            <div class="text-center text-sm text-blue-200">
                <span>¿Ya tienes una cuenta?</span>
                <a href="<?php echo $base; ?>/home/entrar" class="font-semibold text-pink-400 hover:underline">Ingresar</a>
            </div>
        </div>
        
        <div class="hidden md:flex flex-col items-center justify-center p-8">
             <img src="<?php echo $base; ?>/public/img/ico_enyooi.png" class="w-64" alt="logo">
             <p class="text-blue-200 text-center mt-6">Conecta, crea y monetiza tu contenido en un solo lugar.</p>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['menorEdad'])): ?>
<div class="modal fade" id="menorModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="bg-slate-800 text-white rounded-lg shadow-xl border border-slate-700">
      <div class="modal-header flex justify-between items-center p-4 border-b border-slate-700">
        <h5 class="modal-title text-lg font-bold">Registro no permitido</h5>
        <button type="button" class="text-gray-400 hover:text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        Eres menor de edad y no puedes crear una cuenta en ENYOOI.
      </div>
      <div class="modal-footer p-4 flex justify-end">
        <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 font-semibold" data-dismiss="modal">Entendido</button>
      </div>
    </div>
  </div>
</div>
<script>
 document.addEventListener('DOMContentLoaded', function(){
   $('#menorModal').modal('show');
 });
</script>
<?php unset($_SESSION['menorEdad']); endif; ?>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Lógica para mostrar/ocultar carga de documento
    const paisSelect = document.getElementById('pais');
    const subirDocumentoDiv = document.getElementById('subirDocumento');
    const cedulaEcuadorDiv = document.getElementById('cedulaEcuador');

    if (paisSelect) {
        paisSelect.addEventListener('change', function(){
            if(this.value === "Otro"){
                subirDocumentoDiv.style.display = "block";
                cedulaEcuadorDiv.style.display = "none";
            } else {
                subirDocumentoDiv.style.display = "none";
                cedulaEcuadorDiv.style.display = "block";
            }
        });
    }
});
</script>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>
</body>
</html>