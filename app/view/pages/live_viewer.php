<!-- Vista espectador -->
 <?php include_once __DIR__ . '/../custom/header.php'; include_once __DIR__ . '/../custom/navbar.php'; ?>
<div class="container mt-3">
  <div class="row">
    <div class="col-md-8">
      <!-- Player: para WebRTC/HLS/FLV (según modo) -->
      <div id="playerContainer" style="position:relative;width:360px;height:640px;background:#000;border-radius:12px;">
        <video id="player" playsinline autoplay controls style="width:100%;height:100%;"></video>
      </div>
      <div class="mt-2">
        <button class="btn btn-light" id="likeBtn">❤️ Like</button>
      </div>
    </div>
    <div class="col-md-4">
      <h5><?php echo htmlspecialchars($datos['live']->title); ?></h5>
      <div id="chatBox" style="height:420px;overflow:auto;border:1px solid #eee;border-radius:8px;padding:8px;"></div>
      <div class="input-group mt-2">
        <input type="text" id="chatInput" class="form-control" placeholder="Escribe un mensaje...">
        <div class="input-group-append"><button id="sendMsg" class="btn btn-outline-secondary">Enviar</button></div>
      </div>

      <h6 class="mt-3">Donar Gemas</h6>
      <div class="input-group">
        <input type="number" min="1" id="donationAmount" class="form-control" placeholder="Cantidad">
        <div class="input-group-append"><button id="donateBtn" class="btn btn-purple">Donar</button></div>
      </div>

      <div class="mt-3">
        <b>Actividad:</b>
        <ul id="activity" class="list-unstyled mb-0"></ul>
      </div>
    </div>
  </div>
</div>
<script>window.__LIVE_ID__ = <?php echo (int)$datos['live']->id; ?>; window.__MODE__ = "<?php echo $datos['live']->mode; ?>";</script>
<script src="<?php echo URL_PROJECT ?>/public/js/live_viewer.js"></script>
<?php include_once __DIR__ . '/../custom/footer.php'; ?>
