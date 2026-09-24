<?php
require_once 'config/conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ? AND (estado = 'publicado' OR estado IS NULL)");
$stmt->execute([$id]);
$podcast = $stmt->fetch();

if (!$podcast) {
    header('Location: podcast.php');
    exit;
}


function imagenPodcastExiste($ruta) {
    if (empty($ruta)) return false;
    $ruta = ltrim($ruta, '/');
    return file_exists($_SERVER['DOCUMENT_ROOT'] . '/dialogoydesarrollo/' . $ruta);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($podcast['titulo']); ?> - DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        .podcast-detalle {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #ecf0f1;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .podcast-detalle-header {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .podcast-detalle-imagen {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 12px;
            background: #f8f9fa;
            border: 1px solid #ecf0f1;
        }
        .podcast-detalle-no-img {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fde8e6;
            border-radius: 12px;
            color: #c0392b;
            font-size: 4rem;
        }
        .podcast-detalle-info h1 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .podcast-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .podcast-meta .fecha {
            color: #c0392b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .podcast-meta .duracion {
            background: #f8f9fa;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            color: #2c3e50;
            font-weight: 500;
        }
        .podcast-detalle-desc {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        .podcast-botones {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .btn-link-externo {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: #c0392b;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-link-externo:hover {
            background: #a93226;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
            color: white;
        }
        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: transparent;
            color: #2c3e50;
            border: 1px solid #d5d8dc;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-volver:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }
        .podcast-embed-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #ecf0f1;
        }
        .podcast-embed-container iframe {
            width: 100%;
            border-radius: 10px;
            border: none;
            display: block;
        }
        @media (max-width: 768px) {
            .podcast-detalle-header {
                grid-template-columns: 1fr;
            }
            .podcast-detalle-imagen, .podcast-detalle-no-img {
                max-width: 250px;
                margin: 0 auto;
            }
            .podcast-detalle-info h1 {
                font-size: 1.4rem;
                text-align: center;
            }
            .podcast-meta { justify-content: center; }
            .podcast-botones { justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header id="site-header" class="fixed-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg stroke">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/images/logo.png" alt="DDP Noticias" style="height:75px;" />
                </a>
                <button class="navbar-toggler collapsed bg-gradient" type="button" data-toggle="collapse"
                    data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
                    <span class="navbar-toggler-icon fa icon-close fa-times"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="noticias.php">Actualidad</a></li>
                        <li class="nav-item"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
                        <li class="nav-item active"><a class="nav-link" href="podcast.php">Podcast</a></li>
                        <li class="nav-item"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
                        <li class="nav-item"><a class="nav-link" href="alianzas.php">Alianzas</a></li>
                        <li class="nav-item"><a class="nav-link" href="sobre-nosotros.php">Sobre D&D</a></li>
                        <li class="ml-2"><a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- BREADCRUMB -->
    <section class="breadcrumb-area py-sm-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="breadcrumb-contents">
                        <h2 class="title-big"><?php echo htmlspecialchars($podcast['titulo']); ?></h2>
                        <div class="breadcrumb">
                            <ul>
                                <li><a href="index.php">Inicio</a></li>
                                <li><a href="podcast.php">Podcast</a></li>
                                <li class="active">Detalle</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DETALLE DEL PODCAST -->
    <div class="grids-block-5 py-5">
        <section class="py-lg-4 py-md-3">
            <div class="container">

                <div class="podcast-detalle">
                    <div class="podcast-detalle-header">
                        <div>
                            <?php if (!empty($podcast['imagen']) && imagenPodcastExiste($podcast['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars(urlImagenPodcast($podcast['imagen'])); ?>" 
                                     alt="<?php echo htmlspecialchars($podcast['titulo']); ?>" 
                                     class="podcast-detalle-imagen">
                            <?php else: ?>
                                <div class="podcast-detalle-no-img">
                                    <i class="fas fa-podcast"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="podcast-detalle-info">
                            <div class="podcast-meta">
                                <span class="fecha">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo formatearFechaPodcast($podcast['fecha_publicacion']); ?>
                                </span>
                                <?php if (!empty($podcast['duracion'])): ?>
                                <span class="duracion">
                                    <i class="far fa-clock"></i>
                                    <?php echo htmlspecialchars($podcast['duracion']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <h1><?php echo htmlspecialchars($podcast['titulo']); ?></h1>
                            
                            <?php if (!empty($podcast['descripcion'])): ?>
                            <p class="podcast-detalle-desc"><?php echo nl2br(htmlspecialchars($podcast['descripcion'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="podcast-botones">
                                <?php if (!empty($podcast['url_embed'])): ?>
                                <a href="<?php echo htmlspecialchars($podcast['url_embed']); ?>" 
                                   target="_blank" 
                                   class="btn-link-externo">
                                    <i class="fas fa-external-link-alt"></i> Abrir en sitio original
                                </a>
                                <?php endif; ?>
                                <a href="podcast.php" class="btn-volver">
                                    <i class="fas fa-arrow-left"></i> Volver a todos
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- EMBED -->
                    <?php if (!empty($podcast['url_embed'])): ?>
                    <div class="podcast-embed-container">
                        <?php 
                        $url = $podcast['url_embed'];
                        
                        // Si es URL de Spotify
                        if (strpos($url, 'spotify.com') !== false && strpos($url, 'embed') === false) {
                            $url = str_replace('open.spotify.com/', 'open.spotify.com/embed/', $url);
                            echo '<iframe src="' . htmlspecialchars($url) . '" width="100%" height="352" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
                        }
                        // Si es URL de YouTube
                        elseif (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                            if (strpos($url, 'youtu.be') !== false) {
                                $video_id = basename(parse_url($url, PHP_URL_PATH));
                            } else {
                                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                                $video_id = $params['v'] ?? '';
                            }
                            if ($video_id) {
                                echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($video_id) . '" width="100%" height="480" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                            }
                        }
                        // Si es URL de SoundCloud
                        elseif (strpos($url, 'soundcloud.com') !== false) {
                            echo '<iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=' . urlencode($url) . '&color=%23c0392b&auto_play=false&hide_related=true&show_comments=false&show_user=true&show_reposts=false&show_teaser=false"></iframe>';
                        }
                        // Si ya es un iframe completo
                        elseif (strpos($url, '<iframe') !== false) {
                            echo $url;
                        }
                        // Otro tipo de link
                        else {
                            echo '<div style="text-align:center;padding:2rem;">';
                            echo '<i class="fas fa-podcast" style="font-size:3rem;color:#c0392b;margin-bottom:1rem;display:block;"></i>';
                            echo '<p style="color:#7f8c8d;margin-bottom:1rem;">Este podcast está disponible en un sitio externo</p>';
                            echo '<a href="' . htmlspecialchars($url) . '" target="_blank" class="btn-link-externo"><i class="fas fa-external-link-alt"></i> Escuchar ahora</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </div>

    <!-- FOOTER -->
    <section class="w3l-footer-29-main py-5" id="footer">
        <div class="footer-29 py-md-3">
            <div class="container">
                <div class="row footer-top-29">
                    <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
                        <h6 class="footer-title-29">Quiénes Somos</h6>
                        <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                        <div class="main-social-footer-29">
                            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square"></span></a>
                            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png" style="height:20px;"></a>
                            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram"></span></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
                        <ul>
                            <h6 class="footer-title-29">Contenido</h6>
                            <li><a href="noticias.php">Noticias</a></li>
                            <li><a href="videos.php">Videos</a></li>
                            <li><a href="podcast.php">Podcast</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
                        <div class="properties">
                            <h6 class="footer-title-29">Contacto</h6>
                            <ul>
                                <li><a href="mailto:info@dialogoydesarrollo.com.pe">info@dialogoydesarrollo.com.pe</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bottom-copies text-center">
                    <p class="copy-footer-29">© <?php echo date('Y'); ?> Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
                </div>
            </div>
        </div>
        <button onclick="topFunction()" id="movetop" title="Go to top">
            <span class="fa fa-angle-up"></span>
        </button>
    </section>

    <!-- SCRIPTS -->
    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="assets/js/theme-change.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        window.onscroll = function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("movetop").style.display = "block";
            } else {
                document.getElementById("movetop").style.display = "none";
            }
        };
        function topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
    </script>
</body>
</html>