<?php
// api_registrar_compra.php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($datos['productos']) || empty($datos['productos'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No hay productos en el carrito']);
    exit;
}

$sucursal = $datos['sucursal'] ?? 'Reynosa Centro';

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Generar número de ticket único
    $numero_ticket = 'TICKET-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Calcular monto total
    $monto_total = 0;
    foreach ($datos['productos'] as $producto) {
        $monto_total += $producto['precio'] * $producto['cantidad'];
    }
    
    // Crear ticket
    $stmt = $pdo->prepare("INSERT INTO tickets (numero_ticket, usuario_id, fecha_compra, monto_total, sucursal) 
                           VALUES (?, ?, date('now'), ?, ?)");
    $stmt->execute([$numero_ticket, $usuario_id, $monto_total, $sucursal]);
    $ticket_id = $pdo->lastInsertId();
    
    // Guardar detalles del ticket y actualizar stock
    $stmtTicket = $pdo->prepare("INSERT INTO ticket_detalles (ticket_id, producto_id, cantidad, precio_unitario) 
                                 VALUES (?, ?, ?, ?)");
    
    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    
    foreach ($datos['productos'] as $producto) {
        // Guardar detalle
        $stmtTicket->execute([$ticket_id, $producto['id'], $producto['cantidad'], $producto['precio']]);
        
        // Actualizar stock
        $stmtStock->execute([$producto['cantidad'], $producto['id']]);
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'numero_ticket' => $numero_ticket,
        'monto_total' => $monto_total,
        'mensaje' => 'Compra registrada correctamente'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>