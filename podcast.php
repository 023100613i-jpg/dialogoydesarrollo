<?php
require_once 'config/conexion.php';

// Solo mostrar podcasts publicados
$stmt = $pdo->query("SELECT * FROM podcasts WHERE estado = 'publicado' OR estado IS NULL ORDER BY fecha_publicacion DESC");
$podcasts = $stmt->fetchAll();

function urlImagenPodcast($ruta) {
    if (empty($ruta)) return '';
    $ruta = ltrim($ruta, '/');
    return '/dialogoydesarrollo/' . $ruta;
}
function imagenPodcastExiste($ruta) {
    if (empty($ruta)) return false;
    $ruta = ltrim($ruta, '/');
    return file_exists($_SERVER['DOCUMENT_ROOT'] . '/dialogoydesarrollo/' . $ruta);
}
function formatearFechaPodcast($fecha) {
    if (empty($fecha)) return '';
    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Set','Oct','Nov','Dic'];
    $ts = strtotime($fecha);
    return $meses[(int)date('n', $ts) - 1] . ' ' . date('d', $ts) . ', ' . date('Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast - DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        .podcast-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #ecf0f1;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }
        .podcast-card:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            transform: translateY(-3px);
        }
        .podcast-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .podcast-imagen {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid #ecf0f1;
        }
        .podcast-no-img {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fde8e6;
            border-radius: 10px;
            color: #c0392b;
            font-size: 3rem;
        }
        .podcast-info h3 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .podcast-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }
        .podcast-meta .duracion {
            background: #f8f9fa;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            color: #2c3e50;
            font-weight: 500;
        }
        .podcast-meta .fecha {
            color: #c0392b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .podcast-desc {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .podcast-embed {
            margin-top: 1rem;
        }
        .podcast-embed iframe {
            width: 100%;
            border-radius: 10px;
            border: none;
        }
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: #7f8c8d;
        }
        .empty-state i {
            font-size: 4rem;
            color: #c0392b;
            margin-bottom: 1rem;
            display: block;
        }
        @media (max-width: 768px) {
            .podcast-content {
                grid-template-columns: 1fr;
            }
            .podcast-imagen, .podcast-no-img {
                max-width: 250px;
                margin: 0 auto;
            }
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
                        <h2 class="title-big">Podcast</h2>
                        <div class="breadcrumb">
                            <ul>
                                <li><a href="index.php">Inicio</a></li>
                                <li class="active">Podcast</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LISTADO DE PODCASTS -->
    <div class="grids-block-5 py-5">
        <section class="py-lg-4 py-md-3">
            <div class="container">
                <?php if (count($podcasts) > 0): ?>
                    <?php foreach ($podcasts as $podcast): ?>
                    <a href="podcast-detalle.php?id=<?php echo $podcast['id']; ?>" style="text-decoration:none;color:inherit;display:block;">
    <div class="podcast-card">
        <div class="podcast-content">
            <div>
                <?php if (!empty($podcast['imagen']) && imagenPodcastExiste($podcast['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars(urlImagenPodcast($podcast['imagen'])); ?>" 
                         alt="<?php echo htmlspecialchars($podcast['titulo']); ?>" 
                         class="podcast-imagen">
                <?php else: ?>
                    <div class="podcast-no-img">
                        <i class="fas fa-podcast"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="podcast-info">
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
                <h3><?php echo htmlspecialchars($podcast['titulo']); ?></h3>
                <?php if (!empty($podcast['descripcion'])): ?>
                <p class="podcast-desc"><?php echo nl2br(htmlspecialchars($podcast['descripcion'])); ?></p>
                <?php endif; ?>
                <span class="btn-leer" style="color:#c0392b;font-weight:700;">
                    Escuchar <i class="fas fa-arrow-right"></i>
                </span>
            </div>
        </div>
    </div>
</a>
                                <h3><?php echo htmlspecialchars($podcast['titulo']); ?></h3>
                                <?php if (!empty($podcast['descripcion'])): ?>
                                <p class="podcast-desc"><?php echo nl2br(htmlspecialchars($podcast['descripcion'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($podcast['url_embed'])): ?>
                                <div class="podcast-embed">
                                    <?php 
                                    $url = $podcast['url_embed'];
                                    // Si es URL de Spotify
                                    if (strpos($url, 'spotify.com') !== false && strpos($url, 'embed') === false) {
                                        $url = str_replace('open.spotify.com/', 'open.spotify.com/embed/', $url);
                                        echo '<iframe src="' . htmlspecialchars($url) . '" width="100%" height="152" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
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
                                            echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($video_id) . '" width="100%" height="315" frameborder="0" allowfullscreen></iframe>';
                                        }
                                    }
                                    // Si ya es un iframe completo
                                    elseif (strpos($url, '<iframe') !== false) {
                                        echo $url;
                                    }
                                    // Si es otro tipo, mostrar link
                                    else {
                                        echo '<a href="' . htmlspecialchars($url) . '" target="_blank" class="btn-leer" style="color:#c0392b;font-weight:700;text-decoration:none;">Escuchar <i class="fas fa-arrow-right"></i></a>';
                                    }
                                    ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-podcast"></i>
                        <p style="font-size:1.2rem;">No hay podcasts disponibles por el momento.</p>
                    </div>
                <?php endif; ?>
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