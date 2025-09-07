<!-- Vista creador -->
 <?php include_once __DIR__ . '/../custom/header.php'; include_once __DIR__ . '/../custom/navbar.php'; ?>
<div class="container mt-4">
  <h3>Transmitir en vivo</h3>

  <form id="startLiveForm">
    <div class="form-group">
      <label>Título</label>
      <input type="text" name="title" class="form-control" placeholder="Mi transmisión">
    </div>
    <div class="form-group">
      <label>Modo</label>
      <select name="mode" class="form-control">
        <option value="webrtc">Cámara del navegador (WebRTC)</option>
        <option value="rtmp">OBS (RTMP)</option>
      </select>
    </div>
    <div class="form-group form-check">
      <input type="checkbox" name="vertical" id="vertical" class="form-check-input" checked>
      <label for="vertical" class="form-check-label">Formato vertical tipo TikTok</label>
    </div>

    <h5 class="mt-3">Lovense (opcional)</h5>
    <div class="form-row">
      <div class="col">
        <input type="text" id="lovense_token" class="form-control" placeholder="Access Token Lovense">
      </div>
      <div class="col">
        <input type="text" id="lovense_api" class="form-control" placeholder="API Base (opc.)" value="https://api.lovense.com">
      </div>
      <div class="col-auto">
        <button type="button" id="linkLovense" class="btn btn-outline-secondary">Vincular</button>
      </div>
    </div>

    <small class="text-muted">Reglas por rangos de ZAFIRO → intensidad/duración</small>
    <textarea id="lovense_rules" class="form-control mt-2" rows="3" placeholder='[{"min_zafiro":1,"max_zafiro":49,"intensity":5,"duration_ms":1500},{"min_zafiro":50,"max_zafiro":199,"intensity":10,"duration_ms":3000}]'></textarea>

    <button class="btn btn-purple mt-3" type="submit">Iniciar transmisión</button>
  </form>

  <div id="obsBlock" class="alert alert-info mt-3" style="display:none;">
    <b>RTMP (para OBS)</b>
    <div>URL: <span id="rtmpUrl">--</span></div>
    <div>Key: <span id="rtmpKey">--</span></div>
  </div>

  <div id="webrtcBlock" class="mt-3" style="display:none;">
    <video id="preview" playsinline autoplay muted style="width:360px;height:640px;background:#000;border-radius:12px;"></video>
    <div class="mt-2">
      <button id="goLiveWebRTC" class="btn btn-success">Publicar WebRTC</button>
      <button id="stopLive" class="btn btn-danger">Terminar</button>
    </div>
  </div>
</div>
<script src="<?php echo URL_PROJECT ?>/public/js/live_creator.js"></script>
<?php include_once __DIR__ . '/../custom/footer.php'; ?>
