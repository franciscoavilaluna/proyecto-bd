<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metro-Waze | Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card-login {
            background: #1e293b;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            color: #e2e8f0;
        }
        .btn-metro {
            background: #f39c12;
            color: #0f172a;
            font-weight: 600;
            border-radius: 2rem;
            padding: 0.7rem;
        }
        .btn-metro:hover {
            background: #e67e22;
            transform: scale(1.02);
            color: white;
        }
        .form-control, .input-group-text {
            background-color: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }
        .form-control:focus {
            background-color: #1e293b;
            color: white;
            border-color: #f39c12;
            box-shadow: none;
        }
        .input-group-text {
            color: #94a3b8;
        }
        a {
            color: #f39c12;
        }
        hr {
            border-color: #334155;
        }
        .alert-danger {
            background-color: #991b1b;
            color: #fee2e2;
            border-color: #dc2626;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-login p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-subway fa-3x text-warning"></i>
                    <h2 class="mt-3 fw-bold">Metro-Waze</h2>
                    <p class="text-secondary">Líneas 1 y 9 · Monitoreo operativo</p>
                </div>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_GET['error'] == '1' ? '❌ Usuario o contraseña incorrectos.' : '⚠️ Completa todos los campos.' ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form action="validar.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email o nombre de usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="text" name="identificador" class="form-control" placeholder="admin@metrowaze.com" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-metro w-100 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Ingresar
                    </button>
                </form>
                <hr class="my-4">
                <div class="text-center small text-secondary">
                    <i class="fas fa-chart-line"></i> Datos en tiempo real · CDMX
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
