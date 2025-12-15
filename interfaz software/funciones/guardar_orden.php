<?php
header('Content-Type: application/json; charset=utf-8');
include('../funciones/conexion.php');

// Leer JSON entrante
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    // Soportar formulario tradicional (no recomendado)
    $data = $_POST;
}

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

// Validar campos mínimos
if (empty($data['cliente']) || empty($data['productos']) || !is_array($data['productos'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos (cliente/productos)']);
    exit;
}

// Helper: comprobar existencia de tabla
function table_exists($conn, $name) {
    $stmt = $conn->prepare("SHOW TABLES LIKE ?");
    if (!$stmt) return false;
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = ($res && $res->num_rows > 0);
    $stmt->close();
    return $exists;
}

try {
    $conn->begin_transaction();

    // Determinar o crear tabla de ordenes
    $ordersTable = null;
    if (table_exists($conn, 'ordenes')) $ordersTable = 'ordenes';
    elseif (table_exists($conn, 'orden')) $ordersTable = 'orden';
    else {
        // Crear tablas mínimas
        $createOrders = "CREATE TABLE IF NOT EXISTS ordenes (
            id_orden INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(100) NOT NULL,
            fecha DATE DEFAULT NULL,
            cliente VARCHAR(255) DEFAULT NULL,
            estado VARCHAR(50) DEFAULT NULL,
            subtotal DECIMAL(12,2) DEFAULT 0,
            descuento DECIMAL(12,2) DEFAULT 0,
            total DECIMAL(12,2) DEFAULT 0,
            notas TEXT,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($createOrders);
        $ordersTable = 'ordenes';
    }

    // Determinar o crear tabla orden_detalle
    if (!table_exists($conn, 'orden_detalle')) {
        $createDetail = "CREATE TABLE IF NOT EXISTS orden_detalle (
            id_detalle INT AUTO_INCREMENT PRIMARY KEY,
            id_orden INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL,
            precio DECIMAL(12,2) DEFAULT 0,
            subtotal DECIMAL(12,2) DEFAULT 0,
            FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($createDetail);
    }

    // Insertar orden
    $numero = $data['numero'] ?? null;
    $fecha = $data['fecha'] ?? null;
    $cliente = $data['cliente'];
    $estado = $data['estado'] ?? null;
    $subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : 0;
    $descuento = isset($data['descuento']) ? floatval($data['descuento']) : 0;
    $total = isset($data['total']) ? floatval($data['total']) : ($subtotal - $descuento);
    $notas = $data['notas'] ?? null;

    $insertSql = "INSERT INTO " . $ordersTable . " (numero, fecha, cliente, estado, subtotal, descuento, total, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param('ssssddds', $numero, $fecha, $cliente, $estado, $subtotal, $descuento, $total, $notas);
    $stmt->execute();
    $idOrden = $conn->insert_id;
    $stmt->close();

    // Insertar detalle y actualizar stock
    $detailSql = "INSERT INTO orden_detalle (id_orden, id_producto, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)";
    $detailStmt = $conn->prepare($detailSql);
    if (!$detailStmt) throw new Exception("Prepare detail failed: " . $conn->error);

    $updateStockStmt = $conn->prepare("UPDATE productos SET cantidad = GREATEST(cantidad - ?, 0) WHERE id_producto = ?");
    if (!$updateStockStmt) throw new Exception("Prepare update stock failed: " . $conn->error);

    foreach ($data['productos'] as $p) {
        $idProd = isset($p['productoId']) ? intval($p['productoId']) : intval($p['producto_id'] ?? 0);
        $cantidad = isset($p['cantidad']) ? intval($p['cantidad']) : intval($p['cantidad'] ?? 0);
        $precio = isset($p['precio']) ? floatval($p['precio']) : floatval($p['precio'] ?? 0);
        $sub = isset($p['subtotal']) ? floatval($p['subtotal']) : ($precio * $cantidad);

        if ($idProd <= 0 || $cantidad <= 0) continue; // saltar

        $detailStmt->bind_param('iiidd', $idOrden, $idProd, $cantidad, $precio, $sub);
        $detailStmt->execute();

        // actualizar stock
        $updateStockStmt->bind_param('ii', $cantidad, $idProd);
        $updateStockStmt->execute();
    }

    $detailStmt->close();
    $updateStockStmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'id_orden' => $idOrden]);
    $conn->close();
    exit;

} catch (Exception $ex) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
    $conn->close();
    exit;
}

?>