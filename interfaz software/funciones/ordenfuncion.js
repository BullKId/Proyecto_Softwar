// ============================================
// Funciones para Crear Orden
// ============================================

// inventarioProductos será cargado desde el servidor si no hay opciones server-side
let inventarioProductos = [];

// Variables para rastrear los productos agregados a la orden
let productosEnOrden = [];
let contadorOrden = 1;

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarProductosInventario();
    configurarEventosFormulario();
    establecerFechaActual();
    generarNumeroOrden();
});

// Cargar productos del inventario en el select
function cargarProductosInventario() {
    const selectProductos = document.getElementById('productoSelect');
    // Intentar cargar productos desde endpoint JSON (listar.php?format=json)
    fetch('../inventario/listar.php?format=json')
        .then(resp => {
            if (!resp.ok) throw new Error('Network response was not ok');
            return resp.json();
        })
        .then(data => {
            console.log('listar.php returned', Array.isArray(data) ? data.length : typeof data, 'items');
            console.log('listar.php sample:', data && data.slice ? data.slice(0,5) : data);
            // data es un array de filas; adaptamos nombres de columnas comunes
            inventarioProductos = data.map(item => ({
                id: item.id_producto ?? item.id ?? item.producto_id ?? item.id_prod ?? 0,
                nombre: item.nombre ?? item.product_name ?? item.producto ?? 'Sin nombre',
                precio: parseFloat(item.precio ?? item.price ?? item.precio_unitario ?? 0) || 0,
                stock: parseInt(item.stock ?? 0) || 0
            }));

            // Limpiar opciones previas (conservar la opción por defecto con value="")
            for (let i = selectProductos.options.length - 1; i >= 0; i--) {
                if (selectProductos.options[i].value !== '') {
                    selectProductos.remove(i);
                }
            }

            inventarioProductos.forEach(producto => {
                const option = document.createElement('option');
                option.value = producto.id;
                option.textContent = `${producto.nombre} (Stock: ${producto.stock}) - $${producto.precio.toFixed(2)}`;
                option.dataset.precio = producto.precio;
                option.dataset.stock = producto.stock;
                selectProductos.appendChild(option);
            });
        })
        .catch(err => {
            console.warn('No se pudieron cargar productos desde el servidor:', err);
            // Si falla, no hacemos nada: el select quedará con la opción por defecto
        });
}

// Configurar eventos del formulario
function configurarEventosFormulario() {
    const selectProductos = document.getElementById('productoSelect');
    const inputCantidad = document.getElementById('productoCantidad');
    const inputPrecio = document.getElementById('productoPrecio');
    const inputSubtotal = document.getElementById('productoSubtotal');
    const btnAgregar = document.getElementById('agregarProductoBtn');
    const btnGuardar = document.querySelector('.btn-save-form');
    const inputDescuento = document.getElementById('descuento');

    // Cuando se selecciona un producto
    selectProductos.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const precio = parseFloat(selectedOption.dataset.precio) || 0;
        inputPrecio.value = precio.toFixed(2);
        inputCantidad.value = '';
        inputSubtotal.value = '0.00';
    });

    // Cuando cambia la cantidad
    inputCantidad.addEventListener('input', function() {
        actualizarSubtotal();
    });

    // Cuando se hace clic en "Agregar Producto"
    btnAgregar.addEventListener('click', agregarProductoAOrden);

    // Cuando cambia el descuento
    inputDescuento.addEventListener('input', function() {
        calcularTotal();
    });

    // Cuando se envía el formulario
    document.getElementById('ordenForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarOrden();
    });
}

// Establecer la fecha actual
function establecerFechaActual() {
    const inputFecha = document.getElementById('ordenFecha');
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.value = hoy;
}

// Generar número de orden auto-incrementado
function generarNumeroOrden() {
    const inputNumero = document.getElementById('ordenNumero');
    inputNumero.value = `ORD-${Date.now().toString().slice(-6)}-${Math.floor(Math.random() * 100)}`;
}

// Actualizar subtotal de un producto
function actualizarSubtotal() {
    const precio = parseFloat(document.getElementById('productoPrecio').value) || 0;
    const cantidad = parseFloat(document.getElementById('productoCantidad').value) || 0;
    const subtotal = precio * cantidad;
    
    document.getElementById('productoSubtotal').value = subtotal.toFixed(2);
}

