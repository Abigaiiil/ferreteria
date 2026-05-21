<?php
session_start();
require_once 'conexion.php';  // Tu conexión a SQLite

$usuario_activo = null;

if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, email, telefono, pais, rfc, regimen_fiscal, uso_cfdi, cp FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario_activo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gorilla Tools - Ferretería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-navy: #003d71;
            --naranja-fix: #f47920;
            --gris-fondo: #f4f7f6;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gris-fondo);
        }
        .header-main { background-color: var(--azul-navy); }
        .nav-secondary { background-color: var(--naranja-fix); }
        .bg-orange { background-color: var(--naranja-fix); }
        .bg-navy { background-color: var(--azul-navy); }
        .text-navy { color: var(--azul-navy); }
        .search-bar input {
            border-radius: 25px 0 0 25px;
            border: none;
        }
        .search-bar button {
            border-radius: 0 25px 25px 0;
            background: white;
        }
        .user-menu a:hover { text-decoration: underline !important; }
        
        .category-carousel-section {
            position: relative;
            padding: 0 50px;
        }
        .carousel-container {
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }
        .category-wrapper {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: 10px 0;
        }
        .category-wrapper::-webkit-scrollbar { display: none; }
        .category-item {
            flex: 0 0 auto;
            width: 120px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .category-item:hover { transform: translateY(-5px); }
        .icon-box {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .icon-box img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .category-item p {
            margin-top: 10px;
            font-size: 12px;
            font-weight: 500;
            color: var(--azul-navy);
        }
        .btn-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--azul-navy);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
        }
        .btn-arrow:hover { background: var(--naranja-fix); }
        .btn-prev { left: 0; }
        .btn-next { right: 0; }
        
        .modal-login .modal-content, .modal-registro .modal-content, .modal-alerta .modal-content { border-radius: 15px; }
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
        }
        .campo-autollenado { background-color: #e9ecef; }
    </style>
</head>
<body>

<header class="header-main py-3">
    <div class="container-fluid px-5">
        <div class="row align-items-center">
            <div class="col-md-2">
                <a href="index.php">
                    <img src="Logo_GorilaTools-removebg.png" alt="Logo Gorilla Tools" class="img-fluid" style="max-height: 70px;" 
                         onerror="this.src='https://via.placeholder.com/150x70?text=Gorilla+Tools'">
                </a>
            </div>
            <div class="col-md-6">
                <div class="input-group search-bar">
                    <input type="text" id="buscadorPrincipal" class="form-control" placeholder="Buscar productos...">
                        <button class="btn btn-light" id="btnBuscarPrincipal">
                            <i class="bi bi-search"></i>
                        </button>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-end align-items-center gap-3">
                <div id="userMenuContainer"></div>
                <a href="Ferreteria_Cotizacion_BORRADOR.php" class="text-white text-decoration-none position-relative">
                    <i class="bi bi-cart3 fs-4" id="cartIcon"></i>
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
            <li class="nav-item"><a href="Ferreteria_Facturacion_BORRADOR.php" class="nav-link text-white">Facturación</a></li>
            <li class="nav-item"><a href="Ferreteria_Cotizacion_BORRADOR.php" class="nav-link text-white">Cotizador</a></li>
            <li class="nav-item"><a href="Ferreteria_UbicarTienda_BORRADOR.php" class="nav-link text-white">Ubica tu tienda</a></li>
        </ul>
    </div>
</nav>

<section class="container mt-4">
    <div class="banner-promo row g-0 rounded overflow-hidden">
        <div class="col-md-5 bg-orange p-5 d-flex flex-column justify-content-center text-white">
            <h2 class="fw-bold">¡Genera tu cotización fácil y rápido!</h2>
            <ul class="list-unstyled mt-4">
                <li><i class="bi bi-plus-square me-2"></i> Selecciona los productos que necesitas</li>
                <li><i class="bi bi-telephone me-2"></i> Registra tu información de contacto</li>
                <li><i class="bi bi-download me-2"></i> Descárgala o envíala a tu correo</li>
            </ul>
            <a href="#categorias" class="btn btn-light mt-3 fw-bold" style="width: fit-content;">Ver categorías</a>
        </div>
        <div class="col-md-7 bg-navy p-5 text-center text-white">
            <h2 class="fw-bold">¡Obten nuestra app y cotiza desde tu móvil!</h2>
            <p class="mt-3">Disponible para iOS y Android</p>
            <div class="d-flex justify-content-center mt-4">
                <a href="#" class="btn btn-light me-3" id="btnAppStore"><i class="bi bi-apple me-2"></i> App Store</a>
                <a href="#" class="btn btn-light" id="btnGooglePlay"><i class="bi bi-google-play me-2"></i> Google Play</a>
            </div>
        </div>
    </div>
</section>

<section id="categorias" class="container my-5 category-carousel-section">
    <h4 class="mb-4 fw-bold text-navy">CATEGORÍAS</h4>
    <div class="carousel-container position-relative">
        <button class="btn-arrow btn-prev" id="prevCategory"><i class="bi bi-chevron-left"></i></button>
        <div class="category-wrapper d-flex" id="categoryWrapper">
            <!-- ... tus categorías ... -->
             <a href="Categoria_Maquinas.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="maquinas">
                <div class="icon-box"><img src="maquinas-removed.png" alt="Máquinas" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Máquinas y Accesorios</p>
            </div>
            </a>
            <a href="Categoria_Manuales.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="manuales">
                <div class="icon-box"><img src="manuales.png" alt="Manuales" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Herramienta Manual</p>
            </div>
            </a>
            <a href="Categoria_Generadores.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="generadores">
                <div class="icon-box"><img src="generadores.png" alt="Generadores" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Generadores y Bombas</p>
            </div>
            </a>
            <a href="Categoria_Plomeria.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="plomeria">
                <div class="icon-box"><img src="plomeria.png" alt="Plomería" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Productos para Plomería</p>
            </div>
            </a>
            <a href="Categoria_Electrico.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="electricos">
                <div class="icon-box"><img src="electricos.png" alt="Eléctricos" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Productos Eléctricos</p>
            </div>
            </a>
            <a href="Categoria_Seguridad.php" class="text-decoration-none">
            <div class="category-item text-center" data-categoria="seguridad">
                <div class="icon-box"><img src="seguridad.png" alt="Seguridad" onerror="this.src='https://via.placeholder.com/60'"></div>
                <p>Seguridad Industrial</p>
            </div>
            </a>
            <a href="Categoria_Pintura.php" class="text-decoration-none">
                <div class="category-item text-center" data-categoria="pintura">
                    <div class="icon-box"><img src="pintura.png" alt="Pinturas" onerror="this.src='https://via.placeholder.com/60'"></div>
                    <p>Pinturas</p>
                </div>
            </a>
        </div>
        <button class="btn-arrow btn-next" id="nextCategory"><i class="bi bi-chevron-right"></i></button>
    </div>
</section>

<!-- Modales -->
<div class="modal fade modal-alerta" id="modalAlerta" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlertaTitulo">Notificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAlertaMensaje">Mensaje de ejemplo.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-login" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title">Acceder a Gorilla Tools</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="loginEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="loginPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 bg-navy">Ingresar</button>
                </form>
                <hr>
                <p class="text-center mb-0">
                    <small>¿No tienes cuenta? <a href="#" id="btnAbrirRegistro" data-bs-toggle="modal" data-bs-target="#registroModal">Regístrate aquí</a></small>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-registro" id="registroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title">Crear cuenta en Gorilla Tools</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registroForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-navy mb-3">Datos personales</h6>
                            <div class="mb-3"><label class="form-label required">Nombre completo</label><input type="text" class="form-control" id="regNombre" required></div>
                            <div class="mb-3"><label class="form-label required">Correo electrónico</label><input type="email" class="form-control" id="regEmail" required></div>
                            <div class="mb-3"><label class="form-label required">Teléfono</label><input type="tel" class="form-control" id="regTelefono" required></div>
                            <div class="mb-3"><label class="form-label">País</label><select class="form-select" id="regPais"><option>México</option><option>Estados Unidos</option><option>Canadá</option></select></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-navy mb-3">Datos fiscales</h6>
                            <div class="mb-3"><label class="form-label required">RFC</label><input type="text" class="form-control" id="regRfc" maxlength="13" required></div>
                            <div class="mb-3"><label class="form-label required">Régimen fiscal</label><select class="form-select" id="regRegimen" required><option value="">Seleccionar</option><option>General de Ley Personas Morales</option><option>Personas Físicas con Actividades Empresariales</option></select></div>
                            <div class="mb-3"><label class="form-label required">Uso de CFDI</label><select class="form-select" id="regUsoCFDI" required><option value="">Seleccionar</option><option>Gastos en general</option><option>Honorarios médicos</option></select></div>
                            <div class="mb-3"><label class="form-label">Código postal</label><input type="text" class="form-control" id="regCp"></div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><div class="mb-3"><label class="form-label required">Contraseña</label><input type="password" class="form-control" id="regPassword" required></div></div>
                        <div class="col-md-6"><div class="mb-3"><label class="form-label required">Confirmar contraseña</label><input type="password" class="form-control" id="regConfirmPassword" required></div></div>
                    </div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="regTerminos" required><label class="form-check-label">Acepto los <a href="terminos_y_condiciones.html">términos y condiciones</a> y el <a href="aviso_privacidad.html">aviso de privacidad</a></label></div>
                    <button type="submit" class="btn btn-primary w-100 bg-navy">Registrarme</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toastNotification" class="toast-notification"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>


let usuarioActivo = null;
let carrito = JSON.parse(localStorage.getItem('carritoGorilla')) || [];


// Función para mostrar modal personalizado (con botón Aceptar)
function mostrarModal(mensaje, titulo = "Notificación") {
    const modalElement = document.getElementById('modalAlerta');
    if (modalElement) {
        document.getElementById('modalAlertaTitulo').innerText = titulo;
        document.getElementById('modalAlertaMensaje').innerHTML = mensaje;
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error("Modal no encontrado");
    }
}

function mostrarNotificacion(mensaje, tipo = 'success') {
    const toast = document.getElementById('toastNotification');
    toast.textContent = mensaje;
    toast.style.backgroundColor = tipo === 'success' ? '#28a745' : '#dc3545';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}



async function registrarUsuario(datos) {
    if (datos.password !== datos.confirmPassword) {
        mostrarModal('❌ Las contraseñas no coinciden', 'Error', 'error');
        return false;
    }
    if (datos.rfc.length < 12 && datos.rfc !== 'XAXX010101000') {
        mostrarModal('❌ RFC inválido (mínimo 12 caracteres)', 'Error', 'error');
        return false;
    }
    try {
        const response = await fetch('api_registrar.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) });
        const resultado = await response.json();
        if (resultado.success) {
            mostrarModal('¡Registro exitoso! Ahora inicia sesión', 'Hola', 'success');
            document.getElementById('registroForm').reset();
            const registroModal = bootstrap.Modal.getInstance(document.getElementById('registroModal'));
            if (registroModal) registroModal.hide();
            new bootstrap.Modal(document.getElementById('loginModal')).show();
            return true;
        } else {
            mostrarModal('' + resultado.mensaje, 'Error', 'error');
            return false;
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error', 'error');
        return false;
    }
}

async function iniciarSesion(email, password) {
    try {
        const response = await fetch('api_login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }) });
        const resultado = await response.json();
        if (resultado.success) {
            usuarioActivo = resultado.usuario;
            mostrarModal(`¡Bienvenid@, ${usuarioActivo.nombre}!`, '¡Hola!', 'success');
            actualizarUISesion();
            return true;
        } else {
            mostrarModal('❌ ' + resultado.mensaje, 'Error', 'error');
            return false;
        }
    } catch (error) {
        mostrarModal('❌ Error de conexión con el servidor', 'Error', 'error');
        return false;
    }
}

async function cerrarSesion() {
    await fetch('api_cerrar_sesion.php', { method: 'POST' });
    usuarioActivo = null;
    mostrarNotificacion('🔓 Sesión cerrada correctamente');
    actualizarUISesion();
    location.reload();
}

function actualizarUISesion() {
    const container = document.getElementById('userMenuContainer');
    if (!container) return;
    if (usuarioActivo) {
        container.innerHTML = `<div class="dropdown"><a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-4"></i><span>${usuarioActivo.nombre.split(' ')[0]}</span></a><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="Ferreteria_Cotizacion_BORRADOR.php?tab=comprados">Mis compras</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li></ul></div>`;
    } else {
        container.innerHTML = `<a href="#" class="text-white text-decoration-none d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-person-circle fs-4"></i><span>Iniciar sesión</span></a>`;
    }
}

async function cargarSesionActual() {
    try {
        const response = await fetch('api_obtener_usuario.php');
        const resultado = await response.json();
        if (resultado.success) {
            usuarioActivo = resultado.usuario;
            actualizarUISesion();
        }
    } catch (error) { console.log('No hay sesión activa'); }
}

function actualizarCarrito() {
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

// Eventos
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const exito = await iniciarSesion(document.getElementById('loginEmail').value, document.getElementById('loginPassword').value);
    if (exito) { bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide(); e.target.reset(); }
});
document.getElementById('registroForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const datos = {
        nombre: document.getElementById('regNombre').value, email: document.getElementById('regEmail').value,
        telefono: document.getElementById('regTelefono').value, pais: document.getElementById('regPais').value,
        rfc: document.getElementById('regRfc').value, regimen: document.getElementById('regRegimen').value,
        usoCFDI: document.getElementById('regUsoCFDI').value, cp: document.getElementById('regCp').value,
        password: document.getElementById('regPassword').value, confirmPassword: document.getElementById('regConfirmPassword').value
    };
    const exito = await registrarUsuario(datos);
    if (exito) { bootstrap.Modal.getInstance(document.getElementById('registroModal'))?.hide(); e.target.reset(); }
});
document.getElementById('btnAbrirRegistro')?.addEventListener('click', (e) => { e.preventDefault(); bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide(); });

// Carrusel
const wrapper = document.getElementById('categoryWrapper'), prevBtn = document.getElementById('prevCategory'), nextBtn = document.getElementById('nextCategory');
if (wrapper && prevBtn && nextBtn) {
    const scrollAmount = 140;
    prevBtn.addEventListener('click', () => wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
    nextBtn.addEventListener('click', () => wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
}

// Buscador


// Evento para el botón de búsqueda
document.getElementById('btnBuscarPrincipal')?.addEventListener('click', buscarProductos);

// Evento para buscar al presionar Enter
document.getElementById('buscadorPrincipal')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        buscarProductos();
    }
});


