<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
$usuario_nombre = $_SESSION['usuario'];
$es_admin = $_SESSION['es_admin'] ?? false;

$current_page = basename($_SERVER['PHP_SELF']);
$page_titles = [
    'index.php' => 'Dashboard',
    'estaciones.php' => 'Estaciones',
    'lineas.php' => 'Líneas',
    'afluencia.php' => 'Monitoreo de Afluencia',
    'reportes.php' => 'Gestión de Reportes',
    'usuarios.php' => 'Usuarios',
    'nuevo_reporte.php' => 'Nuevo Reporte',
    'ver_reportes.php' => 'Reportes Activos'
];
$page_title = $page_titles[$current_page] ?? 'Metro-Waze';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metro-Waze | <?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ----- SOLO MODO OSCURO (hardcodeado) ----- */
    body {
        background: #121826;
        color: #e2e8f0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        font-family: 'Segoe UI', system-ui;
    }
    .navbar-metro {
        background: #0f172a;
    }
    .navbar-brand, .user-name {
        color: white !important;
    }
    .card-dashboard, .card, .card-header, .modal-content {
        background: #1e293b;
        color: #e2e8f0;
        border-color: #334155;
    }
    .card-dashboard {
        border: none;
        border-radius: 1rem;
        transition: 0.2s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .card-dashboard:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.5);
    }
    .card-dashboard .text-muted,
    .text-muted {
        color: #94a3b8 !important;
    }
    .card-dashboard hr, .modal-content hr {
        border-color: #334155;
    }
    footer {
        background: #1e293b;
        color: #94a3b8;
        border-top: 1px solid #334155;
        text-align: center;
        padding: 1rem;
        margin-top: auto;
    }
    h1, h2, h3, h4, h5, h6, p, span, div:not(.modal-content):not(.card-dashboard) {
        color: #e2e8f0;
    }
    .text-dark, .text-muted {
        color: #94a3b8 !important;
    }
    /* ----- TABLAS (fuerza modo oscuro) ----- */
    .table, .table-hover, .table-bordered, .table-striped {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }
    .table th, .table td {
        border-color: #334155 !important;
        background-color: inherit !important;
    }
    .table thead th, .table-dark thead th {
        background-color: #0f172a !important;
        color: #f1f5f9 !important;
        border-bottom-color: #334155 !important;
    }
    .table-hover tbody tr:hover {
        background-color: #334155 !important;
        color: white !important;
    }
    /* Botones */
    .btn-outline-primary {
        color: #93c5fd;
        border-color: #3b82f6;
    }
    .btn-outline-primary:hover {
        background: #2563eb;
        color: white;
    }
    .btn-outline-success {
        color: #86efac;
        border-color: #22c55e;
    }
    .btn-outline-warning {
        color: #fde047;
        border-color: #eab308;
    }
    .breadcrumb {
        background: transparent;
    }
    .breadcrumb-item a {
        color: #f39c12;
        text-decoration: none;
    }
    .breadcrumb-item.active {
        color: #94a3b8;
    }
    .form-control, .form-select, .input-group-text {
        background-color: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }
    .form-control:focus, .form-select:focus {
        background-color: #1e293b;
        color: white;
        border-color: #f39c12;
        box-shadow: 0 0 0 0.2rem rgba(243,156,18,0.25);
    }
    .modal-header, .modal-footer {
        border-color: #334155;
    }
    .btn-close {
        filter: invert(1);
    }
    .alert-success {
        background-color: #065f46;
        color: #d1fae5;
        border-color: #047857;
    }
    .alert-danger {
        background-color: #991b1b;
        color: #fee2e2;
        border-color: #dc2626;
    }
    .alert-warning {
        background-color: #92400e;
        color: #ffedd5;
        border-color: #d97706;
    }
    .alert-info {
        background-color: #1e3a8a;
        color: #dbeafe;
        border-color: #2563eb;
    }
    a:not(.btn) {
        color: #90cdf4;
    }
    a:not(.btn):hover {
        color: #f39c12;
    }
    .container-flex {
        flex: 1;
    }
</style>
</head>
<body>
<nav class="navbar navbar-metro navbar-expand-lg px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-subway me-2"></i> Metro-Waze
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="user-name">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($usuario_nombre) ?>
                <?php if ($es_admin): ?>
                    <span class="badge bg-warning text-dark ms-1">Admin</span>
                <?php endif; ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container container-flex py-4">
    <?php if ($current_page != 'index.php'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
                </ol>
            </nav>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    <?php endif; ?>
