<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Obtener imagen para eliminarla
        $stmt = $pdo->prepare("SELECT imagen FROM podcasts WHERE id = ?");
        $stmt->execute([$id]);
        $podcast = $stmt->fetch();
        
        if ($podcast && !empty($podcast['imagen']) && file_exists('../' . $podcast['imagen'])) {
            unlink('../' . $podcast['imagen']);
        }
        
        // Eliminar registro
        $stmt = $pdo->prepare("DELETE FROM podcasts WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: index.php?mensaje=eliminado');
        exit;
    } catch(PDOException $e) {
        header('Location: index.php?mensaje=error');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}