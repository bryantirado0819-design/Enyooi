<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';

// Asignamos las variables de forma segura
$usuario = $datos['usuario'] ?? null;
$perfil = $datos['perfil'] ?? null;
$esCreadora = ($usuario && $usuario->rol === 'creadora');
?>

<style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body {
        background: radial-gradient(1200px 600px at 10% 10%, rgba(124, 92, 255, 0.12), transparent 8%),
                    radial-gradient(1000px 600px at 90% 80%, rgba(255, 79, 163, 0.08), transparent 8%),
                    linear-gradient(180deg, #020617 0%, #081127 100%);
    }
    .card-glass {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    }
    .nav-link-settings {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.75rem 1rem; border-radius: 0.5rem;
        color: #cbd5e1; transition: all 0.2s ease-in-out;
    }
    .nav-link-settings:hover {
        background-color: rgba(255, 255, 255, 0.05); color: white; text-decoration: none;
    }
    .nav-link-settings.active {
        background: linear-gradient(to right, var(--accent), var(--accent-2));
        color: white; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .form-control-glass {
        background-color: rgba(0,0,0,0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important; color: #e2e8f0 !important;
    }
    .form-control-glass:focus {
        border-color: #ff7bbd !important;
        box-shadow: 0 0 0 3px rgba(255, 123, 189, 0.3) !important;
    }
    .btn-gradient {
        background: linear-gradient(to right, var(--accent), var(--accent-2));
        border: none; transition: transform 0.2s ease; color: white; font-weight: bold;
    }
    .btn-gradient:hover { transform: scale(1.05); color: white; }
    .image-upload-box {
        border: 2px dashed rgba(255,255,255,0.2); border-radius: 0.75rem;
        padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s ease;
    }
    .image-upload-box:hover {
        border-color: var(--accent); background-color: rgba(255,255,255,0.05);
    }
    .image-preview {
        width: 100px; height: 100px; border-radius: 50%; object-fit: cover;
        border: 3px solid var(--accent-2);
    }
    .verification-section {
        display: none;
        background-color: rgba(0,0,0,0.2);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
    .resend-link {
        color: var(--accent);
        cursor: pointer;
        text-decoration: none;
    }
    .resend-link.disabled {
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

<div class="container my-5 text-white">
    <div class="row">
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="card card-glass p-3">
                <nav class="nav flex-column" id="settings-tabs" role="tablist">
                    <a class="nav-link-settings active" id="profile-tab" data-toggle="tab" href="#profile" role="tab"><i class="fas fa-user-circle fa-fw"></i> Perfil</a>
                    <a class="nav-link-settings" id="security-tab" data-toggle="tab" href="#security" role="tab"><i class="fas fa-shield-alt fa-fw"></i> Seguridad</a>
                    <?php if ($esCreadora): ?>
                        <a class="nav-link-settings" id="verification-tab" data-toggle="tab" href="#verification" role="tab"><i class="fas fa-check-circle fa-fw"></i> Verificación</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <div class="col-lg-9">
            <div id="alert-container">
                <?php if(isset($_SESSION['settings_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['settings_success']; unset($_SESSION['settings_success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if(isset($_SESSION['settings_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['settings_error']; unset($_SESSION['settings_error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card card-glass p-4">
                        <h4><i class="fas fa-user-edit mr-2"></i>Editar Perfil</h4>
                        <hr class="border-secondary my-3">
                        
                        <form action="<?php echo URL_PROJECT; ?>settings/updateProfile" method="POST" class="mb-5">
                            <div class="form-group">
                                <label for="nickname_artistico">Nickname Artístico</label>
                                <input type="text" name="nickname_artistico" class="form-control form-control-glass" value="<?php echo htmlspecialchars($perfil->nickname_artistico ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="usuario">Nombre de Usuario @</label>
                                <input type="text" name="usuario" class="form-control form-control-glass" value="<?php echo htmlspecialchars($usuario->usuario ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="bio">Biografía</label>
                                <textarea name="bio" class="form-control form-control-glass" rows="3"><?php echo htmlspecialchars($perfil->bio ?? ''); ?></textarea>
                            </div>
                            
                            <?php if ($esCreadora): ?>
                            <div class="form-group">
                                <label for="chat_precio">Precio para Iniciar Chat (en Gemas)</label>
                                <input type="number" name="chat_precio" class="form-control form-control-glass" value="<?php echo htmlspecialchars($perfil->chat_precio ?? 0); ?>" min="0">
                                <small class="form-text text-muted">0 significa que es gratis. Es un pago único por usuario.</small>
                            </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-gradient">Guardar Perfil</button>
                        </form>

                        <hr class="my-4 border-secondary">

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h5><i class="fas fa-camera-retro mr-2"></i>Foto de Perfil</h5>
                                <form action="<?php echo URL_PROJECT; ?>settings/updateProfileImage" method="POST" enctype="multipart/form-data" class="mt-3">
                                    <div class="image-upload-box" onclick="this.querySelector('input').click()">
                                        <img src="<?php echo URL_PROJECT . ($perfil->foto_perfil ?? 'public/img/defaults/default_avatar.png'); ?>" id="profile-preview" class="image-preview mx-auto mb-3">
                                        <p class="small text-muted" id="profile-filename">Haz clic para cambiar</p>
                                        <input type="file" name="profile_image" class="d-none" onchange="previewImage(this, 'profile-preview', 'profile-filename')">
                                    </div>
                                    <button type="submit" class="btn btn-secondary mt-3">Actualizar Foto</button>
                                </form>
                            </div>
                            
                            <?php if ($esCreadora): ?>
                            <div class="col-md-6">
                                <h5><i class="fas fa-image mr-2"></i>Banner de Portada</h5>
                                <form action="<?php echo URL_PROJECT; ?>settings/updateBannerImage" method="POST" enctype="multipart/form-data">
                                    <div class="image-upload-box" onclick="this.querySelector('input').click()">
                                        <p class="small text-muted" id="banner-filename">Selecciona tu banner</p>
                                        <input type="file" name="banner_image" class="d-none" onchange="document.getElementById('banner-filename').textContent = this.files[0].name;">
                                    </div>
                                    <button type="submit" class="btn btn-secondary mt-3">Actualizar Banner</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="security" role="tabpanel">
                    <div class="card card-glass p-4">
                        <h4><i class="fas fa-lock mr-2"></i>Correo y Contraseña</h4>
                        <hr class="my-3">
                        <form id="form-email-change" class="mb-5" data-verification-container="verification-container-security">
                            <input type="hidden" name="type" value="email_change">
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <div class="input-group">
                                    <input type="email" name="value" class="form-control form-control-glass" value="<?php echo htmlspecialchars($usuario->correo ?? ''); ?>" required>
                                    <div class="input-group-append"><button type="submit" class="btn btn-outline-light">Enviar Código</button></div>
                                </div>
                                <small class="form-text text-muted">Se enviará un código a tu nuevo correo para confirmar el cambio.</small>
                            </div>
                        </form>
                        <hr class="my-4">
                        <form id="form-password-change" data-verification-container="verification-container-security">
                             <input type="hidden" name="type" value="password_reset">
                             <h5>Cambiar Contraseña</h5>
                            <div class="form-group"><label for="current_password">Contraseña Actual</label><input type="password" name="current_password" class="form-control form-control-glass" required></div>
                            <div class="form-group"><label for="new_password">Nueva Contraseña</label><input type="password" name="new_password" class="form-control form-control-glass" required></div>
                            <button type="submit" class="btn btn-gradient">Solicitar Cambio de Contraseña</button>
                       </form>
                        
                        <div id="verification-container-security"></div>
                    </div>
                </div>

                <?php if ($esCreadora): ?>
                <div class="tab-pane fade" id="verification" role="tabpanel">
                    <div class="card card-glass p-4">
                        <h4><i class="fas fa-user-check mr-2"></i>Verificación de Cuenta
                            <?php if (isset($usuario->cuenta_verificada) && $usuario->cuenta_verificada == 1): ?>
                                <span class="ml-2 text-sm font-semibold text-blue-400 bg-blue-500/10 px-2 py-1 rounded-full"><i class="fas fa-check-circle"></i> Verificada</span>
                            <?php else: ?>
                                <span class="ml-2 text-sm font-semibold text-amber-400 bg-amber-500/10 px-2 py-1 rounded-full"><i class="fas fa-exclamation-triangle"></i> No Verificada</span>
                            <?php endif; ?>
                        </h4>
                        <p class="text-muted small">Verifica tu cuenta para obtener el fueguito 🔥 y demostrar que eres una creadora auténtica. La verificación se hará a tu correo electrónico registrado.</p>
                        <hr class="border-secondary my-3">
                        
                        <?php if(isset($usuario->cuenta_verificada) && $usuario->cuenta_verificada == 1): ?>
                             <div class="alert alert-success"><i class="fas fa-check-circle"></i> ¡Felicidades! Tu cuenta está verificada.</div>
                        <?php else: ?>
                            <form id="form-account-verify" data-verification-container="verification-container-creator">
                                <input type="hidden" name="type" value="account_verify">
                                <p>Se enviará un código de verificación a: <strong><?php echo htmlspecialchars($usuario->correo ?? ''); ?></strong></p>
                                <button type="submit" class="btn btn-gradient mt-3">Enviar Código de Verificación</button>
                            </form>
                            
                            <div id="verification-container-creator"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId, filenameId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = e => {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(filenameId).textContent = input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function() {
        const url_project = "<?php echo URL_PROJECT; ?>";
        let timerInterval;

        function showAlert(message, type = 'success') {
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert" style="transition: opacity 0.5s ease;">${message}<button type="button" class="close" data-dismiss="alert">&times;</button></div>`;
            $('#alert-container').html(alertHtml);
            setTimeout(() => {
                $('#alert-container .alert').fadeOut(500, function() { $(this).remove(); });
            }, 5000);
        }

        function startResendTimer(resendLink) {
            let timeLeft = 300; // 5 minutos en segundos
            resendLink.addClass('disabled').css('pointer-events', 'none');

            timerInterval = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                resendLink.text(`Reenviar código en ${minutes}:${seconds}`);
                
                if (--timeLeft < 0) {
                    clearInterval(timerInterval);
                    resendLink.removeClass('disabled').text('Reenviar código').css('pointer-events', 'auto');
                }
            }, 1000);
        }

        function showVerificationForm(type, containerId, originalForm) {
            clearInterval(timerInterval);
            const container = $(`#${containerId}`);
            const formHtml = `
                <div class="verification-section" style="display:none;">
                    <h5 class="text-white">Introduce el Código de Verificación</h5>
                    <p class="small text-muted mb-3">Revisa tu correo para encontrar el código de 6 dígitos.</p>
                    <form id="form-verify-code">
                        <input type="hidden" id="verificationType" name="type" value="${type}">
                        <input type="text" name="code" class="form-control form-control-glass text-center" style="font-size: 1.5rem; letter-spacing: 0.5rem;" placeholder="------" required maxlength="6" autocomplete="off">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="#" class="resend-link" id="resend-code-link"></a>
                            <div>
                                <button type="button" class="btn btn-secondary" id="cancel-verification">Cancelar</button>
                                <button type="button" id="submit-verification" class="btn btn-gradient">Verificar</button>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            container.html(formHtml).find('.verification-section').slideDown();
            const resendLink = container.find('#resend-code-link');
            startResendTimer(resendLink);

            resendLink.off('click').on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('disabled')) {
                    originalForm.submit();
                    startResendTimer($(this));
                }
            });
        }

        $('#form-email-change, #form-password-change, #form-account-verify').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            let data = form.serialize();
            const type = form.find('input[name="type"]').val();
            
            if (type === 'password_reset') {
                 data += '&new_password=' + encodeURIComponent(form.find('input[name="new_password"]').val());
                 data += '&current_password=' + encodeURIComponent(form.find('input[name="current_password"]').val());
            }

            $.post(`${url_project}settings/sendVerificationCode`, data, function(response) {
                if (response.success) {
                    const containerId = form.data('verification-container');
                    showVerificationForm(type, containerId, form);
                    showAlert(response.message, 'info');
                } else {
                    showAlert(response.message, 'danger');
                }
            }, 'json').fail(() => showAlert('Error de comunicación con el servidor.', 'danger'));
        });

        $(document).on('click', '#submit-verification', function() {
            const form = $('#form-verify-code');
            $.post(`${url_project}settings/verifyAndUpdate`, form.serialize(), function(response) {
                if (response.success) {
                    $('.verification-section').slideUp(() => $(this).remove());
                    showAlert(response.message, 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlert(response.message, 'danger');
                }
            }, 'json').fail(() => showAlert('Error de comunicación.', 'danger'));
        });
        
        $(document).on('click', '#cancel-verification', function() {
             $('.verification-section').slideUp(() => $(this).remove());
             clearInterval(timerInterval);
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('lastTab', $(e.target).attr('href'));
        });
        let lastTab = localStorage.getItem('lastTab');
        if (lastTab) {
            $('#settings-tabs a[href="' + lastTab + '"]').tab('show');
        }
    });
</script>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>