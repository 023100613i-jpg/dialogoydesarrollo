<?php
require_once '../includes/conexion.php';
require_once '../includes/auth.php';

requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = sanitizar($_POST['nombres'] ?? '');
    $ap_paterno = sanitizar($_POST['ap_paterno'] ?? '');
    $ap_materno = sanitizar($_POST['ap_materno'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = sanitizar($_POST['rol'] ?? 'redactor');
    
    if (empty($nombres) || empty($ap_paterno) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($password) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } else {
        try {
            // Generar hash de la contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombres, $ap_paterno, $ap_materno, $email, $hash, $rol]);
            header('Location: index.php?mensaje=creado');
            exit;
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = 'Este email ya está registrado.';
            } else {
                $error = 'Error al crear el usuario: ' . $e->getMessage();
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
    <title>Nuevo Usuario · DDP Noticias</title>
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
        .form-card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 2rem;
            border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
            max-width: 600px;
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
        .form-group label .required {
            color: var(--ddp-red);
        }
        .form-group .help-text {
            font-size: 0.75rem;
            color: var(--ddp-text-secondary);
            margin-top: 0.2rem;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--ddp-border);
        }
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--ddp-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-danger {
            background: #fde8e6;
            color: var(--ddp-danger);
            border: 1px solid #f5c6cb;
        }
        select.form-control {
            appearance: auto;
        }
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
            <li><a href="#"><i class="fas fa-rss"></i> Noticias</a></li>
            <li><a href="#"><i class="fas fa-file-pdf"></i> Boletines</a></li>
            <li><a href="#"><i class="fas fa-podcast"></i> Podcasts</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Videos</a></li>
            <li class="nav-section">Usuarios</li>
            <li><a href="index.php" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="#"><i class="fas fa-user-edit"></i> Autores</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> / <a href="index.php">Usuarios</a> / <span>Nuevo</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'A', 0, 1)); ?></div>
            </div>
        </header>

        <div class="content-area">
            <section class="hero">
                <div>
                    <h1>Nuevo <span>Usuario</span></h1>
                    <p>Completa los datos para crear un nuevo usuario</p>
                </div>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
            </section>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombres">Nombres <span class="required">*</span></label>
                            <input type="text" id="nombres" name="nombres" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['nombres'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ap_paterno">Apellido Paterno <span class="required">*</span></label>
                            <input type="text" id="ap_paterno" name="ap_paterno" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['ap_paterno'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ap_materno">Apellido Materno</label>
                        <input type="text" id="ap_materno" name="ap_materno" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['ap_materno'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Mínimo 4 caracteres" required>
                        <div class="help-text">La contraseña se almacenará con hash por seguridad</div>
                    </div>
                    <div class="form-group">
                        <label for="rol">Rol <span class="required">*</span></label>
                        <select id="rol" name="rol" class="form-control" required>
                            <option value="redactor" <?php echo (($_POST['rol'] ?? '') === 'redactor') ? 'selected' : ''; ?>>Redactor</option>
                            <option value="editor" <?php echo (($_POST['rol'] ?? '') === 'editor') ? 'selected' : ''; ?>>Editor</option>
                            <option value="admin" <?php echo (($_POST['rol'] ?? '') === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red">
                            <i class="fas fa-save"></i> Crear Usuario
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
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