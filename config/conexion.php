<?php
// ============================================
// CONEXIÓN A LA BASE DE DATOS - INFINITYFREE
// ============================================
$host     = 'sql310.infinityfree.com';
$dbname   = 'if0_42983965_revista_digital';
$username = 'if0_42983965';
$password = 'JKLI1785f';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ============================================
// CONSTANTE GLOBAL DE URL BASE
// ============================================
define('BASE_URL', '/');

// ============================================
// FUNCIONES DE FECHAS
// ============================================
function formatearFecha($fecha) {
    if (empty($fecha)) return '';
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $f = new DateTime($fecha);
    return $f->format('d') . ' de ' . $meses[$f->format('n')-1] . ' de ' . $f->format('Y');
}

function formatearFechaCorta($fecha) {
    if (empty($fecha)) return '';
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
              'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $f = new DateTime($fecha);
    return $f->format('d') . ' ' . $meses[$f->format('n')-1] . ' ' . $f->format('Y');
}

function formatearFechaMes($fecha) {
    if (empty($fecha)) return '';
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $f = new DateTime($fecha);
    return $meses[$f->format('n')-1] . ' ' . $f->format('d') . ', ' . $f->format('Y');
}

// ============================================
// FUNCIONES DE URLS E IMÁGENES
// ============================================

/**
 * Genera URL completa para una imagen o archivo
 */
function urlImagen($ruta) {
    if (empty($ruta)) return '';
    $ruta = ltrim($ruta, '/');
    return BASE_URL . $ruta;
}

/**
 * Alias de urlImagen() para PDFs
 */
function urlPdf($ruta) {
    if (empty($ruta)) return '#';
    $ruta = ltrim($ruta, '/');
    return BASE_URL . $ruta;
}

/**
 * Verifica si una imagen/archivo existe físicamente
 */
function imagenExiste($ruta) {
    if (empty($ruta)) return false;
    $ruta = ltrim($ruta, '/');
    $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . $ruta;
    return file_exists($rutaCompleta);
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function sanitizar($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

function obtenerAutores($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, CONCAT(nombres, ' ', ap_paterno) as nombre_completo FROM autores ORDER BY nombres");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function obtenerUsuarios($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, CONCAT(nombres, ' ', ap_paterno) as nombre_completo FROM usuarios ORDER BY nombres");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function obtenerReportajePorId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reportajes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

function obtenerBoletinPorId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM boletines WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

function crearReportaje($pdo, $datos) {
    try {
        $sql = "INSERT INTO reportajes (titulo, resumen_corto, desarrollo, foto_principal, 
                                        pdf_adjunto, fecha_publicacion, es_destacado, autor_id, 
                                        usuario_id, estado) 
                VALUES (:titulo, :resumen_corto, :desarrollo, :foto_principal, 
                        :pdf_adjunto, :fecha_publicacion, :es_destacado, :autor_id, 
                        :usuario_id, :estado)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($datos);
    } catch(PDOException $e) {
        return false;
    }
}

function actualizarReportaje($pdo, $id, $datos) {
    try {
        $sql = "UPDATE reportajes SET 
                titulo = :titulo, 
                resumen_corto = :resumen_corto, 
                desarrollo = :desarrollo, 
                foto_principal = :foto_principal, 
                pdf_adjunto = :pdf_adjunto, 
                fecha_publicacion = :fecha_publicacion, 
                es_destacado = :es_destacado, 
                autor_id = :autor_id,
                estado = :estado
                WHERE id = :id";
        $datos['id'] = $id;
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($datos);
    } catch(PDOException $e) {
        return false;
    }
}
?>