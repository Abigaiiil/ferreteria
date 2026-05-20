<?php
session_start();
require_once 'conexion.php';

$usuarioActivoPHP = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuarioActivoPHP = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador + Sistema de Promesa de Compra</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --azul-navy: #003d71;
            --naranja-fix: #f47920;
            --gris-borde: #ddd;
            --gris-fondo: #f4f7f6;
            --verde-exito: #28a745;
            --rojo-error: #dc3545;
            --amarillo-promesa: #ffc107;
        }
        body { background-color: var(--gris-fondo); font-family: 'Roboto', sans-serif; }
        .nav-secondary { background-color: var(--naranja-fix); padding: 12px 0; }
        .nav-secondary .nav { display: flex; list-style: none; margin: 0 auto; padding: 0 50px; max-width: 1200px; }
        .nav-secondary .nav-item { margin-right: 30px; }
        .nav-secondary .nav-link { color: white !important; text-decoration: none; text-transform: uppercase; font-weight: bold; font-size: 14px; }
        .container-cotizador { max-width: 1400px; margin: 30px auto; padding: 20px; }
        .titulo-azul { color: var(--azul-navy); font-size: 28px; margin-bottom: 10px; }
        
        .search-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .search-box { display: flex; gap: 10px; margin-top: 15px; }
        .search-box input { flex: 1; padding: 12px 20px; border: 2px solid var(--gris-borde); border-radius: 25px; }
        .btn-buscar { background: var(--azul-navy); color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; }
        
        .resultados-busqueda { background: white; border-radius: 10px; margin-bottom: 25px; padding: 15px; display: none; }
        .producto-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid var(--gris-borde); }
        .btn-agregar { background: var(--verde-exito); color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; }
        
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 12px 24px; background: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .tab-btn.active { background: var(--azul-navy); color: white; }
        .tab-panel { display: none; }
        .tab-panel.active-panel { display: block; }
        
        .tabla-responsive { overflow-x: auto; background: white; border-radius: 10px; padding: 15px; margin-bottom: 20px; }
        .tabla-cotizacion { width: 100%; border-collapse: collapse; }
        .tabla-cotizacion thead th { background-color: var(--azul-navy); color: white; padding: 12px; border: 1px solid white; }
        .tabla-cotizacion td { border: 1px solid var(--gris-borde); padding: 12px; text-align: center; }
        .cantidad-input { width: 70px; padding: 5px; text-align: center; }
        .btn-eliminar { background: var(--rojo-error); color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; }
        
        .resumen-footer { display: flex; justify-content: flex-end; margin-top: 20px; }
        .tabla-totales { width: 300px; background: white; border-collapse: collapse; }
        .tabla-totales td { border: 1px solid var(--gris-borde); padding: 12px; }
        .label-naranja { color: var(--naranja-fix); font-weight: bold; }
        
        .acciones { display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px; }
        .btn-guardar, .btn-enviar, .btn-comprar-todo, .btn-apartar-todo { padding: 12px 24px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-guardar { background: var(--azul-navy); color: white; }
        .btn-enviar { background: var(--naranja-fix); color: white; }
        .btn-comprar-todo { background: var(--verde-exito); color: white; }
        .btn-apartar-todo { background: var(--amarillo-promesa); color: #333; }
        
        .empty-msg { text-align: center; padding: 40px; color: #999; }
        
        /* Modal personalizado */
        .modal-alerta .modal-content { border-radius: 15px; }
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
        @media (max-width: 768px) { .search-box { flex-direction: column; } .acciones { flex-direction: column; } .tabs { flex-wrap: wrap; } }
    </style>
</head>
<body>

<nav class="nav-secondary">
    <ul class="nav">
        <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
        <li class="nav-item"><a href="Ferreteria_Facturacion_BORRADOR.php" class="nav-link">Facturación</a></li>
        <li class="nav-item"><a href="Ferreteria_Cotizacion_BORRADOR.php" class="nav-link">Cotizador</a></li>
        <li class="nav-item"><a href="Ferreteria_UbicarTienda_BORRADOR.php" class="nav-link">Ubica tu tienda</a></li>
    </ul>
</nav>

<main class="container-cotizador">
    <h2 class="titulo-azul">Cotizador + Sistema de Promesa de Compra</h2>
    <p>Busca productos, cotiza, y tienes <strong>24 horas</strong> para comprar antes de que se devuelvan al almacén.</p>

   <div class="search-section">
        <div class="search-box">
            <input type="text" id="buscador" placeholder="Buscar por código, clave o descripción">
            <button class="btn-buscar" onclick="buscarProductos()"> Buscar</button>
        </div>
    </div>

    <div id="resultadosPanel" class="resultados-busqueda">
        <h3>Productos disponibles</h3>
        <div id="listaProductos"></div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" data-tab="cotizando"> Cotizando (carrito)</button>
        <button class="tab-btn" data-tab="promesa">En promesa (por comprar)</button>
        <button class="tab-btn" data-tab="comprado">Comprados</button>
    </div>

    <div id="tab-cotizando" class="tab-panel active-panel">
        <div class="tabla-responsive">
            <table class="tabla-cotizacion">
                <thead><tr><th>Código</th><th>Descripción</th><th>Precio</th><th>Cantidad</th><th>Total</th><th>Acción</th></tr></thead>
                <tbody id="cotizacionBody"><tr><td colspan="6" class="empty-msg">No hay productos en cotización</td></tr></tbody>
            </table>
        </div>
        <div class="resumen-footer">
            <table class="tabla-totales">
                <tr><td class="label-naranja">Subtotal</td><td id="subtotal">$0.00</td></tr>
                <tr><td class="label-naranja">IVA (16%)</td><td id="iva">$0.00</td></tr>
                <tr><td class="label-naranja total-final">Total</td><td id="total">$0.00</td></tr>
            </table>
        </div>
        <div class="acciones">
            <button class="btn-guardar" onclick="guardarCotizacion()">Guardar Cotización</button>
            <button class="btn-enviar" onclick="enviarCotizacion()">Solicitar Cotización</button>
            <button class="btn-apartar-todo" onclick="apartarTodo()">Apartar Todo</button>
            <button class="btn-comprar-todo" onclick="comprarTodo()">Comprar Todo</button>
        </div>
    </div>

    <div id="tab-promesa" class="tab-panel">
        <div class="tabla-responsive">
            <table class="tabla-cotizacion">
                <thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Total</th><th>Fecha límite</th><th>Días restantes</th><th>Acción</th></tr></thead>
                <tbody id="promesaBody"><tr><td colspan="7" class="empty-msg">No hay productos en promesa</td></tr></tbody>
            </table>
        </div>
    </div>

    <div id="tab-comprado" class="tab-panel">
        <div class="tabla-responsive">
            <table class="tabla-cotizacion">
                <thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Total</th><th>Fecha compra</th></tr></thead>
                <tbody id="compradoBody"><tr><td colspan="5" class="empty-msg">No hay productos comprados aún</td></tr></tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal de alerta personalizado -->
<div class="modal fade modal-alerta" id="modalAlerta" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlertaTitulo">Notificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAlertaMensaje">Mensaje de ejemplo</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<div id="toastNotification" class="toast-notification"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>
// ========== CATÁLOGO DE PRODUCTOS ==========

const usuarioActivo = <?php echo json_encode($usuarioActivoPHP); ?>;

const productosBD = <?php 
    $stmt = $pdo->query("SELECT id, codigo, nombre as descripcion, precio, stock FROM productos");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>;
let catalogoProductos = productosBD;

// ========== ESTRUCTURAS DE DATOS ==========
let carrito = [];
let promesas = [];
let comprados = [];

// ========== FUNCIONES DE NOTIFICACIÓN ==========
function mostrarModal(mensaje, titulo = "Notificación") {
    const modalElement = document.getElementById('modalAlerta');
    if (modalElement) {
        document.getElementById('modalAlertaTitulo').innerText = titulo;
        document.getElementById('modalAlertaMensaje').innerHTML = mensaje;
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

function mostrarNotificacion(mensaje, tipo = 'success') {
    const toast = document.getElementById('toastNotification');
    toast.textContent = mensaje;
    toast.style.backgroundColor = tipo === 'success' ? '#28a745' : '#dc3545';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

// ========== CARGA DESDE localStorage ==========
// ========== CARGA DESDE localStorage ==========
// ========== CARGA DESDE localStorage ==========
function cargarCarritoDesdeLocalStorage() {
    if (!usuarioActivo) {
        console.log("❌ No hay usuario activo");
        return;
    }
    
    console.log("🔄 Buscando carrito pendiente en el servidor...");
    
    // Obtener carrito pendiente de la sesión PHP
    fetch('api_obtener_carrito.php')
        .then(response => response.json())
        .then(data => {
            console.log("📦 Respuesta del servidor:", data);
            
            if (data.success && data.productos && data.productos.length > 0) {
                console.log("📦 Productos desde servidor:", data.productos);
                
                data.productos.forEach(producto => {
                    const existente = carrito.find(item => item.id === producto.id);
                    const cantidadAAgregar = producto.cantidad || 1;
                    
                    if (existente) {
                        existente.cantidad += cantidadAAgregar;
                    } else {
                        carrito.push({
                            id: producto.id,
                            codigo: producto.codigo || 'N/A',
                            descripcion: producto.descripcion,
                            precio: parseFloat(producto.precio) || 0,
                            cantidad: cantidadAAgregar
                        });
                    }
                });
                
                if (carrito.length > 0) {
                    guardarDatos();
                    renderizarTodo();
                    console.log("✅ Carrito cargado:", carrito);
                }
                
                // Limpiar la sesión después de cargar
                fetch('api_limpiar_carrito.php');
            } else {
                console.log("⚠️ No hay carrito pendiente en el servidor");
            }
        })
        .catch(error => console.error("❌ Error al obtener carrito:", error));
}

function cargarDatos() {
    const savedCarrito = localStorage.getItem('cotizacion_carrito');
    const savedPromesas = localStorage.getItem('cotizacion_promesas');
    const savedComprados = localStorage.getItem('cotizacion_comprados');
    if (savedCarrito) carrito = JSON.parse(savedCarrito);
    if (savedPromesas) promesas = JSON.parse(savedPromesas);
    if (savedComprados) comprados = JSON.parse(savedComprados);
    verificarPromesasVencidas();
    renderizarTodo();
}

function guardarDatos() {
    sessionStorage.setItem('cotizacion_carrito', JSON.stringify(carrito));
    sessionStorage.setItem('cotizacion_promesas', JSON.stringify(promesas));
    sessionStorage.setItem('cotizacion_comprados', JSON.stringify(comprados));
}

// ========== FUNCIONES DE NEGOCIO ==========
function verificarPromesasVencidas() {
    const hoy = new Date();
    let huboCambios = false;
    promesas = promesas.filter(promesa => {
        const fechaLimite = new Date(promesa.fechaLimite);
        if (fechaLimite < hoy) {
            const producto = catalogoProductos.find(p => p.id === promesa.id);
            if (producto) producto.stock += promesa.cantidad;
            huboCambios = true;
            return false;
        }
        return true;
    });
    if (huboCambios) guardarDatos();
}

function buscarProductos() {
    const busqueda = document.getElementById('buscador').value.toLowerCase();
    if (!busqueda) { mostrarModal('Escribe algo para buscar', 'Aviso'); return; }
    
    const resultados = catalogoProductos.filter(p => 
        p.descripcion.toLowerCase().includes(busqueda) || p.codigo.includes(busqueda)
    ).filter(p => p.stock > 0);
    
    const panel = document.getElementById('resultadosPanel');
    const listaDiv = document.getElementById('listaProductos');
    
    if (resultados.length === 0) {
        listaDiv.innerHTML = '<p>No se encontraron productos con stock disponible</p>';
    } else {
        listaDiv.innerHTML = resultados.map(p => `
            <div class="producto-item">
                <div><strong>${p.descripcion}</strong><br>$${p.precio} | Stock: ${p.stock}</div>
                <button class="btn-agregar" onclick="agregarAlCarrito(${p.id})">+ Agregar</button>
            </div>
        `).join('');
    }
    panel.style.display = 'block';
}

function agregarAlCarrito(id) {
    const producto = catalogoProductos.find(p => p.id === id);
    if (!producto || producto.stock <= 0) {
        mostrarModal('Producto sin stock disponible', 'Sin stock');
        return;
    }
    const existente = carrito.find(item => item.id === id);
    if (existente) {
        if (existente.cantidad + 1 > producto.stock) {
            mostrarModal(`Solo hay ${producto.stock} unidades disponibles`, 'Stock limitado');
            return;
        }
        existente.cantidad++;
    } else {
        carrito.push({ ...producto, cantidad: 1 });
    }
    guardarDatos();
    renderizarCarrito();
    document.getElementById('resultadosPanel').style.display = 'none';
    document.getElementById('buscador').value = '';
}

function comprarAhora(id) {
    if (!usuarioActivo) {
        mostrarModal('🔐 Debes iniciar sesión para apartar productos', 'Acción no permitida');
        return;
    }
    // ... resto del código
    const item = carrito.find(i => i.id === id);
    if (!item) return;
    const fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + 7);
    promesas.push({ ...item, fechaLimite: fechaLimite.toISOString(), fechaPromesa: new Date().toISOString() });
    const producto = catalogoProductos.find(p => p.id === item.id);
    if (producto) producto.stock -= item.cantidad;
    carrito = carrito.filter(i => i.id !== id);
    guardarDatos();
    renderizarTodo();
    mostrarModal(`Producto apartado por 7 días. Compra antes del ${fechaLimite.toLocaleDateString()}`, 'Producto apartado');
}


async function comprarProductoDirecto(id) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;
    try {
        const response = await fetch('api_registrar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productos: [{ id: item.id, cantidad: item.cantidad, precio: item.precio }] })
        });
        const resultado = await response.json();
        if (resultado.success) {
            mostrarModal(`¡Compra realizada!<br>Número de Ticket: ${resultado.numero_ticket}`, 'Compra exitosa');
            carrito = carrito.filter(i => i.id !== id);
            guardarDatos();
            renderizarTodo();
            if (confirm('¿Deseas facturar esta compra?')) {
                window.location.href = `Ferreteria_Facturacion_BORRADOR.php?ticket=${resultado.numero_ticket}`;
            }
        } else {
            mostrarModal(resultado.mensaje, 'Error');
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error');
    }
}

async function comprarTodo() {
    if (carrito.length === 0) {
        mostrarModal('No hay productos en la cotización', 'Carrito vacío');
        return;
    }
    try {
        const response = await fetch('api_registrar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productos: carrito })
        });
        const resultado = await response.json();
        if (resultado.success) {
            mostrarModal(`¡Compra realizada! Ticket: ${resultado.numero_ticket}`, 'Compra exitosa');
            carrito = [];
            guardarDatos();
            renderizarTodo();
            if (confirm('¿Deseas facturar esta compra?')) {
                window.location.href = `Ferreteria_Facturacion_BORRADOR.php?ticket=${resultado.numero_ticket}`;
            }
        } else {
            mostrarModal(resultado.mensaje, 'Error');
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error');
    }
}

async function apartarTodo() {
    if (!usuarioActivo) {
        mostrarModal('🔐 Debes iniciar sesión para apartar productos', 'Acción no permitida');
        return;
    }
    if (carrito.length === 0) {
        mostrarModal('No hay productos en el carrito', 'Carrito vacío');
        return;
    }
    
    try {
        const response = await fetch('api_guardar_promesa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productos: carrito })
        });
        const resultado = await response.json();
        if (resultado.success) {
            const fechaLimite = new Date();
            fechaLimite.setDate(fechaLimite.getDate() + 7);
            carrito.forEach(item => {
                promesas.push({ ...item, fechaLimite: fechaLimite.toISOString(), fechaPromesa: new Date().toISOString() });
                const producto = catalogoProductos.find(p => p.id === item.id);
                if (producto) producto.stock -= item.cantidad;
            });
            carrito = [];
            guardarDatos();
            renderizarTodo();
            mostrarModal(`${resultado.cantidad} productos apartados por 24 horas.`, 'Productos apartados');
        } else {
            mostrarModal(resultado.mensaje, 'Error');
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error');
    }
}


function cancelarPromesa(id) {
    if (!usuarioActivo) {
        mostrarModal('🔐 Debes iniciar sesión para cancelar promesas', 'Acción no permitida');
        return;
    }
    const item = promesas.find(p => p.id === id);
    if (!item) return;
    const producto = catalogoProductos.find(p => p.id === item.id);
    if (producto) producto.stock += item.cantidad;
    promesas = promesas.filter(p => p.id !== id);
    guardarDatos();
    renderizarTodo();
    mostrarModal(`Promesa cancelada. ${item.descripcion} devuelto al almacén.`, 'Cancelación');
}

async function finalizarCompra(id) {
    if (!usuarioActivo) {
        mostrarModal('🔐 Debes iniciar sesión para finalizar la compra', 'Acción no permitida');
        return;
    }
    
    const item = promesas.find(p => p.id === id);
    if (!item) return;
    
    try {
        const response = await fetch('api_registrar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                productos: [{ 
                    id: item.id, 
                    cantidad: item.cantidad, 
                    precio: item.precio 
                }],
                esPromesa: true 
            })
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            // Mover de promesas a comprados
            comprados.push({
                ...item,
                fechaCompra: new Date().toISOString()
            });
            
            // Eliminar de promesas
            promesas = promesas.filter(p => p.id !== id);
            
            guardarDatos();
            renderizarTodo();
            
            mostrarModal(`¡Compra finalizada!<br>Guarda el siguiente texto para poder facturar:<br><strong>${resultado.numero_ticket}</strong>`, 'Compra exitosa');
            
            if (confirm('¿Deseas facturar esta compra?')) {
                window.location.href = `Ferreteria_Facturacion_BORRADOR.php?ticket=${resultado.numero_ticket}`;
            }
        } else {
            mostrarModal(resultado.mensaje, 'Error');
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error');
    }
}


function eliminarDelCarrito(id) {
    carrito = carrito.filter(i => i.id !== id);
    guardarDatos();
    renderizarCarrito();
}

function actualizarCantidadCarrito(id, nuevaCantidad) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;
    const producto = catalogoProductos.find(p => p.id === id);
    if (nuevaCantidad <= 0) {
        eliminarDelCarrito(id);
    } else if (producto && nuevaCantidad > producto.stock) {
        mostrarModal(`Stock máximo disponible: ${producto.stock}`, 'Stock excedido');
    } else {
        item.cantidad = nuevaCantidad;
        guardarDatos();
        renderizarCarrito();
    }
}

