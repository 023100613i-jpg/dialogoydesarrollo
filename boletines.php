<?php
require_once 'config/conexion.php';

// Configurar paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 9;
$offset = ($pagina - 1) * $porPagina;

// Contar total de boletines
$totalBoletines = $pdo->query("SELECT COUNT(*) FROM boletines")->fetchColumn();
$totalPaginas = ceil($totalBoletines / $porPagina);

// Obtener boletines con paginación
$sql = "SELECT * FROM boletines 
        ORDER BY fecha_publicacion DESC 
        LIMIT " . (int)$porPagina . " OFFSET " . (int)$offset;

$stmt = $pdo->query($sql);
$boletines = $stmt->fetchAll();





?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletines NTEP - DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        .grids5-info { margin-bottom: 1.5rem; }
        .grids5-info .img-fluid {
            .grids5-info .img-fluid {
    width: 100%;
    height: auto;
    max-height: none;
    object-fit: contain;
    border-radius: 8px;
    background: #f8f9fa;
    display: block;
}
        }
        .blog-info { padding: 1rem 0; }
        .blog-info h5 {
            color: #c0392b; font-size: 0.8rem; font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .blog-info h4 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    color: #7f8c8d;
    font-weight: 500;
}
        
        .blog-info h4 a { color: #2c3e50; text-decoration: none; }
        .blog-info h4 a:hover { color: #c0392b; }
    .btn-ver {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #c0392b;
    font-weight: 700;
    padding: 0;
    font-size: 1.15rem;
    background: transparent;
    border: none;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 0.6rem;
}
.btn-ver:hover {
    color: #c72517;
    gap: 0.8rem;
}
.btn-ver i {
    font-size: 1rem;
    transition: transform 0.3s;
}
.btn-ver:hover i {
    transform: translateX(4px);
}
        .no-image {
            background: #f8f9fa; display: flex; align-items: center;
            justify-content: center; color: #7f8c8d; font-size: 0.9rem;
            width: 100%; height: 220px; border-radius: 8px;
            border: 1px solid #ecf0f1;
        }
        .no-image i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
        .pagination { margin-top: 2rem; text-align: center; }
        .pagination ul {
            list-style: none; padding: 0; margin: 0;
            display: inline-flex; gap: 0.5rem; flex-wrap: wrap;
            justify-content: center;
        }
        .pagination ul li { display: inline-block; }
        .pagination ul li a {
            display: inline-block; padding: 0.5rem 1rem;
            background: #f8f9fa; color: #2c3e50; border-radius: 4px;
            text-decoration: none; font-weight: 500;
            transition: all 0.3s; border: 1px solid #ecf0f1;
        }
        .pagination ul li a:hover {
            background: #c0392b; color: white; border-color: #c0392b;
        }
        .pagination ul li a.active {
            background: #c0392b; color: white; border-color: #c0392b;
        }
        .pagination ul li.prev a,
        .pagination ul li.next a {
            background: transparent; border: 1px solid #ddd;
        }
        .pagination ul li.prev a:hover,
        .pagination ul li.next a:hover {
            background: #c0392b; color: white; border-color: #c0392b;
        }
        .badge-boletin {
            background: #c0392b; color: white; padding: 0.1rem 0.5rem;
            border-radius: 999px; font-size: 0.6rem; font-weight: 600;
            text-transform: uppercase; display: inline-block;
            margin-left: 0.5rem;
        }
        .resumen-boletin {
            color: #555; font-size: 0.9rem; margin-top: 0.5rem;
            line-height: 1.6;
            display: -webkit-box; -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .resumen-boletin p { margin-bottom: 0.3rem; }
        .resumen-boletin img { max-width: 100%; border-radius: 4px; }
    @media (max-width: 768px) {
    .no-image { height: 180px; }
}
@media (max-width: 480px) {
    .no-image { height: 150px; }
    .blog-info h4 { font-size: 0.95rem; }
    .btn-ver { font-size: 1.05rem; }
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
                        <li class="nav-item"><a class="nav-link" href="index.php#actualidad">Actualidad</a></li>
                        <li class="nav-item"><a class="nav-link" href="reportajes.php">Reportajes</a></li>
                        <li class="nav-item"><a class="nav-link" href="podcast.php">Podcast</a></li>
                        <li class="nav-item active"><a class="nav-link" href="boletines.php">Boletín NTEP</a></li>
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
                        <h2 class="title-big">Boletines NTEP</h2>
                        <div class="breadcrumb">
                            <ul>
                                <li><a href="index.php">Inicio</a></li>
                                <li class="active">Boletines</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LISTA DE BOLETINES -->
    <div class="grids-block-5 py-5">
        <section class="py-lg-4 py-md-3">
            <div class="container">
                <div class="row">
                    <?php if (count($boletines) > 0): ?>
                        <?php foreach ($boletines as $boletin): ?>
                        <div class="col-lg-4 col-md-6 grids5-info">
                            <?php 
                            $rutaImagen = $boletin['foto_portada'] ?? '';
                            $rutaPdf = $boletin['archivo_pdf'] ?? '';
                            if (!empty($rutaImagen) && imagenExiste($rutaImagen)) {
                                echo '<a target="_blank" href="' . htmlspecialchars(urlPdf($rutaPdf)) . '" class="d-block">';
                                echo '<img src="' . htmlspecialchars(urlImagen($rutaImagen)) . '" alt="' . htmlspecialchars($boletin['titulo'] ?? 'Boletín') . '" class="img-fluid">';
                                echo '</a>';
                            } else {
                                echo '<div class="no-image"><div><i class="fas fa-file-pdf"></i><br>Sin imagen</div></div>';
                            }
                            ?>
                            <div class="blog-info">
                                <h4>
                                    <?php echo date('M d, Y', strtotime($boletin['fecha_publicacion'])); ?>
                                    
                                </h4>
                                <a target="_blank" href="<?php echo htmlspecialchars(urlPdf($rutaPdf)); ?>" class="btn-ver">
                                    Ver boletín <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p style="color:#7f8c8d;font-size:1.2rem;">No hay boletines disponibles por el momento.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PAGINACIÓN -->
                <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <ul>
                        <?php if ($pagina > 1): ?>
                        <li class="prev"><a href="?pagina=<?php echo $pagina - 1; ?>">‹ Anterior</a></li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li><a href="?pagina=<?php echo $i; ?>" <?php echo ($i == $pagina) ? 'class="active"' : ''; ?>><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        
                        <?php if ($pagina < $totalPaginas): ?>
                        <li class="next"><a href="?pagina=<?php echo $pagina + 1; ?>">Siguiente ›</a></li>
                        <?php endif; ?>
                    </ul>
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