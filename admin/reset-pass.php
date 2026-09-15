<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$error = '';
$exito = '';
$token_valido = false;
$usuario_id = null;
$email = '';
$nombre = '';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=revista_digital;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar token
$token = $_GET['token'] ?? '';
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id, email, nombres, ap_paterno FROM usuarios WHERE reset_token = ? AND reset_expira > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        $token_valido = true;
        $usuario_id = $usuario['id'];
        $email = $usuario['email'];
        $nombre = trim($usuario['nombres'] . ' ' . $usuario['ap_paterno']);
    } else {
        $error = '❌ El enlace de recuperación es inválido o ha expirado.';
        $error .= '<br><br><a href="olvide-pass.php" style="color:#c0392b;text-decoration:underline;">Solicitar nuevo enlace</a>';
    }
} else {
    // Si no hay token, redirigir a recuperación
    header('Location: olvide-pass.php');
    exit;
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    
    if (empty($password) || empty($confirmar)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (strlen($password) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Generar HASH de la nueva contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña con HASH y eliminar token
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
        $stmt->execute([$hash, $usuario_id]);
        
        $exito = 'Contraseña actualizada.';
        $exito .= '<br><br><a href="login.php" style="display:inline-block;background:#c0392b;color:white;padding:12px 25px;text-decoration:none;border-radius:5px;">
        Ir al Login</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña · DDP Noticias</title>
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
        .login-header .logo img {
            height: 70px;
            width: auto;
            max-width: 220px;
        }
        .login-header .logo .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #c0392b;
        }
        .login-header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #2c3e50;
        }
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
            background: #c0392b;
            color: white;
        }
        .btn:hover { background: #a93226; }
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
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
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
        .link-volver {
            text-align: center;
            margin-top: 1rem;
        }
        .link-volver a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .link-volver a:hover { color: #c0392b; }
        .info-usuario {
            background: #f8f9fa;
            padding: 0.8rem 1.2rem;
            border-radius: 0.6rem;
            margin-bottom: 1.5rem;
            text-align: center;
            border-left: 4px solid #c0392b;
        }
        .info-usuario strong {
            color: #c0392b;
        }
        @media (max-width: 480px) {
            .login-container { padding: 2rem 1.5rem; }
            .login-header .logo img { height: 50px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <img src="assets/images/logo.png" alt="DDP Noticias">
                <span class="logo-text">DDP Noticias</span>
            </div>
            <div class="divider">
                <span>Restablecer Contraseña</span>
            </div>
            <?php if ($token_valido && !$exito): ?>
            <p>Ingresa tu nueva contraseña para:</p>
            <p style="font-weight:600;color:#c0392b;font-size:1.1rem;"><?php echo htmlspecialchars($email); ?></p>
            <?php elseif ($exito): ?>
            <p>¡Contraseña actualizada!</p>
            <?php else: ?>
            <p>Verifica tu enlace de recuperación</p>
            <?php endif; ?>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($token_valido && !$exito): ?>
        <!-- FORMULARIO PARA NUEVA CONTRASEÑA -->
        <div class="info-usuario">
            <p>🔐 Restableciendo contraseña para:</p>
            <p><strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nueva contraseña <span class="required">*</span></label>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mínimo 4 caracteres" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirmar contraseña <span class="required">*</span></label>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-check-circle"></i></span>
                    <input type="password" name="confirmar" class="form-control" 
                           placeholder="Repite la contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-key"></i> Restablecer Contraseña
            </button>
        </form>
        <?php endif; ?>
        
        <?php if (!$exito): ?>
        <div class="link-volver">
            <a href="login.php"><i class="fas fa-arrow-left">
            </i> Volver al inicio de sesión</a>
        </div>
        <?php endif; ?>
        
        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> Diálogo y Desarrollo Perú</p>
        </div>
    </div>
</body>
</html>