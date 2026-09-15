<?php
// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si ya está logueado
if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=revista_digital;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Verificar usando password_verify()
            if (password_verify($password, $usuario['password_hash'])) {
                $nombre_completo = trim(
                    ($usuario['nombres'] ?? '') . ' ' . 
                    ($usuario['ap_paterno'] ?? '') . ' ' . 
                    ($usuario['ap_materno'] ?? '')
                );
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $nombre_completo ?: $usuario['nombres'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_logueado'] = true;
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cabin', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 3rem 2.5rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 30px rgba(0,0,0,0.08);
            border: 1px solid #ecf0f1;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .login-header .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .login-header .logo img {
            height: 70px;
            width: auto;
            max-width: 220px;
            margin-bottom: 0.5rem;
        }
        .login-header .logo .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #c0392b;
            letter-spacing: -1px;
            line-height: 1;
        }
        .login-header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 0.5rem;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        .form-group label .required { color: #c0392b; }
        .form-group .input-group { position: relative; }
        .form-group .input-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            border: 2px solid #ecf0f1;
            border-radius: 0.6rem;
            font-family: inherit;
            font-size: 0.95rem;
            background: #f8f9fa;
            color: #2c3e50;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #c0392b;
            background: #ffffff;
        }
        .form-control::placeholder { color: #95a5a6; }
        .form-options {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }
        .form-options a {
            color: #7f8c8d;
            font-size: 0.85rem;
            transition: color 0.2s;
            text-decoration: none;
        }
        .form-options a:hover { color: #c0392b; }
        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 0.6rem;
            font-family: inherit;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-red {
            background: #c0392b;
            color: white;
        }
        .btn-red:hover {
            background: #a93226;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(192, 57, 43, 0.3);
        }
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 0.6rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }
        .alert-danger {
            background: #fde8e6;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #27ae60;
            border: 1px solid #c3e6cb;
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ecf0f1;
        }
        .login-footer p {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        .login-footer .secure {
            font-size: 0.7rem;
            color: #95a5a6;
        }
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ecf0f1;
        }
        .divider::before { margin-right: 1rem; }
        .divider::after { margin-left: 1rem; }
        .divider span {
            color: #95a5a6;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        @media (max-width: 480px) {
            .login-container { padding: 2rem 1.5rem; }
            .login-header .logo img { height: 50px; }
            .login-header .logo .logo-text { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <img src="assets/images/logo.png" alt="DDP Noticias Logo">
                <span class="logo-text">DDP Noticias</span> 
            </div>
            <div class="divider">
                <span>Panel de Administración</span>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'sesion_cerrada'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Sesión cerrada correctamente.
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Correo electrónico <span class="required">*</span></label>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="correo@ejemplo.com" 
                           value="admin@ddp.com" 
                           required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña <span class="required">*</span></label>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="••••••••" value="1234" required>
                </div>
            </div>
            
            <div class="form-options">
                <a href="olvide_pass.php">
                    <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                </a>
            </div>
            
            <button type="submit" class="btn btn-red" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> Diálogo y Desarrollo Perú</p>
            <p class="secure"><i class="fas fa-lock"></i> Conexión segura</p>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner spinner"></i> Iniciando sesión...';
            btn.classList.add('btn-loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>