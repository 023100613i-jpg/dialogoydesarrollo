<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$error = '';

$autores = obtenerAutores($pdo);
$usuarioActual = usuarioActual($pdo);

$nombreUsuario = '';
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
    $nombreUsuario = $nombreCompleto;
    
    $partes = explode(' ', trim($nombreCompleto));
    $iniciales = '';
    foreach ($partes as $parte) {
        if (!empty($parte)) {
            $iniciales .= strtoupper(substr($parte, 0, 1));
        }
    }
    if (strlen($iniciales) < 2) {
        $iniciales = strtoupper(substr($nombreCompleto, 0, 2));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = sanitizar($_POST['titulo'] ?? '');
    $resumen = sanitizar($_POST['resumen_corto'] ?? '');
    $desarrollo = trim($_POST['desarrollo'] ?? '');
    $foto = sanitizar($_POST['foto_principal'] ?? '');
    $url_infografia = trim($_POST['url_infografia'] ?? '');
    $fecha = sanitizar($_POST['fecha_publicacion'] ?? '');
    $destacado = isset($_POST['es_destacado']) ? 1 : 0;
    $estado = $_POST['estado'] ?? 'publicado';
    $autor_id = !empty($_POST['autor_id']) ? (int)$_POST['autor_id'] : 1;
    $usuario_id = $_SESSION['usuario_id'];

    // Subida imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowed)) {
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
                $foto = 'assets/images/' . $nombreArchivo;
            } else {
                $error = 'Error al subir la imagen.';
            }
        } else {
            $error = 'Formato de imagen no permitido.';
        }
    }

    if (empty($titulo)) { $error = 'El título es obligatorio.'; }
    elseif (empty($resumen)) { $error = 'El resumen es obligatorio.'; }
    elseif (empty(trim(strip_tags($desarrollo)))) { $error = 'El desarrollo es obligatorio.'; }
    elseif (empty($fecha)) { $error = 'La fecha de publicación es obligatoria.'; }
    else {
        $datos = [
            'titulo' => $titulo,
            'resumen_corto' => $resumen,
            'desarrollo' => $desarrollo,
            'foto_principal' => $foto,
            'pdf_adjunto' => '',
            'fecha_publicacion' => $fecha,
            'es_destacado' => $destacado,
            'autor_id' => $autor_id,
            'usuario_id' => $usuario_id,
            'estado' => $estado
        ];

        if (crearReportaje($pdo, $datos)) {
            $reportaje_id = $pdo->lastInsertId();
            
            if (!empty($url_infografia)) {
                $stmt = $pdo->prepare("INSERT INTO reportajes_fotos (reportaje_id, url_foto, orden, descripcion) 
                                       VALUES (?, ?, 0, 'Infografía')");
                $stmt->execute([$reportaje_id, $url_infografia]);
            }
            
            header('Location: index.php?mensaje=creado&tipo=success');
            exit;
        } else {
            $error = 'Error al crear el reportaje.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Reportaje · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
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
            --ddp-warning: #f39c12;
            --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --sidebar-width: 270px;
            --header-height: 70px;
            --ddp-radius: 10px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cabin', sans-serif;
            background: var(--ddp-bg);
            color: var(--ddp-text);
            line-height: 1.6;
        }
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
        .sidebar-nav li a.active {
            background: var(--ddp-red-light); color: var(--ddp-red);
        }
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
        .topbar-right { display: flex; align-items: center; gap: 1.25rem; }
.topbar-right .btn-icon {
    background: none; border: none; font-size: 1.1rem;
    color: var(--ddp-text-secondary); cursor: pointer;
    padding: 0.25rem; transition: color 0.2s;
}
.topbar-right .btn-icon:hover { color: var(--ddp-red); }
.topbar-right .user-menu {
    display: flex; align-items: center; gap: 0.75rem;
    cursor: pointer; padding: 0.3rem 0.75rem 0.3rem 0.3rem;
    border-radius: 2rem; transition: background 0.2s;
    position: relative; border: 1px solid transparent;
}
.topbar-right .user-menu:hover {
    background: var(--ddp-red-light);
    border-color: var(--ddp-red-light);
}
.topbar-right .user-menu .user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--ddp-red); color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.85rem; flex-shrink: 0;
}
.topbar-right .user-menu .user-info {
    font-size: 0.85rem; line-height: 1.2;
}
.topbar-right .user-menu .user-info .name {
    font-weight: 600; color: var(--ddp-text); font-size: 0.9rem;
}
.topbar-right .user-menu .user-info .role {
    font-size: 0.65rem; color: var(--ddp-text-secondary);
    text-transform: uppercase; letter-spacing: 0.04em;
}
.topbar-right .user-menu .dropdown-arrow {
    font-size: 0.6rem; color: var(--ddp-text-secondary);
    margin-left: 0.25rem;
}
.topbar-right .user-dropdown {
    display: none; position: absolute;
    top: calc(100% + 0.5rem); right: 0;
    background: var(--ddp-white); border: 1px solid var(--ddp-border);
    border-radius: var(--ddp-radius);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    min-width: 200px; padding: 0.5rem 0; z-index: 100;
}
.topbar-right .user-dropdown.show { display: block; }
.topbar-right .user-dropdown a {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.5rem 1.25rem; color: var(--ddp-text-secondary);
    transition: all 0.2s; font-size: 0.85rem; text-decoration: none;
}
.topbar-right .user-dropdown a:hover {
    background: var(--ddp-red-light); color: var(--ddp-red);
}
.topbar-right .user-dropdown .divider {
    height: 1px; background: var(--ddp-border); margin: 0.3rem 0;
}
.topbar-right .user-dropdown .logout {
    color: var(--ddp-red); font-weight: 600;
}
.topbar-right .user-dropdown .logout:hover {
    background: var(--ddp-red-light); color: var(--ddp-red-dark);
}
@media (max-width: 992px) {
    .topbar-right .user-menu .user-info { display: none; }
}
        .content-area { padding: 2rem; max-width: 1440px; margin: 0 auto; }
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
        .btn-red:hover {
            background: var(--ddp-red-dark); transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
        }
        .btn-outline {
            background: transparent; color: var(--ddp-text);
            border: 1px solid var(--ddp-border-dark);
        }
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
        .form-control:disabled { background: var(--ddp-bg); cursor: not-allowed; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
        .form-actions {
            display: flex; gap: 1rem; margin-top: 1rem;
            padding-top: 1.5rem; border-top: 1px solid var(--ddp-border);
        }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input[type="checkbox"] {
            width: 18px; height: 18px; accent-color: var(--ddp-red); cursor: pointer;
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
        .alert {
            padding: 1rem 1.5rem; border-radius: var(--ddp-radius);
            margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;
        }
        .alert-danger { background: #fde8e6; color: var(--ddp-danger); border: 1px solid #f5c6cb; }

        /* Summernote */
        .note-editor.note-frame {
            border: 1px solid var(--ddp-border) !important;
            border-radius: 0.5rem !important;
            font-family: 'Cabin', sans-serif !important;
        }
        .note-editor.note-frame .note-toolbar {
            background: var(--ddp-bg) !important;
            border-bottom: 1px solid var(--ddp-border) !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .note-editor.note-frame .note-btn {
            background: var(--ddp-white) !important;
            border: 1px solid var(--ddp-border) !important;
            color: var(--ddp-text) !important;
        }
        .note-editor.note-frame .note-btn:hover {
            background: var(--ddp-red-light) !important;
            color: var(--ddp-red) !important;
        }
        .note-editor.note-frame .note-editing-area .note-editable {
            background: var(--ddp-white) !important;
            color: var(--ddp-text) !important;
            min-height: 300px; font-size: 0.95rem; line-height: 1.7;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
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
            <li><a href="index.php" class="active"><i class="fas fa-newspaper"></i> Reportajes</a></li>
            <li><a href="../noticias/index.php"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="../boletines/index.php"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="../podcasts/index.php"><i class="fas fa-podcast"></i> Podcasts</a></li>
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
                    <i class="fas fa-home"></i> / <a href="index.php">Reportajes</a> / <span>Nuevo</span>
                </div>
            </div>
            <div class="topbar-right">
    <button class="btn-icon" title="Notificaciones" onclick="alert('No hay notificaciones');">
        <i class="fas fa-bell"></i>
    </button>
    <div class="user-menu" id="userMenu">
        <div class="user-avatar"><?php echo $iniciales ?: 'U'; ?></div>
        <div class="user-info">
            <div class="name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
            <div class="role"><?php echo strtoupper($_SESSION['usuario_rol'] ?? 'Usuario'); ?></div>
        </div>
        <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
        <div class="user-dropdown" id="userDropdown">
            <a href="../usuarios/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
            <div class="divider"></div>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>
</div>
        </header>

        <div class="content-area">
            <section class="hero">
                <div>
                    <h1>Nuevo <span>Reportaje</span></h1>
                    <p>Completa todos los campos para crear un nuevo reportaje</p>
                </div>
                <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver a la lista</a>
            </section>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título <span class="required">*</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_publicacion">Fecha de publicación <span class="required">*</span></label>
                            <input type="date" id="fecha_publicacion" name="fecha_publicacion" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['fecha_publicacion'] ?? date('Y-m-d')); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="resumen_corto">Resumen / Bajada <span class="required">*</span></label>
                        <input type="text" id="resumen_corto" name="resumen_corto" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['resumen_corto'] ?? ''); ?>" 
                               placeholder="Breve resumen del reportaje" required>
                    </div>

                    <div class="form-group">
                        <label for="desarrollo">Desarrollo / Cuerpo <span class="required">*</span></label>
                        <textarea id="desarrollo" name="desarrollo" class="form-control"><?php echo htmlspecialchars($_POST['desarrollo'] ?? ''); ?></textarea>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Usa el editor para dar formato completo al cuerpo del reportaje.
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto principal</label>
                        <div class="file-upload-wrapper" id="fileUpload">
                            <span class="icon"><i class="fas fa-cloud-upload-alt"></i></span>
                            <p>Haz clic para seleccionar una imagen<br><small>Formatos: JPG, PNG, GIF, WEBP</small></p>
                            <input type="file" name="imagen" accept="image/*" id="imagen">
                            <div class="file-name" id="fileName"></div>
                        </div>
                        <input type="hidden" id="foto_principal" name="foto_principal" value="<?php echo htmlspecialchars($_POST['foto_principal'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="url_infografia">
                            <i class="fas fa-external-link-alt" style="color:var(--ddp-red);"></i>
                            URL de la infografía (PDF)
                        </label>
                        <input type="url" 
                               id="url_infografia" 
                               name="url_infografia" 
                               class="form-control" 
                               placeholder="https://www.dialogoydesarrollo.com.pe/candidatos-con-reinfo.pdf"
                               value="<?php echo htmlspecialchars($_POST['url_infografia'] ?? ''); ?>">
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Pega la URL completa del PDF de la infografía. Se mostrará como botón debajo de la imagen principal del reportaje.
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="autor_id">Autor <span class="required">*</span></label>
                            <select id="autor_id" name="autor_id" class="form-control" required>
                                <option value="">Seleccionar autor...</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?php echo $autor['id']; ?>" <?php echo (($_POST['autor_id'] ?? '') == $autor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($autor['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="usuario_id">Usuario que publica</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($nombreUsuario); ?>" disabled>
                            <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="es_destacado" name="es_destacado" <?php echo isset($_POST['es_destacado']) ? 'checked' : ''; ?>>
                            <label for="es_destacado" style="margin-bottom:0;cursor:pointer;">
                                <i class="fas fa-star" style="color:var(--ddp-warning);"></i> 
                                Marcar como reportaje destacado
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="estado" value="publicado" class="btn btn-red">
                            <i class="fas fa-check"></i> Publicar
                        </button>
                        <button type="submit" name="estado" value="borrador" class="btn btn-outline">
                            <i class="fas fa-save"></i> Guardar como Borrador
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        // Summernote
        $(document).ready(function() {
            $('#desarrollo').summernote({
                height: 400,
                placeholder: 'Escribe aquí el desarrollo completo del reportaje...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        const data = new FormData();
                        data.append('file', files[0]);
                        $.ajax({
                            url: 'subir_imagen_editor.php',
                            method: 'POST',
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                try {
                                    const res = JSON.parse(response);
                                    if (res.url) $('#desarrollo').summernote('insertImage', res.url);
                                } catch(e) {
                                    console.error('Error:', e);
                                }
                            }
                        });
                    }
                }
            });
        });

        // Mostrar nombre del archivo seleccionado (imagen)
        document.getElementById('imagen').addEventListener('change', function() {
            const fileName = document.getElementById('fileName');
            fileName.textContent = this.files[0] ? '📎 ' + this.files[0].name : '';
            fileName.style.color = '#27ae60';
        });
        // User dropdown
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');
        if (userMenu && userDropdown) {
            userMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            });
            document.addEventListener('click', function() {
                userDropdown.classList.remove('show');
            });
        }
        // Sidebar toggle
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