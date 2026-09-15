<?php
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

// Verificar autenticación
requireLogin();

// Obtener datos del usuario actual
$usuarioActual = usuarioActual($pdo);

// ============================================
// INICIALES CORREGIDAS (3 LETRAS)
// ============================================
$iniciales = '';
$nombreCompletoDashboard = '';

if ($usuarioActual) {
    // Construir nombre completo
    $nombreCompletoDashboard = trim(
        ($usuarioActual['nombres'] ?? '') . ' ' . 
        ($usuarioActual['ap_paterno'] ?? '') . ' ' . 
        ($usuarioActual['ap_materno'] ?? '')
    );
    
    // Si está vacío, usar el de sesión
    if (empty($nombreCompletoDashboard)) {
        $nombreCompletoDashboard = $_SESSION['usuario_nombre'] ?? 'Usuario';
    }
    
    // Calcular iniciales (máximo 3 letras)
    $partes = explode(' ', trim($nombreCompletoDashboard));
    $iniciales = '';
    foreach ($partes as $parte) {
        if (!empty($parte)) {
            $iniciales .= strtoupper(substr($parte, 0, 1));
        }
    }
    // Si solo tiene 1 o 2 iniciales, completar con primeras letras
    if (strlen($iniciales) < 2) {
        $iniciales = strtoupper(substr($nombreCompletoDashboard, 0, 2));
    }
}

// Obtener datos para KPI Cards
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalReportajes = $pdo->query("SELECT COUNT(*) FROM reportajes")->fetchColumn();
$totalNoticias = $pdo->query("SELECT COUNT(*) FROM noticias")->fetchColumn();
$totalBoletines = $pdo->query("SELECT COUNT(*) FROM boletines")->fetchColumn();
$totalVideos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$totalPodcasts = $pdo->query("SELECT COUNT(*) FROM podcasts")->fetchColumn();

// Verificar si existe tabla contactos
try {
    $totalContactosPendientes = $pdo->query("SELECT COUNT(*) FROM contactos WHERE estado_atencion = 'pendiente'")->fetchColumn();
} catch(PDOException $e) {
    $totalContactosPendientes = 0;
}

