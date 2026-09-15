<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $link_externo = trim($_POST['link_externo'] ?? '');
    $fecha = trim($_POST['fecha_publicacion'] ?? '');
    $foto = $noticia['foto'];

    // Eliminar imagen
    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if (!empty($noticia['foto']) && file_exists('../' . $noticia['foto'])) {
            unlink('../' . $noticia['foto']);
        }
        $foto = '';
    }

    // Subir nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowed)) {
            if (!empty($noticia['foto']) && file_exists('../' . $noticia['foto'])) {
                unlink('../' . $noticia['foto']);
            }
            
            $nombreArchivo = $file['name'];
            $uploadDir = '../assets/images/';
            $rutaCompleta = $uploadDir . $nombreArchivo;
            
            if (file_exists($rutaCompleta)) {
                $info = pathinfo($nombreArchivo);
                $nombreArchivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                $rutaCompleta = $uploadDir . $nombreArchivo;
            }
            
            if (move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
                $foto = 'assets/images/' . $nombreArchivo;
            } else {
                $error = 'Error al subir la imagen.';
            }
        } else {
            $error = 'Formato no permitido.';
        }
    }

    if (empty($titulo)) { $error = 'El título es obligatorio.'; }
    elseif (empty($link_externo)) { $error = 'El link externo es obligatorio.'; }
    elseif (empty($fecha)) { $error = 'La fecha es obligatoria.'; }
    else {
        try {
            $stmt = $pdo->prepare("UPDATE noticias SET 
                                   titulo = ?, foto = ?, link_externo = ?, fecha_publicacion = ? 
                                   WHERE id = ?");
            $stmt->execute([$titulo, $foto, $link_externo, $fecha, $id]);
            
            header('Location: index.php?mensaje=actualizado');
            exit;
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Noticia · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reutiliza el mismo CSS del crear.php - solo para brevedad, copia el mismo bloque */
        :root { --ddp-red: #c0392b; --ddp-red-dark: #a93226; --ddp-red-light: #fde8e6; --ddp-white: #fff; --ddp-bg: #f8f9fa; --ddp-text: #2c3e50; --ddp-text-secondary: #7f8c8d; --ddp-border: #ecf0f1; --ddp-border-dark: #d5d8dc; --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06); --ddp-danger: #e74c3c; --sidebar-width: 270px; --header-height: 70px; --ddp-radius: 10px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cabin', sans-serif; background: var(--ddp-bg); color: var(--ddp-text); line-height: 1.6; }
        a { text-decoration: none; color: inherit; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh; background: var(--ddp-white); border-right: 1px solid var(--ddp-border); padding: 1.5rem 0; overflow-y: auto; z-index: 1000; transition: transform 0.3s ease; }
        .sidebar-brand { display: flex; align-items: center; gap: 0.75rem; padding: 0 1.5rem 1.5rem; border-bottom: 2px solid var(--ddp-red); }
        .sidebar-brand img { height: 45px; }
        .sidebar-brand span { font-size: 1.1rem; font-weight: 700; color: var(--ddp-red); }
        .sidebar-nav { list-style: none; padding: 1rem 0; }
        .sidebar-nav .nav-section { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ddp-text-secondary); padding: 1rem 1.5rem 0.4rem; font-weight: 600; }
        .sidebar-nav li a { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 1.5rem; color: var(--ddp-text-secondary); transition: all 0.2s; font-size: 0.9rem; border-left: 3px solid transparent; }
        .sidebar-nav li a:hover, .sidebar-nav li a.active { background: var(--ddp-red-light); color: var(--ddp-red); }
        .sidebar-nav li a.active { border-left-color: var(--ddp-red); font-weight: 600; }
        .sidebar-nav li a i { width: 20px; text-align: center; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar { position: sticky; top: 0; z-index: 900; background: var(--ddp-white); border-bottom: 1px solid var(--ddp-border); padding: 0 2rem; height: var(--header-height); display: flex; align-items: center; justify-content: space-between; }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar-left .menu-toggle { display: none; background: none; border: none; font-size: 1.25rem; cursor: pointer; }
        .topbar-left .breadcrumb { font-size: 0.85rem; color: var(--ddp-text-secondary); }
        .topbar-left .breadcrumb span { color: var(--ddp-red); font-weight: 600; }
        .topbar-right .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--ddp-red); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; }
        .content-area { padding: 2rem; }
        .hero { background: var(--ddp-white); border-radius: var(--ddp-radius); padding: 1.75rem 2.5rem; border: 1px solid var(--ddp-border); display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; box-shadow: var(--ddp-shadow); }
        .hero h1 { font-size: 1.5rem; font-weight: 700; }
        .hero h1 span { color: var(--ddp-red); }
        .hero p { color: var(--ddp-text-secondary); }
        .btn { padding: 0.5rem 1.25rem; border: none; border-radius: 0.5rem; font-family: inherit; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; transition: all 0.2s; }
        .btn-red { background: var(--ddp-red); color: white; }
        .btn-red:hover { background: var(--ddp-red-dark); transform: translateY(-2px); }
        .btn-outline { background: transparent; color: var(--ddp-text); border: 1px solid var(--ddp-border-dark); }
        .form-card { background: var(--ddp-white); border-radius: var(--ddp-radius); padding: 2rem; border: 1px solid var(--ddp-border); box-shadow: var(--ddp-shadow); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 0.3rem; }
        .form-group label .required { color: var(--ddp-red); }
        .form-group .help-text { font-size: 0.75rem; color: var(--ddp-text-secondary); margin-top: 0.2rem; }
        .form-control { width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--ddp-border); border-radius: 0.5rem; font-family: inherit; font-size: 0.9rem; }
        .form-control:focus { outline: none; border-color: var(--ddp-red); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-actions { display: flex; gap: 1rem; margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--ddp-border); }
        .file-upload-wrapper { position: relative; border: 2px dashed var(--ddp-border); border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; background: var(--ddp-bg); }
        .file-upload-wrapper:hover { border-color: var(--ddp-red); background: var(--ddp-red-light); }
        .file-upload-wrapper .icon { font-size: 3rem; color: var(--ddp-text-secondary); display: block; margin-bottom: 0.5rem; }
        .file-upload-wrapper p { color: var(--ddp-text-secondary); font-size: 0.9rem; }
        .file-upload-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .file-upload-wrapper .file-name { margin-top: 0.5rem; font-size: 0.8rem; color: #27ae60; font-weight: 500; }
        .current-file { display: flex; align-items: center; gap: 1rem; padding: 0.5rem; background: var(--ddp-bg); border-radius: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap; }
        .current-file .info { font-size: 0.8rem; color: var(--ddp-text-secondary); }
        .current-file .btn-eliminar { background: #e74c3c; color: white; border: none; padding: 0.2rem 0.8rem; border-radius: 0.3rem; font-size: 0.7rem; cursor: pointer; }
        .current-file .btn-eliminar:hover { background: #c0392b; }
        .alert { padding: 1rem 1.5rem; border-radius: var(--ddp-radius); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .alert-danger { background: #fde8e6; color: var(--ddp-danger); border: 1px solid #f5c6cb; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="DDP Noticias">
            <span>DDP Noticias</span>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-section">Principal</li>
            <li><a href="../index.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li class="nav-section">Contenido</li>
            <li><a href="../reportajes/index.php"><i class="fas fa-newspaper"></i> Reportajes</a></li>
            <li><a href="index.php" class="active"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="#"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="#"><i class="fas fa-podcast"></i> Podcasts</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Videos</a></li>
            <li><a href="#"><i class="fas fa-star"></i> Especiales</a></li>
            <li class="nav-section">Usuarios</li>
            <li><a href="../usuarios/index.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="#"><i class="fas fa-user-edit"></i> Autores</a></li>
            <li class="nav-section">Configuración</li>
            <li><a href="#"><i class="fas fa-link"></i> Fuentes</a></li>
            <li><a href="#"><i class="fas fa-handshake"></i> Alianzas</a></li>
            <li><a href="#"><i class="fas fa-share-alt"></i> Redes Sociales</a></li>
            <li class="nav-section">Contacto</li>
            <li><a href="#"><i class="fas fa-envelope"></i> Mensajes</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> / <a href="index.php">Noticias</a> / <span>Editar</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'A', 0, 1)); ?></div>
            </div>
        </header>

        <div class="content-area">
            <section class="hero">
                <div>
                    <h1>Editar <span>Noticia</span></h1>
                    <p>Editando: <strong><?php echo htmlspecialchars($noticia['titulo']); ?></strong></p>
                </div>
                <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
            </section>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título <span class="required">*</span></label>
                        <input type="text" id="titulo" name="titulo" class="form-control" 
                               value="<?php echo htmlspecialchars($noticia['titulo']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="link_externo">Link externo <span class="required">*</span></label>
                            <input type="url" id="link_externo" name="link_externo" class="form-control" 
                                   value="<?php echo htmlspecialchars($noticia['link_externo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_publicacion">Fecha de publicación <span class="required">*</span></label>
                            <input type="date" id="fecha_publicacion" name="fecha_publicacion" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($noticia['fecha_publicacion']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Imagen de la noticia</label>
                        
                        <?php if (!empty($noticia['foto'])): ?>
                        <div class="current-file" id="imagenContainer">
                            <img src="../<?php echo htmlspecialchars($noticia['foto']); ?>" 
                                 style="max-height:60px;border-radius:4px;border:1px solid var(--ddp-border);"
                                 onerror="this.style.display='none'">
                            <div class="info">
                                <strong>Imagen actual:</strong><br>
                                <?php echo htmlspecialchars(basename($noticia['foto'])); ?>
                            </div>
                            <button type="button" class="btn-eliminar" onclick="eliminarImagen()">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="file-upload-wrapper">
                            <span class="icon"><i class="fas fa-cloud-upload-alt"></i></span>
                            <p>Haz clic para seleccionar una nueva imagen<br><small>JPG, PNG, GIF, WEBP</small></p>
                            <input type="file" name="imagen" accept="image/*" id="imagen">
                            <div class="file-name" id="fileName"></div>
                        </div>
                        <input type="hidden" id="eliminar_imagen" name="eliminar_imagen" value="0">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-red">
                            <i class="fas fa-save"></i> Actualizar Noticia
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <a href="eliminar.php?id=<?php echo $noticia['id']; ?>" 
                           class="btn btn-outline" style="margin-left:auto;color:var(--ddp-danger);border-color:var(--ddp-danger);"
                           onclick="return confirm('¿Estás seguro de eliminar esta noticia?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('imagen').addEventListener('change', function() {
            const fileName = document.getElementById('fileName');
            fileName.textContent = this.files[0] ? '📎 ' + this.files[0].name : '';
        });
        function eliminarImagen() {
            if (confirm('¿Eliminar la imagen actual?')) {
                document.getElementById('eliminar_imagen').value = '1';
                var container = document.getElementById('imagenContainer');
                if (container) container.style.display = 'none';
            }
        }
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
    </script>
</body>
</html>