function buscarProductos() {
    const termino = document.getElementById('buscadorPrincipal').value.trim();
    if (termino) {
        window.location.href = `buscar.php?termino=${encodeURIComponent(termino)}`;
    } else {
        mostrarNotificacion('🔍 Escribe un producto para buscar', 'error');
    }
}

// Botones de app
document.getElementById('btnAppStore')?.addEventListener('click', (e) => { e.preventDefault(); window.open('https://www.apple.com/app-store/', '_blank'); });
document.getElementById('btnGooglePlay')?.addEventListener('click', (e) => { e.preventDefault(); window.open('https://play.google.com/store', '_blank'); });

// Evento para el carrito (muestra modal en lugar de notificación)
document.getElementById('cartIcon')?.addEventListener('click', () => {
 const carrito = JSON.parse(localStorage.getItem('carritoGorilla')) || [];
    if (carrito.length === 0) {
        mostrarModal('🛒 Tu carrito está vacío.<br>Agrega productos desde las categorías.', 'Carrito vacío');
    } else {
        const totalItems = carrito.reduce((sum, item) => sum + (item.cantidad || 1), 0);
        const total = carrito.reduce((sum, item) => sum + (item.precio || 0) * (item.cantidad || 1), 0);
        mostrarModal(`📦 Tienes ${totalItems} producto(s) en tu carrito.<br><strong>Total: $${total.toFixed(2)}</strong>`, 'Mi carrito');
    }
});



