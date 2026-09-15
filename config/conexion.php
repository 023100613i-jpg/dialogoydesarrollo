<?php
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
    die("Error de conexión: " . $e->getMessage());
}

function formatearFecha($fecha) {
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $f = new DateTime($fecha);
    return $f->format('d') . ' de ' . $meses[$f->format('n')-1] . ' de ' . $f->format('Y');
}

function formatearFechaCorta($fecha) {
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
              'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $f = new DateTime($fecha);
    return $f->format('d') . ' ' . $meses[$f->format('n')-1] . ' ' . $f->format('Y');
}

function formatearFechaMes($fecha) {
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $f = new DateTime($fecha);
    return $meses[$f->format('n')-1] . ' ' . $f->format('d') . ', ' . $f->format('Y');
}
?>