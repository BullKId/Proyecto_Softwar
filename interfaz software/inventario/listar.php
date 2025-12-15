<?php
include('../funciones/conexion.php');

$sql = "SELECT * FROM productos";
$resultado = $conn->query($sql);

// Soporta salida JSON para peticiones AJAX: listar.php?format=json
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    // Preparar condiciones seguras
    $filters = [];
    $params = [];
    $types = '';

    // Cargar columnas de la tabla para verificar si hay fecha disponible
    $colRes = $conn->query("SHOW COLUMNS FROM productos");
    $columns = [];
    if ($colRes) {
        while ($c = $colRes->fetch_assoc()) $columns[] = $c['Field'];
    }

    if (!empty($_GET['categoria'])) {
        $filters[] = 'categoria = ?';
        $params[] = $_GET['categoria'];
        $types .= 's';
    }
    if (!empty($_GET['busqueda'])) {
        $filters[] = 'nombre LIKE ?';
        $params[] = '%' . $_GET['busqueda'] . '%';
        $types .= 's';
    }

    // Soportar filtro por fecha solamente si existiese una columna de fecha en productos
    $dateCol = null;
    foreach (['updated_at', 'ultima_actualizacion', 'fecha_actualizacion', 'fecha'] as $dc) {
        if (in_array($dc, $columns)) { $dateCol = $dc; break; }
    }
    if ($dateCol) {
        if (!empty($_GET['fechaInicio'])) {
            $filters[] = "$dateCol >= ?";
            $params[] = $_GET['fechaInicio'];
            $types .= 's';
        }
        if (!empty($_GET['fechaFin'])) {
            $filters[] = "$dateCol <= ?";
            $params[] = $_GET['fechaFin'];
            $types .= 's';
        }
    }

    // Construir SQL con WHERE si hace falta
    $sqlQuery = "SELECT * FROM productos";
    if (count($filters) > 0) {
        $sqlQuery .= ' WHERE ' . implode(' AND ', $filters);
    }

    $rows = [];
    if ($stmt = $conn->prepare($sqlQuery)) {
        if (!empty($params)) {
            // bind params dinámicamente
            $bindNames[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $bindName = 'bind' . $i;
                $$bindName = $params[$i];
                $bindNames[] = &$$bindName;
            }
            call_user_func_array([$stmt, 'bind_param'], $bindNames);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
        $stmt->close();
    } else {
        // Fallback: ejecutar consulta simple si prepare no está disponible
        $res = $conn->query($sqlQuery);
        if ($res) {
            while ($r = $res->fetch_assoc()) { $rows[] = $r; }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    $conn->close();
    exit;
}
?>

<h2>Productos en inventario</h2>
<a href="agregar.php">Agregar nuevo producto</a>
<br><br>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Acciones</th>
    </tr>

<?php while($row = $resultado->fetch_assoc()): ?>
<tr data-id="<?= $row['id_producto'] ?>">
    <td><?= $row['id_producto'] ?></td>
    <td><?= $row['nombre'] ?></td>
    <td><?= $row['descripcion'] ?></td>
    <td><?= $row['cantidad'] ?></td>
    <td><?= $row['precio'] ?></td>
    <td>
        <a href="editar.php?id=<?= $row['id_producto'] ?>">Editar</a> |
        <a href="eliminar.php?id=<?= $row['id_producto'] ?>" onclick="return confirm('¿Seguro?')">Eliminar</a>
    </td>
</tr>
<?php endwhile; ?>

</table>
