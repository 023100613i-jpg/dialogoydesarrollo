<?php
require_once 'config/conexion.php';

// Función para obtener datos con manejo de errores
function obtenerDatos($pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function obtenerDato($pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Verifica si la imagen existe físicamente en el servidor.
 */
function imagenExiste($ruta) {
    if (empty($ruta)) return false;
    $ruta = ltrim($ruta, '/');
    $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . '/dialogoydesarrollo/' . $ruta;
    return file_exists($rutaCompleta);
}

/**
 * Genera la URL pública correcta para mostrar la imagen en el HTML.
 */
function urlImagen($ruta) {
    if (empty($ruta)) return '';
    $ruta = ltrim($ruta, '/');
    return '/dialogoydesarrollo/' . $ruta;
}

/**
 * Genera la URL pública correcta para el PDF.
 */
function urlPdf($ruta) {
    if (empty($ruta)) return '#';
    $ruta = ltrim($ruta, '/');
    return '/dialogoydesarrollo/' . $ruta;
}

// Reportaje destacado principal
// Últimos reportajes (solo publicados)
$ultimosReportajes = obtenerDatos($pdo, "SELECT r.*, CONCAT(a.nombres, ' ', a.ap_paterno) as autor 
                                        FROM reportajes r 
                                        LEFT JOIN autores a ON r.autor_id = a.id 
                                        WHERE r.estado = 'publicado' 
                                        ORDER BY r.fecha_publicacion DESC LIMIT 3");


$destacado = obtenerDato($pdo, "SELECT r.*, CONCAT(a.nombres, ' ', a.ap_paterno) as autor 
                              FROM reportajes r 
                              LEFT JOIN autores a ON r.autor_id = a.id 
                              WHERE r.es_destacado = 1 AND r.estado = 'publicado' 
                              ORDER BY r.fecha_publicacion DESC LIMIT 1");

// Noticias recientes
$noticias = obtenerDatos($pdo, "SELECT * FROM noticias WHERE estado = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 3");

// Boletín destacado
$boletin = obtenerDato($pdo, "SELECT * FROM boletines ORDER BY fecha_publicacion DESC LIMIT 1");

// Podcasts
$podcasts = obtenerDatos($pdo, "SELECT * FROM podcasts ORDER BY fecha_publicacion DESC LIMIT 4");

// Especiales
$especiales = obtenerDatos($pdo, "SELECT * FROM especiales ORDER BY fecha_publicacion DESC LIMIT 4");

// Alianzas
$alianzas = obtenerDatos($pdo, "SELECT * FROM alianzas WHERE estado = 1 ORDER BY orden LIMIT 6");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDP Noticias - Diálogo y Desarrollo Perú</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        .badge-destacado {
            background: #c0392b;
            color: white;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        .reportaje-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .reportaje-item:last-child { border-bottom: none; }
        .reportaje-item .fecha {
            color: #c0392b;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .reportaje-item h4 a {
            color: #2c3e50;
            text-decoration: none;
        }
        .reportaje-item h4 a:hover { color: #c0392b; }
        .btn-leer {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #c0392b;
            font-weight: 600;
            padding: 0.3rem 0;
            font-size: 0.9rem;
            background: transparent;
            border: none;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-leer:hover {
            color: #a93226;
            gap: 0.6rem;
        }
        .btn-leer i {
            font-size: 0.75rem;
            transition: transform 0.3s;
        }
        .btn-leer:hover i {
            transform: translateX(3px);
        }
        .noticia-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .noticia-item:last-child { border-bottom: none; }
        .noticia-item .fecha {
            color: #7f8c8d;
            font-size: 0.75rem;
        }
        .noticia-item h5 a {
            color: #2c3e50;
            text-decoration: none;
        }
        .noticia-item h5 a:hover { color: #c0392b; }
        .boletin-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .boletin-box img { max-height: 120px; margin-bottom: 0.5rem; }
        .podcast-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .podcast-item img { max-height: 50px; margin-bottom: 0.5rem; }
        .podcast-item p { font-size: 0.85rem; color: #2c3e50; }
        .especial-item {
            text-align: center;
            padding: 0.5rem;
        }
        .especial-item img {
            border-radius: 8px;
            width: 100%;
            max-height: 150px;
            object-fit: cover;
        }
        .especial-item p {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
            color: #2c3e50;
        }
        .alianza-item img {
            max-height: 60px;
            width: auto;
            filter: grayscale(100%);
            transition: filter 0.3s;
        }
        .alianza-item img:hover { filter: grayscale(0%); }
        
        /* Imagen del boletín */
        .boletin-imagen {
            width: 100%;
            border-radius: 8px;
            object-fit: cover;
        }
        .resumen-boletin-home {
    color: #555;
    font-size: 0.95rem;
    line-height: 1.7;
    margin-bottom: 1rem;
}
.resumen-boletin-home p { margin-bottom: 0.5rem; }
.resumen-boletin-home ul,
.resumen-boletin-home ol { margin: 0.5rem 0 0.5rem 1.25rem; }
.resumen-boletin-home img { max-width: 100%; border-radius: 8px; }
.resumen-boletin-home strong { color: #2c3e50; }
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
                        <li class="nav-item active"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#actualidad">Actualidad</a></li>
                        <li class="nav-item"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
                        <li class="nav-item"><a class="nav-link" href="podcast.php">Podcast</a></li>
                        <li class="nav-item"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
                        <li class="nav-item"><a class="nav-link" href="alianzas.php">Alianzas</a></li>
                        <li class="nav-item"><a class="nav-link" href="sobre-nosotros.php">Sobre D&D</a></li>
                        <li class="ml-2"><a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- BANNER PRINCIPAL -->
    <section class="breadcrumb-area py-sm-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="breadcrumb-contents">
                        <h2 class="title-big">Reportajes</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- VIDEO DESTACADO -->
    <?php if ($destacado): ?>
    <section class="w3l-video w3l-homeblock3" id="video">
        <div class="container-fluid">
            <div class="video-grids-info row">
                <div class="video-gd-right col-lg-6 p-0">
                    <div class="position-relative">
                        <?php if (!empty($destacado['foto_principal']) && imagenExiste($destacado['foto_principal'])): ?>
                        <img src="<?php echo htmlspecialchars(urlImagen($destacado['foto_principal'])); ?>" alt="" class="img-fluid">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                    <div class="p-xl-4 p-0 video-wrap">
                        <h5><?php echo formatearFechaMes($destacado['fecha_publicacion']); ?></h5>
                        <h3 class="title-big text-left mb-4">
                            <a href="reportaje_detalle.php?id=<?php echo $destacado['id']; ?>">
                                <?php echo htmlspecialchars($destacado['titulo'] ?? ''); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($destacado['resumen_corto'] ?? ''); ?></p>
                        <a href="reportaje_detalle.php?id=<?php echo $destacado['id']; ?>" class="btn-leer">
                            Leer <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ÚLTIMOS REPORTAJES -->
    <div class="grids-block-5 py-1">
        <section class="py-lg-4 py-md-3">
            <div class="container">
                <div class="row">
                    <?php foreach ($ultimosReportajes as $reportaje): ?>
                    <div class="col-lg-4 col-md-6 grids5-info mt-5">
                        <?php if (!empty($reportaje['foto_principal']) && imagenExiste($reportaje['foto_principal'])): ?>
                        <a href="reportaje_detalle.php?id=<?php echo $reportaje['id']; ?>" class="d-block">
                            <img src="<?php echo htmlspecialchars(urlImagen($reportaje['foto_principal'])); ?>" alt="" class="img-fluid" />
                        </a>
                        <?php endif; ?>
                        <div class="blog-info">
                            <h5><?php echo formatearFechaCorta($reportaje['fecha_publicacion']); ?></h5>
                            <h4><a href="reportaje_detalle.php?id=<?php echo $reportaje['id']; ?>" class="d-block">
                                <?php echo htmlspecialchars($reportaje['titulo'] ?? 'Sin título'); ?>
                            </a></h4>
                            <a href="reportaje_detalle.php?id=<?php echo $reportaje['id']; ?>" class="btn mt-4 p-0">
                                Leer <span class="fa fa-arrow-right"></span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pagination">
                    <ul>
                        <li><a href="reportajes.php">Ver todos</a></li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
       <!-- NOTICIAS RECIENTES -->
    <section class="breadcrumb-area py-sm-5 py-1">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="breadcrumb-contents">
                        <h2 class="title-big">Noticias Recientes</h2>
                        <a class="anchor" id="actualidad"></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="grids-block-5 py-5">
        <section class="py-lg-4 py-md-3">
            <div class="container">
                <div class="row">
                    <?php if (count($noticias) > 0): ?>
                        <?php foreach ($noticias as $noticia): ?>
                        <div class="col-lg-4 col-md-6 grids5-info mt-lg-0 mt-5">
                            <?php if (!empty($noticia['foto']) && imagenExiste($noticia['foto'])): ?>
                            <a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="d-block">
                                <img src="<?php echo htmlspecialchars(urlImagen($noticia['foto'])); ?>" alt="" class="img-fluid" />
                            </a>
                            <?php endif; ?>
                            <div class="blog-info">
                                <h5><?php echo formatearFecha($noticia['fecha_publicacion']); ?></h5>
                                <h4><a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="d-block">
                                    <?php echo htmlspecialchars($noticia['titulo']); ?>
                                </a></h4>
                                <a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="btn mt-4 p-0">
                                    Leer <span class="fa fa-arrow-right"></span>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p style="color:#7f8c8d;font-size:1.1rem;">No hay noticias publicadas.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="pagination">
    <ul>
        <li>
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru">
                Ver todas
            </a>
        </li>
    </ul>
</div>
            </div>
        </section>
    </div>
    <div class="grids-block-5 py-5">
        <section class="py-lg-4 py-md-3">
            <div class="container">
                <div class="row">
                    <?php foreach ($noticias as $noticia): ?>
                    <div class="col-lg-4 col-md-6 grids5-info mt-lg-0 mt-5">
                        <?php if (!empty($noticia['foto']) && imagenExiste($noticia['foto'])): ?>
                        <a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="d-block">
                            <img src="<?php echo htmlspecialchars(urlImagen($noticia['foto'])); ?>" alt="" class="img-fluid" />
                        </a>
                        <?php endif; ?>
                        <div class="blog-info">
                            <h5><?php echo formatearFecha($noticia['fecha_publicacion']); ?></h5>
                            <h4><a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="d-block">
                                <?php echo htmlspecialchars($noticia['titulo']); ?>
                            </a></h4>
                            <a target="_blank" href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="btn mt-4 p-0">
                                Leer <span class="fa fa-arrow-right"></span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pagination">
                    <ul>
                        <li><a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru">Ver todos</a></li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
<?php if ($boletin): ?>

<?php endif; ?>
    <!-- BOLETÍN NTEP -->
    <section class="w3l-homeblock5 py-0">
        <div class="container py-lg-5 py-4">
            <div class="row">
                <div class="col-lg-8 align-self">
                    <h3 class="title-big mb-4">Boletin NTEP Año <?php echo date('Y'); ?></h3>
                    <?php 
if ($boletin): 
    $resumenHTML = $boletin['resumen'] ?? '';
    $resumenTexto = trim(strip_tags($resumenHTML));
    if (!empty($resumenTexto)): 
?>
    <div class="resumen-boletin-home">
        <?php echo $resumenHTML; ?>
    </div>
<?php 
    endif;
endif; 
?>
                    <div class="row mt-sm-4 mt-2 px-3">
                        <div class="col-6 p-0">
                            <span>N° <?php echo htmlspecialchars($boletin['numero_boletin'] ?? '45'); ?></span>
                            <h4><?php echo $boletin ? formatearFechaCorta($boletin['fecha_publicacion']) : '28 agosto'; ?></h4>
                        </div>
                        <div class="col-6 p-0">
                            <?php if ($boletin && !empty($boletin['archivo_pdf'])): ?>
                            <span><a target="_blank" href="<?php echo htmlspecialchars(urlPdf($boletin['archivo_pdf'])); ?>" class="facebook"><span class="fa fa-download"></span></a></span>
                            <?php endif; ?>
                            <h4><a href="boletines.php">Ver Boletin</a></h4>
                        </div>
                        <center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                    </div>
                </div>
                <div class="col-lg-4 mt-lg-0 mt-4">
                    <?php if ($boletin && !empty($boletin['foto_portada']) && imagenExiste($boletin['foto_portada'])): ?>
                    <a target="_blank" href="<?php echo htmlspecialchars(urlPdf($boletin['archivo_pdf'])); ?>" class="d-block">
                        <img src="<?php echo htmlspecialchars(urlImagen($boletin['foto_portada'])); ?>" class="img-fluid radius-image boletin-imagen" alt="Boletín NTEP">
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- PODCAST -->
    <section class="w3l-homeblock3 py-5">
        <div class="container py-lg-5 py-md-4">
            <h3 class="title-big mb-5 text-center">Podcast</h3>
            <div class="row">
                <?php foreach ($podcasts as $podcast): ?>
                <div class="col-lg-3 col-sm-6">
                    <div class="area-box">
                        <img src="assets/images/podcast.png">
                        <p><?php echo htmlspecialchars($podcast['título'] ?? ''); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <center><a href="podcast.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
        </div>
    </section>

    <!-- ALIANZAS -->
    <section class="w3l-logos w3l-homeblock3 py-5">
        <div class="container py-lg-3">
            <h5 class="title-small mb-1 text-center">DyD Perú</h5>
            <h3 class="title-big mb-md-5 mb-4 text-center">Alianzas</h3>
            <div class="row">
                <?php foreach ($alianzas as $alianza): ?>
                <div class="col-lg-2 col-md-4 col-6 mb-4 text-center alianza-item">
                    <?php if (!empty($alianza['logo']) && imagenExiste($alianza['logo'])): ?>
                    <img src="<?php echo htmlspecialchars(urlImagen($alianza['logo'])); ?>" alt="<?php echo htmlspecialchars($alianza['nombre_organizacion']); ?>" class="img-fluid">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ESPECIALES -->
    <section class="w3l-team" id="team">
        <div class="teams1 py-5 mb-3">
            <div class="container py-lg-3 pb-lg-5 pb-4">
                <div class="teams1-content">
                    <h3 class="title-big text-center mb-5">Especiales</h3>
                    <div class="row">
                        <?php foreach ($especiales as $especial): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="d-grid team-info">
                                <div class="column position-relative">
                                    <?php if (!empty($especial['imagen_portada']) && imagenExiste($especial['imagen_portada'])): ?>
                                    <a href="<?php echo htmlspecialchars($especial['url_destino']); ?>">
                                        <img src="<?php echo htmlspecialchars(urlImagen($especial['imagen_portada'])); ?>" alt="" class="img-fluid rounded team-image" />
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <div class="column">
                                    <p><?php echo htmlspecialchars($especial['título'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NOSOTROS -->
    <section class="w3l-banner py-0" id="work">
        <div class="midd-w3 py-lg-4 py-md-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mt-lg-0 mt-lg-5 about-right-faq align-self">
                        <h5 class="title-small mb-2">DDP Noticias</h5>
                        <h3 class="title-banner">Diálogo y Desarrollo Perú</h3>
                        <p class="mt-4">Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                        <a href="sobre-nosotros.php" class="btn btn-style btn-primary mt-md-5 mt-4">Nosotros</a>
                    </div>
                    <div class="col-md-6 left-wthree-img mt-lg-0 mt-4">
                        <img src="assets/images/bannerimg.jpg" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- REDES SOCIALES -->
    <div class="middle py-5">
        <div class="container py-xl-5 py-lg-3">
            <div class="welcome-left text-center py-md-5 py-3">
                <h3 class="title-big">Síguenos en nuestras Redes Sociales</h3>
                <div class="main-social-footer-29">
                    <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square fa-2x"></span></a>
                    <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokg.png" style="height:30px;"></a>
                    <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram fa-2x"></span></a>
                </div>
            </div>
        </div>
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