<?php
// ver_datos.php - Ver los datos guardados en Render (SIN LOGIN)
require_once 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Datos - Gorilla Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; font-family: monospace; }
        .datos { background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #003d71; color: white; }
        .advertencia { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
            <a href="index.php" class="float-end">← Volver al inicio</a>
        </div>
        
        <h1>Visor de Base de Datos</h1>

        <h2>Usuarios registrados</h2>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>RFC</th><th>Fecha registro</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT id, nombre, email, telefono, rfc, fecha_registro FROM usuarios ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['rfc']) . "</td>";
                    echo "<td>{$row['fecha_registro']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Productos en catálogo</h2>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT id, codigo, nombre, precio, stock, categoria FROM productos ORDER BY id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['codigo']}</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>\${$row['precio']}</td>";
                    echo "<td>{$row['stock']}</td>";
                    echo "<td>{$row['categoria']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Tickets de compra</h2>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Número Ticket</th><th>Usuario</th><th>Fecha</th><th>Monto</th><th>Sucursal</th><th>Facturado</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT t.*, u.nombre as usuario_nombre FROM tickets t LEFT JOIN usuarios u ON t.usuario_id = u.id ORDER BY t.id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['numero_ticket']}</td>";
                    echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                    echo "<td>{$row['fecha_compra']}</td>";
                    echo "<td>\${$row['monto_total']}</td>";
                    echo "<td>{$row['sucursal']}</td>";
                    echo "<td>" . ($row['ya_facturado'] ? '✅ Sí' : '❌ No') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Cotizaciones</h2>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Folio</th><th>Usuario</th><th>Fecha creación</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT c.*, u.nombre as usuario_nombre FROM cotizaciones c LEFT JOIN usuarios u ON c.usuario_id = u.id ORDER BY c.id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['folio']}</td>";
                    echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                    echo "<td>{$row['fecha_creacion']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Promesas de compra</h2>
        <table class="table table-bordered">
            <thead>
                <tr><th>ID</th><th>Producto ID</th><th>Usuario</th><th>Cantidad</th><th>Fecha apartado</th><th>Fecha límite</th><th>Estado</th></tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT p.*, u.nombre as usuario_nombre FROM promesas p LEFT JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['producto_id']}</td>";
                    echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                    echo "<td>{$row['cantidad']}</td>";
                    echo "<td>{$row['fecha_apartado']}</td>";
                    echo "<td>{$row['fecha_limite']}</td>";
                    echo "<td>{$row['estado']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <br>
        <a href="index.php" class="btn btn-primary">← Volver al inicio</a>
    </div>
</body>
</html>