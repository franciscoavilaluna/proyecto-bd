<?php
require_once 'header.php';
require_once 'conexion.php';

if (!$es_admin) { echo "<div class='alert alert-danger'>Acceso denegado. No eres administrador.</div>"; require_once 'footer.php'; exit; }

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar'])) {
        $nombre = $_POST['nombre'];
        $id_linea = $_POST['id_linea'];
        $tiempo = $_POST['tiempo_espera'];
        $stmt = $pdo->prepare("INSERT INTO estacion (nombre, id_linea, tiempo_espera_segundos) VALUES (?,?,?)");
        $stmt->execute([$nombre, $id_linea, $tiempo]);
        echo "<div class='alert alert-success'>Estación agregada</div>";
    } elseif (isset($_POST['editar'])) {
        $id = $_POST['id_estacion'];
        $nombre = $_POST['nombre'];
        $id_linea = $_POST['id_linea'];
        $tiempo = $_POST['tiempo_espera'];
        $stmt = $pdo->prepare("UPDATE estacion SET nombre=?, id_linea=?, tiempo_espera_segundos=? WHERE id_estacion=?");
        $stmt->execute([$nombre, $id_linea, $tiempo, $id]);
        echo "<div class='alert alert-success'>Actualizado</div>";
    } elseif (isset($_GET['eliminar'])) {
        $id = $_GET['eliminar'];
        $pdo->prepare("DELETE FROM estacion WHERE id_estacion=?")->execute([$id]);
        echo "<div class='alert alert-warning'>Eliminado</div>";
    }
}

$estaciones = $pdo->query("SELECT e.*, l.nombre as linea_nombre FROM estacion e JOIN linea l ON e.id_linea = l.id_linea ORDER BY e.id_estacion")->fetchAll();
$lineas = $pdo->query("SELECT * FROM linea")->fetchAll();
?>

<h3><i class="fas fa-map-marker-alt me-2"></i>Gestión de Estaciones</h3>
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalEstacion">+ Nueva estación</button>

<table class="table table-bordered table-hover">
    <thead class="table-dark"><th>ID</th><th>Nombre</th><th>Línea</th><th>Tiempo espera (seg)</th><th>Acciones</th></tr>
    </thead>
    <tbody class="table-dark">
    <?php foreach ($estaciones as $e): ?>
    <tr>
        <td><?=$e['id_estacion']?></td>
        <td><?=htmlspecialchars($e['nombre'])?></td>
        <td><?=htmlspecialchars($e['linea_nombre'])?></td>
        <td><?=$e['tiempo_espera_segundos']?></td>
        <td>
            <button class="btn btn-sm btn-warning" onclick="editarEstacion(<?=htmlspecialchars(json_encode($e))?>)">Editar</button>
            <a href="?eliminar=<?=$e['id_estacion']?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal para agregar/editar -->
<div class="modal fade" id="modalEstacion" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Estación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_estacion" id="edit_id">
                <div class="mb-2"><label>Nombre</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
                <div class="mb-2"><label>Línea</label>
                    <select name="id_linea" id="edit_id_linea" class="form-select">
                        <?php foreach ($lineas as $l): ?>
                            <option value="<?=$l['id_linea']?>"><?=htmlspecialchars($l['nombre'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2"><label>Tiempo espera (seg)</label><input type="number" name="tiempo_espera" id="edit_tiempo" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" id="btnAgregar" class="btn btn-primary">Agregar</button>
                <button type="submit" name="editar" id="btnEditar" class="btn btn-warning" style="display:none">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarEstacion(e) {
    document.getElementById('edit_id').value = e.id_estacion;
    document.getElementById('edit_nombre').value = e.nombre;
    document.getElementById('edit_id_linea').value = e.id_linea;
    document.getElementById('edit_tiempo').value = e.tiempo_espera_segundos;
    document.getElementById('btnAgregar').style.display = 'none';
    document.getElementById('btnEditar').style.display = 'inline-block';
    new bootstrap.Modal(document.getElementById('modalEstacion')).show();
}
// Reiniciar modal cuando se cierra
document.getElementById('modalEstacion').addEventListener('hidden.bs.modal', function () {
    document.getElementById('edit_id').value = '';
    document.getElementById('btnAgregar').style.display = 'inline-block';
    document.getElementById('btnEditar').style.display = 'none';
});
</script>

<?php require_once 'footer.php'; ?>
