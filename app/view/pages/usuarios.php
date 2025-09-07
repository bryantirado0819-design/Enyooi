<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';
?>

<style>
/* Estilos para la página de exploración de creadoras */
body {
    background: radial-gradient(1200px 600px at 10% 10%, rgba(124, 92, 255, 0.12), transparent 8%),
                radial-gradient(1000px 600px at 90% 80%, rgba(255, 79, 163, 0.08), transparent 8%),
                linear-gradient(180deg, #020617 0%, #081127 100%);
}

.creator-card {
    background-color: rgba(30, 41, 59, 0.5); /* Fondo oscuro semitransparente */
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px); /* Para Safari */
    transition: all 0.3s ease;
    overflow: hidden; /* Para que el banner no se salga de los bordes redondeados */
}
.creator-card:hover {
    transform: translateY(-8px);
    border-color: rgba(124, 92, 255, 0.7);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
}

.card-banner {
    height: 120px;
    background-size: cover;
    background-position: center;
}

.card-body {
    position: relative;
}

.card-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid #1e293b; /* Un color de borde que combine */
    margin-top: -50px; /* Para que se superponga al banner */
    object-fit: cover;
}

.btn-subscribe {
    background: linear-gradient(to right, #ff7bbd, #7c5cff);
    color: white;
    font-weight: 600;
    border: none;
    transition: all 0.2s ease;
}
.btn-subscribe:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(124, 92, 255, 0.4);
}
</style>

<div class="container mt-5">
    <h1 class="text-center text-white font-weight-bold mb-5" style="font-size: 2.5rem;">Explorar Creadoras</h1>

    <div class="row">
        <?php if (!empty($datos['creadoras'])) : ?>
            <?php foreach ($datos['creadoras'] as $creadora) : ?>
                <div class="col-lg-4 col-md-6 mb-5">
                    <a href="<?php echo URL_PROJECT ?>perfil/<?php echo htmlspecialchars($creadora->usuario); ?>" class="text-decoration-none">
                        <div class="creator-card text-white">
                            <div class="card-banner" style="background-image: url('<?php echo URL_PROJECT . htmlspecialchars($creadora->banner_portada); ?>');"></div>
                            
                            <div class="card-body d-flex flex-column align-items-center p-4">
                                <img src="<?php echo URL_PROJECT . htmlspecialchars($creadora->foto_perfil); ?>" class="card-avatar" alt="Foto de <?php echo htmlspecialchars($creadora->nickname_artistico); ?>">
                                
                                <h5 class="font-weight-bold mt-3 mb-1"><?php echo htmlspecialchars($creadora->nickname_artistico); ?></h5>
                                <p class="text-muted small">@<?php echo htmlspecialchars($creadora->usuario); ?></p>
                                
                                <p class="text-center my-3" style="font-size: 0.9rem; color: #cbd5e1; min-height: 4.5rem;">
                                    <?php echo htmlspecialchars(substr($creadora->bio, 0, 100)) . (strlen($creadora->bio) > 100 ? '...' : ''); ?>
                                </p>

                                <div class="d-flex w-100 justify-content-around my-3 border-top border-bottom border-secondary py-2">
                                    <div class="text-center">
                                        <div class="font-weight-bold"><?php echo $creadora->total_publicaciones; ?></div>
                                        <div class="small text-muted">Posts</div>
                                    </div>
                                </div>
                                
                                <button class="btn-subscribe btn btn-block rounded-pill py-2">
                                    Suscribirse por $<?php echo number_format($creadora->precio_suscripcion, 2); ?>/mes
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-white col-12">No se encontraron creadoras en este momento.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>