// Obtener últimos reportajes
$stmt = $pdo->query("SELECT r.*, 
                            CONCAT(u.nombres, ' ', u.ap_paterno, ' ', COALESCE(u.ap_materno, '')) as autor 
                     FROM reportajes r 
                     LEFT JOIN usuarios u ON r.usuario_id = u.id 
                     ORDER BY r.fecha_publicacion DESC 
                     LIMIT 5");
$ultimosReportajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para el gráfico mensual
$stmt = $pdo->query("SELECT MONTH(fecha_publicacion) as mes, COUNT(*) as total 
                     FROM reportajes 
                     WHERE YEAR(fecha_publicacion) = YEAR(CURDATE()) 
                     GROUP BY MONTH(fecha_publicacion) 
                     ORDER BY mes");
$datosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$valores = array_fill(0, 12, 0);
foreach ($datosGrafico as $dato) {
    $valores[$dato['mes'] - 1] = $dato['total'];
}
$labels = json_encode(array_slice($meses, 0, date('n')));
$data = json_encode(array_slice($valores, 0, date('n')));

// Obtener mensajes de contacto recientes
try {
    $stmt = $pdo->query("SELECT * FROM contactos ORDER BY fecha_envio DESC LIMIT 7");
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $contactos = [];
}

// Estadísticas de mineros
$totalMineros = 735;
$totalGobiernosRegionales = 43;
$totalAlcaldias = 692;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · DDP Noticias</title>
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ===== PALETA DDP NOTICIAS ===== */
        :root {
            --ddp-red: #c0392b;
            --ddp-red-dark: #a93226;
            --ddp-red-light: #fde8e6;
            --ddp-white: #ffffff;
            --ddp-bg: #f8f9fa;
            --ddp-text: #2c3e50;
            --ddp-text-secondary: #7f8c8d;
            --ddp-text-light: #95a5a6;
            --ddp-border: #ecf0f1;
            --ddp-border-dark: #d5d8dc;
            --ddp-success: #27ae60;
            --ddp-danger: #e74c3c;
            --ddp-warning: #f39c12;
            --ddp-shadow: 0 2px 12px rgba(0,0,0,0.06);
            --ddp-shadow-hover: 0 4px 24px rgba(0,0,0,0.12);
            --sidebar-width: 270px;
            --header-height: 70px;
            --ddp-radius: 10px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cabin', sans-serif;
            background: var(--ddp-bg);
            color: var(--ddp-text);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--ddp-white);
            border-right: 1px solid var(--ddp-border);
            padding: 1.5rem 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 2px solid var(--ddp-red);
        }
        .sidebar-brand img { height: 45px; width: auto; }
        .sidebar-brand span {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ddp-red);
            letter-spacing: -0.5px;
        }
        .sidebar-nav { list-style: none; padding: 1rem 0; }
        .sidebar-nav .nav-section {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ddp-text-secondary);
            padding: 1rem 1.5rem 0.4rem;
            font-weight: 600;
        }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.5rem;
            color: var(--ddp-text-secondary);
            transition: all 0.2s;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .sidebar-nav li a.active {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
            border-left-color: var(--ddp-red);
            font-weight: 600;
        }
        .sidebar-nav li a i { width: 20px; text-align: center; }
        .sidebar-nav li a .badge {
            margin-left: auto;
            background: var(--ddp-red);
            color: white;
            font-size: 0.6rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-weight: 600;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* ===== TOPBAR ===== */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            background: var(--ddp-white);
            border-bottom: 1px solid var(--ddp-border);
            padding: 0 2rem;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .topbar-left .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--ddp-text);
            cursor: pointer;
            padding: 0.25rem 0.5rem;
        }
        .topbar-left .breadcrumb {
            font-size: 0.85rem;
            color: var(--ddp-text-secondary);
        }
        .topbar-left .breadcrumb span {
            color: var(--ddp-red);
            font-weight: 600;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .topbar-right .btn-icon {
            background: none;
            border: none;
            font-size: 1.1rem;
            color: var(--ddp-text-secondary);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
            position: relative;
        }
        .topbar-right .btn-icon:hover { color: var(--ddp-red); }
        .topbar-right .btn-icon .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: var(--ddp-red);
            border-radius: 50%;
            border: 2px solid var(--ddp-white);
        }
        .topbar-right .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.3rem 0.75rem 0.3rem 0.3rem;
            border-radius: 2rem;
            transition: background 0.2s;
            position: relative;
            border: 1px solid transparent;
        }
        .topbar-right .user-menu:hover {
            background: var(--ddp-red-light);
            border-color: var(--ddp-red-light);
        }
        .topbar-right .user-menu .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ddp-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .topbar-right .user-menu .user-info {
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .topbar-right .user-menu .user-info .name {
            font-weight: 600;
            color: var(--ddp-text);
            font-size: 0.9rem;
        }
        .topbar-right .user-menu .user-info .role {
            font-size: 0.65rem;
            color: var(--ddp-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .topbar-right .user-menu .dropdown-arrow {
            font-size: 0.6rem;
            color: var(--ddp-text-secondary);
            margin-left: 0.25rem;
        }
        .topbar-right .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--ddp-white);
            border: 1px solid var(--ddp-border);
            border-radius: var(--ddp-radius);
            box-shadow: var(--ddp-shadow-hover);
            min-width: 200px;
            padding: 0.5rem 0;
            z-index: 100;
        }
        .topbar-right .user-dropdown.show {
            display: block;
        }
        .topbar-right .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1.25rem;
            color: var(--ddp-text-secondary);
            transition: all 0.2s;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .topbar-right .user-dropdown a:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .topbar-right .user-dropdown .divider {
            height: 1px;
            background: var(--ddp-border);
            margin: 0.3rem 0;
        }
        .topbar-right .user-dropdown .logout {
            color: var(--ddp-red);
            font-weight: 600;
        }
        .topbar-right .user-dropdown .logout:hover {
            background: var(--ddp-red-light);
            color: var(--ddp-red-dark);
        }

        /* ===== CONTENT AREA ===== */
        .content-area { padding: 2rem; max-width: 1440px; margin: 0 auto; }

        /* ===== HERO ===== */
        .hero-welcome {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 1.75rem 2.5rem;
            border: 1px solid var(--ddp-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            box-shadow: var(--ddp-shadow);
        }
        .hero-welcome .hero-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ddp-text);
        }
        .hero-welcome .hero-text h1 span { color: var(--ddp-red); }
        .hero-welcome .hero-text p {
            color: var(--ddp-text-secondary);
            font-size: 0.95rem;
        }
        .hero-welcome .hero-date {
            text-align: right;
            color: var(--ddp-text-secondary);
            font-size: 0.85rem;
        }
        .hero-welcome .hero-date strong { color: var(--ddp-text); }
        .hero-welcome .hero-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        .hero-welcome .hero-actions .btn {
            padding: 0.5rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .hero-welcome .hero-actions .btn-red {
            background: var(--ddp-red);
            color: white;
        }
        .hero-welcome .hero-actions .btn-red:hover {
            background: var(--ddp-red-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(192,57,43,0.3);
        }
        .hero-welcome .hero-actions .btn-outline {
            background: transparent;
            color: var(--ddp-text);
            border: 1px solid var(--ddp-border-dark);
        }
        .hero-welcome .hero-actions .btn-outline:hover {
            background: var(--ddp-bg);
            border-color: var(--ddp-text-secondary);
        }

        /* ===== KPI CARDS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .kpi-card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 1.25rem 1.5rem;
            border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
            transition: all 0.3s;
        }
        .kpi-card:hover {
            box-shadow: var(--ddp-shadow-hover);
            transform: translateY(-3px);
            border-color: var(--ddp-red-light);
        }
        .kpi-card .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.25rem;
        }
        .kpi-card .kpi-label {
            font-size: 0.8rem;
            color: var(--ddp-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 500;
        }
        .kpi-card .kpi-icon {
            width: 38px;
            height: 38px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--ddp-red);
            background: var(--ddp-red-light);
        }
        .kpi-card .kpi-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--ddp-text);
            line-height: 1.2;
        }
        .kpi-card .kpi-change {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--ddp-text-secondary);
            margin-top: 0.15rem;
        }
        .kpi-card .kpi-change .up { color: var(--ddp-success); }
        .kpi-card .kpi-change .down { color: var(--ddp-danger); }

        /* ===== MINEROS STATS ===== */
        /* ===== MINEROS STATS ===== */
.mineros-stats {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-bottom: 1.25rem;
    background: var(--ddp-red-light);
    border-radius: var(--ddp-radius);
    padding: 1.5rem 2rem;
    border-left: 4px solid var(--ddp-red);
    flex-wrap: wrap;
}
.mineros-stats .minero-stat {
    text-align: center;
    flex: 1;
    min-width: 120px;
    padding: 0.5rem;
}
.mineros-stats .minero-stat .number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--ddp-red);
    line-height: 1.2;
}
.mineros-stats .minero-stat .label {
    font-size: 0.7rem;
    color: var(--ddp-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}
.mineros-stats .minero-stat .sub-label {
    font-size: 0.65rem;
    color: var(--ddp-text-light);
}
.mineros-stats-divider {
    width: 1px;
    height: 50px;
    background: var(--ddp-border-dark);
    flex-shrink: 0;
}

        /* ===== GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }
        .col-12 { grid-column: span 12; }
        .col-6 { grid-column: span 6; }

        /* ===== CARDS ===== */
        .card {
            background: var(--ddp-white);
            border-radius: var(--ddp-radius);
            padding: 1.5rem;
            border: 1px solid var(--ddp-border);
            box-shadow: var(--ddp-shadow);
            transition: box-shadow 0.3s;
        }
        .card:hover { box-shadow: var(--ddp-shadow-hover); }
        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--ddp-border);
        }
        .card-head .card-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--ddp-text);
        }
        .card-head .card-title small {
            font-weight: 400;
            font-size: 0.8rem;
            color: var(--ddp-text-secondary);
        }
        .card-head .card-action {
            color: var(--ddp-red);
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: opacity 0.2s;
            text-decoration: none;
        }
        .card-head .card-action:hover { opacity: 0.8; }
        .card-head .card-action i { font-size: 0.7rem; }

        /* ===== REPORT LIST ===== */
        .report-list .report-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--ddp-border);
        }
        .report-list .report-item:last-child { border-bottom: none; }
        .report-list .report-item .report-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .report-list .report-item .report-info .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .report-list .report-item .report-info .status-dot.published { background: var(--ddp-success); }
        .report-list .report-item .report-info .status-dot.draft { background: var(--ddp-warning); }
        .report-list .report-item .report-info .report-title {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--ddp-text);
        }
        .report-list .report-item .report-info .report-meta {
            font-size: 0.75rem;
            color: var(--ddp-text-secondary);
        }
        .report-list .report-item .report-stats {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.8rem;
            color: var(--ddp-text-secondary);
        }
        .report-list .report-item .report-stats .stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .report-list .report-item .report-stats .stat i { font-size: 0.7rem; }
        .report-list .report-item .tag {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .report-list .report-item .tag.published {
            background: #d4edda;
            color: var(--ddp-success);
        }
        .report-list .report-item .tag.draft {
            background: #fff3cd;
            color: var(--ddp-warning);
        }

        /* ===== CHART ===== */
        .chart-container { position: relative; height: 220px; }

        /* ===== STATS FOOTER ===== */
        .stats-footer {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--ddp-border);
        }
        .stats-footer .stat-item .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--ddp-text-secondary);
            letter-spacing: 0.04em;
        }
        .stats-footer .stat-item .stat-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ddp-text);
        }
        .stats-footer .stat-item .stat-value .trend {
            font-size: 0.7rem;
            font-weight: 400;
        }
        .stats-footer .stat-item .stat-value .trend.up { color: var(--ddp-success); }
        .stats-footer .stat-item .stat-value .trend.down { color: var(--ddp-danger); }

        /* ===== TODO LIST ===== */
        .todo-list { list-style: none; }
        .todo-list .todo-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--ddp-border);
        }
        .todo-list .todo-item:last-child { border-bottom: none; }
        .todo-list .todo-item.done .todo-text {
            text-decoration: line-through;
            color: var(--ddp-text-secondary);
        }
        .todo-list .todo-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--ddp-red);
            cursor: pointer;
            flex-shrink: 0;
        }
        .todo-list .todo-item .todo-text {
            flex: 1;
            font-size: 0.9rem;
            color: var(--ddp-text);
        }
        .todo-list .todo-item .todo-badge {
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .todo-list .todo-item .todo-badge.urgent {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .todo-list .todo-item .todo-badge.soon {
            background: #fff3cd;
            color: var(--ddp-warning);
        }
        .todo-list .todo-item .todo-badge.later {
            background: var(--ddp-border);
            color: var(--ddp-text-secondary);
        }
        .todo-list .todo-item .todo-badge.done {
            background: #d4edda;
            color: var(--ddp-success);
        }

        /* ===== TABLE ===== */
        .table-wrapper { overflow-x: auto; }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .table thead th {
            text-align: left;
            padding: 0.6rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ddp-text-secondary);
            font-weight: 600;
            border-bottom: 2px solid var(--ddp-border);
        }
        .table tbody td {
            padding: 0.6rem 0.5rem;
            border-bottom: 1px solid var(--ddp-border);
            color: var(--ddp-text);
        }
        .table tbody tr:hover td { background: var(--ddp-red-light); }
        .table .tag {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .table .tag.pending {
            background: var(--ddp-red-light);
            color: var(--ddp-red);
        }
        .table .tag.attended {
            background: #d4edda;
            color: var(--ddp-success);
        }
        .view-all {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--ddp-red);
            font-weight: 500;
            font-size: 0.85rem;
            margin-top: 1rem;
            transition: opacity 0.2s;
            text-decoration: none;
        }
        .view-all:hover { opacity: 0.8; }

        /* ===== SUMMARY ===== */
        .summary-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }
        .summary-hero .summary-main {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .summary-hero .summary-main .icon {
            font-size: 2.5rem;
            color: var(--ddp-red);
        }
        .summary-hero .summary-main .info .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ddp-text);
        }
        .summary-hero .summary-main .info .number sup {
            font-size: 1rem;
            font-weight: 400;
            color: var(--ddp-text-secondary);
        }
        .summary-hero .summary-main .info .condition {
            font-size: 0.85rem;
            color: var(--ddp-text-secondary);
        }
        .summary-hero .summary-main .info .condition strong {
            color: var(--ddp-text);
        }
        .summary-hero .summary-date {
            text-align: right;
        }
        .summary-hero .summary-date h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ddp-text);
        }
        .summary-hero .summary-date p {
            font-size: 0.75rem;
            color: var(--ddp-text-secondary);
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .summary-stats .stat .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--ddp-text-secondary);
            letter-spacing: 0.04em;
        }
        .summary-stats .stat .value {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--ddp-text);
        }

        /* ===== ACTIVITY ===== */
        .activity-feed {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        .activity-feed .activity-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--ddp-border);
        }
        .activity-feed .activity-item:last-child { border-bottom: none; }
        .activity-feed .activity-item .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--ddp-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.7rem;
            flex-shrink: 0;
        }
        .activity-feed .activity-item .avatar.me { background: var(--ddp-text-secondary); }
        .activity-feed .activity-item .content { flex: 1; }
        .activity-feed .activity-item .content .text {
            font-size: 0.85rem;
            color: var(--ddp-text);
        }
        .activity-feed .activity-item .content .text strong { color: var(--ddp-red); }
        .activity-feed .activity-item .content .time {
            font-size: 0.65rem;
            color: var(--ddp-text-secondary);
        }
        .activity-input {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--ddp-border);
        }
        .activity-input input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--ddp-border);
            border-radius: 0.5rem;
            background: var(--ddp-bg);
            color: var(--ddp-text);
            font-family: inherit;
            font-size: 0.85rem;
        }
        .activity-input input:focus {
            outline: none;
            border-color: var(--ddp-red);
        }
        .activity-input button {
            background: var(--ddp-red);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .activity-input button:hover { background: var(--ddp-red-dark); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .col-6 { grid-column: span 12; }
            .mineros-stats { grid-template-columns: 1fr; gap: 0.75rem; }
            .mineros-stats-divider { display: none; }
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar-left .menu-toggle { display: block; }
            .hero-welcome {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }
            .hero-welcome .hero-actions { justify-content: center; flex-wrap: wrap; }
            .hero-welcome .hero-date { text-align: center; margin-top: 0.5rem; }
            .summary-hero { flex-direction: column; text-align: center; gap: 0.5rem; }
            .summary-hero .summary-date { text-align: center; }
            .topbar-right .user-menu .user-info { display: none; }
        }
        @media (max-width: 600px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .content-area { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .topbar-left .breadcrumb { display: none; }
            .stats-footer { grid-template-columns: repeat(2, 1fr); }
            .summary-stats { grid-template-columns: 1fr; }
            .report-list .report-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            .report-list .report-item .report-stats {
                width: 100%;
                justify-content: flex-start;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="assets/images/logo.png" alt="DDP Noticias" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2245%22 height=%2245%22%3E%3Crect width=%2245%22 height=%2245%22 fill=%22%23c0392b%22/%3E%3Ctext x=%228%22 y=%2232%22 font-size=%2228%22 fill=%22white%22%3ED%3C/text%3E%3C/svg%3E'">
        <span>DDP Noticias</span>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-section">Principal</li>
        <li><a href="dashboard/index.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        
        <li class="nav-section">Contenido</li>
        <li><a href="reportajes/index.php"><i class="fas fa-newspaper"></i> Reportajes</a></li>
        <li><a href="noticias/index.php"><i class="fas fa-rss"></i> Noticias</a></li>
        <li><a href="boletines/index.php"><i class="fas fa-file-pdf"></i> Boletines</a></li>
        <li><a href="podcast/index.php"><i class="fas fa-podcast"></i> Podcasts</a></li>
        <li><a href="videos/index.php"><i class="fas fa-video"></i> Videos</a></li>
        <li><a href="especiales/index.php"><i class="fas fa-star"></i> Especiales</a></li>
        
        <?php if (puedeGestionarUsuarios()): ?>
        <li class="nav-section">Usuarios</li>
        <li><a href="usuarios/index.php"><i class="fas fa-users"></i> Usuarios</a></li>
        <li><a href="autores/index.php"><i class="fas fa-user-edit"></i> Autores</a></li>
        <?php endif; ?>
        
        <?php if (puedeGestionarConfiguracion()): ?>
        <li class="nav-section">Configuración</li>
        <li><a href="fuentes/index.php"><i class="fas fa-link"></i> Fuentes</a></li>
        <li><a href="alianzas/index.php"><i class="fas fa-handshake"></i> Alianzas</a></li>
        <li><a href="redes-sociales/index.php"><i class="fas fa-share-alt"></i> Redes Sociales</a></li>
        <?php endif; ?>
        
        <li class="nav-section">Contacto</li>
        <li><a href="mensajes/index.php"><i class="fas fa-envelope"></i> Mensajes <span class="badge"><?php echo $totalContactosPendientes; ?></span></a></li>
    </ul>
</aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home" style="color: var(--ddp-text-secondary);"></i> / <span>Dashboard</span>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn-icon" title="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <?php if ($totalContactosPendientes > 0): ?>
                    <span class="notification-dot"></span>
                    <?php endif; ?>
                </button>
                
                <!-- MENÚ DE USUARIO MEJORADO -->
                <div class="user-menu" id="userMenu">
                    <div class="user-avatar"><?php echo $iniciales ?: 'U'; ?></div>
                    <div class="user-info">
                        <div class="name"><?php echo htmlspecialchars($nombreCompletoDashboard); ?></div>
                        <div class="role"><?php echo strtoupper($_SESSION['usuario_rol'] ?? 'Usuario'); ?></div>
                    </div>
                    <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="usuarios/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="content-area">
            <!-- HERO -->
            <section class="hero-welcome">
                <div class="hero-text">
                    <h1>Bienvenido, <span><?php echo htmlspecialchars($nombreCompletoDashboard); ?></span></h1>
                    <p>
                        <strong><?php echo $totalReportajes; ?></strong> reportajes · 
                        <strong><?php echo $totalNoticias; ?></strong> noticias · 
                        <strong><?php echo $totalBoletines; ?></strong> boletines · 
                        <strong><?php echo $totalVideos; ?></strong> videos · 
                        <strong><?php echo $totalPodcasts; ?></strong> podcasts
                    </p>
                </div>
                <div>
                    <div class="hero-date">
                        <i class="far fa-calendar-alt"></i> 
                        <strong><?php echo date('l, d \d\e F \d\e Y'); ?></strong>
                    </div>
                    <div class="hero-actions">
                        <a href="reportajes/crear.php" class="btn btn-red">
                            <i class="fas fa-plus"></i> Nuevo reportaje
                        </a>
                    </div>
                </div>
            </section>

            <!-- KPI CARDS -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-label">Usuarios</span>
                        <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($totalUsuarios); ?></div>
                    <div class="kpi-change"><i class="fas fa-user-check up"></i> Registrados</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-label">Reportajes</span>
                        <div class="kpi-icon"><i class="fas fa-newspaper"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($totalReportajes); ?></div>
                    <div class="kpi-change"><i class="fas fa-file up"></i> Publicados</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-label">Noticias</span>
                        <div class="kpi-icon"><i class="fas fa-rss"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($totalNoticias); ?></div>
                    <div class="kpi-change"><i class="fas fa-link up"></i> Enlazadas</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-label">Boletines</span>
                        <div class="kpi-icon"><i class="fas fa-file-pdf"></i></div>
                    </div>
                    <div class="kpi-value"><?php echo number_format($totalBoletines); ?></div>
                    <div class="kpi-change"><i class="fas fa-check up"></i> Activos</div>
                </div>
            </section>

            <!-- MINEROS STATS -->
<div class="mineros-stats">
    <div class="minero-stat">
        <div class="number"><?php echo $totalMineros; ?></div>
        <div class="label">MINEROS CON REINFO</div>
        <div class="sub-label">vigente o suspendido</div>
    </div>
    <div class="mineros-stats-divider"></div>
    <div class="minero-stat">
        <div class="number"><?php echo $totalAlcaldias; ?></div>
        <div class="label">ALCALDÍAS Y REGIDURÍAS</div>
        <div class="sub-label">postulaciones registradas</div>
    </div>
    <div class="mineros-stats-divider"></div>
    <div class="minero-stat">
        <div class="number"><?php echo $totalGobiernosRegionales; ?></div>
        <div class="label">GOBIERNOS REGIONALES</div>
        <div class="sub-label">candidatos postulan</div>
    </div>
</div>

            <!-- GRID -->
            <div class="grid">
                <!-- ÚLTIMOS REPORTAJES -->
                <section class="col-12 card">
                    <div class="card-head">
                        <div class="card-title">
                            Últimos Reportajes <small>· Contenido reciente</small>
                        </div>
                        <a href="reportajes/index.php" class="card-action">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="report-list">
                        <?php if (count($ultimosReportajes) > 0): ?>
                            <?php foreach ($ultimosReportajes as $reportaje): ?>
                            <div class="report-item">
                                <div class="report-info">
                                    <span class="status-dot <?php echo ($reportaje['estado'] ?? 'borrador') === 'publicado' ? 'published' : 'draft'; ?>"></span>
                                    <div>
                                        <div class="report-title"><?php echo htmlspecialchars($reportaje['titulo'] ?? 'Sin título'); ?></div>
                                        <div class="report-meta">
                                            <?php echo htmlspecialchars($reportaje['autor'] ?? 'Sin autor'); ?> · 
                                            <?php echo isset($reportaje['fecha_publicacion']) ? date('d/m/Y', strtotime($reportaje['fecha_publicacion'])) : 'Sin fecha'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="report-stats">
                                    <span class="stat">
                                        <i class="fas fa-star" style="color:<?php echo $reportaje['es_destacado'] ? 'var(--ddp-warning)' : 'var(--ddp-text-light)'; ?>"></i>
                                        <?php echo $reportaje['es_destacado'] ? 'Destacado' : 'Normal'; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="report-item">
                                <div class="report-info">
                                    <div class="report-title" style="color: var(--ddp-text-secondary);">
                                        <i class="fas fa-info-circle"></i> No hay reportajes
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- GRÁFICO MENSUAL -->
                <section class="col-6 card">
                    <div class="card-head">
                        <div class="card-title">
                            Reportajes por mes <small>· <?php echo date('Y'); ?></small>
                        </div>
                        <span class="card-action">
                            <i class="far fa-calendar-alt"></i> <?php echo date('F'); ?>
                        </span>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                    <div class="stats-footer">
                        <div class="stat-item">
                            <div class="stat-label">Total</div>
                            <div class="stat-value"><?php echo $totalReportajes; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Este mes</div>
                            <div class="stat-value">
                                <?php 
                                try {
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM reportajes WHERE MONTH(fecha_publicacion) = MONTH(CURDATE()) AND YEAR(fecha_publicacion) = YEAR(CURDATE())");
                                    echo $stmt->fetchColumn(); 
                                } catch(PDOException $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Destacados</div>
                            <div class="stat-value">
                                <?php 
                                try {
                                    echo $pdo->query("SELECT COUNT(*) FROM reportajes WHERE es_destacado = 1")->fetchColumn(); 
                                } catch(PDOException $e) {
                                    echo '0';
                                }
                                ?>
                                <span class="trend up"><i class="fas fa-arrow-up"></i></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Podcasts</div>
                            <div class="stat-value">
                                <?php echo $totalPodcasts; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- RESUMEN DEL DÍA -->
                <section class="col-6 card">
                    <div class="card-head">
                        <div class="card-title">
                            Resumen <small>· <?php echo date('d/m/Y'); ?></small>
                        </div>
                        <span class="card-action">
                            <i class="fas fa-calendar-check"></i> Hoy
                        </span>
                    </div>
                    <div class="summary-hero">
                        <div class="summary-main">
                            <span class="icon">📊</span>
                            <div class="info">
                                <div class="number">
                                    <?php echo $totalReportajes + $totalNoticias; ?><sup>publicaciones</sup>
                                </div>
                                <div class="condition">
                                    <strong>Total de contenidos</strong> · <?php echo date('l'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="summary-date">
                            <h5><?php echo date('l'); ?></h5>
                            <p><?php echo strtoupper(date('M d, Y')); ?></p>
                        </div>
                    </div>
                    <div class="summary-stats">
                        <div class="stat">
                            <div class="label">Reportajes</div>
                            <div class="value"><?php echo $totalReportajes; ?></div>
                        </div>
                        <div class="stat">
                            <div class="label">Videos</div>
                            <div class="value"><?php echo $totalVideos; ?></div>
                        </div>
                        <div class="stat">
                            <div class="label">Podcasts</div>
                            <div class="value"><?php echo $totalPodcasts; ?></div>
                        </div>
                    </div>
                </section>

                <!-- ACTIVIDAD RECIENTE -->
                <section class="col-12 card">
                    <div class="card-head">
                        <div class="card-title">
                            Actividad reciente <small>· Últimas acciones en el sistema</small>
                        </div>
                        <a href="#" class="card-action">
                            Ver historial <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="activity-feed">
                        <div class="activity-item">
                            <div class="avatar">A</div>
                            <div class="content">
                                <div class="text">
                                    📝 Nuevo reportaje publicado: 
                                    <strong>"<?php 
                                        try {
                                            $stmt = $pdo->query("SELECT titulo FROM reportajes ORDER BY fecha_publicacion DESC LIMIT 1");
                                            $titulo = $stmt->fetchColumn();
                                            echo htmlspecialchars(substr($titulo, 0, 40)) . '…';
                                        } catch(PDOException $e) {
                                            echo 'Sin reportajes';
                                        }
                                    ?>"</strong>
                                </div>
                                <div class="time">Hace 2 horas</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="avatar me">S</div>
                            <div class="content">
                                <div class="text">
                                    ✅ <strong><?php 
                                        try {
                                            echo $pdo->query("SELECT COUNT(*) FROM contactos WHERE estado_atencion = 'atendido'")->fetchColumn(); 
                                        } catch(PDOException $e) {
                                            echo '0';
                                        }
                                    ?></strong> 
                                    mensajes atendidos hoy
                                </div>
                                <div class="time">Hace 4 horas</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="avatar">L</div>
                            <div class="content">
                                <div class="text">
                                    📊 <strong><?php echo $totalReportajes; ?></strong> reportajes totales en el sistema
                                </div>
                                <div class="time">Hace 6 horas</div>
                            </div>
                        </div>
                    </div>
                    <div class="activity-input">
                        <input type="text" placeholder="Escribe una nota rápida…" id="activityInput">
                        <button onclick="addActivityNote()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        // ===== CHART =====
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const labels = <?php echo $labels; ?>;
        const data = <?php echo $data; ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Reportajes publicados',
                    data: data,
                    backgroundColor: 'rgba(192, 57, 43, 0.7)',
                    borderColor: 'rgba(192, 57, 43, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#7f8c8d',
                        },
                        grid: {
                            color: '#ecf0f1',
                        }
                    },
                    x: {
                        ticks: {
                            color: '#7f8c8d',
                        },
                        grid: { display: false }
                    }
                }
            }
        });

        // ===== SIDEBAR TOGGLE =====
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('menuToggle');
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // ===== USER DROPDOWN =====
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');

        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });

        // ===== ACTIVITY NOTE =====
        function addActivityNote() {
            const input = document.getElementById('activityInput');
            const text = input.value.trim();
            if (!text) return;

            const feed = document.querySelector('.activity-feed');
            const item = document.createElement('div');
            item.className = 'activity-item';
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            item.innerHTML = `
                <div class="avatar me">T</div>
                <div class="content">
                    <div class="text">📝 ${text}</div>
                    <div class="time">${timeStr}</div>
                </div>
            `;
            feed.appendChild(item);
            input.value = '';
            feed.scrollTop = feed.scrollHeight;
        }

        document.getElementById('activityInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') addActivityNote();
        });
    </script>
</body>
</html>