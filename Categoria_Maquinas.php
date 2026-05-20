

mquinaaaaaaaaaaaaaaaaaaaaaaas
<?php
session_start();
require_once 'conexion.php';

$stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria = 'maquinas' ORDER BY nombre");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas y Accesorios - Gorilla Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --azul-navy: #003d71;
            --naranja-fix: #f47920;
            --gris-fondo: #f8f9fa;
            --gris-borde: #e9ecef;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--gris-fondo); }
        .header-main { background-color: var(--azul-navy); }
        .nav-secondary { background-color: var(--naranja-fix); }
        .nav-secondary .nav-link { color: white !important; font-weight: bold; }
        .search-bar input { border-radius: 25px 0 0 25px; border: none; }
        .search-bar button { border-radius: 0 25px 25px 0; background: white; }
        .breadcrumb-custom { background: transparent; padding: 15px 0; margin-bottom: 20px; }
        .breadcrumb-custom a { color: var(--azul-navy); text-decoration: none; }
        .breadcrumb-custom a:hover { color: var(--naranja-fix); }
        .category-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; }
        .category-title { font-size: 28px; font-weight: bold; color: var(--azul-navy); }
        .product-count { color: #6c757d; font-size: 14px; }
        .filters-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; padding: 15px 0; border-bottom: 1px solid var(--gris-borde); }
        .show-select, .sort-select { padding: 8px 15px; border: 1px solid var(--gris-borde); border-radius: 8px; background: white; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .product-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.3s, box-shadow 0.3s; position: relative; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .product-checkbox { position: absolute; top: 12px; right: 12px; width: 20px; height: 20px; cursor: pointer; accent-color: var(--naranja-fix); }
        .product-brand { font-size: 12px; color: var(--naranja-fix); font-weight: bold; margin-bottom: 5px; }
        .product-name { font-size: 14px; font-weight: 600; color: var(--azul-navy); margin-bottom: 8px; min-height: 40px; padding-right: 25px; }
        .product-details { font-size: 11px; color: #6c757d; margin-bottom: 10px; }
        .product-price { font-size: 18px; font-weight: bold; color: var(--azul-navy); margin-bottom: 12px; }
        .selection-info { background: #e8f4fd; border-radius: 10px; padding: 12px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .selected-count { font-weight: bold; color: var(--azul-navy); }
        .btn-cotizar-seleccion { background: var(--azul-navy); color: white; border: none; padding: 12px 30px; border-radius: 30px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .btn-cotizar-seleccion:hover { background: var(--naranja-fix); transform: scale(1.02); }
        .btn-cotizar-todos { background: var(--naranja-fix); color: white; border: none; padding: 12px 30px; border-radius: 30px; font-weight: bold; cursor: pointer; transition: all 0.3s; margin-left: 10px; }
        .btn-cotizar-todos:hover { background: var(--azul-navy); }
        .pagination-container { display: flex; justify-content: center; align-items: center; gap: 10px; margin: 40px 0; flex-wrap: wrap; }
        .pagination-btn { padding: 8px 14px; border: 1px solid var(--gris-borde); background: white; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .pagination-btn:hover { background: var(--azul-navy); color: white; border-color: var(--azul-navy); }
        .pagination-btn.active { background: var(--azul-navy); color: white; border-color: var(--azul-navy); }
        .pagination-btn.disabled { opacity: 0.5; cursor: not-allowed; }
        .toast-notification { position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 12px 24px; border-radius: 8px; z-index: 9999; display: none; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media (max-width: 768px) { .products-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; } .filters-bar { flex-direction: column; align-items: flex-start; } .selection-info { flex-direction: column; align-items: stretch; text-align: center; } }
    </style>
</head>
<body>

<header class="header-main py-3">
    <div class="container-fluid px-5">
        <div class="row align-items-center">
            <div class="col-md-2"><a href="index.php"><img src="Logo_GorilaTools-removebg.png" alt="Logo" style="max-height: 70px;" onerror="this.src='https://via.placeholder.com/150x70?text=Gorilla+Tools'"></a></div>
            <div class="col-md-6"><div class="input-group search-bar"><input type="text" id="buscador" class="form-control" placeholder="Buscar productos..."><button class="btn btn-light" id="btnBuscar"><i class="bi bi-search"></i></button></div></div>
            <div class="col-md-4 d-flex justify-content-end align-items-center text-white"><i class="bi bi-person-circle fs-4 me-2"></i><a href="Ferreteria_Facturacion_BORRADOR.php" class="text-white text-decoration-none">Mi cuenta</a></div>
        </div>
    </div>
</header>

<nav class="nav-secondary py-2"><div class="container"><ul class="nav justify-content-start gap-4 text-uppercase fw-bold"><li class="nav-item"><a href="index.php" class="nav-link text-white">Inicio</a></li><li class="nav-item"><a href="Ferreteria_Facturacion_BORRADOR.php" class="nav-link text-white">Facturación</a></li><li class="nav-item"><a href="Ferreteria_Cotizacion_BORRADOR.php" class="nav-link text-white">Cotizador</a></li><li class="nav-item"><a href="Ferreteria_UbicarTienda_BORRADOR.php" class="nav-link text-white">Ubica tu tienda</a></li></ul></div></nav>

<main class="container my-4">
    <div class="breadcrumb-custom">
        <a href="index.php">Inicio</a> / <a href="#">Máquinas y Accesorios</a>
    </div>
    <div class="category-header">
        <h1 class="category-title">Máquinas y Accesorios <span class="product-count" id="productCount"></span></h1>
    </div>
    <div class="filters-bar">
        <div>
            <label>Mostrar:</label>
            <select id="showPerPage" class="show-select">
                <option value="24">24</option>
                <option value="48">48</option>
                <option value="96">96</option>
            </select>
        </div>
        <div>
            <label>Ordenar:</label>
            <select id="sortBy" class="sort-select">
                <option value="relevancia">Relevancia</option>
                <option value="precio-asc">Precio: menor a mayor</option>
                <option value="precio-desc">Precio: mayor a menor</option>
            </select>
        </div>
    </div>
    
    <!-- Barra de selección con solo el botón de cotizar -->
    <div class="selection-info">
        <div>
            <i class="bi bi-check2-square"></i> Seleccionados: <span id="selectedCount">0</span>
        </div>
        <div>
            <button id="cotizarSeleccionadosBtn" style="background: #f47920; color: white; border: none; padding: 12px 30px; border-radius: 30px; font-weight: bold; cursor: pointer;">
    <i class="bi bi-cart-plus"></i> Cotizar seleccionados
</button>
        </div>
    </div>
    
    <div id="productsGrid" class="products-grid"></div>
    <div id="pagination" class="pagination-container"></div>
</main>

<div id="toastNotification" class="toast-notification"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>
const productos = <?php echo json_encode($productos); ?>;

// Usuario activo desde PHP (NO llamar session_start ni conexion aquí, ya están al inicio del archivo)
const usuarioActivo = <?php 
    $user = null;
    if (isset($_SESSION['usuario_id'])) {
        $user = ['id' => $_SESSION['usuario_id']];
    }
    echo json_encode($user);
?>;

function mostrarModal(mensaje, titulo = "Notificación") {
    const modalElement = document.getElementById('modalAlerta');
    if (modalElement) {
        document.getElementById('modalAlertaTitulo').innerText = titulo;
        document.getElementById('modalAlertaMensaje').innerHTML = mensaje;
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

let seleccionados = new Set(), currentPage = 1, itemsPerPage = 24, currentSort = "relevancia", searchTerm = "";

function notificar(m) { 
    const t = document.getElementById('toastNotification'); 
    t.textContent = m; 
    t.style.display = 'block'; 
    setTimeout(() => t.style.display = 'none', 3000); 
}

function actContador() { 
    document.getElementById('selectedCount').innerText = seleccionados.size; 
}

function guardarSeleccionadosEnStorage() {
    if (!usuarioActivo) return;
    const idsSeleccionados = Array.from(seleccionados);
    sessionStorage.setItem(`seleccionados_${usuarioActivo.id}`, JSON.stringify(idsSeleccionados));
    console.log("💾 Seleccionados guardados:", idsSeleccionados);
}

function cargarSeleccionadosDesdeStorage() {
    if (!usuarioActivo) return;
    const guardado = sessionStorage.getItem(`seleccionados_${usuarioActivo.id}`);
    if (guardado) {
        const ids = JSON.parse(guardado);
        seleccionados.clear();
        ids.forEach(id => seleccionados.add(id));
        actContador();
        renderizar();
        console.log("📦 Seleccionados cargados:", ids);
    }
}

function toggleSel(id) { 
    if(seleccionados.has(id)) {
        seleccionados.delete(id);
    } else {
        seleccionados.add(id);
    }
    actContador(); 
    
    const cb = document.getElementById(`chk_${id}`); 
    if(cb) cb.checked = seleccionados.has(id);
    
    guardarSeleccionadosEnStorage();
    console.log("🔄 Toggle producto:", id);
}

function filtrar() { 
    let f = productos.filter(p => !searchTerm || p.nombre.toLowerCase().includes(searchTerm) || p.clave.toLowerCase().includes(searchTerm)); 
    if(currentSort === 'precio-asc') f.sort((a, b) => a.precio - b.precio); 
    if(currentSort === 'precio-desc') f.sort((a, b) => b.precio - a.precio); 
    return f; 
}

function cotizar(productosList) {
    if (productosList.length === 0) {
        notificar('No hay productos seleccionados');
        return;
    }
    
    fetch('api_obtener_usuario.php')
        .then(response => response.json())
        .then(usuario => {
            if (!usuario.success) {
                mostrarModal('Debes iniciar sesión para cotizar productos', 'Acción no permitida');
                return;
            }
            
            const productosParaCarrito = productosList.map(p => ({ 
                id: p.id, 
                codigo: p.codigo || p.clave || 'N/A',
                descripcion: p.nombre,
                precio: parseFloat(p.precio),
                cantidad: 1
            }));
            
            fetch('api_guardar_carrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ productos: productosParaCarrito })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    notificar(`${productosList.length} producto(s) enviados al cotizador`);
                    sessionStorage.removeItem(`seleccionados_${usuario.id}`);
                    seleccionados.clear();
                    actContador();
                    renderizar();
                    
                    setTimeout(() => {
                        window.location.href = 'Ferreteria_Cotizacion_BORRADOR.php';
                    }, 500);
                } else {
                    mostrarModal('Error al guardar', 'Error');
                }
            });
        })
        .catch(() => {
            mostrarModal('Error de conexión', 'Error');
        });
}

function cotizarSel() { 
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const idsSeleccionados = Array.from(checkboxes).map(cb => parseInt(cb.id.replace('chk_', '')));
    
    if (idsSeleccionados.length === 0) {
        notificar('No hay productos seleccionados');
        return;
    }
    
    const productosSeleccionados = productos.filter(p => idsSeleccionados.includes(p.id));
    cotizar(productosSeleccionados);
}

function renderizar() {
    const filtrados = filtrar();
    document.getElementById('productCount').innerHTML = `(${filtrados.length})`;
    const start = (currentPage - 1) * itemsPerPage;
    const pag = filtrados.slice(start, start + itemsPerPage);
    const grid = document.getElementById('productsGrid');
    if(pag.length === 0){
        grid.innerHTML = '<div class="text-center py-5"><h3>No hay productos</h3></div>';
        return;
    }
    grid.innerHTML = pag.map(p => `
        <div class="product-card">
            <input type="checkbox" class="product-checkbox" id="chk_${p.id}" ${seleccionados.has(p.id) ? 'checked' : ''} onchange="toggleSel(${p.id})">
            <div class="product-brand">${p.marca}</div>
            <div class="product-name">${p.nombre}</div>
            <div class="product-details">Clave: ${p.clave}</div>
            <div class="product-price">$${parseFloat(p.precio).toFixed(2)}</div>
        </div>
    `).join('');
    
    const totalPages = Math.ceil(filtrados.length / itemsPerPage);
    let html = `<button class="pagination-btn" onclick="cambiarPag(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>«</button>`;
    for(let i = 1; i <= totalPages; i++) html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="cambiarPag(${i})">${i}</button>`;
    html += `<button class="pagination-btn" onclick="cambiarPag(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>»</button>`;
    document.getElementById('pagination').innerHTML = html;
}

function cambiarPag(p) { 
    const total = Math.ceil(filtrar().length / itemsPerPage); 
    if(p >= 1 && p <= total){ 
        currentPage = p; 
        renderizar(); 
        window.scrollTo({top: 300}); 
    } 
}

document.getElementById('showPerPage')?.addEventListener('change', (e) => { itemsPerPage = parseInt(e.target.value); currentPage = 1; renderizar(); });
document.getElementById('sortBy')?.addEventListener('change', (e) => { currentSort = e.target.value; currentPage = 1; renderizar(); });
document.getElementById('btnBuscar')?.addEventListener('click', () => { searchTerm = document.getElementById('buscador').value; currentPage = 1; renderizar(); });
document.getElementById('cotizarSeleccionadosBtn')?.addEventListener('click', cotizarSel);
document.getElementById('cotizarTodosBtn')?.addEventListener('click', () => { selTodos(); setTimeout(cotizarSel, 100); });

renderizar(); 
actContador();
cargarSeleccionadosDesdeStorage();
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
</body>
</html>