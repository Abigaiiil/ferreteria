<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación Electrónica - Generar PDF</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --azul-fix: #003d71;
            --naranja-fix: #f47920;
            --gris-fondo: #f4f7f6;
            --gris-borde: #ddd;
            --verde-exito: #28a745;
            --rojo-error: #dc3545;
            --amarillo-advertencia: #ffc107;
        }
        body { background-color: var(--gris-fondo); font-family: 'Roboto', sans-serif; }
        
        .nav-secondary { background-color: var(--naranja-fix); padding: 12px 0; }
        .nav-secondary .nav { display: flex; list-style: none; margin: 0 auto; padding: 0 50px; max-width: 1200px; }
        .nav-secondary .nav-item { margin-right: 30px; }
        .nav-secondary .nav-link { color: white !important; text-decoration: none; text-transform: uppercase; font-weight: bold; font-size: 14px; }
        
        .container-facturacion { display: flex; max-width: 1200px; margin: 40px auto; gap: 30px; padding: 20px; }
        .info-section { flex: 1.2; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .titulo-azul { color: var(--azul-fix); font-size: 1.6rem; margin-bottom: 15px; }
        .texto-naranja { color: var(--naranja-fix); font-weight: 500; }
        .info-section ul { list-style: none; padding: 0; margin: 15px 0; }
        .info-section ul li { padding: 5px 0; }
        .info-section ul li::before { content: "✔️"; margin-right: 10px; color: var(--naranja-fix); }
        .contacto { margin-top: 20px; font-size: 0.9rem; background: #f9f9f9; padding: 10px; border-radius: 8px; }
        
        .form-section { flex: 1; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        
        /* NUEVA SECCIÓN: VALIDACIÓN DE TICKET */
        .validacion-ticket {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid var(--gris-borde);
        }
        .validacion-ticket h4 {
            color: var(--azul-fix);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .validacion-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .validacion-input-group input {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-validar {
            background: var(--azul-fix);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .resultado-validacion {
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        .resultado-validacion.exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .resultado-validacion.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .resultado-validacion.advertencia {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .productos-ticket {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--gris-borde);
        }
        .productos-ticket table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .productos-ticket th, .productos-ticket td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid var(--gris-borde);
        }
        .productos-ticket th {
            background: var(--azul-fix);
            color: white;
        }
        
        .steps { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .step { flex: 1; text-align: center; position: relative; z-index: 2; }
        .step-number { width: 36px; height: 36px; background: #e0e0e0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: #777; margin-bottom: 8px; }
        .step.active .step-number { background: var(--azul-fix); color: white; }
        .step.completed .step-number { background: var(--verde-exito); color: white; }
        .progress-bar { position: absolute; top: 18px; left: 0; height: 3px; background: #e0e0e0; width: 100%; z-index: 1; }
        .progress-fill { height: 100%; width: 0%; background: var(--verde-exito); transition: width 0.3s; }
        
        .step-panel { display: none; animation: fadeIn 0.3s ease; }
        .step-panel.active-panel { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .required::after { content: " *"; color: red; }
        input, select { width: 100%; padding: 12px; border: 1px solid var(--gris-borde); border-radius: 8px; font-size: 14px; }
        .row-2cols { display: flex; gap: 15px; }
        .row-2cols .form-group { flex: 1; }
        
        .nav-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn-next, .btn-prev, .btn-submit { padding: 12px 28px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-next { background: var(--azul-fix); color: white; }
        .btn-prev { background: #6c757d; color: white; }
        .btn-submit { background: var(--naranja-fix); color: white; }
        
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; display: none; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .spinner { width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid var(--naranja-fix); border-radius: 50%; animation: spin 1s linear infinite; background: white; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        #pdfContent { position: fixed; left: -9999px; top: 0; width: 800px; background: white; padding: 30px; font-family: 'Roboto', sans-serif; }
        
        @media (max-width: 900px) { .container-facturacion { flex-direction: column; } .row-2cols { flex-direction: column; gap: 0; } }
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

<main class="container-facturacion">
    <section class="info-section">
    <h1 class="titulo-azul">Facturación Electrónica<br>Gorilla Tools</h1>
    <p class="texto-naranja"><strong>Completa los datos para generar tu factura.</strong></p>
    <p>Solo es posible facturar tickets dentro de los 90 días naturales siguientes a la fecha de compra.</p>
    <div class="requisitos">
        <p><strong>Datos necesarios:</strong></p>
        <ul>
            <li>Número de venta (Ticket)</li>
            <li>Fecha de compra</li>
            <li>Sucursal</li>
            <li>Monto total</li>
        </ul>
    </div>
    <p class="contacto">Atención: <strong>868 455 9524</strong><br> comprasonline@gorillatools.com</p>
</section>

    <div class="form-section">
        <!-- ========== NUEVA SECCIÓN: VALIDACIÓN DE TICKET ========== -->
        <div class="validacion-ticket">
            <h4>Validar ticket de compra</h4>
            <div class="validacion-input-group">
                <input type="text" id="validarTicketInput" placeholder="Ingresa el número de ticket (Ej: G-123456, TICKET-001)">
                <button class="btn-validar" onclick="validarTicket()">Validar ticket</button>
            </div>
            <div id="resultadoValidacion" class="resultado-validacion"></div>
        </div>

        <div class="steps">
            <div class="step active" data-step="1"><div class="step-number">1</div><div class="step-label">Datos de factura</div></div>
            <div class="step" data-step="2"><div class="step-number">2</div><div class="step-label">Datos de compra</div></div>
            <div class="step" data-step="3"><div class="step-number">3</div><div class="step-label">Datos fiscales</div></div>
            <div class="progress-bar"><div class="progress-fill"></div></div>
        </div>

        <div id="successMessage" class="success-message"> ¡Factura generada con éxito! Descargando PDF...</div>

        <form id="facturacionForm">
    <!-- PASO 1: Datos de facturación -->
    <div id="step1" class="step-panel active-panel">
        <h3 style="margin-bottom: 20px; color: var(--azul-fix);">Datos de facturación</h3>
        <div class="form-group">
            <label class="required">Nombre completo / Razón social</label>
            <input type="text" id="nombreCliente" placeholder="Ej: Juan Pérez">
        </div>
        <div class="form-group">
            <label class="required">Correo electrónico</label>
            <input type="email" id="emailCliente" placeholder="cliente@ejemplo.com">
        </div>
        <div class="row-2cols">
            <div class="form-group"><label>Teléfono</label><input type="tel" id="telefono" placeholder="868 123 4567"></div>
            <div class="form-group"><label>País</label><select id="pais"><option>México</option><option>Estados Unidos</option></select></div>
        </div>
    </div>

            <div id="step2" class="step-panel">
                <h3 style="margin-bottom: 20px; color: var(--azul-fix);">Datos de la compra</h3>
                <div class="form-group">
                    <label class="required">Número de ticket / venta</label>
                    <input type="text" id="numTicket" placeholder="Ej: G-123456">
                </div>
                <div class="row-2cols">
                    <div class="form-group"><label class="required">Fecha de compra</label><input type="date" id="fechaCompra"></div>
                    <div class="form-group"><label class="required">Monto total (MXN)</label><input type="number" step="0.01" id="montoTotal" placeholder="0.00"></div>
                </div>
                <div class="form-group">
                    <label class="required">Sucursal</label>
                    <select id="sucursal">
                        <option value="">Seleccionar</option>
                        <option>Reynosa GT Centro</option>
                        <option>Reynosa GT Sur</option>
                        <option>Matamoros GT Lauro Villar</option>
                        <option>Matamoros GT Centro</option>
                    </select>
                </div>
                <div class="form-group"><label>Comentarios</label><input type="text" id="comentarios" placeholder="Opcional"></div>
            </div>

            <div id="step3" class="step-panel">
                <h3 style="margin-bottom: 20px; color: var(--azul-fix);">Datos fiscales</h3>
                <div class="form-group"><label class="required">RFC</label><input type="text" id="rfc" placeholder="XAXX010101000" maxlength="13"></div>
                <div class="form-group">
                    <label class="required">Régimen fiscal</label>
                    <select id="regimenFiscal">
                        <option value="">Seleccionar</option>
                        <option>General de Ley Personas Morales</option>
                        <option>Personas Físicas con Actividades Empresariales</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="required">Uso de CFDI</label>
                    <select id="usoCFDI">
                        <option value="">Seleccionar</option>
                        <option>Gastos en general</option>
                        <option>Honorarios médicos</option>
                    </select>
                </div>
                <div class="form-group"><label>Código postal</label><input type="text" id="cp" placeholder="87000"></div>
            </div>

            <div class="nav-buttons">
                <button type="button" id="prevBtn" class="btn-prev" style="display: none;">← Atrás</button>
                <button type="button" id="nextBtn" class="btn-next">Siguiente →</button>
                <button type="submit" id="submitBtn" class="btn-submit" style="display: none;"> Generar Factura PDF</button>
            </div>
        </form>
    </div>
</main>

<div id="pdfContent">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #003d71;">FACTURA ELECTRÓNICA</h2>
        <p style="color: #f47920;">Gorilla Tools - Ferretería Industrial</p>
    </div>
    <div id="pdfDynamicContent"></div>
</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <p style="color: white; margin-top: 20px;">Generando PDF...</p>
</div>

<script>
// ========== VALIDACIÓN DE TICKET CON BASE DE DATOS REAL ==========
let ticketValidadoActual = null;
let ticketYaFacturado = false;

async function validarTicket() {
    const ticketIngresado = document.getElementById('validarTicketInput').value.trim().toUpperCase();
    const resultadoDiv = document.getElementById('resultadoValidacion');
    
    if (!ticketIngresado) {
        resultadoDiv.innerHTML = '<strong>Error:</strong> Ingresa un número de ticket.';
        resultadoDiv.className = 'resultado-validacion error';
        resultadoDiv.style.display = 'block';
        return;
    }
    
    try {
        const response = await fetch(`api_validar_ticket.php?ticket=${ticketIngresado}`);
        const resultado = await response.json();
        
        if (resultado.success) {
            // Autollenar datos de compra
            document.getElementById('numTicket').value = resultado.ticket.numero_ticket;
            document.getElementById('fechaCompra').value = resultado.ticket.fecha_compra;
            document.getElementById('sucursal').value = resultado.ticket.sucursal;
            document.getElementById('montoTotal').value = resultado.ticket.monto_total;
            
            // Generar HTML de productos
            let productosHtml = '<table><thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>';
            resultado.productos.forEach(p => {
                const subtotal = p.cantidad * p.precio_unitario;
                productosHtml += `<tr>
                    <td>${p.codigo}</td>
                    <td>${p.nombre}</td>
                    <td>${p.cantidad}</td>
                    <td>$${p.precio_unitario.toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                </tr>`;
            });
            productosHtml += '</tbody></table>';
            
            resultadoDiv.innerHTML = `
                <strong>Ticket válido</strong><br>
                Ticket: ${resultado.ticket.numero_ticket}<br>
                Monto: $${resultado.ticket.monto_total}<br>
                Fecha: ${resultado.ticket.fecha_compra}<br>
                <div class="productos-ticket">${productosHtml}</div>
            `;
            resultadoDiv.className = 'resultado-validacion exito';
            resultadoDiv.style.display = 'block';
            
            ticketValidadoActual = ticketIngresado;
            ticketYaFacturado = false;
            
            // Habilitar botón de generar factura
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').style.opacity = '1';
            document.getElementById('submitBtn').style.cursor = 'pointer';
            
        } else {
            resultadoDiv.innerHTML = `<strong>ERROR</strong><br>${resultado.mensaje}`;
            resultadoDiv.className = 'resultado-validacion error';
            resultadoDiv.style.display = 'block';
            ticketValidadoActual = null;
            ticketYaFacturado = false;
            document.getElementById('submitBtn').disabled = true;
        }
    } catch (error) {
        resultadoDiv.innerHTML = '<strong>Error</strong> No se pudo conectar con el servidor';
        resultadoDiv.className = 'resultado-validacion error';
        resultadoDiv.style.display = 'block';
    }
}
    
    // Generar tabla de productos para tickets disponibles
    function generarTablaProductosDisponibles(productos) {
        let html = '<table style="width:100%; border-collapse:collapse;">';
        html += '<thead><tr style="background:#28a745; color:white;"><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>';
        
        productos.forEach(p => {
            const subtotal = p.cantidad * p.precio;
            html += `<tr>
                        <td>${p.codigo}</td>
                        <td>${p.descripcion}</td>
                        <td>${p.cantidad}</td>
                        <td>$${p.precio.toFixed(2)}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                     </tr>`;
        });
        html += '</tbody></table>';
        return html;
    }
    
    // ========== CONTROL DE PASOS (igual que antes) ==========
    let currentStep = 1;
    const totalSteps = 3;
    const stepPanels = {1: document.getElementById('step1'), 2: document.getElementById('step2'), 3: document.getElementById('step3')};
    const progressFill = document.querySelector('.progress-fill');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const successMsgDiv = document.getElementById('successMessage');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    function updateStepsUI() {
        for (let i = 1; i <= totalSteps; i++) {
            const stepDiv = document.querySelector(`.step[data-step="${i}"]`);
            if (i < currentStep) { stepDiv.classList.add('completed'); stepDiv.classList.remove('active'); }
            else if (i === currentStep) { stepDiv.classList.add('active'); stepDiv.classList.remove('completed'); }
            else { stepDiv.classList.remove('active', 'completed'); }
        }
        for (let i = 1; i <= totalSteps; i++) {
            if (i === currentStep) stepPanels[i].classList.add('active-panel');
            else stepPanels[i].classList.remove('active-panel');
        }
        const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressFill.style.width = `${progressPercent}%`;
        
        if (currentStep === 1) { prevBtn.style.display = 'none'; nextBtn.style.display = 'inline-block'; submitBtn.style.display = 'none'; }
        else if (currentStep === totalSteps) { prevBtn.style.display = 'inline-block'; nextBtn.style.display = 'none'; submitBtn.style.display = 'inline-block'; }
        else { prevBtn.style.display = 'inline-block'; nextBtn.style.display = 'inline-block'; submitBtn.style.display = 'none'; }
    }
    
    function validateStep(step) {
        if (step === 1) {
            const nombre = document.getElementById('nombreCliente').value.trim();
            const email = document.getElementById('emailCliente').value.trim();
            if (!nombre) { alert('Ingresa el nombre completo'); return false; }
            if (!email || !email.includes('@')) { alert('Ingresa un correo válido'); return false; }
            return true;
        }
        else if (step === 2) {
            const ticket = document.getElementById('numTicket').value.trim();
            const fecha = document.getElementById('fechaCompra').value;
            const monto = document.getElementById('montoTotal').value;
            const sucursal = document.getElementById('sucursal').value;
            
            if (!ticket) {
                alert('Primero valida un ticket');
                return false;
            }
            if (!fecha) {
                alert('Fecha de compra obligatoria');
                return false;
            }
            if (!monto || parseFloat(monto) <= 0) {
                alert('Monto mayor a 0');
                return false;
            }
            if (!sucursal) {
                alert('Selecciona una sucursal');
                return false;
            }
            return true;
        }
        else if (step === 3) {
            const rfc = document.getElementById('rfc').value.trim();
            if (!rfc) { alert('RFC obligatorio'); return false; }
            if (rfc.length < 12 && rfc !== 'XAXX010101000') { alert('RFC inválido (mínimo 12 caracteres)'); return false; }
            return true;
        }
        return true;
    }
    
    function nextStep() {
    if (validateStep(currentStep)) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepsUI();
            window.scrollTo({ top: document.querySelector('.form-section').offsetTop - 20, behavior: 'smooth' });
        }
    }
}
    function prevStep() { if (currentStep > 1) { currentStep--; updateStepsUI(); } }
    
    function generarContenidoPDF(datos) {
    const folioFiscal = 'GOR-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
    const fechaActual = new Date().toLocaleString('es-MX');
    const subtotal = parseFloat(datos.monto) / 1.16;
    const iva = parseFloat(datos.monto) - subtotal;
    
    return `
    <div style="width: 100%; font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto;">
        <div style="text-align: center; border-bottom: 2px solid #f47920; padding-bottom: 10px; margin-bottom: 15px;">
            <h1 style="color: #003d71; margin: 0; font-size: 22px;">GORILLA TOOLS</h1>
            <p style="color: #f47920; margin: 3px 0; font-size: 12px;">Ferretería Industrial</p>
            <p style="font-size: 10px; color: #666; margin: 0;">RFC: GOTOOLS123456 | Tel: 868 455 9524</p>
        </div>

        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="background: #003d71; color: white; padding: 5px 15px; display: inline-block; border-radius: 5px; font-size: 16px; margin: 0;">FACTURA ELECTRÓNICA</h2>
            <p style="margin: 8px 0 3px 0; font-size: 11px;"><strong>Folio Fiscal:</strong> ${folioFiscal}</p>
            <p style="margin: 0; font-size: 11px;"><strong>Fecha de emisión:</strong> ${fechaActual}</p>
        </div>

        <div style="border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; border-radius: 5px;">
            <h3 style="color: #003d71; margin: 0 0 8px 0; font-size: 14px;">DATOS DEL CLIENTE</h3>
            <table style="width: 100%; font-size: 11px;">
                <tr><td style="padding: 3px; width: 100px;"><strong>Nombre:</strong></td><td style="padding: 3px;">${datos.cliente}</td></tr>
                <tr><td style="padding: 3px;"><strong>RFC:</strong></td><td style="padding: 3px;">${datos.rfc}</td></tr>
                <tr><td style="padding: 3px;"><strong>Correo:</strong></td><td style="padding: 3px;">${datos.email || 'No especificado'}</td></tr>
                <tr><td style="padding: 3px;"><strong>Teléfono:</strong></td><td style="padding: 3px;">${datos.telefono || 'No especificado'}</td></tr>
            </table>
        </div>

        <div style="border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; border-radius: 5px;">
            <h3 style="color: #003d71; margin: 0 0 8px 0; font-size: 14px;">DATOS DE LA COMPRA</h3>
            <table style="width: 100%; font-size: 11px;">
                <tr><td style="padding: 3px; width: 100px;"><strong>Ticket:</strong></td><td style="padding: 3px;">${datos.ticket}</td></tr>
                <tr><td style="padding: 3px;"><strong>Fecha compra:</strong></td><td style="padding: 3px;">${datos.fecha}</td></tr>
                <tr><td style="padding: 3px;"><strong>Sucursal:</strong></td><td style="padding: 3px;">${datos.sucursal}</td></tr>
            </table>
        </div>

        <div style="border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; border-radius: 5px;">
            <h3 style="color: #003d71; margin: 0 0 8px 0; font-size: 14px;">DATOS FISCALES</h3>
            <table style="width: 100%; font-size: 11px;">
                <tr><td style="padding: 3px; width: 100px;"><strong>Régimen fiscal:</strong></td><td style="padding: 3px;">${datos.regimen || 'No especificado'}</td></tr>
                <tr><td style="padding: 3px;"><strong>Uso de CFDI:</strong></td><td style="padding: 3px;">${datos.usoCFDI || 'No especificado'}</td></tr>
                <tr><td style="padding: 3px;"><strong>Código postal:</strong></td><td style="padding: 3px;">${datos.cp || 'No especificado'}</td></tr>
                <tr><td style="padding: 3px;"><strong>País:</strong></td><td style="padding: 3px;">${datos.pais || 'México'}</td></tr>
            </table>
        </div>

        <div style="border: 1px solid #ddd; padding: 12px; border-radius: 5px;">
            <h3 style="color: #003d71; margin: 0 0 8px 0; font-size: 14px;">RESUMEN</h3>
            <table style="width: 50%; margin-left: auto; font-size: 12px;">
                <tr><td style="padding: 5px;"><strong>Subtotal:</strong></td><td style="text-align: right;">$${subtotal.toFixed(2)}</td></tr>
                <tr><td style="padding: 5px;"><strong>IVA (16%):</strong></td><td style="text-align: right;">$${iva.toFixed(2)}</td></tr>
                <tr style="background: #f47920; color: white;"><td style="padding: 8px;"><strong>TOTAL:</strong></td><td style="text-align: right; font-weight: bold;">$${parseFloat(datos.monto).toFixed(2)}</td></tr>
            </table>
        </div>

        <div style="text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #888;">
            <p>Gorilla Tools - Calidad y servicio que inspiran confianza.</p>
            <p>Este documento es una simulación con fines académicos.</p>
        </div>
    </div>
    `;
}

// ========== GENERAR FACTURA (VERSIÓN ÚNICA) ==========
async function generateFactura(event) {
    event.preventDefault();
    
    if (!validateStep(3)) return;
    
    const ticket = document.getElementById('numTicket').value.trim().toUpperCase();
    
    if (!ticket) {
        alert('Primero valida un ticket');
        return;
    }
    
    const facturaData = {
        cliente: document.getElementById('nombreCliente').value,
        email: document.getElementById('emailCliente').value,
        telefono: document.getElementById('telefono').value,
        pais: document.getElementById('pais').value,
        ticket: ticket,
        fecha: document.getElementById('fechaCompra').value,
        monto: document.getElementById('montoTotal').value,
        sucursal: document.getElementById('sucursal').value,
        rfc: document.getElementById('rfc').value,
        regimen: document.getElementById('regimenFiscal').value,
        usoCFDI: document.getElementById('usoCFDI').value,
        cp: document.getElementById('cp').value
    };
    
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'flex';
    
    try {
        // Abrir nueva ventana para imprimir
        const ventana = window.open('', '_blank');
        
        if (!ventana) {
            alert('Por favor, permite ventanas emergentes para generar la factura');
            loadingOverlay.style.display = 'none';
            return;
        }
        
        const contenidoHTML = generarContenidoPDF(facturaData);
        
        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Factura ${facturaData.ticket}</title>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: white; }
                    @media print { body { margin: 0; padding: 0; } button { display: none; } }
                </style>
            </head>
            <body>
                ${contenidoHTML}
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print(); setTimeout(() => window.close(), 1000);" 
                            style="background: #003d71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                        Guardar como PDF / Imprimir
                    </button>
                    <button onclick="window.close()" 
                            style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                        Cerrar
                    </button>
                </div>
                <script>
                    setTimeout(() => { window.print(); }, 500);
                <\/script>
            </body>
            </html>
        `);
        
        ventana.document.close();
        loadingOverlay.style.display = 'none';
        
        const successMsg = document.getElementById('successMessage');
        successMsg.style.display = 'block';
        setTimeout(() => { successMsg.style.display = 'none'; }, 3000);
        
        try {
            await fetch('api_marcar_facturado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ticket: ticket })
            });
        } catch(e) {
            console.log('No se pudo marcar como facturado');
        }
        
    } catch (error) {
        console.error('Error:', error);
        loadingOverlay.style.display = 'none';
        alert('❌ Error al generar la factura: ' + error.message);
    }
}



    // Cargar datos del usuario logueado desde el servidor
    async function cargarDatosUsuarioFacturacion() {
        try {
            const response = await fetch('api_obtener_usuario.php');
            const resultado = await response.json();
            
            if (resultado.success) {
                const usuario = resultado.usuario;
                
                // Autollenar campos
                const nombreInput = document.getElementById('nombreCliente');
                const emailInput = document.getElementById('emailCliente');
                const telefonoInput = document.getElementById('telefono');
                const rfcInput = document.getElementById('rfc');
                const cpInput = document.getElementById('cp');
                const paisSelect = document.getElementById('pais');
                const regimenSelect = document.getElementById('regimenFiscal');
                const usoSelect = document.getElementById('usoCFDI');
                
                if (nombreInput) nombreInput.value = usuario.nombre;
                if (emailInput) emailInput.value = usuario.email;
                if (telefonoInput) telefonoInput.value = usuario.telefono || '';
                if (rfcInput) rfcInput.value = usuario.rfc;
                if (cpInput) cpInput.value = usuario.cp || '';
                if (paisSelect && usuario.pais) paisSelect.value = usuario.pais;
                
                // ========== NUEVO: Llenar régimen fiscal y uso de CFDI ==========
                if (regimenSelect && usuario.regimen_fiscal) {
                    regimenSelect.value = usuario.regimen_fiscal;
                }
                if (usoSelect && usuario.uso_cfdi) {
                    usoSelect.value = usuario.uso_cfdi;
                }
                
                // Hacer campos de solo lectura
                const camposLectura = ['nombreCliente', 'emailCliente', 'rfc', 'cp', 'telefono'];
                camposLectura.forEach(id => {
                    const campo = document.getElementById(id);
                    if (campo) {
                        campo.readOnly = true;
                        campo.style.backgroundColor = '#e9ecef';
                    }
                });
                
                // Deshabilitar selects
                const selects = ['pais', 'regimenFiscal', 'usoCFDI'];
                selects.forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        select.disabled = true;
                        select.style.backgroundColor = '#e9ecef';
                    }
                });
                
                console.log('✅ Datos del usuario autollenados en facturación');
            }
        } catch (error) {
            console.log('No hay sesión activa o error al cargar usuario');
        }
    }
// Al final del script, asegúrate de que el event listener esté así:
document.getElementById('facturacionForm').addEventListener('submit', generateFactura);
    // Ejecutar al cargar la página
    cargarDatosUsuarioFacturacion();

    document.getElementById('nextBtn')?.addEventListener('click', nextStep);
document.getElementById('prevBtn')?.addEventListener('click', prevStep);
</script>

<footer style="background-color: #003d71; color: white; margin-top: 60px; padding: 30px; text-align: center;">
    <p>&copy; 2026 Gorilla Tools - Todos los derechos reservados</p>
</footer>
</body>
</html>