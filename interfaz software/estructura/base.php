<?php
// Incluir conexión a la base de datos
include('../funciones/conexion.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../diseños/base.css">
    <title>inicio</title>
</head>
<body >
      <!-- ============Navbar============ -->
      <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" data-bs-theme="dark">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="#navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto">
              <a class="icon-link" href="#">
  <svg xmlns="http://www.w3.org/2000/svg" class="bi" viewBox="0 0 16 16" aria-hidden="true"  >
    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
  </svg>
</a>

    <a class="navbar-brand" href="base.php"  >InventarioNet</a>
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

  <!-- ============es para buscar los productos acomoden bien la base de datos con las variables para que funcione al momento de buscar :V -->
            <form class="d-flex align-items-center" role="search" onsubmit="return false;">
            <form class="d-flex align-items-center" role="search" onsubmit="return false;">
              <input id="searchInput" class="form-control form-control-sm me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-success btn-sm" type="button">Search</button>
            </form>
          </div>
        </div>
      </nav>

      <!-- Main -->
      <main class="container main-content mt-4">
        <header class="d-flex align-items-center justify-content-between mb-4">

        </header>

        <!-- Metrics -->
        <section class="row mb-4">
          <div class="col-md-4">
            <div class="metric-card p-3">
              <div class="metric-title">Productos</div>
              <div id="metricProducts" class="metric-value">—</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card p-3">
              <div class="metric-title">En stock</div>
              <div id="metricInStock" class="metric-value">—</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card p-3">
              <div class="metric-title">Valor inventario</div>
              <div id="metricValue" class="metric-value">—</div>
            </div>
          </div>
        </section>

        <!-- Table -->
        <section class="table-actions mb-3">
          <!-- acciones a la derecha (botones) -->
        </section>

        <div class="table-responsive products-list mb-4">
          <table class="products-table" aria-label="Lista de productos">
            <thead>
              <tr>
                <th></th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>SKU</th>
                <th>Variante</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Stock</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM productos";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
            ?>
              <tr class="product-row" data-id="<?= htmlspecialchars($row['id_producto']) ?>">
                <td><input type="checkbox" aria-label="Seleccionar producto"></td>
                <td class="product-name"><img src="../imagenes/thumb1.png" alt="" class="product-thumb"><span class="product-name__text" data-field="product-name"><?= htmlspecialchars($row['nombre'] ?? '') ?></span></td>
                <td class="product-category" data-field="categoria"><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                <td class="product-sku" data-field="codigo"><?= htmlspecialchars($row['codigo'] ?? '') ?></td>
                <td class="product-variant" data-field="variante"><?= htmlspecialchars($row['variante'] ?? '') ?></td>
                <td class="product-price" data-field="precio">$<?= number_format((float)($row['precio'] ?? 0), 2) ?></td>
                <td class="product-status" data-field="existencia"><span class="badge <?= (strtolower($row['existencia'] ?? '') === 'active') ? 'badge--active' : 'badge--out' ?>"><?= htmlspecialchars($row['existencia'] ?? '') ?></span></td>
                <td class="product-stock" data-field="stock"><?= htmlspecialchars($row['stock'] ?? $row['cantidad'] ?? 0) ?></td>
                <td class="actions-menu">
                  <div class="action-buttons"><button type="button" class="btn-action btn-edit">Editar</button><button type="button" class="btn-action btn-delete">Eliminar</button></div>
                </td>
              </tr>
            <?php
                }
            } else {
            ?>
              <tr><td colspan="9">No hay productos registrados.</td></tr>
            <?php
            }
            ?>
            </tbody>
          </table>
        </div>

        <!-- Paginación (gestionada por funciones/funcion.js) -->
        <nav class="pagination" aria-label="Paginación de productos">
          <button class="page-prev">Anterior</button>
          <ul class="page-numbers"></ul>
          <button class="page-next">Siguiente</button>
        </nav>

      </main>

      <!-- Scripts -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
      <script src="../funciones/funcion.js"></script>
      <script src="../funciones/inicio.js"></script>

      </body>
      </html>
