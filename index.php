<?php
require_once 'header.php';
require_once 'conexion.php';

$total_reportes = $pdo->query("SELECT COUNT(*) FROM reporte WHERE activo = 1")->fetchColumn();

$reportes_24h = $pdo->query("SELECT COUNT(*) FROM reporte WHERE activo = 1 AND fecha_hora >= NOW() - INTERVAL 1 DAY")->fetchColumn();
$reportes_anterior = $pdo->query("SELECT COUNT(*) FROM reporte WHERE activo = 1 AND fecha_hora < NOW() - INTERVAL 1 DAY")->fetchColumn();
$incremento = $reportes_24h - $reportes_anterior;
$incremento_texto = ($incremento >= 0 ? "+$incremento" : "$incremento") . " en 24h";

$sql_recomendada = "
    SELECT l.nombre, AVG(log.afluencia_promedio) as afluencia_prom
    FROM log
    JOIN estacion e ON log.id_estacion = e.id_estacion
    JOIN linea l ON e.id_linea = l.id_linea
    WHERE log.hora_fecha >= NOW() - INTERVAL 1 HOUR
    GROUP BY l.id_linea
    ORDER BY afluencia_prom ASC
    LIMIT 1
";
$recomendada = $pdo->query($sql_recomendada)->fetch();
if ($recomendada) {
    $linea_nombre = $recomendada['nombre'];
    $afluencia_valor = round($recomendada['afluencia_prom'], 1);
    if ($afluencia_valor < 80) {
        $fluidez = 'Alta';
        $fluidez_clase = 'text-success';
    } elseif ($afluencia_valor < 150) {
        $fluidez = 'Moderada';
        $fluidez_clase = 'text-warning';
    } else {
        $fluidez = 'Alta saturación';
        $fluidez_clase = 'text-danger';
    }
} else {
    $sql_historica = "
        SELECT l.nombre, AVG(log.afluencia_promedio) as afluencia_prom
        FROM log
        JOIN estacion e ON log.id_estacion = e.id_estacion
        JOIN linea l ON e.id_linea = l.id_linea
        GROUP BY l.id_linea
        ORDER BY afluencia_prom ASC
        LIMIT 1
    ";
    $recomendada = $pdo->query($sql_historica)->fetch();
    $linea_nombre = $recomendada['nombre'] ?? 'Línea 9';
    $fluidez = 'Datos históricos';
    $fluidez_clase = 'text-info';
}

$total_estaciones = $pdo->query("SELECT COUNT(*) FROM estacion")->fetchColumn();

$l1_count = $pdo->query("SELECT COUNT(*) FROM estacion e JOIN linea l ON e.id_linea = l.id_linea WHERE l.nombre = 'Línea 1'")->fetchColumn();
$l9_count = $pdo->query("SELECT COUNT(*) FROM estacion e JOIN linea l ON e.id_linea = l.id_linea WHERE l.nombre = 'Línea 9'")->fetchColumn();

