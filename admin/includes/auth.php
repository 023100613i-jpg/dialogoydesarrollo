<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function iniciarSesion($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Construir nombre completo con ap_materno
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
        return ['success' => true, 'usuario' => $usuario];
    }
    return ['error' => 'Email o contraseña incorrectos.'];
}

function cerrarSesion() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function estaAutenticado() {
    return isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true;
}

function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

function esEditor() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'editor';
}

function esRedactor() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'redactor';
}

function puedeGestionarUsuarios() {
    return esAdmin();
}

function puedeGestionarConfiguracion() {
    return esAdmin();
}

function puedeEliminarContenidoAjeno() {
    return esAdmin() || esEditor();
}

function requireLogin() {
    if (!estaAutenticado()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!esAdmin()) {
        header('Location: ../error.php');
        exit;
    }
}

function requireEditor() {
    requireLogin();
    if (!esAdmin() && !esEditor()) {
        header('Location: ../error.php');
        exit;
    }
}

function usuarioActual($pdo) {
    if (!estaAutenticado()) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch();
}

function cambiarContrasena($pdo, $usuario_id, $contrasena_actual, $contrasena_nueva) {
    $usuario = usuarioActual($pdo);
    if (!$usuario) {
        return ['error' => 'Usuario no encontrado.'];
    }
    
    if (!password_verify($contrasena_actual, $usuario['password_hash'])) {
        return ['error' => 'La contraseña actual es incorrecta.'];
    }
    
    if (strlen($contrasena_nueva) < 4) {
        return ['error' => 'La nueva contraseña debe tener al menos 4 caracteres.'];
    }
    
    $hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    if ($stmt->execute([$hash, $usuario_id])) {
        return ['success' => 'Contraseña actualizada correctamente.'];
    }
    return ['error' => 'Error al actualizar la contraseña.'];
}
?>