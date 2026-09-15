<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$estado = $_GET['estado'] ?? '';

if (!in_array($estado, ['publicado', 'archivado', 'borrador'])) {
    header('Location: index.php?mensaje=error');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE boletines SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    
    $mensaje = $estado === 'archivado' ? 'archivado' : ($estado === 'borrador' ? 'borrador' : 'publicado');
    header("Location: index.php?mensaje=$mensaje");
    exit;
} catch(PDOException $e) {
    header('Location: index.php?mensaje=error');
    exit;
}