$reportes_recientes = $pdo->query("
    SELECT r.*, u.nombre as usuario, e.nombre as estacion
    FROM reporte r
    JOIN usuario u ON r.id_usuario = u.id_usuario
    JOIN estacion e ON r.id_estacion = e.id_estacion
    WHERE r.activo = 1
    ORDER BY r.fecha_hora DESC
    LIMIT 5
")->fetchAll();
?>

<style>
    .dashboard-card {
        border-radius: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        border: none;
        background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.4);
    }
    .stat-number {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .card-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
    .action-card {
        background-color: #1e293b;
        border: 1px solid #334155;
        border-radius: 1rem;
        transition: all 0.2s;
        height: 100%;
        text-align: center;
        padding: 1.5rem 1rem;
    }
    .action-card:hover {
        background-color: #2d3a4f;
        transform: translateY(-3px);
    }
    .action-icon {
        font-size: 2.8rem;
        margin-bottom: 1rem;
    }
    .btn-outline-custom {
        border-radius: 2rem;
        padding: 0.4rem 1.2rem;
        font-weight: 500;
    }
    .table-responsive {
        border-radius: 1rem;
        overflow: hidden;
    }
    .table-dark {
        background-color: #0f172a;
    }
    .table-dark th {
        background-color: #1e293b;
        border-bottom: 2px solid #334155;
    }
    h4 i {
        margin-right: 0.5rem;
    }
</style>

<div class="container mt-4">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="dashboard-card p-3 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">Reportes activos</div>
                        <div class="stat-number text-white"><?= $total_reportes ?></div>
                        <span class="badge bg-success bg-opacity-25 text-success mt-1 px-2 py-1"><?= $incremento_texto ?></span>
                    </div>
                    <i class="fas fa-exclamation-triangle card-icon text-warning"></i>
                </div>
                <hr class="my-2 bg-secondary">
                <div class="small text-muted mt-2">
                    <i class="fas fa-users me-1"></i> Incidentes reportados por usuarios
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card p-3 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">Línea recomendada</div>
                        <div class="stat-number text-white"><?= htmlspecialchars($linea_nombre) ?></div>
                        <span class="badge <?= $fluidez_clase ?> bg-opacity-25 px-2 py-1">Fluidez <?= $fluidez ?></span>
                    </div>
                    <i class="fas fa-check-circle card-icon text-success"></i>
                </div>
                <hr class="my-2 bg-secondary">
                <div class="small text-muted mt-2">
                    <i class="fas fa-chart-line me-1"></i> Basado en afluencia última hora
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card p-3 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">Estaciones totales</div>
                        <div class="stat-number text-white"><?= $total_estaciones ?></div>
                        <span class="text-info small fw-semibold"><?= $l1_count ?> L1 + <?= $l9_count ?> L9</span>
                    </div>
                    <i class="fas fa-subway card-icon text-primary"></i>
                </div>
                <hr class="my-2 bg-secondary">
                <div class="small text-muted mt-2">
                    <i class="fas fa-map-marked-alt me-1"></i> Monitoreadas en red
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3"><i class="fas fa-cogs text-secondary"></i> Gestión operativa</h4>
    <div class="row g-4 mb-5">
        <?php if ($es_admin): ?>
            <div class="col-md-3 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-map-marker-alt action-icon text-primary"></i>
                    <h5 class="mb-2">Estaciones</h5>
                    <a href="estaciones.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">Administrar</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-chart-line action-icon text-success"></i>
                    <h5 class="mb-2">Afluencia</h5>
                    <a href="afluencia.php" class="btn btn-outline-success btn-sm rounded-pill px-3">Gestionar logs</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-flag-checkered action-icon text-warning"></i>
                    <h5 class="mb-2">Reportes</h5>
                    <a href="reportes.php" class="btn btn-outline-warning btn-sm rounded-pill px-3">Validar</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-route action-icon text-info"></i>
                    <h5 class="mb-2">Mejor ruta</h5>
                    <a href="prediccion.php" class="btn btn-outline-info btn-sm rounded-pill px-3">Consultar</a>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-4 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-exclamation-triangle action-icon text-warning"></i>
                    <h5 class="mb-2">Reportar incidente</h5>
                    <a href="nuevo_reporte.php" class="btn btn-outline-warning btn-sm rounded-pill px-3">Crear</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-list action-icon text-primary"></i>
                    <h5 class="mb-2">Ver reportes cercanos</h5>
                    <a href="ver_reportes.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">Consultar</a>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="action-card">
                    <i class="fas fa-route action-icon text-info"></i>
                    <h5 class="mb-2">Mejor ruta</h5>
                    <a href="prediccion.php" class="btn btn-outline-info btn-sm rounded-pill px-3">Consultar</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mt-4 overflow-hidden">
        <div class="card-header bg-dark bg-opacity-75 text-white fw-semibold py-3">
            <i class="fas fa-bell me-2"></i> Reportes recientes
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="border-bottom border-secondary">
                            <th>Estación</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reportes_recientes) > 0): ?>
                            <?php foreach ($reportes_recientes as $rep): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($rep['estacion']) ?></td>
                                <td><span class="badge bg-secondary bg-opacity-50"><?= htmlspecialchars($rep['categoria']) ?></span></td>
                                <td><?= htmlspecialchars($rep['descripcion']) ?></td>
                                <td><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($rep['usuario']) ?></td>
                                <td><?= date('d/m H:i', strtotime($rep['fecha_hora'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay reportes activos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
