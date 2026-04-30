<?php
require_once 'header.php';
require_once 'conexion.php';

if (!$es_admin) {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    require_once 'footer.php';
    exit;
}

if (isset($_GET['toggle_activo'])) {
    $id = $_GET['toggle_activo'];
    $pdo->prepare("UPDATE reporte SET activo = NOT activo WHERE id_reporte = ?")->execute([$id]);
    echo "<div class='alert alert-info'>Estado del reporte cambiado</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validar'])) {
    $id_reporte = $_POST['id_reporte'];
    $id_usuario = $_POST['id_usuario'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    $check = $pdo->prepare("SELECT * FROM validacion WHERE id_reporte = ? AND id_usuario = ?");
    $check->execute([$id_reporte, $id_usuario]);
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO validacion (id_usuario, id_reporte, estado) VALUES (?, ?, ?)");
        $stmt->execute([$id_usuario, $id_reporte, $estado]);
        echo "<div class='alert alert-success'>Validación registrada</div>";
    } else {
        echo "<div class='alert alert-warning'>Este usuario ya validó este reporte</div>";
    }
}

$reportes = $pdo->query("
    SELECT r.*, u.nombre as usuario, e.nombre as estacion,
           (SELECT COUNT(*) FROM validacion WHERE id_reporte = r.id_reporte AND estado = 1) as votos_positivos,
           (SELECT COUNT(*) FROM validacion WHERE id_reporte = r.id_reporte AND estado = 0) as votos_negativos
    FROM reporte r
    JOIN usuario u ON r.id_usuario = u.id_usuario
    JOIN estacion e ON r.id_estacion = e.id_estacion
    ORDER BY r.fecha_hora DESC
")->fetchAll();

$usuarios = $pdo->query("SELECT id_usuario, nombre FROM usuario ORDER BY nombre")->fetchAll();
?>

<h3><i class="fas fa-flag-checkered me-2"></i>Gestión de Reportes</h3>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr><th>ID</th><th>Estación</th><th>Categoría</th><th>Descripción</th><th>Usuario</th><th>Fecha</th><th>Votos (+/-)</th><th>Activo</th><th>Acciones</th></tr>
    </thead>
    <tbody class="table-dark">
    <?php foreach ($reportes as $r): ?>
        <tr>
            <td><?= $r['id_reporte'] ?></td>
            <td><?= htmlspecialchars($r['estacion']) ?></td>
            <td><?= htmlspecialchars($r['categoria']) ?></td>
            <td><?= htmlspecialchars($r['descripcion']) ?></td>
            <td><?= htmlspecialchars($r['usuario']) ?></td>
            <td><?= date('d/m H:i', strtotime($r['fecha_hora'])) ?></td>
            <td><?= $r['votos_positivos'] ?> / <?= $r['votos_negativos'] ?></td>
            <td><?= $r['activo'] ? 'SI' : 'NO' ?></td>
            <td>
                <a href="?toggle_activo=<?= $r['id_reporte'] ?>" class="btn btn-sm btn-warning">Cambiar estado</a>
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalValidar" data-id="<?= $r['id_reporte'] ?>" data-desc="<?= htmlspecialchars($r['descripcion']) ?>">Validar voto</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="modalValidar" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar validación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_reporte" id="validar_id_reporte">
                <div class="mb-2">
                    <label>Usuario que vota</label>
                    <select name="id_usuario" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Voto</label>
                    <select name="estado" class="form-select" required>
                        <option value="1">Útil</option>
                        <option value="0">No útil</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="validar" class="btn btn-primary">Registrar voto</button>
            </div>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById('modalValidar');
    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var desc = button.getAttribute('data-desc');
        document.getElementById('validar_id_reporte').value = id;
        modal.querySelector('.modal-title').textContent = 'Validar reporte: ' + desc;
    });
</script>

<?php require_once 'footer.php'; ?>
