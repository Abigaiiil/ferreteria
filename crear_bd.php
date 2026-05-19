<?php
// crear_bd.php - Ejecutar UNA SOLA VEZ
$ruta_bd = "gorilla_tools.db";

try {
    $pdo = new PDO("sqlite:$ruta_bd");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tabla de usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        telefono TEXT,
        pais TEXT,
        rfc TEXT,
        regimen_fiscal TEXT,
        uso_cfdi TEXT,
        cp TEXT,
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Tabla de productos
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo TEXT UNIQUE,
        nombre TEXT NOT NULL,
        precio REAL NOT NULL,
        stock INTEGER DEFAULT 0,
        categoria TEXT
    )");
    
    // Tabla de cotizaciones
    $pdo->exec("CREATE TABLE IF NOT EXISTS cotizaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        folio TEXT UNIQUE NOT NULL,
        usuario_id INTEGER,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");
    
    // Tabla de promesas de compra (productos apartados)
    $pdo->exec("CREATE TABLE IF NOT EXISTS promesas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        producto_id INTEGER NOT NULL,
        cantidad INTEGER NOT NULL,
        usuario_id INTEGER,
        fecha_apartado DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_limite DATETIME,
        estado TEXT DEFAULT 'activa',
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");
    
    // ========== TABLAS NUEVAS ==========
    
    // Tabla de tickets (compra realizada)
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_ticket TEXT UNIQUE NOT NULL,
        usuario_id INTEGER,
        fecha_compra DATE NOT NULL,
        monto_total REAL NOT NULL,
        sucursal TEXT,
        ya_facturado INTEGER DEFAULT 0,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");
    
    // Tabla de detalles del ticket (productos comprados)
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_detalles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ticket_id INTEGER NOT NULL,
        producto_id INTEGER NOT NULL,
        cantidad INTEGER NOT NULL,
        precio_unitario REAL NOT NULL,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    )");
    
    // =====================================
    
    // Insertar productos de ejemplo
    $pdo->exec("INSERT OR IGNORE INTO productos (codigo, nombre, precio, stock, categoria) VALUES 
        ('001', 'Taladro Percutor', 1250.00, 10, 'maquinas'),
        ('002', 'Martillo Demoledor', 2800.00, 5, 'maquinas'),
        ('003', 'Disco de Corte', 180.00, 50, 'manuales'),
        ('004', 'Llave Inglesa', 95.00, 20, 'manuales')
    ");
    
    echo "<h2>✅ Base de datos actualizada con éxito!</h2>";
    echo "<p>📁 Archivo: " . realpath($ruta_bd) . "</p>";
    echo "<p>Tablas creadas: usuarios, productos, cotizaciones, promesas, <strong>tickets, ticket_detalles</strong></p>";
    echo "<br><a href='index.html'>← Volver al inicio</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>