// Inicializar
actualizarUISesion();
actualizarCarrito();
cargarSesionActual();

document.addEventListener('hidden.bs.modal', function() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
});
</script>

<!-- ========== FOOTER ========== -->
<footer style="background-color: #003d71; color: white; margin-top: 60px; padding: 40px 20px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 30px;">
            <div style="flex: 1; min-width: 200px;"><img src="Logo_GorilaTools-removebg.png" alt="Gorilla Tools" style="max-height: 50px; margin-bottom: 15px;" onerror="this.src='https://via.placeholder.com/150x50?text=Gorilla+Tools'"><p style="font-size: 14px; opacity: 0.8;">Tu ferretería de confianza con las mejores herramientas y precios competitivos.</p>
            <div style="display: flex; gap: 15px; margin-top: 15px;">
                <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" style="color: white; font-size: 20px;">
    <i class="bi bi-facebook"></i>
</a>
<a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" style="color: white; font-size: 20px;">
    <i class="bi bi-instagram"></i>
</a>
<a href="https://web.whatsapp.com" target="_blank" rel="noopener noreferrer" style="color: white; font-size: 20px;">
    <i class="bi bi-whatsapp"></i>
</a>
<a href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer" style="color: white; font-size: 20px;">
    <i class="bi bi-tiktok"></i>
