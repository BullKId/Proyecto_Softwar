<?php
include('../funciones/conexion.php');

// Obtener categorías desde BD para el select (mantener diseño)
$categorias = [];
$resCats = $conn->query("SELECT DISTINCT categoria FROM productos");
if ($resCats) {
    while ($r = $resCats->fetch_assoc()) {
        if (!empty($r['categoria'])) $categorias[] = $r['categoria'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Inventario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../diseños/base.css">
    <link rel="stylesheet" href="../diseños/reportes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<!-- ========================Contenedor principal de reportes======================== -->
<div class="container-fluid reportes-container mt-5 pt-3">

    <!--============seccion============-->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="titulo-reportes">Reportes del Inventario</h1>
        </div>

        <div class="col-md-4 d-flex justify-content-end align-items-center gap-2">
            <button class="btn btn-primary btn-sm" onclick="exportarPDF()"><i class="bi bi-file-pdf"></i> Exportar PDF</button>
            <button class="btn btn-success btn-sm" onclick="exportarExcel()"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
        </div>
    </div>

    <!--============Filtros============-->
    <div class="card mb-4 filtros-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio">
                </div>

                <div class="col-md-3">
                    <label for="fechaFin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin">
                </div>

                <div class="col-md-3">
                    <label for="filtroCategoria" class="form-label">Categoría</label>
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= ucfirst(htmlspecialchars($cat)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="buscarProducto" class="form-label">Buscar Producto</label>
                    <input type="text" class="form-control" id="buscarProducto" placeholder="Nombre del producto">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <button class="btn btn-info" onclick="aplicarFiltros()">Aplicar Filtros</button>
                    <button class="btn btn-secondary" onclick="limpiarFiltros()">Limpiar Filtros</button>
                </div>
            </div>
        </div>
    </div>

    <!--============Estadísticas============-->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card estadisticas-card">
                <div class="card-body">
                    <h6 class="card-title">Total de Productos</h6>
                    <h3 class="numero-estadistica" id="totalProductos">0</h3>
                    <small class="text-muted">En el inventario</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card estadisticas-card">
                <div class="card-body">
                    <h6 class="card-title">Valor Total</h6>
                    <h3 class="numero-estadistica" id="valorTotal">$0</h3>
                    <small class="text-muted">Inventario total</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card estadisticas-card">
                <div class="card-body">
                    <h6 class="card-title">Stock Bajo</h6>
                    <h3 class="numero-estadistica alerta" id="stockBajo">0</h3>
                    <small class="text-muted">Productos con bajo stock</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card estadisticas-card">
                <div class="card-body">
                    <h6 class="card-title">Movimientos</h6>
                    <h3 class="numero-estadistica" id="movimientos">0</h3>
                    <small class="text-muted">Últimos 30 días</small>
                </div>
            </div>
        </div>
    </div>

    <!--============Gráficos============-->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Distribución de Productos por Categoría</h5></div>
                <div class="card-body"><canvas id="graficoCategoria"></canvas></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Movimiento de Inventario</h5></div>
                <div class="card-body"><canvas id="graficoMovimiento"></canvas></div>
            </div>
        </div>
    </div>

    <!--============Tabla dinámica============-->
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Detalle del Inventario</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaInventario">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Producto</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Cantidad Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio Unitario</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th>Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!--============Productos con stock bajo============-->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning"><h5 class="card-title mb-0">Productos con Stock Bajo</h5></div>
                <div class="card-body"><div id="productosBajo" class="lista-productos"></div></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success"><h5 class="card-title mb-0">✓ Productos sin Movimiento</h5></div>
                <div class="card-body"><div id="productosSinMovimiento" class="lista-productos"></div></div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../funciones/funcion.js"></script>
<script src="../funciones/inicio.js"></script>
<script src="../funciones/ordenfuncion.js"></script>
<script src="../funciones/reportes.js"></script>

</body>
</html>
