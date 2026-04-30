<?php
$host = "localhost";
$db   = "metro";
$user = "pacosmosis";
$pass = "ibero";  // Si tienes contraseña en MySQL, escríbela aquí

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
