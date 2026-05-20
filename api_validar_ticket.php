<?php
// api_validar_ticket.php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

$ticket = isset($_GET['ticket']) ? $_GET['ticket'] : '';

if (empty($ticket)) {
    echo json_encode(['success' => false, 'mensaje' => 'No se proporcionó ticket']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE numero_ticket = ? AND ya_facturado = 0");
    $stmt->execute([$ticket]);
    $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticketData) {
        echo json_encode(['success' => false, 'mensaje' => 'Ticket no encontrado o ya fue facturado']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT td.*, p.nombre, p.codigo, p.precio as precio_unitario 
                           FROM ticket_detalles td
                           JOIN productos p ON td.producto_id = p.id
                           WHERE td.ticket_id = ?");
    $stmt->execute([$ticketData['id']]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'ticket' => [
            'numero_ticket' => $ticketData['numero_ticket'],
            'fecha_compra' => $ticketData['fecha_compra'],
            'sucursal' => $ticketData['sucursal'],
            'monto_total' => $ticketData['monto_total']
        ],
        'productos' => $productos
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>