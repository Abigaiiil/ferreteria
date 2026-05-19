<?php
// api_marcar_facturado.php
require_once 'conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);
$ticket = $datos['ticket'] ?? '';

if ($ticket) {
    $stmt = $pdo->prepare("UPDATE tickets SET ya_facturado = 1 WHERE numero_ticket = ?");
    $stmt->execute([$ticket]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>