<?php
// Endpoint para actualizar un producto via AJAX
header('Content-Type: application/json; charset=utf-8');
include('../funciones/conexion.php');

// Obtener y sanitizar entrada
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta id']);
    exit;
}

// Campos permitidos (validar y limpiar)
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
$categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : null;
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : null;
$variante = isset($_POST['variante']) ? trim($_POST['variante']) : null;
$precio = isset($_POST['precio']) ? trim($_POST['precio']) : null;
$existencia = isset($_POST['existencia']) ? trim($_POST['existencia']) : null;
$stock = isset($_POST['stock']) ? trim($_POST['stock']) : null;

// Construir query dinámico según campos enviados
$fields = [];
$types = '';
$values = [];

if ($nombre !== null) { $fields[] = 'nombre = ?'; $types .= 's'; $values[] = $nombre; }
if ($descripcion !== null) { $fields[] = 'descripcion = ?'; $types .= 's'; $values[] = $descripcion; }
if ($categoria !== null) { $fields[] = 'categoria = ?'; $types .= 's'; $values[] = $categoria; }
if ($codigo !== null) { $fields[] = 'codigo = ?'; $types .= 's'; $values[] = $codigo; }
if ($variante !== null) { $fields[] = 'variante = ?'; $types .= 's'; $values[] = $variante; }
if ($precio !== null) {
    // validar número
    $precio_num = filter_var(str_replace(',', '.', $precio), FILTER_VALIDATE_FLOAT);
    if ($precio_num === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Precio inválido']);
        exit;
    }
    $fields[] = 'precio = ?'; $types .= 'd'; $values[] = $precio_num;
}
if ($existencia !== null) { $fields[] = 'existencia = ?'; $types .= 's'; $values[] = $existencia; }
if ($stock !== null) {
    $stock_int = filter_var($stock, FILTER_VALIDATE_INT);
    if ($stock_int === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Stock inválido']);
        exit;
    }
    $fields[] = 'stock = ?'; $types .= 'i'; $values[] = $stock_int;
}

if (count($fields) === 0) {
    echo json_encode(['success' => false, 'error' => 'No hay campos para actualizar']);
    exit;
}

$sql = 'UPDATE productos SET ' . implode(', ', $fields) . ' WHERE id_producto = ?';
$types .= 'i';
$values[] = $id;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error prepare: ' . $conn->error]);
    exit;
}

// bind_param requiere variables por referencia
$bind_names[] = $types;
for ($i=0; $i < count($values); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $values[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array([$stmt, 'bind_param'], $bind_names);

$ok = $stmt->execute();
if (!$ok) {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'affected_rows' => $stmt->affected_rows]);

$stmt->close();
$conn->close();

?>
