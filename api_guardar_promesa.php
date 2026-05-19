<?php
// api_guardar_promesa.php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($datos['productos']) || empty($datos['productos'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No hay productos']);
    exit;
}

$fecha_limite = date('Y-m-d H:i:s', strtotime('+7 days'));

try {
    foreach ($datos['productos'] as $producto) {
        $stmt = $pdo->prepare("INSERT INTO promesas (producto_id, cantidad, usuario_id, fecha_limite, estado) 
                               VALUES (?, ?, ?, ?, 'activa')");
        $stmt->execute([$producto['id'], $producto['cantidad'], $usuario_id, $fecha_limite]);
    }
    
    echo json_encode(['success' => true, 'cantidad' => count($datos['productos'])]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>