</a>
            </div>
        </div>
            <div style="flex: 1; min-width: 150px;"><h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Enlaces rápidos</h4><ul style="list-style: none; padding: 0;"><li style="margin-bottom: 10px;"><a href="index.php" style="color: white; text-decoration: none;">Inicio</a></li><li style="margin-bottom: 10px;"><a href="Ferreteria_Facturacion_BORRADOR.php" style="color: white; text-decoration: none;">Facturación</a></li><li style="margin-bottom: 10px;"><a href="Ferreteria_Cotizacion_BORRADOR.php" style="color: white; text-decoration: none;">Cotizador</a></li><li style="margin-bottom: 10px;"><a href="Ferreteria_UbicarTienda_BORRADOR.php" style="color: white; text-decoration: none;">Ubica tu tienda</a></li></ul></div>
            <div style="flex: 1; min-width: 200px;"><h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Contacto</h4><ul style="list-style: none; padding: 0;"><li style="margin-bottom: 10px;"><i class="bi bi-telephone-fill" style="margin-right: 10px;"></i> 868 455 9524</li><li style="margin-bottom: 10px;"><i class="bi bi-envelope-fill" style="margin-right: 10px;"></i> comprasonline@gorillatools.com</li><li style="margin-bottom: 10px;"><i class="bi bi-geo-alt-fill" style="margin-right: 10px;"></i> Matamoros / Reynosa, Tamaulipas</li></ul></div>
            <div style="flex: 1; min-width: 180px;"><h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Horarios</h4><ul style="list-style: none; padding: 0;"><li style="margin-bottom: 8px;">Lun - Vie: 8:00 - 20:00</li><li style="margin-bottom: 8px;">Sábado: 9:00 - 18:00</li><li style="margin-bottom: 8px;">Domingo: Cerrado</li></ul></div>
        </div>
        <hr style="border-color: #f47920; margin: 30px 0 20px;">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 12px; opacity: 0.7;"><p>&copy; 2026 Gorilla Tools - Todos los derechos reservados</p><div><a href="aviso_privacidad.html" style="color: white; text-decoration: none; margin-left: 20px;">Aviso de privacidad</a><a href="terminos_y_condiciones.html" style="color: white; text-decoration: none; margin-left: 20px;">Términos y condiciones</a></div></div>
    </div>
</footer>

</body>
</html>