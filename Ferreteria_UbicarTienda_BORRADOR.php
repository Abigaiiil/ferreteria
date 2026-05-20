<?php
session_start();
require_once 'conexion.php';

// Verificar si hay usuario logueado (opcional para esta página)
$usuario_activo = null;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_activo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubica tu tienda - Gorilla Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Leaflet.js para mapas -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --azul-navy: #003d71;
            --naranja-fix: #f47920;
            --rojo-gorilla: #ff000f;
            --gris-fondo: #f4f7f6;
            --gris-borde: #ddd;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--gris-fondo);
        }

        /* Barra de navegación */
        .nav-secondary {
            background-color: var(--naranja-fix);
            padding: 12px 0;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-secondary .nav {
            display: flex;
            list-style: none;
            margin: 0 auto;
            padding: 0 50px;
            max-width: 1200px;
        }

        .nav-secondary .nav-item {
            margin-right: 30px;
        }

        .nav-secondary .nav-link {
            color: white !important;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .nav-secondary .nav-link:hover {
            opacity: 0.8;
        }

        /* Contenedor principal */
        .container-tiendas {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .titulo-seccion {
            color: var(--azul-navy);
            font-size: 28px;
            margin-bottom: 25px;
        }

        .flecha-roja {
            color: var(--rojo-gorilla);
            font-size: 32px;
            margin-right: 10px;
        }

        /* Buscador mejorado */
        .search-area {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .input-wrapper {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 45px 14px 20px;
            border: 2px solid var(--gris-borde);
            border-radius: 30px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--naranja-fix);
        }

        .btn-geo {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--rojo-gorilla);
            cursor: pointer;
            font-size: 18px;
        }

        .btn-buscar {
            background-color: var(--rojo-gorilla);
            color: white;
            border: none;
            padding: 0 35px;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-buscar:hover {
            transform: scale(1.02);
        }

        /* Filtros rápidos */
        .filtros-rapidos {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filtro-btn {
            background: white;
            border: 1px solid var(--gris-borde);
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filtro-btn:hover, .filtro-btn.activo {
            background: var(--azul-navy);
            color: white;
            border-color: var(--azul-navy);
        }

        /* Mapa y lista */
        .map-interface {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 20px;
            height: 600px;
        }

        .results-panel {
            background: white;
            border-radius: 15px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .results-header {
            background: var(--azul-navy);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            position: sticky;
            top: 0;
        }

        .store-card {
            padding: 20px;
            border-bottom: 1px solid var(--gris-borde);
            cursor: pointer;
            transition: background 0.2s;
        }

        .store-card:hover {
            background: #f9f9f9;
        }

        .store-card.seleccionada {
            background: #fff3e0;
            border-left: 4px solid var(--naranja-fix);
        }

        .store-name {
            color: var(--rojo-gorilla);
            font-size: 18px;
            margin: 0 0 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .distancia {
            font-size: 12px;
            background: var(--azul-navy);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
        }

        .store-address {
            font-size: 14px;
            line-height: 1.5;
            color: #555;
            margin-bottom: 10px;
        }

        .store-tel {
            font-size: 14px;
            margin-bottom: 8px;
        }

        .store-tel i, .store-address i {
            margin-right: 8px;
            color: var(--naranja-fix);
        }

        .view-hours {
            color: var(--azul-navy);
            font-size: 13px;
            text-decoration: none;
            font-weight: bold;
        }

        .view-hours:hover {
            text-decoration: underline;
        }

        .horarios {
            margin-top: 10px;
            padding: 10px;
            background: #f8f8f8;
            border-radius: 8px;
            font-size: 12px;
            display: none;
        }

        .horarios.visible {
            display: block;
        }

        .service-center {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed var(--gris-borde);
        }

        .service-center i {
            font-size: 24px;
            color: var(--naranja-fix);
        }

        /* Mapa */
        .map-display {
            background: #eee;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Modal de horarios */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 25px;
            max-width: 400px;
            width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .close-modal {
            cursor: pointer;
            font-size: 24px;
        }

        /* Header personalizado */
        .header-main {
            background-color: var(--azul-navy);
            padding: 15px 0;
        }

        .user-menu a {
            color: white;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .map-interface {
                grid-template-columns: 1fr;
                height: auto;
            }
            .map-display {
                height: 400px;
            }
            .nav-secondary .nav {
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>

<!-- Header principal -->
<header class="header-main">
    <div class="container-fluid px-5">
        <div class="row align-items-center">
            <div class="col-md-2">
                <a href="index.php">
                    <img src="Logo_GorilaTools-removebg.png" alt="Logo Gorilla Tools" style="max-height: 70px;" 
                         onerror="this.src='https://via.placeholder.com/150x70?text=Gorilla+Tools'">
                </a>
            </div>
            <div class="col-md-6">
                <div class="input-group search-bar" style="display: none;">
                    <!-- Espacio reservado -->
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-end align-items-center text-white">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <?php if ($usuario_activo): ?>
                    <div class="dropdown">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars(explode(' ', $usuario_activo['nombre'])[0]); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="Ferreteria_Facturacion_BORRADOR.php">Mis facturas</a></li>
                            <li><a class="dropdown-item" href="Ferreteria_Cotizacion_BORRADOR.php">Mis cotizaciones</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="index.php" class="text-white text-decoration-none">Iniciar sesión | Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<nav class="nav-secondary">
    <ul class="nav">
        <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
        <li class="nav-item"><a href="Ferreteria_Facturacion_BORRADOR.php" class="nav-link">Facturación</a></li>
        <li class="nav-item"><a href="Ferreteria_Cotizacion_BORRADOR.php" class="nav-link">Cotizador</a></li>
        <li class="nav-item"><a href="Ferreteria_UbicarTienda_BORRADOR.php" class="nav-link">Ubica tu tienda</a></li>
    </ul>
</nav>

<main class="container-tiendas">
    <h2 class="titulo-seccion">
        <span class="flecha-roja">›</span> Encuentra tu tienda más cercana
    </h2>

    <!-- Buscador -->
    <section class="search-area">
        <div class="input-wrapper">
            <input type="text" id="buscador" placeholder="Escribe una ciudad...">
            <button class="btn-geo" id="btnGeolocalizar" title="Usar mi ubicación">
                <i class="bi bi-cursor-fill"></i>
            </button>
        </div>
        <button class="btn-buscar" id="btnBuscar">Buscar</button>
    </section>

    <!-- Filtros rápidos -->
    <div class="filtros-rapidos">
        <button class="filtro-btn activo" data-filtro="todas">Todas</button>

        <button class="filtro-btn" data-filtro="matamoros">Matamoros</button>
        <button class="filtro-btn" data-filtro="reynosa">Reynosa</button>
    </div>

    <div class="map-interface">
        <aside class="results-panel">
            <div class="results-header">
                <span id="totalTiendas">0</span> tiendas disponibles
            </div>
            <div id="listaTiendas"></div>
        </aside>

        <section class="map-display">
            <div id="map"></div>
        </section>
    </div>
</main>

<!-- Modal de horarios -->
<div id="modalHorarios" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Horarios</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div id="modalHorariosContent"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ========== BASE DE DATOS DE TIENDAS ==========
    const tiendas = [
        {
            id: 1,
            nombre: "Gorilla Tools Matamoros",
            direccion: "Vicente Guerrero #123, Zona Centro, 87300, Heroica Matamoros, Tamps.",
            lat: 25.879,
            lng: -97.504,
            telefono: "868 455 9524",
            horarios: "Lun-Vie: 8:00 - 20:00 | Sáb: 9:00 - 18:00 | Dom: Cerrado",
            centroServicio: false,
            atencion24h: false,
            ciudad: "matamoros",
        },
        {
            id: 2,
            nombre: "Gorilla Tools Reynosa Centro",
            direccion: "Mariano #456, Zona Centro, 88500, Reynosa, Tamps.",
            lat: 26.092,
            lng: -98.278,
            telefono: "899 123 4567",
            horarios: "Lun-Vie: 8:00 - 20:00 | Sáb: 9:00 - 18:00 | Dom: Cerrado",
            centroServicio: false,
            atencion24h: false,
            ciudad: "reynosa",
        },
        {
            id: 3,
            nombre: "Gorilla Tools Reynosa Sur",
            direccion: "Boulevard Morelos #789, Colonia del Bosque, 88700, Reynosa, Tamps.",
            lat: 26.055,
            lng: -98.285,
            telefono: "899 987 6543",
            horarios: "Lun-Vie: 8:00 - 20:00 | Sáb: 9:00 - 18:00 | Dom: Cerrado",
            centroServicio: false,
            atencion24h: false,
            ciudad: "reynosa",
        },
        {
            id: 4,
            nombre: "Gorilla Tools Matamoros Norte",
            direccion: "Av. Lauro Villar #1000, Colonia Modelo, 87390, Matamoros, Tamps.",
            lat: 25.895,
            lng: -97.512,
            telefono: "868 333 2222",
            horarios: "Lun-Vie: 8:00 - 20:00 | Sáb: 9:00 - 18:00 | Dom: Cerrado",
            centroServicio: false,
            atencion24h: false,
            ciudad: "matamoros",
        }
    ];

    let mapa;
    let marcadores = [];
    let tiendaSeleccionada = null;
    let filtroActual = "todas";

    // Inicializar mapa centrado en Tamaulipas
    function initMap() {
        mapa = L.map('map').setView([25.9, -97.9], 8);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            subdomains: 'abcd',
            maxZoom: 18
        }).addTo(mapa);
    }

    // Crear ícono personalizado
    function crearIcono(tienda) {
        const esCentro = tienda.centroServicio;
        const es24h = tienda.atencion24h;
        let color = esCentro ? '#f47920' : '#003d71';
        if (es24h) color = '#28a745';
        
        const iconHtml = `
            <div style="
                background: ${color};
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid white;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            ">
                <i class="bi bi-tools" style="color: white; font-size: 14px;"></i>
            </div>
        `;
        
        return L.divIcon({
            html: iconHtml,
            iconSize: [30, 30],
            className: 'custom-marker'
        });
    }

    // Mostrar/ocultar horarios
    function toggleHorarios(id, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const horariosDiv = document.getElementById(`horarios-${id}`);
        if (horariosDiv) {
            horariosDiv.classList.toggle('visible');
        }
    }

    // Renderizar lista de tiendas
    function renderizarLista(tiendasFiltradas) {
        const container = document.getElementById('listaTiendas');
        document.getElementById('totalTiendas').innerText = tiendasFiltradas.length;
        
        if (tiendasFiltradas.length === 0) {
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">No se encontraron tiendas</div>';
            return;
        }
        
        container.innerHTML = tiendasFiltradas.map(tienda => `
            <div class="store-card ${tiendaSeleccionada?.id === tienda.id ? 'seleccionada' : ''}" 
                 onclick="seleccionarTienda(${tienda.id})">
                <div class="store-name">
                    ${tienda.nombre}
                    ${tienda.atencion24h ? '<span class="distancia">24h</span>' : ''}
                    ${tienda.centroServicio ? '<span class="distancia" style="background:#f47920">Servicio</span>' : ''}
                </div>
                <div class="store-address">
                    <i class="bi bi-geo-alt-fill"></i> ${tienda.direccion}
                </div>
                <div class="store-tel">
                    <i class="bi bi-telephone-fill"></i> ${tienda.telefono}
                </div>
                <a href="#" class="view-hours" onclick="toggleHorarios(${tienda.id}, event)">Ver horarios</a>
                <div id="horarios-${tienda.id}" class="horarios">
                    <strong>Horario de atención:</strong><br>
                    ${tienda.horarios}<br><br>
                    
                </div>
            </div>
        `).join('');
    }

    // Actualizar marcadores en el mapa
    function actualizarMapa(tiendasFiltradas) {
        // Limpiar marcadores existentes
        marcadores.forEach(m => mapa.removeLayer(m));
        marcadores = [];
        
        tiendasFiltradas.forEach(tienda => {
            const icono = crearIcono(tienda);
            const marker = L.marker([tienda.lat, tienda.lng], { icon: icono })
                .addTo(mapa)
                .bindPopup(`
                    <b>${tienda.nombre}</b><br>
                    ${tienda.direccion}<br>
                    📞 ${tienda.telefono}<br>
                    ${tienda.centroServicio ? '🔧 Centro de servicio' : ''}
                    ${tienda.atencion24h ? '🕐 Atención 24h' : ''}
                `);
            
            marker.on('click', () => seleccionarTienda(tienda.id, true));
            marcadores.push(marker);
        });
        
        // Ajustar zoom para mostrar todas las tiendas
        if (tiendasFiltradas.length > 0) {
            const bounds = L.latLngBounds(tiendasFiltradas.map(t => [t.lat, t.lng]));
            mapa.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Seleccionar una tienda
    function seleccionarTienda(id, scrollToLista = false) {
        tiendaSeleccionada = tiendas.find(t => t.id === id);
        
        // Actualizar lista
        const tiendasFiltradas = filtrarTiendas();
        renderizarLista(tiendasFiltradas);
        
        // Centrar mapa en la tienda seleccionada
        if (tiendaSeleccionada) {
            mapa.setView([tiendaSeleccionada.lat, tiendaSeleccionada.lng], 14);
            
            // Abrir popup del marcador
            const marker = marcadores.find(m => {
                const pos = m.getLatLng();
                return Math.abs(pos.lat - tiendaSeleccionada.lat) < 0.001 && 
                       Math.abs(pos.lng - tiendaSeleccionada.lng) < 0.001;
            });
            if (marker) marker.openPopup();
        }
    }

    // Filtrar tiendas por búsqueda y filtro
    function filtrarTiendas() {
        const busqueda = document.getElementById('buscador').value.toLowerCase();
        let filtradas = [...tiendas];
        
        // Búsqueda por ciudad/dirección
        if (busqueda) {
            filtradas = filtradas.filter(t => 
                t.direccion.toLowerCase().includes(busqueda) ||
                t.nombre.toLowerCase().includes(busqueda) ||
                t.ciudad.toLowerCase().includes(busqueda)
            );
        }
        
        // Aplicar filtro rápido
        if (filtroActual === 'centro') {
            filtradas = filtradas.filter(t => t.centroServicio);
        } else if (filtroActual === '24h') {
            filtradas = filtradas.filter(t => t.atencion24h);
        } else if (filtroActual === 'matamoros') {
            filtradas = filtradas.filter(t => t.ciudad === 'matamoros');
        } else if (filtroActual === 'reynosa') {
            filtradas = filtradas.filter(t => t.ciudad === 'reynosa');
        }
        
        return filtradas;
    }

    // Actualizar toda la UI
    function actualizarUI() {
        const tiendasFiltradas = filtrarTiendas();
        renderizarLista(tiendasFiltradas);
        actualizarMapa(tiendasFiltradas);
    }

    // Geolocalización
    function usarMiUbicacion() {
        if (!navigator.geolocation) {
            mostrarModal('Tu navegador no soporta geolocalización', 'Error');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                mapa.setView([latitude, longitude], 13);
                
                // Agregar marcador de ubicación actual
                const userIcon = L.divIcon({
                    html: '<div style="background: #28a745; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    className: 'user-marker'
                });
                
                L.marker([latitude, longitude], { icon: userIcon })
                    .addTo(mapa)
                    .bindPopup("<strong>📍 Tu ubicación actual</strong>")
                    .openPopup();
                
                // Calcular distancias
                tiendas.forEach(tienda => {
                    const distancia = calcularDistancia(latitude, longitude, tienda.lat, tienda.lng);
                    tienda.distancia = distancia;
                });
                
                const tiendasOrdenadas = [...tiendas].sort((a, b) => a.distancia - b.distancia);
                const tiendasCercanas = tiendasOrdenadas.slice(0, 3);
                
                let mensaje = "📍 Las 3 tiendas más cercanas a ti:\n\n";
                tiendasCercanas.forEach((t, i) => {
                    mensaje += `${i+1}. ${t.nombre}\n   📍 ${t.distancia.toFixed(1)} km\n`;
                });
                mostrarModal(mensaje.replace(/\n/g, '<br>'), 'Tiendas cercanas');
                
            },
            (error) => {
                let mensaje = "Error al obtener ubicación: ";
                switch(error.code) {
                    case error.PERMISSION_DENIED: mensaje += "Permiso denegado"; break;
                    case error.POSITION_UNAVAILABLE: mensaje += "Ubicación no disponible"; break;
                    case error.TIMEOUT: mensaje += "Tiempo de espera agotado"; break;
                    default: mensaje += error.message;
                }
                mostrarModal(mensaje, 'Error');
            }
        );
    }
    
    // Calcular distancia (Haversine)
    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Mostrar modal simple (sin depender de Bootstrap)
    function mostrarModal(mensaje, titulo = "Notificación") {
        // Crear modal temporal si no existe
        let modal = document.getElementById('modalSimple');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modalSimple';
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 2000;
                display: flex; justify-content: center; align-items: center;
            `;
            modal.innerHTML = `
                <div style="background: white; border-radius: 15px; padding: 25px; max-width: 400px; width: 90%;">
                    <h3 style="color: #003d71; margin-bottom: 15px;" id="modalSimpleTitulo"></h3>
                    <div style="margin-bottom: 20px;" id="modalSimpleMensaje"></div>
                    <button style="background: #f47920; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Aceptar</button>
                </div>
            `;
            document.body.appendChild(modal);
            modal.querySelector('button').onclick = () => modal.style.display = 'none';
        }
        document.getElementById('modalSimpleTitulo').innerText = titulo;
        document.getElementById('modalSimpleMensaje').innerHTML = mensaje;
        modal.style.display = 'flex';
    }

    // Ver horarios completos en modal
    function verHorariosCompletos(id) {
        const tienda = tiendas.find(t => t.id === id);
        if (!tienda) return;
        
        document.getElementById('modalTitulo').innerText = tienda.nombre;
        document.getElementById('modalHorariosContent').innerHTML = `
            <p><strong> Horario:</strong><br>${tienda.horarios}</p>
            <p><strong>Teléfono:</strong><br>${tienda.telefono}</p>
            <p><strong> Dirección:</strong><br>${tienda.direccion}</p>
        `;
        document.getElementById('modalHorarios').style.display = 'flex';
    }
    
    // Eventos y listeners
    document.getElementById('btnBuscar').addEventListener('click', actualizarUI);
    document.getElementById('buscador').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') actualizarUI();
    });
    document.getElementById('btnGeolocalizar').addEventListener('click', usarMiUbicacion);
    
    document.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            filtroActual = btn.dataset.filtro;
            actualizarUI();
        });
    });
    
    document.querySelector('.close-modal').addEventListener('click', () => {
        document.getElementById('modalHorarios').style.display = 'none';
    });
    
    window.onclick = (event) => {
        const modal = document.getElementById('modalHorarios');
        if (event.target === modal) modal.style.display = 'none';
    };
    
    // Inicializar
    initMap();
    setTimeout(() => actualizarUI(), 500);
</script>

<!-- Footer -->
<footer style="background-color: #003d71; color: white; margin-top: 60px; padding: 40px 20px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 30px;">
            <div style="flex: 1; min-width: 200px;">
                <img src="Logo_GorilaTools-removebg.png" alt="Gorilla Tools" style="max-height: 50px; margin-bottom: 15px;" 
                     onerror="this.src='https://via.placeholder.com/150x50?text=Gorilla+Tools'">
                <p style="font-size: 14px; opacity: 0.8;">Tu ferretería de confianza con las mejores herramientas y precios competitivos.</p>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <a href="https://www.facebook.com" target="_blank" style="color: white; font-size: 20px;"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com" target="_blank" style="color: white; font-size: 20px;"><i class="bi bi-instagram"></i></a>
                    <a href="https://web.whatsapp.com" target="_blank" style="color: white; font-size: 20px;"><i class="bi bi-whatsapp"></i></a>
                    <a href="https://www.tiktok.com" target="_blank" style="color: white; font-size: 20px;"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Enlaces rápidos</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><a href="index.php" style="color: white; text-decoration: none;">Inicio</a></li>
                    <li style="margin-bottom: 10px;"><a href="Ferreteria_Facturacion_BORRADOR.php" style="color: white; text-decoration: none;">Facturación</a></li>
                    <li style="margin-bottom: 10px;"><a href="Ferreteria_Cotizacion_BORRADOR.php" style="color: white; text-decoration: none;">Cotizador</a></li>
                    <li style="margin-bottom: 10px;"><a href="Ferreteria_UbicarTienda_BORRADOR.php" style="color: white; text-decoration: none;">Ubica tu tienda</a></li>
                </ul>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Contacto</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><i class="bi bi-telephone-fill" style="margin-right: 10px;"></i> 868 455 9524</li>
                    <li style="margin-bottom: 10px;"><i class="bi bi-envelope-fill" style="margin-right: 10px;"></i> comprasonline@gorillatools.com</li>
                    <li style="margin-bottom: 10px;"><i class="bi bi-geo-alt-fill" style="margin-right: 10px;"></i> Matamoros / Reynosa, Tamaulipas</li>
                </ul>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <h4 style="color: #f47920; font-size: 18px; margin-bottom: 15px;">Horarios</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">Lun - Vie: 8:00 - 20:00</li>
                    <li style="margin-bottom: 8px;">Sábado: 9:00 - 18:00</li>
                    <li style="margin-bottom: 8px;">Domingo: Cerrado</li>
                </ul>
            </div>
        </div>
        <hr style="border-color: #f47920; margin: 30px 0 20px;">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 12px; opacity: 0.7;">
            <p>&copy; 2026 Gorilla Tools - Todos los derechos reservados</p>
            <div>
                <a href="aviso_privacidad.html" style="color: white; text-decoration: none; margin-left: 20px;">Aviso de privacidad</a>
                <a href="terminos_y_condiciones.html" style="color: white; text-decoration: none; margin-left: 20px;">Términos y condiciones</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>