<?php
// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once '../includes/conexion.php';
require_once '../includes/auth.php';

// Verificar autenticación
requireLogin();

// SOLO ADMINISTRADORES pueden acceder a usuarios
if (!esAdmin()) {
    header('Location: ../error.php');
    exit;
}

//Verificar si hay mensajes de éxito 
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

// Obtener el usuario actual completo
$usuarioActual = usuarioActual($pdo);

// Construir nombre completo e iniciales
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

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT id, nombres, ap_paterno, ap_materno, email, rol, created_at FROM usuarios ORDER BY id");
$usuarios = $stmt->fetchAll();

// Contar usuarios
$totalUsuarios = count($usuarios);
$adminCount = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
$editorCount = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'editor'")->fetchColumn();
$redactorCount = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'redactor'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios · DDP Noticias</title>
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
            --ddp-warning: #f39c12;
            --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --ddp-shadow-hover: 0 4px 24px rgba(0,0,0,0.12);
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

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
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

        /* ===== MAIN CONTENT ===== */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* ===== TOPBAR ===== */
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
        .topbar-left .breadcrumb span {
            color: var(--ddp-red);
            font-weight: 600;
        }
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
            position: relative;
        }
        .topbar-right .btn-icon:hover {
            color: var(--ddp-red);
        }
        
        /* ===== MENÚ DE USUARIO ===== */
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
        
        /* ===== DROPDOWN ===== */
        .topbar-right .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--ddp-white);
            border: 1px solid var(--ddp-border);
            border-radius: var(--ddp-radius);
            box-shadow: var(--ddp-shadow-hover);
            min-width: 200px;
            padding: 0.5rem 0;
            z-index: 100;
        }
        .topbar-right .user-dropdown.show {
            display: block;
        }
        .topbar-right .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1.25rem;
            color: var(--ddp-text-secondary);
            transition: all 0.2s;
            font-size: 0.85rem;
            text-decoration: none;
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
        .topbar-right .user-dropdown .logout:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red-dark);
        }

        /* ===== CONTENT AREA ===== */
        .content-area { padding: 2rem; max-width: 1440px; margin: 0 auto; }

        /* ===== HERO ===== */
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
        .hero-actions { display: flex; gap: 0.75rem; }

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
        }
        .btn-red {
            background: var(--ddp-red);
            color: white;
        }
        .btn-red:hover {
            background: var(--ddp-red-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
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

        /* ===== STATS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 1rem 1.5rem;
            border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
            text-align: center;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ddp-red);
            line-height: 1.2;
        }
        .stat-card .label {
            font-size: 0.8rem;
            color: var(--ddp-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 500;
        }

        /* ===== TABLE ===== */
        .table-wrapper { overflow-x: auto; }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            overflow: hidden;
            box-shadow: var(--ddp-shadow);
            border: 1px solid var(--ddp-border);
        }
        .table thead {
            background: var(--ddp-bg);
        }
        .table thead th {
            text-align: left;
            padding: 0.8rem 1rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ddp-text-secondary);
            font-weight: 600;
            border-bottom: 2px solid var(--ddp-border);
        }
        .table tbody td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--ddp-border);
            color: var(--ddp-text);
            vertical-align: middle;
        }
        .table tbody tr:hover td {
            background: var(--ddp-red-light);
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table .tag {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: inline-block;
        }
        .table .tag.admin {
            background: #fde8e6;
            color: var(--ddp-red);
        }
        .table .tag.editor {
            background: #dbeafe;
            color: #2563eb;
        }
        .table .tag.redactor {
            background: #fef3c7;
            color: #d97706;
        }
        .table .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .table .actions a {
            padding: 0.25rem 0.6rem;
            border-radius: 0.3rem;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .table .actions .edit {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .table .actions .edit:hover {
            background: var(--ddp-red);
            color: white;
        }
        .table .actions .delete {
            background: #fde8e6;
            color: var(--ddp-danger);
        }
        .table .actions .delete:hover {
            background: var(--ddp-danger);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--ddp-text-secondary);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--ddp-border-dark);
        }
        .empty-state h3 {
            color: var(--ddp-text);
            margin-bottom: 0.5rem;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
            .hero-actions { justify-content: center; flex-wrap: wrap; }
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
    <!-- ===== SIDEBAR COMPLETO ===== -->
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
            <li><a href="#"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="#"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="#"><i class="fas fa-podcast"></i> Podcasts</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Videos</a></li>
            <li><a href="#"><i class="fas fa-star"></i> Especiales</a></li>
            
            <li class="nav-section">Usuarios</li>
            <li><a href="#" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="#"><i class="fas fa-user-edit"></i> Autores</a></li>
            
            <li class="nav-section">Configuración</li>
            <li><a href="#"><i class="fas fa-link"></i> Fuentes</a></li>
            <li><a href="#"><i class="fas fa-handshake"></i> Alianzas</a></li>
            <li><a href="#"><i class="fas fa-share-alt"></i> Redes Sociales</a></li>
            
            <li class="nav-section">Contacto</li>
            <li><a href="#"><i class="fas fa-envelope"></i> Mensajes</a></li>
        </ul>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- TOPBAR CON MENÚ DE USUARIO -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home" style="color: var(--ddp-text-secondary);"></i> / <span>Usuarios</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn-icon" title="Notificaciones" onclick="alert('No hay notificaciones');">
                    <i class="fas fa-bell"></i>
                </button>
                
                <!-- MENÚ DE USUARIO -->
                <div class="user-menu" id="userMenu">
                    <div class="user-avatar"><?php echo $iniciales ?: 'U'; ?></div>
                    <div class="user-info">
                        <div class="name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                        <div class="role"><?php echo strtoupper($_SESSION['usuario_rol'] ?? 'Usuario'); ?></div>
                    </div>
                    <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <div class="divider"></div>
                        <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="content-area">
            <!-- HERO -->
            <section class="hero">
                <div>
                    <h1>Gestión de <span>Usuarios</span></h1>
                    <p>Administra los usuarios del sistema</p>
                </div>
                <div class="hero-actions">
                    <a href="crear.php" class="btn btn-red">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </section>

            <!-- ESTADÍSTICAS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="number"><?php echo $totalUsuarios; ?></div>
                    <div class="label">Total Usuarios</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $adminCount; ?></div>
                    <div class="label">Administradores</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $editorCount; ?></div>
                    <div class="label">Editores</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $redactorCount; ?></div>
                    <div class="label">Redactores</div>
                </div>
            </div>

            <!-- TABLA DE USUARIOS -->
            <?php if (count($usuarios) > 0): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th style="text-align:center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>#<?php echo $usuario['id']; ?></td>
                            <td>
                                <strong>
                                    <?php 
                                    $nombreCompletoUser = trim(
                                        ($usuario['nombres'] ?? '') . ' ' . 
                                        ($usuario['ap_paterno'] ?? '') . ' ' . 
                                        ($usuario['ap_materno'] ?? '')
                                    );
                                    echo htmlspecialchars($nombreCompletoUser ?: 'Sin nombre');
                                    ?>
                                </strong>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <span class="tag <?php echo $usuario['rol']; ?>">
                                    <?php 
                                    $roles = [
                                        'admin' => 'Administrador',
                                        'editor' => 'Editor',
                                        'redactor' => 'Redactor'
                                    ];
                                    echo $roles[$usuario['rol']] ?? $usuario['rol'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo isset($usuario['created_at']) ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '-'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                    <a href="eliminar.php?id=<?php echo $usuario['id']; ?>" class="delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <span style="color:var(--ddp-text-light);font-size:0.7rem;" title="No puedes eliminar tu propia cuenta">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No hay usuarios</h3>
                <p>No se encontraron usuarios registrados.</p>
                <a href="crear.php" class="btn btn-red" style="margin-top:1rem;display:inline-flex;">
                    <i class="fas fa-plus"></i> Crear primer usuario
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ===== SIDEBAR TOGGLE =====
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

        // ===== USER DROPDOWN =====
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