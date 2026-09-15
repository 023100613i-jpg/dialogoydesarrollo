<?php
// Funciones helper para el CRUD de reportajes

function obtenerReportajes($pdo, $limite = null) {
    $sql = "SELECT r.*, 
                   CONCAT(u.nombres, ' ', u.ap_paterno, ' ', COALESCE(u.ap_materno, '')) as usuario_nombre,
                   CONCAT(a.nombres, ' ', a.ap_paterno) as autor_nombre
            FROM reportajes r 
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN autores a ON r.autor_id = a.id
            ORDER BY r.fecha_publicacion DESC";
    
    if ($limite) {
        $sql .= " LIMIT " . (int)$limite;
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function obtenerReportajePorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT r.*, 
                                  CONCAT(u.nombres, ' ', u.ap_paterno, ' ', COALESCE(u.ap_materno, '')) as usuario_nombre,
                                  CONCAT(a.nombres, ' ', a.ap_paterno) as autor_nombre
                           FROM reportajes r 
                           LEFT JOIN usuarios u ON r.usuario_id = u.id
                           LEFT JOIN autores a ON r.autor_id = a.id 
                           WHERE r.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function crearReportaje($pdo, $datos) {
    $sql = "INSERT INTO reportajes 
            (titulo, resumen_corto, desarrollo, foto_principal, pdf_adjunto, 
             fecha_publicacion, es_destacado, estado, autor_id, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $datos['titulo'],
            $datos['resumen_corto'],
            $datos['desarrollo'],
            $datos['foto_principal'],
            $datos['pdf_adjunto'] ?? '',
            $datos['fecha_publicacion'],
            $datos['es_destacado'],
            $datos['estado'] ?? 'publicado',
            $datos['autor_id'],
            $datos['usuario_id']
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function actualizarReportaje($pdo, $id, $datos) {
    $sql = "UPDATE reportajes SET 
            titulo = ?, resumen_corto = ?, desarrollo = ?, 
            foto_principal = ?, pdf_adjunto = ?, 
            fecha_publicacion = ?, es_destacado = ?, estado = ?, autor_id = ? 
            WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $datos['titulo'],
            $datos['resumen_corto'],
            $datos['desarrollo'],
            $datos['foto_principal'],
            $datos['pdf_adjunto'] ?? '',
            $datos['fecha_publicacion'],
            $datos['es_destacado'],
            $datos['estado'] ?? 'publicado',
            $datos['autor_id'],
            $id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}
function eliminarReportaje($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM reportajes WHERE id = ?");
    return $stmt->execute([$id]);
}

function obtenerAutores($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, 
                                    CONCAT(nombres, ' ', ap_paterno, ' ', COALESCE(ap_materno, '')) as nombre_completo 
                             FROM autores 
                             ORDER BY ap_paterno");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function obtenerUsuarios($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, 
                                    CONCAT(nombres, ' ', ap_paterno, ' ', COALESCE(ap_materno, '')) as nombre_completo 
                             FROM usuarios 
                             ORDER BY ap_paterno");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function obtenerTotalReportajes($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM reportajes");
    return $stmt->fetchColumn();
}

function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

function obtenerNombreAutor($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT CONCAT(nombres, ' ', ap_paterno, ' ', COALESCE(ap_materno, '')) as nombre_completo 
                               FROM autores WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ? $result['nombre_completo'] : 'Sin autor';
    } catch(PDOException $e) {
        return 'Sin autor';
    }
}

// ============================================================
// FUNCIÓN PARA BOLETINES (SOLO UNA VEZ)
// ============================================================
function obtenerBoletinPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT b.*, 
                                  CONCAT(u.nombres, ' ', u.ap_paterno) as usuario_nombre 
                           FROM boletines b 
                           LEFT JOIN usuarios u ON b.usuario_id = u.id 
                           WHERE b.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>