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
    // Si no hay logs en la última hora, mostrar línea con menor afluencia histórica general
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

<div class="row g-4 mb-5"
    <div class="col-md-4">
        <div class="card-dashboard p-3 shadow-sm">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-uppercase small text-muted">Reportes activos</div>
                    <div class="h1 fw-bold mb-0"><?= $total_reportes ?></div>
                    <span class="text-success small"><?= $incremento_texto ?></span>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
            </div>
            <hr>
            <div class="small text-muted">Incidentes reportados por usuarios</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard p-3 shadow-sm">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-uppercase small text-muted">Línea recomendada</div>
                    <div class="h1 fw-bold mb-0"><?= htmlspecialchars($linea_nombre) ?></div>
                    <span class="<?= $fluidez_clase ?> small">Fluidez <?= $fluidez ?></span>
                </div>
                <i class="fas fa-check-circle fa-2x text-success"></i>
            </div>
            <hr>
            <div class="small text-muted">Basado en afluencia última hora</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard p-3 shadow-sm">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-uppercase small text-muted">Estaciones totales</div>
                    <div class="h1 fw-bold mb-0"><?= $total_estaciones ?></div>
                    <span class="text-info small"><?= $l1_count ?> L1 + <?= $l9_count ?> L9</span>
                </div>
                <i class="fas fa-subway fa-2x text-primary"></i>
            </div>
            <hr>
            <div class="small text-muted">Monitoreadas en red</div>
        </div>
    </div>
</div>

<h4 class="mb-3"><i class="fas fa-cogs me-2"></i>Gestión operativa</h4>
<div class="row g-4 mb-5">
    <?php if ($es_admin): ?>
    <div class="col-md-3">
        <div class="card-dashboard p-3 text-center h-100 shadow-sm">
            <i class="fas fa-map-marker-alt fa-3x text-primary mb-2"></i>
            <h5>Estaciones</h5>
            <a href="estaciones.php" class="btn btn-sm btn-outline-primary mt-2">Administrar</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard p-3 text-center h-100 shadow-sm">
            <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
            <h5>Afluencia</h5>
            <a href="afluencia.php" class="btn btn-sm btn-outline-success mt-2">Gestionar logs</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard p-3 text-center h-100 shadow-sm">
            <i class="fas fa-flag-checkered fa-3x text-warning mb-2"></i>
            <h5>Reportes</h5>
            <a href="reportes.php" class="btn btn-sm btn-outline-warning mt-2">Validar reportes</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-dashboard p-3 text-center h-100 shadow-sm">
            <i class="fas fa-route fa-3x text-info mb-2"></i>
            <h5>Mejor ruta</h5>
            <a href="prediccion.php" class="btn btn-sm btn-outline-info mt-2">Consultar</a>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-4">
        <div class="card-dashboard p-3 text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-2"></i>
            <h5>Reportar incidente</h5>
            <a href="nuevo_reporte.php" class="btn btn-sm btn-outline-warning">Crear reporte</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard p-3 text-center">
            <i class="fas fa-list fa-3x text-primary mb-2"></i>
            <h5>Ver reportes cercanos</h5>
            <a href="ver_reportes.php" class="btn btn-sm btn-outline-primary">Consultar</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard p-3 text-center">
            <i class="fas fa-route fa-3x text-info mb-2"></i>
            <h5>Mejor ruta</h5>
            <a href="prediccion.php" class="btn btn-sm btn-outline-info">Consultar</a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>
