<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ?");
$stmt->execute([$id]);
$podcast = $stmt->fetch();

if (!$podcast) {
    header('Location: index.php');
    exit;
}

$error = '';

$usuarioActual = usuarioActual($pdo);

$nombreCompleto = '';
$iniciales = '';

if ($usuarioActual) {
    $nombreCompleto = trim(
        ($usuarioActual['nombres'] ?? '') . ' ' . 
        ($usuarioActual['ap_paterno'] ?? '') . ' ' . 
        ($usuarioActual['ap_materno'] ?? '')
    );
    if (empty($nombreCompleto)) {
        $nombreCompleto = $_SESSION['usuario_nombre'] ?? 'Usuario';
    }
    $partes = explode(' ', trim($nombreCompleto));
    $iniciales = '';
    foreach ($partes as $parte) {
        if (!empty($parte)) $iniciales .= strtoupper(substr($parte, 0, 1));
    }
    if (strlen($iniciales) < 2) {
        $iniciales = strtoupper(substr($nombreCompleto, 0, 2));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $url_embed = trim($_POST['url_embed'] ?? '');
    $duracion = trim($_POST['duracion'] ?? '');
    $fecha = trim($_POST['fecha_publicacion'] ?? '');
    $estado = $_POST['estado'] ?? $podcast['estado'] ?? 'publicado';

    if (!in_array($estado, ['publicado', 'borrador', 'archivado'])) {
        $estado = 'publicado';
    }

    $imagen = $podcast['imagen'];

    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if (!empty($podcast['imagen']) && file_exists('../' . $podcast['imagen'])) {
            unlink('../' . $podcast['imagen']);
        }
        $imagen = '';
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowed)) {
            if (!empty($podcast['imagen']) && file_exists('../' . $podcast['imagen'])) {
                unlink('../' . $podcast['imagen']);
            }
            
            $nombreArchivo = $file['name'];
            $uploadDir = '../assets/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $rutaCompleta = $uploadDir . $nombreArchivo;
            if (file_exists($rutaCompleta)) {
                $info = pathinfo($nombreArchivo);
                $nombreArchivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                $rutaCompleta = $uploadDir . $nombreArchivo;
            }
            
            if (move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
                $imagen = 'assets/images/' . $nombreArchivo;
            }
        }
    }

    if (empty($titulo)) { $error = 'El título es obligatorio.'; }
    elseif (empty($url_embed)) { $error = 'La URL del embed es obligatoria.'; }
    elseif (empty($fecha)) { $error = 'La fecha es obligatoria.'; }
    else {
        try {
            $stmt = $pdo->prepare("UPDATE podcasts SET 
                titulo = ?, descripcion = ?, imagen = ?, 
                url_embed = ?, duracion = ?, fecha_publicacion = ?, estado = ?
                WHERE id = ?");
            $stmt->execute([$titulo, $descripcion, $imagen, $url_embed, $duracion, $fecha, $estado, $id]);
            
            header('Location: index.php?mensaje=actualizado');
            exit;
        } catch(PDOException $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Podcast · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ddp-red: #c0392b;
            --ddp-red-dark: #a93226;
            --ddp-red-light: #fde8e6;
            --ddp-white: #ffffff;
            --ddp-bg: #f8f9fa;
            --ddp-text: #2c3e50;
            --ddp-text-secondary: #7f8c8d;
            --ddp-border: #ecf0f1;
            --ddp-border-dark: #d5d8dc;
            --ddp-success: #27ae60;
            --ddp-danger: #e74c3c;
            --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --sidebar-width: 270px;
            --header-height: 70px;
            --ddp-radius: 10px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cabin', sans-serif; background: var(--ddp-bg); color: var(--ddp-text); line-height: 1.6; }
        a { text-decoration: none; color: inherit; }
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width); height: 100vh;
            background: var(--ddp-white);
            border-right: 1px solid var(--ddp-border);
            padding: 1.5rem 0; overflow-y: auto;
            z-index: 1000; transition: transform 0.3s ease;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0 1.5rem 1.5rem; border-bottom: 2px solid var(--ddp-red);
        }
        .sidebar-brand img { height: 45px; width: auto; }
        .sidebar-brand span { font-size: 1.1rem; font-weight: 700; color: var(--ddp-red); }
        .sidebar-nav { list-style: none; padding: 1rem 0; }
        .sidebar-nav .nav-section {
            font-size: 0.65rem; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--ddp-text-secondary);
            padding: 1rem 1.5rem 0.4rem; font-weight: 600;
        }
        .sidebar-nav li a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1.5rem; color: var(--ddp-text-secondary);
            transition: all 0.2s; font-size: 0.9rem;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a:hover,
        .sidebar-nav li a.active { background: var(--ddp-red-light); color: var(--ddp-red); }
        .sidebar-nav li a.active { border-left-color: var(--ddp-red); font-weight: 600; }
        .sidebar-nav li a i { width: 20px; text-align: center; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            position: sticky; top: 0; z-index: 900;
            background: var(--ddp-white); border-bottom: 1px solid var(--ddp-border);
            padding: 0 2rem; height: var(--header-height);
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar-left .menu-toggle {
            display: none; background: none; border: none;
            font-size: 1.25rem; color: var(--ddp-text);
            cursor: pointer; padding: 0.25rem 0.5rem;
        }
        .topbar-left .breadcrumb { font-size: 0.85rem; color: var(--ddp-text-secondary); }
        .topbar-left .breadcrumb span { color: var(--ddp-red); font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: 1.25rem; }
        .topbar-right .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--ddp-red); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.85rem;
        }
        .content-area { padding: 2rem; max-width: 100%; }
        .hero {
            background: var(--ddp-white); border-radius: var(--ddp-radius);
            padding: 1.75rem 2.5rem; border: 1px solid var(--ddp-border);
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem; box-shadow: var(--ddp-shadow);
        }
        .hero h1 { font-size: 1.5rem; font-weight: 700; color: var(--ddp-text); }
        .hero h1 span { color: var(--ddp-red); }
        .hero p { color: var(--ddp-text-secondary); font-size: 0.95rem; }
        .btn {
            padding: 0.5rem 1.25rem; border: none; border-radius: 0.5rem;
            font-family: inherit; font-weight: 500; cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem; transition: all 0.2s;
        }
        .btn-red { background: var(--ddp-red); color: white; }
        .btn-red:hover { background: var(--ddp-red-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(192,57,43,0.3); }
        .btn-outline { background: transparent; color: var(--ddp-text); border: 1px solid var(--ddp-border-dark); }
        .btn-outline:hover { background: var(--ddp-bg); }
        .form-card {
            background: var(--ddp-white); border-radius: var(--ddp-radius);
            padding: 2rem; border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 0.3rem; color: var(--ddp-text); }
        .form-group label .required { color: var(--ddp-red); }
        .form-group .help-text { font-size: 0.75rem; color: var(--ddp-text-secondary); margin-top: 0.2rem; }
        .form-control {
            width: 100%; padding: 0.6rem 1rem;
            border: 1px solid var(--ddp-border); border-radius: 0.5rem;
            font-family: inherit; font-size: 0.9rem;
            background: var(--ddp-white); color: var(--ddp-text);
            transition: border-color 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--ddp-red); }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-actions {
            display: flex; gap: 1rem; margin-top: 1rem;
            padding-top: 1.5rem; border-top: 1px solid var(--ddp-border);
        }
        .file-upload-wrapper {
            position: relative; border: 2px dashed var(--ddp-border);
            border-radius: 0.5rem; padding: 2rem; text-align: center;
            cursor: pointer; transition: all 0.3s; background: var(--ddp-bg);
        }
        .file-upload-wrapper:hover { border-color: var(--ddp-red); background: var(--ddp-red-light); }
        .file-upload-wrapper .icon {
            font-size: 3rem; color: var(--ddp-text-secondary);
            display: block; margin-bottom: 0.5rem;
        }
        .file-upload-wrapper p { color: var(--ddp-text-secondary); font-size: 0.9rem; }
        .file-upload-wrapper input[type="file"] {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; opacity: 0; cursor: pointer;
        }
        .file-upload-wrapper .file-name {
            margin-top: 0.5rem; font-size: 0.8rem;
            color: var(--ddp-success); font-weight: 500;
        }
        .current-file {
            display: flex; align-items: center; gap: 1rem;
            padding: 0.5rem; background: var(--ddp-bg);
            border-radius: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap;
        }
        .current-file img { max-height: 60px; border-radius: 4px; }
        .current-file .info { font-size: 0.8rem; color: var(--ddp-text-secondary); }
        .current-file .btn-eliminar {
            background: #e74c3c; color: white; border: none;
            padding: 0.2rem 0.8rem; border-radius: 0.3rem;
            font-size: 0.7rem; cursor: pointer;
        }
        .alert {
            padding: 1rem 1.5rem; border-radius: var(--ddp-radius);
            margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;
        }
        .alert-danger { background: #fde8e6; color: var(--ddp-danger); border: 1px solid #f5c6cb; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .content-area { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .topbar-left .breadcrumb { display: none; }
            .form-actions { flex-direction: column; }
            .form-actions .btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="DDP Noticias" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2245%22 height=%2245%22%3E%3Crect width=%2245%22 height=%2245%22 fill=%22%23c0392b%22/%3E%3Ctext x=%228%22 y=%2232%22 font-size=%2228%22 fill=%22white%22%3ED%3C/text%3E%3C/svg%3E'">
            <span>DDP Noticias</span>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-section">Principal</li>
            <li><a href="../index.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li class="nav-section">Contenido</li>
            <li><a href="../reportajes/index.php"><i class="fas fa-newspaper"></i> Reportajes</a></li>
            <li><a href="../noticias/index.php"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="../boletines/index.php"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="index.php" class="active"><i class="fas fa-podcast"></i> Podcasts</a></li>
            <li><a href="../videos/index.php"><i class="fas fa-video"></i> Videos</a></li>
            <li><a href="../especiales/index.php"><i class="fas fa-star"></i> Especiales</a></li>
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
                    <i class="fas fa-home"></i> / <a href="index.php">Podcasts</a> / <span>Editar</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo $iniciales ?: 'U'; ?></div>
            </div>
        </header>

        <div class="content-area">
            <section class="hero">
                <div>
                    <h1>Editar <span>Podcast</span></h1>
                    <p>Editando: <strong><?php echo htmlspecialchars($podcast['titulo']); ?></strong></p>
                </div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
            </section>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="" enctype="multipart/form-data">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título <span class="required">*</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control" 
                                   value="<?php echo htmlspecialchars($podcast['titulo'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_publicacion">Fecha de publicación <span class="required">*</span></label>
                            <input type="date" id="fecha_publicacion" name="fecha_publicacion" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($podcast['fecha_publicacion'] ?? date('Y-m-d')); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control"><?php echo htmlspecialchars($podcast['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="url_embed">URL del Embed (Spotify/YouTube) <span class="required">*</span></label>
                            <input type="url" id="url_embed" name="url_embed" class="form-control" 
                                   value="<?php echo htmlspecialchars($podcast['url_embed'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="duracion">Duración</label>
                            <input type="text" id="duracion" name="duracion" class="form-control" 
                                   value="<?php echo htmlspecialchars($podcast['duracion'] ?? ''); ?>" 
                                   placeholder="Ej: 15:30">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Imagen de portada</label>
                        
                        <?php if (!empty($podcast['imagen']) && file_exists('../' . $podcast['imagen'])): ?>
                        <div class="current-file" id="imagenContainer">
                            <img src="../<?php echo htmlspecialchars($podcast['imagen']); ?>" alt="">
                            <div class="info">
                                <strong>Imagen actual:</strong><br>
                                <?php echo htmlspecialchars(basename($podcast['imagen'])); ?>
                            </div>
                            <button type="button" class="btn-eliminar" onclick="eliminarImagen()">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="file-upload-wrapper">
                            <span class="icon"><i class="fas fa-cloud-upload-alt"></i></span>
                            <p>Haz clic para seleccionar una nueva imagen<br><small>Formatos: JPG, PNG, GIF, WEBP</small></p>
                            <input type="file" name="imagen" accept="image/*" id="imagen">
                            <div class="file-name" id="fileName"></div>
                        </div>
                        <input type="hidden" id="eliminar_imagen" name="eliminar_imagen" value="0">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="actualizar" value="publicado" class="btn btn-red">
                            <i class="fas fa-check"></i> Publicar
                        </button>
                        <button type="submit" name="actualizar" value="borrador" class="btn btn-outline">
                            <i class="fas fa-save"></i> Guardar como Borrador
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <a href="eliminar.php?id=<?php echo $podcast['id']; ?>" 
                           class="btn btn-outline" 
                           style="margin-left:auto;color:var(--ddp-danger);border-color:var(--ddp-danger);"
                           onclick="return confirm('¿Estás seguro de eliminar este podcast?')">
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
            fileName.style.color = '#27ae60';
        });

        function eliminarImagen() {
            if (confirm('¿Estás seguro de eliminar la imagen actual?')) {
                document.getElementById('eliminar_imagen').value = '1';
                var container = document.getElementById('imagenContainer');
                if (container) container.style.display = 'none';
            }
        }

        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('menuToggle');
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>