function diasRestantes(fechaLimite) {
    const hoy = new Date();
    const limite = new Date(fechaLimite);
    return Math.ceil((limite - hoy) / (1000 * 60 * 60 * 24));
}

function renderizarCarrito() {
    console.log("🔄 Renderizando carrito, productos:", carrito);
    const tbody = document.getElementById('cotizacionBody');
    let subtotal = 0;
    
    if (!carrito || carrito.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-msg">No hay productos en cotización</td></tr>';
        document.getElementById('subtotal').innerText = '$0.00';
        document.getElementById('iva').innerText = '$0.00';
        document.getElementById('total').innerText = '$0.00';
        return;
    }
    
    const filas = carrito.map(item => {
        // Asegurar que el precio sea número
        const precio = parseFloat(item.precio) || 0;
        const cantidad = parseInt(item.cantidad) || 1;
        const totalItem = precio * cantidad;
        subtotal += totalItem;
        
        // Obtener descripción correctamente
        const descripcion = item.descripcion || item.nombre || 'Producto';
        const codigo = item.codigo || item.clave || 'N/A';
        
        return `
            <tr>
                <td>${codigo}</td>
                <td style="text-align:left">${descripcion}</td>
                <td>$${precio.toFixed(2)}</td>
                <td><input type="number" class="cantidad-input" value="${cantidad}" min="1" onchange="actualizarCantidadCarrito(${item.id}, parseInt(this.value))"></td>
                <td>$${totalItem.toFixed(2)}</td>
                <td>
                    <button style="background:#28a745; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;" onclick="comprarProductoDirecto(${item.id})">Comprar</button>
                    <button style="background:#ffc107; color:#333; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;" onclick="comprarAhora(${item.id})">Apartar</button>
                    <button style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px; margin:0 2px;" onclick="eliminarDelCarrito(${item.id})">Eliminar</button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = filas.join('');
    const iva = subtotal * 0.16;
    const total = subtotal + iva;
    document.getElementById('subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('iva').innerText = `$${iva.toFixed(2)}`;
    document.getElementById('total').innerText = `$${total.toFixed(2)}`;
}

function renderizarPromesas() {
    const tbody = document.getElementById('promesaBody');
    if (promesas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-msg">No hay productos en promesa</td></tr>';
        return;
    }
    tbody.innerHTML = promesas.map(p => {
        const dias = diasRestantes(p.fechaLimite);
        const claseUrgente = dias <= 2 ? 'urgente' : '';
        return `
            <tr>
                <td>${p.codigo}</td>
                <td style="text-align:left">${p.descripcion}</td>
                <td>${p.cantidad}</td>
                <td>$${(p.precio * p.cantidad).toFixed(2)}</td>
                <td>${new Date(p.fechaLimite).toLocaleDateString()}</td>
                <td class="contador ${claseUrgente}">${dias} días</td>
                <td>
                    <button style="background:#28a745; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;" onclick="finalizarCompra(${p.id})">Pagar</button> 
                    <button style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px; margin:0 2px;" onclick="cancelarPromesa(${p.id})">Cancelar</button></td>
            </tr>
        `;
    }).join('');
}

function renderizarComprados() {
    const tbody = document.getElementById('compradoBody');
    if (comprados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-msg">No hay productos comprados aún</td></tr>';
        return;
    }
    tbody.innerHTML = comprados.map(c => `
        <tr>
            <td>${c.codigo}</td>
            <td style="text-align:left">${c.descripcion}</td>
            <td>${c.cantidad}</td>
            <td>$${(c.precio * c.cantidad).toFixed(2)}</td>
            <td>${new Date(c.fechaCompra).toLocaleDateString()}</td>
        </tr>
    `).join('');
}

function renderizarTodo() {
    renderizarCarrito();
    renderizarPromesas();
    renderizarComprados();
}

function cambiarTab(tabId) {
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active-panel'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`tab-${tabId}`).classList.add('active-panel');
    document.querySelector(`.tab-btn[data-tab="${tabId}"]`).classList.add('active');
}

async function guardarCotizacion() {
    if (carrito.length === 0) {
        mostrarModal('No hay productos en la cotización', 'Carrito vacío');
        return;
    }
    try {
        const response = await fetch('api_guardar_cotizacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productos: carrito })
        });
        const resultado = await response.json();
        if (resultado.success) {
            mostrarModal(`Cotización guardada con éxito. Folio: ${resultado.folio}`, 'Éxito');
        } else {
            mostrarModal(resultado.mensaje, 'Error');
        }
    } catch (error) {
        mostrarModal('Error de conexión con el servidor', 'Error');
    }
}

function enviarCotizacion() {
    if (carrito.length === 0) {
        mostrarModal('No hay productos en la cotización', 'Carrito vacío');
        return;
    }
    mostrarModal(`Cotización enviada. Te contactaremos en breve. Total: $${document.getElementById('total').innerText}`, 'Cotización enviada');
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => cambiarTab(btn.dataset.tab));
});

document.getElementById('buscador').addEventListener('keypress', e => { if (e.key === 'Enter') buscarProductos(); });

setInterval(() => {
    verificarPromesasVencidas();
    renderizarPromesas();
}, 60000);

cargarDatos();
cargarCarritoDesdeLocalStorage();
</script>

<footer style="background-color: #003d71; color: white; margin-top: 60px; padding: 30px; text-align: center;">
    <p>&copy; 2026 Gorilla Tools - Sistema de Promesa de Compra (24 horas para pagar)</p>
</footer>

</body>
</html>