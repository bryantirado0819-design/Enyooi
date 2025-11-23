<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Elige tu rol - ENYOOI</title>
  <link rel="stylesheet" href="/public/assets/css/styles.css">
  <style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body { background:#0f172a; color:#e6eef8; font-family:'Poppins',sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .card{width:95%;max-width:920px;padding:28px;border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(255,255,255,0.02));border:1px solid rgba(255,255,255,0.06);backdrop-filter: blur(8px);box-shadow:0 10px 40px rgba(2,6,23,0.6);}
    .choice{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:28px;border-radius:14px;cursor:pointer;transition:transform .12s ease, box-shadow .12s ease;}
    .choice:hover{transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.4);}
    .icon{width:72px;height:72px;border-radius:18px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;font-size:28px;}
    .creadora{background:linear-gradient(135deg,rgba(255,79,163,0.06),rgba(124,92,255,0.04));border:1px solid rgba(255,79,163,0.08);}
    .espectador{background:linear-gradient(135deg,rgba(124,92,255,0.04),rgba(104,166,255,0.03));border:1px solid rgba(124,92,255,0.06);}
    .btn-choose{margin-top:12px;padding:10px 18px;border-radius:10px;color:#fff;font-weight:700;background:linear-gradient(135deg,var(--accent),var(--accent-2));border:none;cursor:pointer;}
    .small{color:#bcd8ff;font-size:0.95rem}
    @media(max-width:640px){ .card{padding:18px;} .choice{padding:18px;} .icon{width:60px;height:60px;font-size:22px;} .btn-choose{padding:8px 14px;font-size:14px;} .grid-cols-2{grid-template-columns:1fr !important;} }
  </style>
</head>
<body>
  <div class="card" role="main" aria-labelledby="title">
    <h2 id="title" style="font-size:20px;margin:0 0 6px 0">Elige tu rol en ENYOOI</h2>
    <div class="small" style="text-align:left">Selecciona una opción para continuar. Esta pantalla aparece solo una vez.</div>

    <?php if (!empty($_SESSION['roleError'])): ?>
      <div style="margin-top:12px;background:#3b4256;padding:10px;border-radius:8px;color:#ffdede;">
        <?= htmlspecialchars($_SESSION['roleError']); unset($_SESSION['roleError']); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/home/role_select" style="margin-top:18px;">
      <div class="grid-cols-2" style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
        <div class="choice creadora" role="button" aria-pressed="false">
          <div class="icon" style="background:linear-gradient(135deg,var(--accent),var(--accent-2));">🎤</div>
          <div style="font-weight:700;font-size:18px">Ser Creadora</div>
          <div class="small" style="text-align:center;margin-top:8px">Transmitir, vender contenido y recibir pagos.</div>
          <button type="submit" name="role" value="creadora" class="btn-choose">Quiero ser creadora</button>
        </div>

        <div class="choice espectador">
          <div class="icon" style="background:linear-gradient(135deg,#7c5cff,#68a6ff);">👀</div>
          <div style="font-weight:700;font-size:18px">Seguir como Espectador</div>
          <div class="small" style="text-align:center;margin-top:8px">Ver transmisiones y apoyar a creadoras.</div>
          <button type="submit" name="role" value="espectador" class="btn-choose">Seguir como espectador</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
