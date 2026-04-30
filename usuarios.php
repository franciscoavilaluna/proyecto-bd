<?php
require_once 'header.php';
require_once 'conexion.php';

if (!$es_admin) {
    echo "<div class='alert alert-danger'>Acceso denegado. No eres administrador.</div>";
    require_once 'footer.php';
    exit;
}

// Procesar acciones
$mensaje = '';
$error = '';

// AGREGAR usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $puntos = (int)$_POST['puntos_reputacion'];
    $es_admin_val = isset($_POST['es_admin']) ? 1 : 0;

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos obligatorios deben llenarse.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, password, puntos_reputacion, es_admin) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $password, $puntos, $es_admin_val]);
            $mensaje = "Usuario agregado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al agregar: " . $e->getMessage();
        }
    }
}

// EDITAR usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = (int)$_POST['id_usuario'];
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $puntos = (int)$_POST['puntos_reputacion'];
    $es_admin_val = isset($_POST['es_admin']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE usuario SET nombre=?, email=?, puntos_reputacion=?, es_admin=? WHERE id_usuario=?");
        $stmt->execute([$nombre, $email, $puntos, $es_admin_val, $id]);

        // Si se envió nueva contraseña, actualizarla
        if (!empty($_POST['password'])) {
            $stmt2 = $pdo->prepare("UPDATE usuario SET password=? WHERE id_usuario=?");
            $stmt2->execute([$_POST['password'], $id]);
        }
        $mensaje = "Usuario actualizado correctamente.";
    } catch (PDOException $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}

// ELIMINAR usuario (con protección)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // No permitir eliminar el propio usuario logueado
    if ($id == $_SESSION['user_id']) {
        $error = "No puedes eliminarte a ti mismo.";
    } else {
        try {
            // Con ON DELETE CASCADE configurado en BD, no necesitas borrar validaciones manualmente.
            // Pero si aún no lo has hecho, puedes dejar esta línea comentada o ejecutarla como respaldo.
            // $pdo->prepare("DELETE FROM validacion WHERE id_usuario = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $mensaje = "Usuario eliminado permanentemente.";
        } catch (PDOException $e) {
            $error = "No se pudo eliminar el usuario: " . $e->getMessage();
        }
    }
}

// Obtener lista actualizada de usuarios
$usuarios = $pdo->query("SELECT * FROM usuario ORDER BY id_usuario")->fetchAll();
?>

<h3><i class="fas fa-users me-2"></i>Gestión de Usuarios</h3>

<?php if ($mensaje): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($mensaje) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Nuevo usuario</button>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Puntos</th><th>Admin</th><th>Acciones</th></tr>
        </thead>
        <tbody class="table-dark">
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id_usuario'] ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['puntos_reputacion'] ?></td>
                <td><?= $u['es_admin'] ? 'Sí' : 'No' ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)">Editar</button>
                    <?php if ($u['id_usuario'] != $_SESSION['user_id']): ?>
                        <a href="?eliminar=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar permanentemente a este usuario? Todas sus validaciones y reportes se borrarán.')">Eliminar</a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary" disabled title="No puedes eliminarte">Eliminar</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar/editar (igual que antes) -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" id="edit_id">
                <div class="mb-2"><label>Nombre</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
                <div class="mb-2"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                <div class="mb-2"><label>Contraseña (dejar en blanco para no cambiar)</label><input type="password" name="password" id="edit_password" class="form-control"></div>
                <div class="mb-2"><label>Puntos de reputación</label><input type="number" name="puntos_reputacion" id="edit_puntos" class="form-control" required></div>
                <div class="mb-2 form-check">
                    <input type="checkbox" name="es_admin" id="edit_admin" class="form-check-input">
                    <label class="form-check-label">Es administrador</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" id="btnAgregar" class="btn btn-primary">Agregar</button>
                <button type="submit" name="editar" id="btnEditar" class="btn btn-warning" style="display:none">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarUsuario(data) {
    document.getElementById('edit_id').value = data.id_usuario;
    document.getElementById('edit_nombre').value = data.nombre;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_puntos').value = data.puntos_reputacion;
    document.getElementById('edit_admin').checked = (data.es_admin == 1);
    document.getElementById('edit_password').value = '';
    document.getElementById('btnAgregar').style.display = 'none';
    document.getElementById('btnEditar').style.display = 'inline-block';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}
document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
    document.getElementById('edit_id').value = '';
    document.getElementById('edit_nombre').value = '';
    document.getElementById('edit_email').value = '';
    document.getElementById('edit_puntos').value = '';
    document.getElementById('edit_admin').checked = false;
    document.getElementById('edit_password').value = '';
    document.getElementById('btnAgregar').style.display = 'inline-block';
    document.getElementById('btnEditar').style.display = 'none';
});
</script>

<?php require_once 'footer.php'; ?>