// Agregar producto a la orden
function agregarProductoAOrden() {
    const selectProductos = document.getElementById('productoSelect');
    const inputCantidad = document.getElementById('productoCantidad');
    const inputPrecio = document.getElementById('productoPrecio');

    const productoId = selectProductos.value;
    const cantidad = parseInt(inputCantidad.value) || 0;
    const precio = parseFloat(inputPrecio.value) || 0;

    // Validaciones
    if (!productoId) {
        alert('Por favor selecciona un producto');
        return;
    }

    if (cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }

    // Obtener información del producto
    const selectedOption = selectProductos.options[selectProductos.selectedIndex];
    // Primero intentamos desde el option[data-stock]
    let stock = parseInt(selectedOption.dataset.stock);
    if (Number.isNaN(stock)) stock = undefined;
    let nombreProducto = selectedOption.textContent.split('(')[0].trim();

    // Si no hay stock en el option, buscar en inventarioProductos (fallback)
    if (stock === undefined || stock === 0) {
        const prodInfo = inventarioProductos.find(p => String(p.id) === String(productoId));
        if (prodInfo) {
            // usar valores del inventario si existen
            stock = prodInfo.stock || 0;
            if (!nombreProducto || nombreProducto === '-- Selecciona un producto --') nombreProducto = prodInfo.nombre;
            // si el precio en el input es 0 o vacío, asignarlo desde prodInfo
            if (!precio || precio === 0) {
                document.getElementById('productoPrecio').value = prodInfo.precio.toFixed(2);
            }
        }
    }
    stock = parseInt(stock) || 0;

    if (cantidad > stock) {
        alert(`Stock insuficiente. Stock disponible: ${stock}`);
        return;
    }

    // Crear objeto del producto
    const producto = {
        id: Date.now(),
        productoId: productoId,
        nombre: nombreProducto,
        cantidad: cantidad,
        precio: precio,
        subtotal: precio * cantidad
    };

    // Agregar a la lista
    productosEnOrden.push(producto);

    // Actualizar tabla
    actualizarTablaProductos();

    // Limpiar campos
    selectProductos.value = '';
    inputCantidad.value = '';
    inputPrecio.value = '';
    document.getElementById('productoSubtotal').value = '0.00';

    // Calcular totales
    calcularTotal();
}

// Actualizar la tabla de productos
function actualizarTablaProductos() {
    const tableBody = document.getElementById('productosTableBody');
    const emptyMessage = document.getElementById('emptyTableMessage');

    tableBody.innerHTML = '';

    if (productosEnOrden.length === 0) {
        emptyMessage.classList.remove('hidden');
        return;
    }

    emptyMessage.classList.add('hidden');

    productosEnOrden.forEach((producto, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${producto.nombre}</td>
            <td>${producto.cantidad}</td>
            <td>$${producto.precio.toFixed(2)}</td>
            <td>$${producto.subtotal.toFixed(2)}</td>
            <td>
                <button type="button" class="btn-remove-producto" onclick="removerProductoDeOrden(${index})">
                    Eliminar
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Remover producto de la orden
function removerProductoDeOrden(index) {
    productosEnOrden.splice(index, 1);
    actualizarTablaProductos();
    calcularTotal();
}

// Calcular totales
function calcularTotal() {
    const subtotal = productosEnOrden.reduce((acc, producto) => acc + producto.subtotal, 0);
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const total = subtotal - descuento;

    document.getElementById('subtotalAmount').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('totalAmount').textContent = `$${Math.max(total, 0).toFixed(2)}`;
}

// Guardar orden (simular envío a servidor)
async function guardarOrden() {
    if (productosEnOrden.length === 0) {
        alert('Debes agregar al menos un producto a la orden');
        return;
    }

    const cliente = document.getElementById('ordenCliente').value.trim();
    const estado = document.getElementById('ordenEstado').value;
    const numero = document.getElementById('ordenNumero').value;
    const fecha = document.getElementById('ordenFecha').value;
    const notas = document.getElementById('ordenNotas').value.trim();
    const subtotal = productosEnOrden.reduce((acc, p) => acc + p.subtotal, 0);
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const total = subtotal - descuento;

    if (!cliente) {
        alert('Por favor ingresa el nombre del cliente');
        return;
    }

    const orden = {
        numero: numero,
        fecha: fecha,
        cliente: cliente,
        estado: estado,
        productos: productosEnOrden,
        subtotal: subtotal,
        descuento: descuento,
        total: total,
        notas: notas
    };

    try {
        const resp = await fetch('../funciones/guardar_orden.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orden)
        });
        let json = null;
        try {
            json = await resp.json();
        } catch (e) {
            // no JSON response
            json = null;
        }

        if (!resp.ok) {
            const err = json && json.error ? json.error : `${resp.status} ${resp.statusText}`;
            alert('Error guardando la orden: ' + err);
            return;
        }

        if (!json || !json.success) {
            const err = json && json.error ? json.error : 'Respuesta inesperada del servidor';
            alert('Error: ' + err);
            return;
        }

        mostrarMensajeExito();

        // Limpiar formulario
        document.getElementById('ordenForm').reset();
        productosEnOrden = [];
        generarNumeroOrden();
        establecerFechaActual();
        actualizarTablaProductos();
        calcularTotal();

    } catch (error) {
        console.error('Error enviando orden:', error);
        alert('Ocurrió un error al enviar la orden. Revisa la consola para más detalles.');
    }
}

// Guardar orden en localStorage (para demostración)
function guardarOrdenEnLocaStorage(orden) {
    try {
        let ordenes = JSON.parse(localStorage.getItem('ordenes')) || [];
        ordenes.push(orden);
        localStorage.setItem('ordenes', JSON.stringify(ordenes));
    } catch (error) {
        console.error('Error al guardar en localStorage:', error);
    }
}

// Mostrar mensaje de éxito
function mostrarMensajeExito() {
    const mensaje = document.createElement('div');
    mensaje.className = 'success-message';
    mensaje.textContent = '✓ Orden creada exitosamente';
    mensaje.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: #10b981;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        z-index: 2000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(mensaje);

    // Agregar animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);

    setTimeout(() => {
        mensaje.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => mensaje.remove(), 300);
    }, 3000);
}
