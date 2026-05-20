<?php
// api_guardar_carrito.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No hay sesión']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
$_SESSION['carrito_pendiente'] = $datos['productos'];

echo json_encode(['success' => true]);
?>