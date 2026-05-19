<?php
// conexion.php
$ruta_bd = __DIR__ . "/gorilla_tools.db";

try {
    $pdo = new PDO("sqlite:$ruta_bd");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>