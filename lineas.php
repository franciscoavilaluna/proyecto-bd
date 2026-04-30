<?php
require_once 'header.php';
require_once 'conexion.php';

if (!$es_admin) {
    echo "<div class='alert alert-danger'>Acceso denegado. No eres administrador.</div>";
    require_once 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar'])) {
        $nombre = $_POST['nombre'];
        $stmt = $pdo->prepare("INSERT INTO linea (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        echo "<div class='alert alert-success'>Línea agregada</div>";
    } elseif (isset($_POST['editar'])) {
        $id = $_POST['id_linea'];
        $nombre = $_POST['nombre'];
        $stmt = $pdo->prepare("UPDATE linea SET nombre = ? WHERE id_linea = ?");
        $stmt->execute([$nombre, $id]);
        echo "<div class='alert alert-success'>Línea actualizada</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM linea WHERE id_linea = ?")->execute([$id]);
    echo "<div class='alert alert-warning'>Línea eliminada</div>";
}

$lineas = $pdo->query("SELECT * FROM linea ORDER BY id_linea")->fetchAll();
?>

<h3><i class="fas fa-chart-line me-2"></i>Gestión de Líneas</h3>
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalLinea">+ Nueva línea</button>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
    </thead>
    <tbody class="table-dark">
    <?php foreach ($lineas as $l): ?>
        <tr>
            <td><?= $l['id_linea'] ?></td>
            <td><?= htmlspecialchars($l['nombre']) ?></td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editarLinea(<?= htmlspecialchars(json_encode($l)) ?>)">Editar</button>
                <a href="?eliminar=<?= $l['id_linea'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="modalLinea" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Línea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_linea" id="edit_id">
                <div class="mb-2"><label>Nombre</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" id="btnAgregar" class="btn btn-primary">Agregar</button>
                <button type="submit" name="editar" id="btnEditar" class="btn btn-warning" style="display:none">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarLinea(data) {
    document.getElementById('edit_id').value = data.id_linea;
    document.getElementById('edit_nombre').value = data.nombre;
    document.getElementById('btnAgregar').style.display = 'none';
    document.getElementById('btnEditar').style.display = 'inline-block';
    new bootstrap.Modal(document.getElementById('modalLinea')).show();
}
document.getElementById('modalLinea').addEventListener('hidden.bs.modal', function () {
    document.getElementById('edit_id').value = '';
    document.getElementById('edit_nombre').value = '';
    document.getElementById('btnAgregar').style.display = 'inline-block';
    document.getElementById('btnEditar').style.display = 'none';
});
</script>

<?php require_once 'footer.php'; ?>
