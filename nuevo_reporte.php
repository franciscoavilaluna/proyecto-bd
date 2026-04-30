<?php
require_once 'header.php';
require_once 'conexion.php';

$mensaje = '';
$error = '';

$estaciones = $pdo->query("SELECT id_estacion, nombre FROM estacion ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_estacion = $_POST['id_estacion'] ?? 0;
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($id_estacion && $categoria && $descripcion) {
        $stmt = $pdo->prepare("INSERT INTO reporte (id_usuario, id_estacion, categoria, descripcion, fecha_hora, activo) VALUES (?, ?, ?, ?, NOW(), 1)");
        $stmt->execute([$_SESSION['user_id'], $id_estacion, $categoria, $descripcion]);
        $mensaje = "Reporte enviado correctamente. Gracias por colaborar.";
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>

<h3 class="fw-semibold mb-4"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Nuevo reporte</h3>

<?php if ($mensaje): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Estación *</label>
                <select name="id_estacion" class="form-select" required>
                    <option value="">Selecciona una estación</option>
                    <?php foreach ($estaciones as $e): ?>
                        <option value="<?= $e['id_estacion'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Categoría *</label>
                <select name="categoria" class="form-select" required>
                    <option value="">Selecciona una categoría</option>
                    <option value="Limpieza">Limpieza</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Retrasos">Retrasos / Demoras</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Descripción del incidente *</label>
                <textarea name="descripcion" rows="4" class="form-control" placeholder="Describe detalladamente lo que ocurre..." required></textarea>
            </div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Enviar reporte</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
