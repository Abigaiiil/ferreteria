<?php
session_start();
require_once 'conexion.php';

$termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
$resultados = [];

if (!empty($termino)) {
    $busqueda = "%$termino%";
    // Usa solo las columnas que existen en tu tabla
    $stmt = $pdo->prepare("SELECT id, codigo, nombre, precio, stock FROM productos WHERE nombre LIKE ? OR codigo LIKE ?");
    $stmt->execute([$busqueda, $busqueda]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$usuario_activo = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT id, codigo, nombre, precio, stock, marca, clave FROM productos WHERE nombre LIKE ? OR codigo LIKE ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_activo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar - Gorilla Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --azul-navy: #003d71; --naranja-fix: #f47920; --gris-fondo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--gris-fondo); }
        .header-main { background-color: var(--azul-navy); }
        .nav-secondary { background-color: var(--naranja-fix); }
        .product-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .product-price { color: var(--azul-navy); font-size: 18px; font-weight: bold; }
        .btn-cotizar { background: var(--naranja-fix); color: white; border: none; padding: 8px 20px; border-radius: 25px; cursor: pointer; }
        .btn-cotizar:hover { background: var(--azul-navy); }
        .cart-count {
            background: #f47920;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -8px;
            right: -12px;
        }
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 9999;
            display: none;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

<header class="header-main py-3">
    <div class="container-fluid px-5">
        <div class="row align-items-center">
            <div class="col-md-2">
                <a href="index.php">
                    <img src="Logo_GorilaTools-removebg.png" alt="Logo" class="img-fluid" style="max-height: 70px;">
                </a>
            </div>
            <div class="col-md-6">
                <div class="input-group search-bar">
                    <input type="text" id="buscadorPrincipal" class="form-control" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($termino); ?>">
                    <button class="btn btn-light" id="btnBuscarPrincipal"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-end align-items-center gap-3">
                <div id="userMenuContainer">
                    <?php if ($usuario_activo): ?>
                        <div class="dropdown">
                            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(explode(' ', $usuario_activo['nombre'])[0]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="index.php" class="text-white text-decoration-none">Iniciar sesión</a>
                    <?php endif; ?>
                </div>
                <a href="Ferreteria_Cotizacion_BORRADOR.php" class="text-white text-decoration-none position-relative">
                    <i class="bi bi-cart3 fs-4"></i>
                    <span id="cartCount" class="cart-count" style="display: none;">0</span>
                </a>
            </div>
        </div>
    </div>
</header>

<nav class="nav-secondary py-2">
    <div class="container">
        <ul class="nav justify-content-start gap-4 text-uppercase fw-bold">
            <li class="nav-item"><a href="index.php" class="nav-link text-white">Inicio</a></li>
            <li class="nav-item"><a href="Ferreteria_Facturacion_BORRADOR.html" class="nav-link text-white">Facturación</a></li>
            <li class="nav-item"><a href="Ferreteria_Cotizacion_BORRADOR.html" class="nav-link text-white">Cotizador</a></li>
            <li class="nav-item"><a href="Ferreteria_UbicarTienda_BORRADOR.html" class="nav-link text-white">Ubica tu tienda</a></li>
        </ul>
    </div>
</nav>

<main class="container my-4">
    <h3 class="text-navy mb-4">Resultados de búsqueda: "<?php echo htmlspecialchars($termino); ?>"</h3>
    
    <?php if (empty($termino)): ?>
        <div class="alert alert-warning">Ingresa un término de búsqueda.</div>
    <?php elseif (count($resultados) === 0): ?>
        <div class="alert alert-info">No se encontraron productos para "<?php echo htmlspecialchars($termino); ?>"</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($resultados as $producto): ?>
            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <div class="product-brand text-orange fw-bold"><?php echo htmlspecialchars($producto['marca'] ?? 'Gorilla'); ?></div>
                    <div class="product-name fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                    <div class="product-details small text-muted">Clave: <?php echo htmlspecialchars($producto['clave'] ?? 'N/A'); ?> | Código: <?php echo htmlspecialchars($producto['codigo']); ?></div>
                    <div class="product-price mt-2">$<?php echo number_format($producto['precio'], 2); ?></div>
                    <button class="btn-cotizar mt-3 w-100" onclick="agregarAlCarrito({id: <?php echo $producto['id']; ?>, nombre: '<?php echo addslashes($producto['nombre']); ?>', precio: <?php echo $producto['precio']; ?>, codigo: '<?php echo $producto['codigo']; ?>'})">
                        <i class="bi bi-cart-plus"></i> Cotizar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function mostrarNotificacion(mensaje, tipo = 'success') {
    const toast = document.getElementById('toastNotification');
    toast.textContent = mensaje;
    toast.style.backgroundColor = tipo === 'success' ? '#28a745' : '#dc3545';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

function agregarAlCarrito(producto) {
    let carrito = JSON.parse(localStorage.getItem('carritoGorilla')) || [];
    const existente = carrito.find(item => item.id === producto.id);
    if (existente) {
        existente.cantidad = (existente.cantidad || 1) + 1;
    } else {
        carrito.push({ 
            id: producto.id, 
            nombre: producto.nombre, 
            precio: producto.precio, 
            codigo: producto.codigo,
            cantidad: 1 
        });
    }
    localStorage.setItem('carritoGorilla', JSON.stringify(carrito));
    mostrarNotificacion(`✅ ${producto.nombre} agregado al carrito`);
}

function actualizarContadorCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carritoGorilla')) || [];
    const total = carrito.reduce((sum, item) => sum + (item.cantidad || 1), 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        if (total > 0) {
            cartCount.textContent = total;
            cartCount.style.display = 'flex';
        } else {
            cartCount.style.display = 'none';
        }
    }
}

function buscar() {
    const termino = document.getElementById('buscadorPrincipal').value.trim();
    if (termino) {
        window.location.href = `buscar.php?termino=${encodeURIComponent(termino)}`;
    } else {
        mostrarNotificacion('Ingresa un término de búsqueda', 'error');
    }
}

document.getElementById('btnBuscarPrincipal')?.addEventListener('click', buscar);
document.getElementById('buscadorPrincipal')?.addEventListener('keypress', (e) => { if (e.key === 'Enter') buscar(); });
actualizarContadorCarrito();
</script>

<style>
.text-orange { color: #f47920; }
.text-navy { color: #003d71; }
</style>

<footer style="background-color: #003d71; color: white; margin-top: 60px; padding: 30px; text-align: center;">
    <p>&copy; 2026 Gorilla Tools - Todos los derechos reservados</p>
</footer>
</body>
</html>