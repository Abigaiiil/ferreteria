<?php
// api_guardar_cotizacion.php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($datos['productos']) || empty($datos['productos'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No hay productos en la cotización']);
    exit;
}

$folio = 'COT-' . date('Ymd') . '-' . rand(1000, 9999);

try {
    $stmt = $pdo->prepare("INSERT INTO cotizaciones (folio, usuario_id, fecha_creacion) VALUES (?, ?, datetime('now'))");
    $stmt->execute([$folio, $usuario_id]);
    $cotizacion_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    
    foreach ($datos['productos'] as $producto) {
        $stmt->execute([$cotizacion_id, $producto['id'], $producto['cantidad'], $producto['precio']]);
    }
    
    echo json_encode(['success' => true, 'folio' => $folio]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>