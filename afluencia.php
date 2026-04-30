<?php
require_once 'header.php';
require_once 'conexion.php';

if (!$es_admin) {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    require_once 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $id_estacion = $_POST['id_estacion'];
    $hora_fecha = $_POST['hora_fecha'];
    $afluencia = $_POST['afluencia_promedio'];
    $stmt = $pdo->prepare("INSERT INTO log (id_estacion, hora_fecha, afluencia_promedio) VALUES (?, ?, ?)");
    $stmt->execute([$id_estacion, $hora_fecha, $afluencia]);
    echo "<div class='alert alert-success'>Registro agregado</div>";
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM log WHERE id_historico = ?")->execute([$id]);
    echo "<div class='alert alert-warning'>Registro eliminado</div>";
}

$logs = $pdo->query("
    SELECT l.*, e.nombre as estacion 
    FROM log l 
    JOIN estacion e ON l.id_estacion = e.id_estacion 
    ORDER BY l.hora_fecha DESC
")->fetchAll();

$estaciones = $pdo->query("SELECT id_estacion, nombre FROM estacion ORDER BY nombre")->fetchAll();
?>

<h3><i class="fas fa-chart-line me-2"></i>Monitoreo de Afluencia</h3>
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalLog">+ Registrar afluencia</button>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr><th>ID</th><th>Estación</th><th>Fecha/Hora</th><th>Afluencia promedio</th><th>Acciones</th></tr>
    </thead>
    <tbody class="table-dark">
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['id_historico'] ?></td>
            <td><?= htmlspecialchars($log['estacion']) ?></td>
            <td><?= $log['hora_fecha'] ?></td>
            <td><?= $log['afluencia_promedio'] ?></td>
            <td>
                <a href="?eliminar=<?= $log['id_historico'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="modalLog" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo registro de afluencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label>Estación</label>
                    <select name="id_estacion" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($estaciones as $e): ?>
                            <option value="<?= $e['id_estacion'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Fecha y hora</label>
                    <input type="datetime-local" name="hora_fecha" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Afluencia promedio (0-500)</label>
                    <input type="number" step="0.01" name="afluencia_promedio" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
