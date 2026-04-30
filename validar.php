<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$identificador = trim($_POST['identificador'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($identificador) || empty($password)) {
    header("Location: login.php?error=2");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = :id OR nombre = :id");
    $stmt->execute(['id' => $identificador]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['es_admin'] = (bool)$user['es_admin'];
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: login.php?error=1");
    exit();
}
?>
