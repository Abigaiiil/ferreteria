<?php
// api_limpiar_carrito.php
session_start();

unset($_SESSION['carrito_pendiente']);
echo json_encode(['success' => true]);
?>