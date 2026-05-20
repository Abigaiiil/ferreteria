<?php
// api_obtener_carrito.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$productos = $_SESSION['carrito_pendiente'] ?? [];
echo json_encode(['success' => true, 'productos' => $productos]);
?>