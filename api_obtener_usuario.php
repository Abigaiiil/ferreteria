<?php
// api_obtener_usuario.php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No hay sesión activa']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, email, telefono, pais, rfc, regimen_fiscal, uso_cfdi, cp FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo json_encode(['success' => true, 'usuario' => $usuario]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Usuario no encontrado']);
}
?>