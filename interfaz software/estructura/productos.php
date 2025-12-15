<?php
// Incluir conexión y obtener productos desde la base de datos
include('../funciones/conexion.php');

$productos = [];
$res = $conn->query("SELECT * FROM productos");
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['imagen'] = $r['imagen'] ?? '../imagenes/thumb1.png';
    $r['nombre'] = $r['nombre'] ?? '';
    $r['categoria'] = $r['categoria'] ?? '';
    $r['codigo'] = $r['codigo'] ?? '';
    $r['variante'] = $r['variante'] ?? '';
    $r['precio'] = $r['precio'] ?? 0;
    $r['existencia'] = $r['existencia'] ?? ($r['estado'] ?? 'Inactive');
    $r['stock'] = $r['stock'] ?? ($r['cantidad'] ?? 0);
    $productos[] = $r;
  }
}
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
<body>
<div>
<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="icon-link" href="#">
      <!-- SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" class="bi" viewBox="0 0 16 16" aria-hidden="true">
        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
      </svg>
    </a>
    <a class="navbar-brand" href="base.php">InventarioNet</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="base.php">inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="productos.php">productos</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">información</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="orden.php">crear orden</a></li>
            <li><a class="dropdown-item" href="reportes.php">reportes</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
  <form class="d-flex" role="search">
    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
    <button class="btn btn-outline-success" type="submit">Search</button>
  </form>
</nav>
</div>

<section class="products-list">
  <div class="table-actions">
    <button type="button" class="btn-add">Agregar Producto</button>
  </div>
  <div class="table-responsive">
    <table class="products-table" aria-label="Lista de productos">
      <thead>
        <tr>
          <th><input type="checkbox" aria-label="Seleccionar todos"></th>
          <th>Product Name</th>
          <th>Categoria</th>
          <th>codigo</th>
          <th>Variante</th>
          <th>Precio</th>
          <th>existencia</th>
          <th>stock</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($productos) === 0): ?>
          <tr><td colspan="9">No hay productos registrados.</td></tr>
        <?php endif; ?>

        <?php foreach ($productos as $producto): ?>
        <tr class="product-row" data-id="<?= htmlspecialchars($producto['id_producto'] ?? $producto['id'] ?? '') ?>">
          <td><input type="checkbox" aria-label="Seleccionar producto"></td>
          <td class="product-name">
            <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="" class="product-thumb">
            <span class="product-name__text" data-field="product-name"><?= htmlspecialchars($producto['nombre']) ?></span>
          </td>
          <td class="product-category" data-field="categoria"><?= htmlspecialchars($producto['categoria']) ?></td>
          <td class="product-sku" data-field="codigo"><?= htmlspecialchars($producto['codigo']) ?></td>
          <td class="product-variant" data-field="variante"><?= htmlspecialchars($producto['variante']) ?></td>
          <td class="product-price" data-field="precio">$<?= number_format((float)$producto['precio'], 2) ?></td>
          <td class="product-status" data-field="existencia">
            <?php if(strtolower($producto['existencia']) == 'active'){ ?>
              <span class="badge badge--active">Active</span>
            <?php } else { ?>
              <span class="badge badge--inactive"><?= htmlspecialchars($producto['existencia']) ?></span>
            <?php } ?>
          </td>
          <td class="product-stock" data-field="stock"><?= htmlspecialchars($producto['stock']) ?></td>
          <td class="actions-menu">
            <div class="action-buttons">
              <button type="button" class="btn-action btn-edit">Editar</button>
              <button type="button"  class="btn-action btn-delete">Eliminar</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <nav class="pagination" aria-label="Paginación de productos">
    <button class="page-prev" aria-label="Anterior">&lt;</button>
    <ul class="page-numbers">
      <li><button class="page is-current" aria-current="page">1</button></li>
      <li><button class="page">2</button></li>
      <li><button class="page">3</button></li>
    </ul>
    <button class="page-next" aria-label="Siguiente">&gt;</button>
  </nav>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../funciones/funcion.js"></script>
</body>
</html>