<?php
// crear_ticket_prueba.php
require_once 'conexion.php';

// Verificar que hay productos
$stmt = $pdo->query("SELECT id, nombre, precio FROM productos LIMIT 1");
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("❌ No hay productos en la base de datos. Ejecuta primero crear_bd.php");
}

$numero_ticket = 'TICKET-' . date('Ymd') . '-' . rand(1000, 9999);
$usuario_id = $_SESSION['usuario_id'] ?? 1;

$stmt = $pdo->prepare("INSERT INTO tickets (numero_ticket, usuario_id, fecha_compra, monto_total, sucursal) 
                       VALUES (?, ?, date('now'), ?, 'Reynosa Centro')");
$stmt->execute([$numero_ticket, $usuario_id, $producto['precio']]);
$ticket_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO ticket_detalles (ticket_id, producto_id, cantidad, precio_unitario) 
                       VALUES (?, ?, 1, ?)");
$stmt->execute([$ticket_id, $producto['id'], $producto['precio']]);

echo "✅ Ticket de prueba creado exitosamente!<br>";
echo "📋 Número de ticket: <strong style='font-size:20px; color:#003d71;'>$numero_ticket</strong><br>";
echo "💰 Monto: $" . $producto['precio'] . "<br>";
echo "📦 Producto: " . $producto['nombre'] . "<br><br>";
echo "<a href='Ferreteria_Facturacion_BORRADOR.php'>← Ir a facturación</a>";
?>