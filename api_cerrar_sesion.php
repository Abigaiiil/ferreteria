<?php
// api_cerrar_sesion.php
session_start();
session_destroy();
echo json_encode(['success' => true, 'mensaje' => 'Sesión cerrada']);
?>