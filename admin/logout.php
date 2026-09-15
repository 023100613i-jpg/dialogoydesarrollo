<?php
require_once 'includes/auth.php';

cerrarSesion();
header('Location: login.php?mensaje=sesion_cerrada');
exit;
?>