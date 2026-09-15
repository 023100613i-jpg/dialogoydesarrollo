<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

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
        if (!empty($parte)) {
            $iniciales .= strtoupper(substr($parte, 0, 1));
        }
    }
    if (strlen($iniciales) < 2) {
        $iniciales = strtoupper(substr($nombreCompleto, 0, 2));
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$boletin = obtenerBoletinPorId($pdo, $id);

if (!$boletin) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $numero_boletin = trim($_POST['numero_boletin'] ?? '');
    
    // El resumen ahora es HTML enriquecido
    $resumen = $_POST['resumen'] ?? '';
    $resumen = trim($resumen);
    
    $fecha = trim($_POST['fecha_publicacion'] ?? '');

    $foto_portada = $boletin['foto_portada'];
    $archivo_pdf  = $boletin['archivo_pdf'];

    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if (!empty($boletin['foto_portada']) && file_exists('../' . $boletin['foto_portada'])) {
            unlink('../' . $boletin['foto_portada']);
        }
        $foto_portada = '';
    }

    if (isset($_POST['eliminar_pdf']) && $_POST['eliminar_pdf'] == '1') {
        if (!empty($boletin['archivo_pdf']) && file_exists('../' . $boletin['archivo_pdf'])) {
            unlink('../' . $boletin['archivo_pdf']);
        }
        $archivo_pdf = '';
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowed)) {
            if (!empty($boletin['foto_portada']) && file_exists('../' . $boletin['foto_portada'])) {
                unlink('../' . $boletin['foto_portada']);
            }
            
            $nombreArchivo = $file['name'];
            $uploadDir = '../assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $rutaCompleta = $uploadDir . $nombreArchivo;
            if (file_exists($rutaCompleta)) {
                $info = pathinfo($nombreArchivo);
                $nombreArchivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                $rutaCompleta = $uploadDir . $nombreArchivo;
            }
            
            if (move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
                $foto_portada = 'assets/images/' . $nombreArchivo;
            } else {
                $error = 'Error al subir la imagen.';
            }
        } else {
            $error = 'Formato de imagen no permitido.';
        }
    }

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pdf_file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($extension === 'pdf') {
            if (!empty($boletin['archivo_pdf']) && file_exists('../' . $boletin['archivo_pdf'])) {
                unlink('../' . $boletin['archivo_pdf']);
            }
            
            $nombreArchivo = $file['name'];
            $pdfDir = '../assets/pdf/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            
            $rutaCompleta = $pdfDir . $nombreArchivo;
            if (file_exists($rutaCompleta)) {
                $info = pathinfo($nombreArchivo);
                $nombreArchivo = $info['filename'] . '_' . time() . '.' . $info['extension'];
                $rutaCompleta = $pdfDir . $nombreArchivo;
            }
            
            if (move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
                $archivo_pdf = 'assets/pdf/' . $nombreArchivo;
            } else {
                $error = 'Error al subir el PDF.';
            }
        } else {
            $error = 'Formato no permitido. Solo PDF.';
        }
    }

    if (empty($numero_boletin)) { $error = 'El número de boletín es obligatorio.'; }
    elseif (empty($archivo_pdf)) { $error = 'El archivo PDF es obligatorio.'; }
    elseif (empty($fecha)) { $error = 'La fecha de publicación es obligatoria.'; }
    else {
        try {
            $stmt = $pdo->prepare("UPDATE boletines SET 
                                   numero_boletin = ?, resumen = ?, 
                                   foto_portada = ?, archivo_pdf = ?, 
                                   fecha_publicacion = ? 
                                   WHERE id = ?");
            $stmt->execute([$numero_boletin, $resumen, $foto_portada, $archivo_pdf, $fecha, $id]);
            
            header('Location: index.php?mensaje=actualizado');
            exit;
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = 'Este número de boletín ya está registrado.';
            } else {
                $error = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Boletín · DDP Noticias</title>
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
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--ddp-white);
            border-right: 1px solid var(--ddp-border);
            padding: 1.5rem 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 2px solid var(--ddp-red);
        }
        .sidebar-brand img { height: 45px; width: auto; }
        .sidebar-brand span {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ddp-red);
        }
        .sidebar-nav { list-style: none; padding: 1rem 0; }
        .sidebar-nav .nav-section {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ddp-text-secondary);
            padding: 1rem 1.5rem 0.4rem;
            font-weight: 600;
        }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.5rem;
            color: var(--ddp-text-secondary);
            transition: all 0.2s;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .sidebar-nav li a.active {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
            border-left-color: var(--ddp-red);
            font-weight: 600;
        }
        .sidebar-nav li a i { width: 20px; text-align: center; }

        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            background: var(--ddp-white);
            border-bottom: 1px solid var(--ddp-border);
            padding: 0 2rem;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .topbar-left .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--ddp-text);
            cursor: pointer;
            padding: 0.25rem 0.5rem;
        }
        .topbar-left .breadcrumb {
            font-size: 0.85rem;
            color: var(--ddp-text-secondary);
        }
        .topbar-left .breadcrumb a { color: var(--ddp-text-secondary); }
        .topbar-left .breadcrumb a:hover { color: var(--ddp-red); }
        .topbar-left .breadcrumb span { color: var(--ddp-red); font-weight: 600; }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .topbar-right .btn-icon {
            background: none;
            border: none;
            font-size: 1.1rem;
            color: var(--ddp-text-secondary);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
        }
        .topbar-right .btn-icon:hover { color: var(--ddp-red); }
        .topbar-right .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.3rem 0.75rem 0.3rem 0.3rem;
            border-radius: 2rem;
            transition: background 0.2s;
            position: relative;
            border: 1px solid transparent;
        }
        .topbar-right .user-menu:hover {
            background: var(--ddp-red-light);
            border-color: var(--ddp-red-light);
        }
        .topbar-right .user-menu .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ddp-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .topbar-right .user-menu .user-info {
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .topbar-right .user-menu .user-info .name {
            font-weight: 600;
            color: var(--ddp-text);
            font-size: 0.9rem;
        }
        .topbar-right .user-menu .user-info .role {
            font-size: 0.65rem;
            color: var(--ddp-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .topbar-right .user-menu .dropdown-arrow {
            font-size: 0.6rem;
            color: var(--ddp-text-secondary);
            margin-left: 0.25rem;
        }
        .topbar-right .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--ddp-white);
            border: 1px solid var(--ddp-border);
            border-radius: var(--ddp-radius);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            min-width: 200px;
            padding: 0.5rem 0;
            z-index: 100;
        }
        .topbar-right .user-dropdown.show { display: block; }
        .topbar-right .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1.25rem;
            color: var(--ddp-text-secondary);
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .topbar-right .user-dropdown a:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .topbar-right .user-dropdown .divider {
            height: 1px;
            background: var(--ddp-border);
            margin: 0.3rem 0;
        }
        .topbar-right .user-dropdown .logout {
            color: var(--ddp-red);
            font-weight: 600;
        }

        .content-area { padding: 2rem; max-width: 1440px; margin: 0 auto; }

        .hero {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 1.75rem 2.5rem;
            border: 1px solid var(--ddp-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            box-shadow: var(--ddp-shadow);
        }
        .hero h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ddp-text);
        }
        .hero h1 span { color: var(--ddp-red); }
        .hero p { color: var(--ddp-text-secondary); font-size: 0.95rem; }

        .btn {
            padding: 0.5rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-red {
            background: var(--ddp-red);
            color: white;
        }
        .btn-red:hover {
            background: var(--ddp-red-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
            color: white;
        }
        .btn-outline {
            background: transparent;
            color: var(--ddp-text);
            border: 1px solid var(--ddp-border-dark);
        }
        .btn-outline:hover {
            background: var(--ddp-bg);
            border-color: var(--ddp-text-secondary);
        }

        .form-card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            box-shadow: var(--ddp-shadow);
            border: 1px solid var(--ddp-border);
            max-width: 960px;
            margin: 0 auto;
        }
        .form-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--ddp-border);
        }
        .form-card-header h2 {
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .form-card-header h2 i { color: var(--ddp-red); }
        .form-card-body { padding: 2rem; }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--ddp-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }
        .alert-error {
            background: #fde8e6;
            color: var(--ddp-red);
            border: 1px solid #f5c6cb;
        }

        .form-group { margin-bottom: 1.5rem; }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ddp-text);
            margin-bottom: 0.4rem;
        }
        .form-label .required { color: var(--ddp-red); }
        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1px solid var(--ddp-border-dark);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--ddp-text);
            background: var(--ddp-white);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--ddp-red);
            box-shadow: 0 0 0 3px rgba(192,57,43,0.1);
        }
        .form-hint {
            font-size: 0.78rem;
            color: var(--ddp-text-secondary);
            margin-top: 0.35rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .file-preview {
            margin-top: 0.85rem;
            padding: 1rem;
            border: 1px dashed var(--ddp-border-dark);
            border-radius: 0.5rem;
            background: var(--ddp-bg);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .file-preview img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--ddp-border);
        }
        .file-preview .file-info { flex: 1; min-width: 0; }
        .file-preview .file-name {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--ddp-text);
        }
        .file-preview .file-meta {
            font-size: 0.75rem;
            color: var(--ddp-text-secondary);
        }
        .pdf-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px dashed var(--ddp-border-dark);
            border-radius: 0.5rem;
            background: var(--ddp-bg);
            margin-top: 0.85rem;
        }
        .pdf-preview .pdf-icon {
            width: 48px;
            height: 48px;
            background: var(--ddp-red-light);
            color: var(--ddp-red);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .pdf-preview .pdf-info { flex: 1; min-width: 0; }
        .pdf-preview .pdf-name {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--ddp-text);
        }
        .pdf-preview .pdf-meta {
            font-size: 0.75rem;
            color: var(--ddp-text-secondary);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--ddp-red);
            cursor: pointer;
        }
        .checkbox-wrapper label {
            font-size: 0.82rem;
            color: var(--ddp-text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--ddp-border);
            margin-top: 1rem;
        }

        /* Ajustes Summernote */
        .note-editor.note-frame {
            border: 1px solid var(--ddp-border-dark) !important;
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
            min-height: 280px;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .topbar-right .user-menu .user-info { display: none; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
        }
        @media (max-width: 600px) {
            .content-area { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .topbar-left .breadcrumb { display: none; }
            .form-row { grid-template-columns: 1fr; }
            .form-card-body { padding: 1.25rem; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions .btn { width: 100%; justify-content: center; }
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
            <li><a href="#"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="index.php" class="active"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="#"><i class="fas fa-podcast"></i> Podcasts</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Videos</a></li>
            <li><a href="#"><i class="fas fa-star"></i> Especiales</a></li>
            <li class="nav-section">Usuarios</li>
            <li><a href="../usuarios/index.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="#"><i class="fas fa-user-edit"></i> Autores</a></li>
            <li class="nav-section">Contacto</li>
            <li><a href="#"><i class="fas fa-envelope"></i> Mensajes</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home" style="color: var(--ddp-text-secondary);"></i> / 
                    <a href="index.php">Boletines</a> / 
                    <span>Editar</span>
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
                    <h1>Editar <span>Boletín</span></h1>
                    <p>Modifica la información del boletín</p>
                </div>
                <div class="hero-actions">
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </section>

            <div class="form-card">
                <div class="form-card-header">
                    <h2><i class="fas fa-edit"></i> Boletín N° <?= htmlspecialchars($boletin['numero_boletin']) ?></h2>
                </div>
                <div class="form-card-body">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">

                        <div class="form-group">
                            <label class="form-label" for="numero_boletin">
                                Número de Boletín <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="numero_boletin" 
                                   name="numero_boletin" 
                                   value="<?= htmlspecialchars($boletin['numero_boletin']) ?>" 
                                   required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="fecha_publicacion">
                                    Fecha de Publicación <span class="required">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_publicacion" 
                                       name="fecha_publicacion" 
                                       value="<?= htmlspecialchars($boletin['fecha_publicacion']) ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="resumen">
                                Contenido del Boletín (HTML enriquecido)
                            </label>
                            <textarea class="form-control" 
                                      id="resumen" 
                                      name="resumen" 
                                      rows="12"><?= htmlspecialchars($boletin['resumen'] ?? '') ?></textarea>
                            <div class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Usa el editor para dar formato: negritas, listas, imágenes, títulos, etc.
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="imagen">Imagen de Portada</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="imagen" 
                                   name="imagen" 
                                   accept="image/*">
                            <div class="form-hint">Si no seleccionas una nueva imagen, se mantendrá la actual.</div>

                            <?php if (!empty($boletin['foto_portada']) && file_exists('../' . $boletin['foto_portada'])): ?>
                                <div class="file-preview">
                                    <img src="../<?= htmlspecialchars($boletin['foto_portada']) ?>" 
                                         alt="Portada actual">
                                    <div class="file-info">
                                        <div class="file-name"><?= htmlspecialchars(basename($boletin['foto_portada'])) ?></div>
                                        <div class="file-meta">Imagen actual en uso</div>
                                    </div>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" 
                                               id="eliminar_imagen" 
                                               name="eliminar_imagen" 
                                               value="1">
                                        <label for="eliminar_imagen">Eliminar</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pdf_file">
                                Archivo PDF <span class="required">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="pdf_file" 
                                   name="pdf_file" 
                                   accept="application/pdf">
                            <div class="form-hint">Si no seleccionas un nuevo PDF, se mantendrá el actual.</div>

                            <?php if (!empty($boletin['archivo_pdf']) && file_exists('../' . $boletin['archivo_pdf'])): ?>
                                <div class="pdf-preview">
                                    <div class="pdf-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="pdf-info">
                                        <div class="pdf-name"><?= htmlspecialchars(basename($boletin['archivo_pdf'])) ?></div>
                                        <div class="pdf-meta">PDF actual en uso</div>
                                    </div>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" 
                                               id="eliminar_pdf" 
                                               name="eliminar_pdf" 
                                               value="1">
                                        <label for="eliminar_pdf">Eliminar</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" name="actualizar" class="btn btn-red">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
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

        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('open');
            });
            document.addEventListener('click', function(e) {
                const sidebar = document.getElementById('sidebar');
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });
        }

        // Summernote
        $(document).ready(function() {
            $('#resumen').summernote({
                height: 320,
                placeholder: 'Escribe aquí el contenido del boletín...',
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
                                const res = JSON.parse(response);
                                if (res.url) {
                                    $('#resumen').summernote('insertImage', res.url);
                                }
                            }
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>