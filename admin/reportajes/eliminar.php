<?php
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    // Verificar si el reportaje existe
    $reportaje = obtenerReportajePorId($pdo, $id);
    if ($reportaje) {
        if (eliminarReportaje($pdo, $id)) {
            header('Location: index.php?mensaje=eliminado&tipo=success');
            exit;
        }
    }
}

header('Location: index.php?mensaje=error&tipo=danger');
exit;
?>