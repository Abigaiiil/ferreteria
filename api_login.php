<?php
// api_login.php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    $email = $datos['email'];
    $password = $datos['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        unset($usuario['password']);
        
        echo json_encode([
            'success' => true, 
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => $usuario
        ]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Correo o contraseña incorrectos']);
    }
}
?>