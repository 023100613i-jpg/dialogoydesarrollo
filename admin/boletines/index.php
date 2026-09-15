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
        if (!empty($parte)) $iniciales .= strtoupper(substr($parte, 0, 1));
    }
    if (strlen($iniciales) < 2) {
        $iniciales = strtoupper(substr($nombreCompleto, 0, 2));
    }
}

// ============================================
// FILTRO POR ESTADO
// ============================================
$filtro = $_GET['filtro'] ?? 'todos';

$totalBoletines = 0;
$totalPublicados = 0;
$totalArchivados = 0;
$totalBorradores = 0;

try {
    $totalBoletines = $pdo->query("SELECT COUNT(*) FROM boletines")->fetchColumn();
    $totalPublicados = $pdo->query("SELECT COUNT(*) FROM boletines WHERE estado = 'publicado'")->fetchColumn();
    $totalArchivados = $pdo->query("SELECT COUNT(*) FROM boletines WHERE estado = 'archivado'")->fetchColumn();
    $totalBorradores = $pdo->query("SELECT COUNT(*) FROM boletines WHERE estado = 'borrador'")->fetchColumn();
} catch(PDOException $e) {
    $totalPublicados = $totalBoletines;
}

$where = '';
if ($filtro === 'publicados') $where = "WHERE b.estado = 'publicado'";
elseif ($filtro === 'archivados') $where = "WHERE b.estado = 'archivado'";
elseif ($filtro === 'borradores') $where = "WHERE b.estado = 'borrador'";

