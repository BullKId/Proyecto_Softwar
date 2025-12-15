// Inventory data will be loaded from server; keep master list and filtered list
let inventarioData = [];
let inventarioFiltrado = [];

// Fetch inventory from server when the page loads
async function loadInventarioFromServer(filters = {}) {
    try {
        // Build query string for filters
        const params = new URLSearchParams({ format: 'json' });
        if (filters.categoria) params.append('categoria', filters.categoria);
        if (filters.busqueda) params.append('busqueda', filters.busqueda);
        if (filters.fechaInicio) params.append('fechaInicio', filters.fechaInicio);
        if (filters.fechaFin) params.append('fechaFin', filters.fechaFin);
        const url = '../inventario/listar.php?' + params.toString();
        const res = await fetch(url, { cache: 'no-cache' });
        if (!res.ok) {
            console.warn('No se pudo cargar inventario desde backend; HTTP:', res.status);
            inventarioData = [];
            inventarioFiltrado = [];
            return;
        }
        const data = await res.json();
        // Map backend rows to the fields this script expects
        inventarioData = data.map(row => ({
            id: row.id_producto || row.id || row.ID || null,
            nombre: row.nombre || row.name || '',
            categoria: row.categoria || row.descripcion || '',
            cantidad: (row.stock !== undefined) ? parseInt(row.stock, 10) : ((row.cantidad !== undefined) ? parseInt(row.cantidad, 10) : 0),
            stockMinimo: (row.stock_minimo !== undefined) ? parseInt(row.stock_minimo, 10) : (row.minimo !== undefined ? parseInt(row.minimo, 10) : null),
            precio: (row.precio !== undefined) ? parseFloat(row.precio) : 0,
            ultima_actualizacion: row.ultima_actualizacion || row.updated_at || row.fecha_actualizacion || null
        }));
        inventarioFiltrado = [...inventarioData];
    } catch (err) {
        console.error('Error al obtener datos de inventario:', err);
        inventarioData = [];
        inventarioFiltrado = [];
    }
}

// Inicializar la página
document.addEventListener('DOMContentLoaded', async function () {
    await loadInventarioFromServer();
    cargarReportes();
    inicializarGraficos();
});

// Cargar todos los reportes
function cargarReportes() {
    actualizarEstadisticas();
    cargarTablaInventario();
    mostrarProductosBajo();
    mostrarProductosSinMovimiento();
}

// Actualizar estadísticas
function actualizarEstadisticas() {
    const totalProductos = inventarioFiltrado.length;
    const valorTotal = inventarioFiltrado.reduce((sum, item) => sum + (item.cantidad * item.precio), 0);
    const stockBajo = inventarioFiltrado.filter(item => item.cantidad < getStockMinimo(item)).length;
    const movimientos = Math.floor(Math.random() * 100) + 50; // Simulado

    document.getElementById('totalProductos').textContent = totalProductos;
    document.getElementById('valorTotal').textContent = '$' + valorTotal.toFixed(2);
    document.getElementById('stockBajo').textContent = stockBajo;
    document.getElementById('movimientos').textContent = movimientos;
}

