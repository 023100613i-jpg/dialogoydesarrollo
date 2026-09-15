<?php
require_once '../includes/conexion.php';
require_once '../includes/auth.php';

// Verificar autenticación
requireLogin();

// SOLO ADMINISTRADORES
if (!esAdmin()) {
    header('Location: ../error.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// No permitir eliminar a sí mismo
if ($id == $_SESSION['usuario_id']) {
    header('Location: index.php?error=no_puedes_eliminarte');
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    if ($stmt->execute([$id])) {
        header('Location: index.php?mensaje=eliminado');
        exit;
    }
}

header('Location: index.php?error=error');
exit;
?>