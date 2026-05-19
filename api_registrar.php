<?php
// api_registrar.php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    $nombre = $datos['nombre'];
    $email = $datos['email'];
    $password = password_hash($datos['password'], PASSWORD_DEFAULT);
    $telefono = $datos['telefono'];
    $pais = $datos['pais'];
    $rfc = strtoupper($datos['rfc']); // Convertir a mayúsculas
    $regimen = $datos['regimen'];
    $usoCFDI = $datos['usoCFDI'];
    $cp = $datos['cp'];
    
    // ========== VALIDACIÓN DE RFC ==========
    if (strlen($rfc) < 12 && $rfc !== 'XAXX010101000') {
        echo json_encode(['success' => false, 'mensaje' => 'RFC inválido (mínimo 12 caracteres)']);
        exit;
    }
    
    // Validar que los campos obligatorios no estén vacíos
    if (empty($nombre) || empty($email) || empty($datos['password']) || empty($telefono)) {
        echo json_encode(['success' => false, 'mensaje' => 'Completa todos los campos obligatorios']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, telefono, pais, rfc, regimen_fiscal, uso_cfdi, cp) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password, $telefono, $pais, $rfc, $regimen, $usoCFDI, $cp]);
        
        echo json_encode(['success' => true, 'mensaje' => 'Usuario registrado correctamente']);
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false) {
            echo json_encode(['success' => false, 'mensaje' => 'Este correo ya está registrado']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
}
?>