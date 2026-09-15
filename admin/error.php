<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cabin', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .error-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0,0,0,0.08);
            border: 1px solid #ecf0f1;
        }
        .error-container .icon {
            font-size: 4rem;
            color: #c0392b;
            margin-bottom: 1rem;
        }
        .error-container h1 {
            font-size: 1.8rem;
            color: #c0392b;
            margin-bottom: 0.5rem;
        }
        .error-container p {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #a93226;
        }
        .btn-outline {
            background: transparent;
            color: #2c3e50;
            border: 1px solid #ecf0f1;
            margin-left: 0.5rem;
        }
        .btn-outline:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon"><i class="fas fa-lock"></i></div>
        <h1>Acceso Denegado</h1>
        <p>No tienes permisos suficientes para acceder a esta sección.</p>
        <p style="font-size:0.85rem;color:#95a5a6;">
            Tu rol actual es: <strong><?php echo strtoupper($_SESSION['usuario_rol'] ?? 'Usuario'); ?></strong>
        </p>
        <br>
        <a href="index.php" class="btn"><i class="fas fa-home"></i> Ir al Dashboard</a>
        <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
</body>
</html>