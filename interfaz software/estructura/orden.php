<?php
// Conectar a la base de datos
include '../funciones/conexion.php';

// Obtener productos para poblar el select
$productos = [];
$sqlProductos = "SELECT id_producto, nombre, stock, precio FROM productos ORDER BY nombre";
$resProductos = $conn->query($sqlProductos);
if ($resProductos) {
  while ($r = $resProductos->fetch_assoc()) {
    $productos[] = $r;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>crear orden </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../diseños/base.css">
    <link rel="stylesheet" href="../diseños/orden.css">
</head>
<body>
    
   <!-- ===========Navbar===========-->
   <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" data-bs-theme="dark">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="#navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto">
              <a class="icon-link" href="#">
                <svg xmlns="http://www.w3.org/2000/svg" class="bi" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                </svg>
              </a>

              <a class="navbar-brand" href="base.php">InventarioNet</a>
              <li class="nav-item"><a class="nav-link active" aria-current="page" href="base.php">Inicio</a></li>
              <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Información</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="orden.php">Crear orden</a></li>
                  <li><a class="dropdown-item" href="reportes.php">Reportes</a></li>
                </ul>
              </li>
            </ul>

            <form class="d-flex align-items-center" role="search" onsubmit="return false;">
              <input id="searchInput" class="form-control form-control-sm me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-success btn-sm" type="button">Search</button>
            </form>
          </div>
        </div>
      </nav>

      <div class="main-content">

        <!-- ============formulario para ordenes============ -->
        <div class="container">
          <div class="orden-form-section">
            <h2 class="form-title">Crear Nueva Orden</h2>
            <form id="ordenForm" class="orden-form">

              <!-- Información General -->
              <div class="form-section">
                <h3 class="section-title">Información General</h3>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="ordenNumero">Número de Orden *</label>
                    <input type="text" id="ordenNumero" name="ordenNumero" placeholder="Auto-generado" readonly>
                  </div>
                  <div class="form-group">
                    <label for="ordenFecha">Fecha *</label>
                    <input type="date" id="ordenFecha" name="ordenFecha" required>
                  </div>
                  <div class="form-group">
                    <label for="ordenCliente">Cliente *</label>
                    <input type="text" id="ordenCliente" name="ordenCliente" placeholder="Nombre del cliente" required>
                  </div>
                  <div class="form-group">
                    <label for="ordenEstado">Estado</label>
                    <select id="ordenEstado" name="ordenEstado" required>
                      <option value="pendiente">Pendiente</option>
                      <option value="en-proceso">En proceso</option>
                      <option value="completada">Completada</option>
                      <option value="cancelada">Cancelada</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Agregar Productos -->
              <div class="form-section">
                <h3 class="section-title">Agregar Productos</h3>
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label for="productoSelect">Seleccionar Producto *</label>
                    <select id="productoSelect" name="productoSelect" required>
                      <option value="">-- Selecciona un producto --</option>
                      <?php foreach ($productos as $prod): ?>
                        <option value="<?= htmlspecialchars($prod['id_producto']) ?>" data-precio="<?= htmlspecialchars($prod['precio']) ?>" data-stock="<?= htmlspecialchars($prod['stock']) ?>">
                          <?= htmlspecialchars($prod['nombre']) ?> (Stock: <?= htmlspecialchars($prod['stock']) ?>) - $<?= number_format($prod['precio'], 2) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="productoCantidad">Cantidad *</label>
                    <input type="number" id="productoCantidad" name="productoCantidad" placeholder="0" min="1" required>
                  </div>
                  <div class="form-group">
                    <label for="productoPrecio">Precio Unitario</label>
                    <input type="number" id="productoPrecio" name="productoPrecio" placeholder="0.00" step="0.01" readonly>
                  </div>
                  <div class="form-group">
                    <label for="productoSubtotal">Subtotal</label>
                    <input type="number" id="productoSubtotal" name="productoSubtotal" placeholder="0.00" step="0.01" readonly>
                  </div>
                </div>
                <button type="button" id="agregarProductoBtn" class="btn-add-producto">Agregar Producto</button>
              </div>

              <!-- Tabla -->
              <div class="form-section">
                <h3 class="section-title">Productos en la Orden</h3>
                <div class="table-responsive-form">
                  <table id="productosTable" class="productos-orden-table">
                    <thead>
                      <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                      </tr>
                    </thead>
                    <tbody id="productosTableBody"></tbody>
                  </table>
                  <div id="emptyTableMessage" class="empty-table-message">
                    No hay productos agregados. Agrega un producto para continuar.
                  </div>
                </div>
              </div>

              <!-- Resumen -->
              <div class="form-section">
                <h3 class="section-title">Resumen</h3>
                <div class="resumen-grid">
                  <div class="resumen-item">
                    <span class="resumen-label">Subtotal:</span>
                    <span id="subtotalAmount" class="resumen-value">$0.00</span>
                  </div>
                  <div class="resumen-item">
                    <span class="resumen-label">Descuento:</span>
                    <input type="number" id="descuento" name="descuento" placeholder="0.00" step="0.01" min="0" class="resumen-input">
                  </div>
                  <div class="resumen-item">
                    <span class="resumen-label">Total:</span>
                    <span id="totalAmount" class="resumen-value total">$0.00</span>
                  </div>
                </div>
              </div>

              <!-- Notas -->
              <div class="form-section">
                <h3 class="section-title">Notas Adicionales</h3>
                <div class="form-group full-width">
                  <label for="ordenNotas">Observaciones</label>
                  <textarea id="ordenNotas" name="ordenNotas" placeholder="Agregar notas o comentarios sobre la orden..." rows="4"></textarea>
                </div>
              </div>

              <div class="form-actions">
                <button type="reset" class="btn-cancel-form">Limpiar Formulario</button>
                <button type="submit" class="btn-save-form">Crear Orden</button>
              </div>

            </form>
          </div>
        </div>

      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
      <script src="../funciones/funcion.js"></script>
      <script src="../funciones/inicio.js"></script>
      <script src="../funciones/ordenfuncion.js"></script>

</body>
</html>
