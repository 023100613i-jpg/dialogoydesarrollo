<?php
require_once '../includes/conexion.php';
require_once '../includes/auth.php';

// Verificar autenticación
requireLogin();

$usuario = usuarioActual($pdo);

// Construir nombre completo e iniciales
$nombreCompleto = trim(
    ($usuario['nombres'] ?? '') . ' ' . 
    ($usuario['ap_paterno'] ?? '') . ' ' . 
    ($usuario['ap_materno'] ?? '')
);

$iniciales = '';
$partes = explode(' ', trim($nombreCompleto));
foreach ($partes as $parte) {
    if (!empty($parte)) {
        $iniciales .= strtoupper(substr($parte, 0, 1));
    }
}
if (strlen($iniciales) < 2) {
    $iniciales = strtoupper(substr($nombreCompleto, 0, 2));
}

$error = '';
$exito = '';

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_pass'])) {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';
    
    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } elseif (strlen($nueva) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } else {
        $resultado = cambiarContrasena($pdo, $usuario['id'], $actual, $nueva);
        if (isset($resultado['success'])) {
            $exito = $resultado['success'];
        } else {
            $error = $resultado['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil · DDP Noticias</title>
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
        .topbar-left .breadcrumb span {
            color: var(--ddp-red);
            font-weight: 600;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .topbar-right .user-avatar {
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

        .profile-card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 2rem;
            border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
            max-width: 600px;
        }
        .profile-card .avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--ddp-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .profile-card .info-group {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--ddp-border);
        }
        .profile-card .info-group .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--ddp-text-secondary);
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .profile-card .info-group .value {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .profile-card .info-group .badge {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .profile-card .info-group .badge.admin {
            background: #fde8e6;
            color: #c0392b;
        }
        .profile-card .info-group .badge.editor {
            background: #dbeafe;
            color: #2563eb;
        }
        .profile-card .info-group .badge.redactor {
            background: #fef3c7;
            color: #d97706;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.3rem;
            color: var(--ddp-text);
        }
        .form-control {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid var(--ddp-border);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.9rem;
            background: var(--ddp-white);
            color: var(--ddp-text);
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--ddp-red);
        }

        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 0.6rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: #d4edda;
            color: #27ae60;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #fde8e6;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero { flex-direction: column; text-align: center; gap: 1rem; }
        }
        @media (max-width: 600px) {
            .content-area { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .topbar-left .breadcrumb { display: none; }
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
            <li class="nav-section">Mi Cuenta</li>
            <li><a href="#" class="active"><i class="fas fa-user"></i> Mi Perfil</a></li>
            <?php if (esAdmin()): ?>
            <li><a href="index.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <?php endif; ?>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> / <span>Mi Perfil</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo $iniciales ?: 'U'; ?></div>
            </div>
        </header>

        <div class="content-area">
            <section class="hero">
                <div>
                    <h1>Mi <span>Perfil</span></h1>
                    <p>Gestiona tu información personal</p>
                </div>
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </section>

            <div class="profile-card">
                <div class="avatar-large"><?php echo $iniciales ?: 'U'; ?></div>

                <div class="info-group">
                    <div class="label">Nombre completo</div>
                    <div class="value"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                </div>

                <div class="info-group">
                    <div class="label">Correo electrónico</div>
                    <div class="value"><?php echo htmlspecialchars($usuario['email']); ?></div>
                </div>

                <div class="info-group">
                    <div class="label">Rol</div>
                    <div class="value">
                        <span class="badge <?php echo $usuario['rol']; ?>">
                            <?php 
                            $roles = [
                                'admin' => 'Administrador',
                                'editor' => 'Editor',
                                'redactor' => 'Redactor'
                            ];
                            echo $roles[$usuario['rol']] ?? $usuario['rol'];
                            ?>
                        </span>
                    </div>
                </div>

                <div class="info-group" style="border-bottom:none;margin-bottom:0;padding-bottom:0;">
                    <div class="label">Miembro desde</div>
                    <div class="value"><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></div>
                </div>

                <hr style="border:none;border-top:2px solid var(--ddp-border);margin:1.5rem 0;">

                <h3 style="margin-bottom:1rem;color:var(--ddp-text);">Cambiar Contraseña</h3>

                <?php if ($exito): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Contraseña actual</label>
                        <input type="password" name="password_actual" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password_nueva" class="form-control" placeholder="Mínimo 4 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmar" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="cambiar_pass" class="btn btn-red">
                        <i class="fas fa-key"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
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