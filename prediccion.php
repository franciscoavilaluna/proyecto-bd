<?php
require_once 'header.php';
require_once 'conexion.php';

// Lista de estaciones (puedes mantenerla hardcodeada o extraerla de la BD)
$lista_estaciones = [
    'Balbuena', 'Balderas', 'Boulevard Puerto Aéreo', 'Candelaria',
    'Centro Médico', 'Chabacano', 'Chapultepec', 'Chilpancingo',
    'Ciudad Deportiva', 'Cuauhtémoc', 'Gómez Farías', 'Insurgentes',
    'Isabel la Católica', 'Jamaica', 'Juanacatlán', 'Lázaro Cárdenas',
    'Merced', 'Mixiuhca', 'Moctezuma', 'Observatorio', 'Pantitlán',
    'Patriotismo', 'Pino Suárez', 'Puebla', 'Salto del Agua',
    'San Lázaro', 'Sevilla', 'Tacubaya', 'Velódromo', 'Zaragoza'
];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-route text-warning"></i> Consultar mejor ruta (Línea 1 vs Línea 9)</h2>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="prediccion.php">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Estación de origen *</label>
                        <select name="origen" class="form-select" required>
                            <option value="">Selecciona origen...</option>
                            <?php foreach ($lista_estaciones as $est): ?>
                                <option value="<?= htmlspecialchars($est) ?>"><?= htmlspecialchars($est) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Estación de destino *</label>
                        <select name="destino" class="form-select" required>
                            <option value="">Selecciona destino...</option>
                            <?php foreach ($lista_estaciones as $est): ?>
                                <option value="<?= htmlspecialchars($est) ?>"><?= htmlspecialchars($est) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="fas fa-search"></i> Calcular
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $origen   = trim($_POST['origen'] ?? '');
        $destino  = trim($_POST['destino'] ?? '');

        if (empty($origen) || empty($destino)) {
            echo '<div class="alert alert-danger mt-4">Por favor selecciona origen y destino.</div>';
        } else {
            // Tiempos ideales (simulados)
            $tiempo_ideal_L1 = 45;
            $tiempo_ideal_L9 = 38;

            // Contar incidentes activos en estaciones de la Línea 1 (id_linea = 1)
            $sql_l1 = "SELECT COUNT(*) 
                       FROM reporte r
                       JOIN estacion e ON r.id_estacion = e.id_estacion
                       WHERE e.id_linea = 1
                         AND r.categoria IN ('Retrasos / Demoras', 'Mantenimiento', 'retraso', 'incidente', 'Limpieza', 'Seguridad')
                         AND r.activo = 1";
            $stmt_l1 = $pdo->query($sql_l1);
            $incidentes_l1 = (int) $stmt_l1->fetchColumn();

            // Contar incidentes activos en estaciones de la Línea 9 (id_linea = 9)
            $sql_l9 = "SELECT COUNT(*) 
                       FROM reporte r
                       JOIN estacion e ON r.id_estacion = e.id_estacion
                       WHERE e.id_linea = 9
                         AND r.categoria IN ('Retrasos / Demoras', 'Mantenimiento', 'retraso', 'incidente', 'Limpieza', 'Seguridad')
                         AND r.activo = 1";
            $stmt_l9 = $pdo->query($sql_l9);
            $incidentes_l9 = (int) $stmt_l9->fetchColumn();

            // Calcular tiempos reales (cada incidente suma 12 minutos)
            $tiempo_real_L1 = $tiempo_ideal_L1 + ($incidentes_l1 * 12);
            $tiempo_real_L9 = $tiempo_ideal_L9 + ($incidentes_l9 * 12);

            // Mostrar resultados
            echo '<div class="card mt-4 shadow-sm border-0"><div class="card-body">';
            echo '<h4 class="text-warning mb-3"><i class="fas fa-chart-simple"></i> Resultado del análisis</h4>';
            echo '<p class="text-white">Viaje analizado: <strong>' . htmlspecialchars($origen) . '</strong> → <strong>' . htmlspecialchars($destino) . '</strong></p>';

            if ($tiempo_real_L1 <= $tiempo_real_L9) {
                echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Te recomendamos la <strong>Línea 1</strong> ({$tiempo_real_L1} mins aprox).<br>";
                echo "<span class='small'>Línea 9: {$incidentes_l9} incidentes ({$tiempo_real_L9} mins).</span></div>";
            } else {
                echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Te recomendamos la <strong>Línea 9</strong> ({$tiempo_real_L9} mins aprox).<br>";
                echo "<span class='small'>Línea 1: {$incidentes_l1} incidentes ({$tiempo_real_L1} mins).</span></div>";
            }
            echo '</div></div>';
        }
    }
    ?>
</div>

<?php require_once 'footer.php'; ?>