$stmt = $pdo->query("SELECT b.*, 
                            CONCAT(u.nombres, ' ', u.ap_paterno) as usuario_nombre 
                     FROM boletines b 
                     LEFT JOIN usuarios u ON b.usuario_id = u.id 
                     $where
                     ORDER BY b.fecha_publicacion DESC");
$boletines = $stmt->fetchAll();

$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletines · DDP Noticias</title>
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
            --ddp-text-light: #95a5a6;
            --ddp-border: #ecf0f1;
            --ddp-border-dark: #d5d8dc;
            --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --ddp-shadow-hover: 0 4px 24px rgba(0,0,0,0.12);
            --ddp-danger: #e74c3c;
            --ddp-success: #27ae60;
            --ddp-warning: #f39c12;
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
        .btn-red:hover {
            background: var(--ddp-red-dark); transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
        }
        .btn-outline {
            background: transparent; color: var(--ddp-text);
            border: 1px solid var(--ddp-border-dark);
        }
        .btn-outline:hover { background: var(--ddp-bg); }

        /* STATS CLICKEABLES */
        .stats-row {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 1rem; margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--ddp-white); border-radius: var(--ddp-radius);
            padding: 1rem 1.5rem; border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow); text-align: center;
            text-decoration: none; color: inherit;
            cursor: pointer; transition: all 0.3s;
            display: block;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--ddp-shadow-hover);
            border-color: var(--ddp-red-light);
        }
        .stat-card.active {
            border-color: var(--ddp-red);
            box-shadow: 0 0 0 3px rgba(192,57,45,0.1);
        }
        .stat-card .number {
            font-size: 2rem; font-weight: 700;
            color: var(--ddp-red); line-height: 1.2;
        }
        .stat-card .label {
            font-size: 0.8rem; color: var(--ddp-text-secondary);
            text-transform: uppercase; letter-spacing: 0.04em; font-weight: 500;
        }

        .table-wrapper { overflow-x: auto; }
        .table {
            width: 100%; border-collapse: collapse; font-size: 0.875rem;
            background: var(--ddp-white); border-radius: var(--ddp-radius);
            overflow: hidden; box-shadow: var(--ddp-shadow);
            border: 1px solid var(--ddp-border);
        }
        .table thead { background: var(--ddp-bg); }
        .table thead th {
            text-align: left; padding: 0.8rem 1rem;
            font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 0.04em; color: var(--ddp-text-secondary);
            font-weight: 600; border-bottom: 2px solid var(--ddp-border);
        }
        .table tbody td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--ddp-border);
            color: var(--ddp-text); vertical-align: middle;
        }
        .table tbody tr:hover td { background: var(--ddp-red-light); }
        .table tbody tr:last-child td { border-bottom: none; }
        .table .actions {
            display: flex; gap: 0.5rem; justify-content: center;
        }
        .table .actions a {
            padding: 0.25rem 0.6rem; border-radius: 0.3rem;
            font-size: 0.75rem; font-weight: 500; transition: all 0.2s;
        }
        .table .actions .view { background: var(--ddp-bg); color: var(--ddp-text-secondary); }
        .table .actions .view:hover { background: var(--ddp-border); }
        .table .actions .edit { background: var(--ddp-red-light); color: var(--ddp-red); }
        .table .actions .edit:hover { background: var(--ddp-red); color: white; }
        .table .actions .delete { background: #fde8e6; color: var(--ddp-danger); }
        .table .actions .delete:hover { background: var(--ddp-danger); color: white; }
        .table .actions .archive { background: #e2e3e5; color: #6c757d; }
        .table .actions .archive:hover { background: #6c757d; color: white; }
        .table .actions .publish { background: #d4edda; color: var(--ddp-success); }
        .table .actions .publish:hover { background: var(--ddp-success); color: white; }
        .table .actions .draft { background: #fff3cd; color: var(--ddp-warning); }
        .table .actions .draft:hover { background: var(--ddp-warning); color: white; }
        .table .pdf-icon {
            color: #e74c3c; font-size: 1.1rem;
        }
        .table .pdf-icon:hover { color: #c0392b; }

        /* BADGES DE ESTADO */
        .badge-estado {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-estado.publicado { background: #d4edda; color: var(--ddp-success); }
        .badge-estado.archivado { background: #e2e3e5; color: #6c757d; }
        .badge-estado.borrador { background: #fff3cd; color: var(--ddp-warning); }

        .empty-state {
            text-align: center; padding: 4rem 2rem;
            background: var(--ddp-white); border-radius: var(--ddp-radius);
            border: 1px solid var(--ddp-border); box-shadow: var(--ddp-shadow);
        }
        .empty-state .icon-wrapper {
            width: 80px; height: 80px; margin: 0 auto 1.5rem;
            background: var(--ddp-red-light); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .empty-state .icon-wrapper i { font-size: 2.5rem; color: var(--ddp-red); }
        .empty-state h3 { font-size: 1.5rem; color: var(--ddp-text); margin-bottom: 0.5rem; }
        .empty-state p { color: var(--ddp-text-secondary); margin-bottom: 1.5rem; }

        .alert {
            padding: 1rem 1.5rem; border-radius: var(--ddp-radius);
            margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;
        }
        .alert-success { background: #d4edda; color: var(--ddp-success); border: 1px solid #c3e6cb; }
        .alert-danger { background: #fde8e6; color: var(--ddp-danger); border: 1px solid #f5c6cb; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .topbar-right .user-menu .user-info { display: none; }
        }
        @media (max-width: 600px) {
            .content-area { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .topbar-left .breadcrumb { display: none; }
            .stats-row { grid-template-columns: 1fr; }
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
            <li><a href="index.php" class="active"><i class="fas fa-file-pdf"></i> Boletines</a></li>
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
                    <i class="fas fa-home"></i> / <span>Boletines</span>
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
                    <h1>Gestión de <span>Boletines</span></h1>
                    <p>Administra los boletines NTEP</p>
                </div>
                <a href="crear.php" class="btn btn-red">
                    <i class="fas fa-plus"></i> Nuevo Boletín
                </a>
            </section>

            <!-- STATS CLICKEABLES -->
            <div class="stats-row">
                <a href="?filtro=todos" class="stat-card <?php echo $filtro === 'todos' ? 'active' : ''; ?>">
                    <div class="number"><?php echo $totalBoletines; ?></div>
                    <div class="label">Total Boletines</div>
                </a>
                <a href="?filtro=publicados" class="stat-card <?php echo $filtro === 'publicados' ? 'active' : ''; ?>">
                    <div class="number"><?php echo $totalPublicados; ?></div>
                    <div class="label">Activos</div>
                </a>
                <a href="?filtro=archivados" class="stat-card <?php echo $filtro === 'archivados' ? 'active' : ''; ?>">
                    <div class="number"><?php echo $totalArchivados; ?></div>
                    <div class="label">Archivados</div>
                </a>
                <a href="?filtro=borradores" class="stat-card <?php echo $filtro === 'borradores' ? 'active' : ''; ?>">
                    <div class="number"><?php echo $totalBorradores; ?></div>
                    <div class="label">Borradores</div>
                </a>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    $mensajes = [
                        'creado' => 'Boletín creado exitosamente.',
                        'actualizado' => 'Boletín actualizado exitosamente.',
                        'eliminado' => 'Boletín eliminado exitosamente.',
                        'publicado' => 'Boletín publicado exitosamente.',
                        'archivado' => 'Boletín archivado exitosamente.',
                        'borrador' => 'Boletín marcado como borrador.',
                        'error' => 'Ocurrió un error al procesar la solicitud.'
                    ];
                    echo $mensajes[$mensaje] ?? 'Operación completada.';
                ?>
            </div>
            <?php endif; ?>

            <?php if (count($boletines) > 0): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>N° Boletín</th>
                            <th>Resumen</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>PDF</th>
                            <th style="text-align:center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($boletines as $boletin): 
                            $estadoActual = $boletin['estado'] ?? 'publicado';
                        ?>
                        <tr>
                            <td>#<?php echo $boletin['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($boletin['numero_boletin'] ?? 'Sin número'); ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($boletin['resumen'])): ?>
                                <div style="font-size:0.8rem;color:var(--ddp-text-secondary);">
                                    <?php echo htmlspecialchars(substr($boletin['resumen'], 0, 80)) . (strlen($boletin['resumen'] ?? '') > 80 ? '…' : ''); ?>
                                </div>
                                <?php else: ?>
                                <span style="color:var(--ddp-text-light);font-size:0.8rem;">Sin descripción</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-estado <?php echo $estadoActual; ?>">
                                    <?php 
                                    $labels = ['publicado' => 'Publicado', 'archivado' => 'Archivado', 'borrador' => 'Borrador'];
                                    echo $labels[$estadoActual] ?? 'Publicado';
                                    ?>
                                </span>
                            </td>
                            <td><?php echo isset($boletin['fecha_publicacion']) ? date('d/m/Y', strtotime($boletin['fecha_publicacion'])) : '-'; ?></td>
                            <td>
                                <?php if (!empty($boletin['archivo_pdf'])): ?>
                                    <a href="../<?php echo htmlspecialchars($boletin['archivo_pdf']); ?>" target="_blank" title="Ver PDF" class="pdf-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--ddp-text-light);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="ver.php?id=<?php echo $boletin['id']; ?>" class="view" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?php echo $boletin['id']; ?>" class="edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if ($estadoActual !== 'publicado'): ?>
                                    <a href="cambiar-estado.php?id=<?php echo $boletin['id']; ?>&estado=publicado" 
                                       class="publish" title="Publicar"
                                       onclick="return confirm('¿Publicar este boletín?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($estadoActual !== 'borrador'): ?>
                                    <a href="cambiar-estado.php?id=<?php echo $boletin['id']; ?>&estado=borrador" 
                                       class="draft" title="Marcar como Borrador"
                                       onclick="return confirm('¿Marcar este boletín como borrador?')">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($estadoActual !== 'archivado'): ?>
                                    <a href="cambiar-estado.php?id=<?php echo $boletin['id']; ?>&estado=archivado" 
                                       class="archive" title="Archivar"
                                       onclick="return confirm('¿Archivar este boletín?')">
                                        <i class="fas fa-archive"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="eliminar.php?id=<?php echo $boletin['id']; ?>" 
                                       class="delete" 
                                       title="Eliminar" 
                                       onclick="return confirm('¿Estás seguro de eliminar este boletín?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="icon-wrapper">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3>No hay boletines<?php echo $filtro !== 'todos' ? ' en este filtro' : ''; ?></h3>
                <p>
                    <?php if ($filtro === 'todos'): ?>
                        Comienza a gestionar los boletines NTEP. Crea tu primer boletín para compartir información importante.
                    <?php else: ?>
                        No hay boletines con el estado seleccionado.
                    <?php endif; ?>
                </p>
                <?php if ($filtro === 'todos'): ?>
                <a href="crear.php" class="btn btn-red">
                    <i class="fas fa-plus"></i> Crear primer boletín
                </a>
                <?php else: ?>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-list"></i> Ver todos los boletines
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>