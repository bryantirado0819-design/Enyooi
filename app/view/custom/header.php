
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- Font Awesome-->
    <script src="https://kit.fontawesome.com/9cb27254ca.js" crossorigin="anonymous"></script>
    <!--Css-->
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/style.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/login.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/register.css">
    <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/perfil.css">
        <link rel="stylesheet" href="<?php echo URL_PROJECT; ?>/public/css/glassmorphism-feed.css">
   <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40JuKakVxfgGNGgoIHPUV6k/vJXVjQoCc52XN6s5gQ/V1Q/mFz5w/yBv1C/X+R02M/v1F0A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!--Animation-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.10.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.10.4/ScrollTrigger.min.js"></script>
    <!--Icon-->
    <link rel="icon" type="image/png" href="<?php echo URL_PROJECT; ?>/public/img/ico_enyooi.png"/>

    <title><?php echo PROJECT_NAME ?></title>
     <script>
        window.enyooiConfig = {
            userId: <?php echo isset($_SESSION['logueando']) ? json_encode($_SESSION['logueando']) : 'null'; ?>,
            urlBase: '<?php echo RUTA_URL; ?>'
        };
    </script>
</head>
<body>

