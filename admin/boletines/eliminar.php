<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

// Solo admin y editor pueden eliminar
if (!esAdmin() && !esEditor()) {
    header('Location: index.php?error=no_autorizado');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM boletines WHERE id = ?");
    $stmt->execute([$id]);
    $boletin = $stmt->fetch();
    
    if ($boletin) {
        // Eliminar imagen si existe
        if (!empty($boletin['foto_portada']) && file_exists('../' . $boletin['foto_portada'])) {
            unlink('../' . $boletin['foto_portada']);
        }
        
        // Eliminar PDF si existe
        if (!empty($boletin['archivo_pdf']) && file_exists('../' . $boletin['archivo_pdf'])) {
            unlink('../' . $boletin['archivo_pdf']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM boletines WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: index.php?mensaje=eliminado');
            exit;
        }
    }
}

header('Location: index.php?mensaje=error');
exit;