// Cargar tabla de inventario
function cargarTablaInventario() {
    const cuerpoTabla = document.getElementById('cuerpoTabla');
    cuerpoTabla.innerHTML = '';
    if (!inventarioFiltrado || inventarioFiltrado.length === 0) {
        const fila = document.createElement('tr');
        fila.innerHTML = `<td colspan="9" class="text-center text-muted">No hay productos registrados.</td>`;
        cuerpoTabla.appendChild(fila);
        return;
    }

    inventarioFiltrado.forEach(producto => {
        const valorTotal = producto.cantidad * producto.precio;
        const estado = determinarEstado(producto);
        const estiloBadge = obtenerEstiloBadge(producto);
        const fechaActualizacion = producto.ultima_actualizacion ? producto.ultima_actualizacion : new Date().toLocaleDateString('es-ES');

        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td><strong>#${producto.id}</strong></td>
            <td>${producto.nombre}</td>
            <td><span class="badge bg-info">${producto.categoria}</span></td>
            <td><strong>${producto.cantidad}</strong> unidades</td>
            <td>${getStockMinimo(producto)}</td>
            <td>$${producto.precio.toFixed(2)}</td>
            <td>$${valorTotal.toFixed(2)}</td>
            <td>${estiloBadge}</td>
            <td>${fechaActualizacion}</td>
        `;
        cuerpoTabla.appendChild(fila);
    });
}

// Determinar estado del producto
function determinarEstado(producto) {
    if (producto.cantidad === 0) return 'Sin stock';
    if (producto.cantidad < getStockMinimo(producto)) return 'Stock bajo';
    return 'Normal';
}

// Obtener estilo del badge
function obtenerEstiloBadge(producto) {
    const estado = determinarEstado(producto);
    if (estado === 'Sin stock' || estado === 'Stock bajo') {
        return `<span class="estado-critico">${estado}</span>`;
    } else if (estado === 'Normal' && producto.cantidad < getStockMinimo(producto) * 1.5) {
        return `<span class="estado-bajo">${estado}</span>`;
    }
    return `<span class="estado-normal">${estado}</span>`;
}

// Mostrar productos con stock bajo
function mostrarProductosBajo() {
    const contenedor = document.getElementById('productosBajo');
    const productosBajo = inventarioFiltrado.filter(item => item.cantidad < getStockMinimo(item));

    if (productosBajo.length === 0) {
        contenedor.innerHTML = '<p class="text-muted m-0">No hay productos con stock bajo</p>';
        return;
    }

    contenedor.innerHTML = '';
    productosBajo.forEach(producto => {
        const diferencia = getStockMinimo(producto) - producto.cantidad;
        const item = document.createElement('div');
        item.className = 'item-producto';
        item.innerHTML = `
            <div>
                <div class="nombre-producto">${producto.nombre}</div>
                <small class="text-muted">Falta: ${diferencia} unidades</small>
            </div>
            <div class="cantidad-producto">${producto.cantidad}</div>
        `;
        contenedor.appendChild(item);
    });
}

// Mostrar productos sin movimiento (simulado)
function mostrarProductosSinMovimiento() {
    const contenedor = document.getElementById('productosSinMovimiento');
    // Simulamos productos sin movimiento (cada 3 productos)
    const productosSinMovimiento = inventarioFiltrado.filter((item, index) => index % 3 === 0);

    if (productosSinMovimiento.length === 0) {
        contenedor.innerHTML = '<p class="text-muted m-0">No hay productos sin movimiento</p>';
        return;
    }

    contenedor.innerHTML = '';
    productosSinMovimiento.forEach(producto => {
        const item = document.createElement('div');
        item.className = 'item-producto';
        item.innerHTML = `
            <div>
                <div class="nombre-producto">${producto.nombre}</div>
                <small class="text-muted">Sin movimiento: 30+ días</small>
            </div>
            <div style="background-color: #95a5a6; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                ${producto.cantidad} unidades
            </div>
        `;
        contenedor.appendChild(item);
    });
}

// Util: Obtener stock mínimo estimado si no está definido
function getStockMinimo(producto) {
    if (producto.stockMinimo !== null && typeof producto.stockMinimo !== 'undefined') return producto.stockMinimo;
    // Provide a reasonable default: 20% of stock rounded, minimum 1
    const calc = Math.max(1, Math.floor(producto.cantidad * 0.2));
    return calc;
}

// Inicializar gráficos
function inicializarGraficos() {
    crearGraficoCategoria();
    crearGraficoMovimiento();
}

// Gráfico de distribución por categoría
function crearGraficoCategoria() {
    const ctx = document.getElementById('graficoCategoria').getContext('2d');
    
    // Contar productos por categoría
    const categorias = {};
    inventarioFiltrado.forEach(producto => {
        categorias[producto.categoria] = (categorias[producto.categoria] || 0) + 1;
    });

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(categorias).map(cat => cat.charAt(0).toUpperCase() + cat.slice(1)),
            datasets: [{
                data: Object.values(categorias),
                backgroundColor: [
                    '#3498db',
                    '#e74c3c',
                    '#27ae60',
                    '#f39c12',
                    '#9b59b6'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Gráfico de movimiento de inventario
function crearGraficoMovimiento() {
    const ctx = document.getElementById('graficoMovimiento').getContext('2d');
    
    // Datos simulados de movimiento de últimos 7 días
    const dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sab', 'Dom'];
    const entrada = [45, 52, 48, 61, 55, 67, 72];
    const salida = [38, 42, 35, 48, 52, 58, 65];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dias,
            datasets: [
                {
                    label: 'Entrada',
                    data: entrada,
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Salida',
                    data: salida,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Aplicar filtros
async function aplicarFiltros() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const categoria = document.getElementById('filtroCategoria').value;
    const busqueda = document.getElementById('buscarProducto').value;

    // Load filtered data from backend
    await loadInventarioFromServer({
        fechaInicio: fechaInicio || undefined,
        fechaFin: fechaFin || undefined,
        categoria: categoria || undefined,
        busqueda: busqueda || undefined
    });

    cargarReportes();
    mostrarAlerta('Filtros aplicados correctamente', 'success');
}

// Limpiar filtros
async function limpiarFiltros() {
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    document.getElementById('filtroCategoria').value = '';
    document.getElementById('buscarProducto').value = '';
    
    await loadInventarioFromServer();
    cargarReportes();
    mostrarAlerta('Filtros limpios', 'info');
}

// Exportar a PDF (simulado)
function exportarPDF() {
    mostrarAlerta('Descargando reporte en PDF...', 'success');
    // Aquí iría la lógica real de exportación a PDF
    console.log('Exportando PDF...');
}

// Exportar a Excel (simulado)
function exportarExcel() {
    mostrarAlerta('Descargando reporte en Excel...', 'success');
    // Aquí iría la lógica real de exportación a Excel
    console.log('Exportando Excel...');
}

// Mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertas = document.querySelector('.alertas-container');
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Crear contenedor si no existe
    if (!alertas) {
        const contenedor = document.createElement('div');
        contenedor.className = 'alertas-container position-fixed top-0 end-0 p-3';
        contenedor.style.zIndex = '9999';
        document.body.appendChild(contenedor);
    }
    
    document.querySelector('.alertas-container').appendChild(alerta);
    
    // Remover alerta después de 3 segundos
    setTimeout(() => {
        alerta.remove();
    }, 3000);
}

// Función para actualizar datos en tiempo real (simular)
setInterval(() => {
    // Puedes agregar lógica aquí para actualizar datos en tiempo real desde un servidor
}, 30000); // Cada 30 segundos
