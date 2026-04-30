<?php
require_once 'header.php';
require_once 'conexion.php';

// Procesar voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['votar'])) {
    $id_reporte = $_POST['id_reporte'];
    $estado = (int)$_POST['estado'];
    $id_usuario = $_SESSION['user_id'];

    $check = $pdo->prepare("SELECT * FROM validacion WHERE id_reporte = ? AND id_usuario = ?");
    $check->execute([$id_reporte, $id_usuario]);
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO validacion (id_usuario, id_reporte, estado) VALUES (?, ?, ?)");
        $stmt->execute([$id_usuario, $id_reporte, $estado]);
        $mensaje_voto = "Gracias por tu validación.";
    } else {
        $mensaje_voto = "Ya has votado este reporte anteriormente.";
    }
}

// Filtros
$filtro_estacion = $_GET['estacion'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';

$sql = "SELECT r.*, u.nombre as usuario, e.nombre as estacion,
               (SELECT COUNT(*) FROM validacion WHERE id_reporte = r.id_reporte AND estado = 1) as votos_pos,
               (SELECT COUNT(*) FROM validacion WHERE id_reporte = r.id_reporte AND estado = 0) as votos_neg
        FROM reporte r
        JOIN usuario u ON r.id_usuario = u.id_usuario
        JOIN estacion e ON r.id_estacion = e.id_estacion
        WHERE r.activo = 1";
$params = [];
if ($filtro_estacion) {
    $sql .= " AND e.id_estacion = ?";
    $params[] = $filtro_estacion;
}
if ($filtro_categoria) {
    $sql .= " AND r.categoria = ?";
    $params[] = $filtro_categoria;
}
$sql .= " ORDER BY r.fecha_hora DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reportes = $stmt->fetchAll();

$estaciones = $pdo->query("SELECT id_estacion, nombre FROM estacion ORDER BY nombre")->fetchAll();
$categorias = $pdo->query("SELECT DISTINCT categoria FROM reporte ORDER BY categoria")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Reportes activos</h3>
    <a href="nuevo_reporte.php" class="btn btn-warning"><i class="fas fa-plus"></i> Nuevo reporte</a>
</div>

<?php if (isset($mensaje_voto)): ?>
    <div class="alert alert-info alert-dismissible fade show"><?= $mensaje_voto ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Estación</label>
                <select name="estacion" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($estaciones as $e): ?>
                        <option value="<?= $e['id_estacion'] ?>" <?= ($filtro_estacion == $e['id_estacion']) ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= htmlspecialchars($c['categoria']) ?>" <?= ($filtro_categoria == $c['categoria']) ? 'selected' : '' ?>><?= htmlspecialchars($c['categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (count($reportes) === 0): ?>
    <div class="alert alert-secondary">No hay reportes activos con los filtros seleccionados.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($reportes as $rep): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-dashboard p-3 shadow-sm h-100">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($rep['estacion']) ?></h5>
                        <span class="badge bg-secondary"><?= htmlspecialchars($rep['categoria']) ?></span>
                    </div>
                    <p class="small text-muted mt-2"><?= nl2br(htmlspecialchars($rep['descripcion'])) ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small><i class="fas fa-user"></i> <?= htmlspecialchars($rep['usuario']) ?></small>
                        <small><i class="fas fa-calendar-alt"></i> <?= date('d/m H:i', strtotime($rep['fecha_hora'])) ?></small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-success"><i class="fas fa-thumbs-up"></i> <?= $rep['votos_pos'] ?></span>
                            <span class="text-danger ms-2"><i class="fas fa-thumbs-down"></i> <?= $rep['votos_neg'] ?></span>
                        </div>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id_reporte" value="<?= $rep['id_reporte'] ?>">
                            <button type="submit" name="votar" value="1" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Consideras útil este reporte?')"><i class="fas fa-thumbs-up"></i> Útil</button>
                            <button type="submit" name="votar" value="0" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Consideras que este reporte no es útil?')"><i class="fas fa-thumbs-down"></i> No útil</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
