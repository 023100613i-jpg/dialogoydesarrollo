<?php
require_once '../includes/conexion.php';
require_once '../includes/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT foto FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if ($noticia) {
    if (!empty($noticia['foto']) && file_exists('../' . $noticia['foto'])) {
        unlink('../' . $noticia['foto']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php?mensaje=eliminado');
exit;