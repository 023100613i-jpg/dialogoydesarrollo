<?php
// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'revista_digital';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// Función para obtener nombre completo de usuario
function obtenerNombreUsuario($pdo, $id) {
    $stmt = $pdo->prepare("SELECT CONCAT(nombres, ' ', ap_paterno, ' ', COALESCE(ap_materno, '')) as nombre_completo 
                           FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['nombre_completo'] : 'Usuario desconocido';
}

// Función para sanitizar datos
function sanitizar($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Función para obtener el rol del usuario
function obtenerRolUsuario($pdo, $id) {
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['rol'] : 'redactor';
}
?>