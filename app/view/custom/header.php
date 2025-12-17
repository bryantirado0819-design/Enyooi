<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" xintegrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- Font Awesome-->
    <script src="https://kit.fontawesome.com/9cb27254ca.js" crossorigin="anonymous"></script>
    
    <!--Css CORREGIDO: Ya no llevan '/public' -->
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/css/login.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/css/register.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/css/perfil.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/css/glassmorphism-feed.css">
    
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Reemplaza la línea actual de font-awesome con esta: -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" />
    <!--Animation-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.10.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.10.4/ScrollTrigger.min.js"></script>
    
    <!--Icon CORREGIDO -->
    <link rel="icon" type="image/png" href="<?php echo URL_PROJECT; ?>/img/ico_enyooi.png"/>

    <title><?php echo PROJECT_NAME ?></title>
     <script>
        
        const isLoggedIn = <?php echo isset($_SESSION['logueando']) ? 'true' : 'false'; ?>;

        
        window.enyooiConfig = {
            userId: <?php echo isset($_SESSION['logueando']) ? json_encode($_SESSION['logueando']) : 'null'; ?>,
            urlBase: '<?php echo RUTA_URL; ?>'
        };
    </script>
</head>
<body>