<?php
// Soporta JSON para peticiones AJAX y redirección para peticiones tradicionales
header('Content-Type: application/json; charset=utf-8');
include('../funciones/conexion.php');

// Aceptar id y flag force para eliminar referencias relacionadas si fuera necesario
$id = null;
$force = false;
// aceptar id por GET o POST
if (isset($_GET['id'])) { $id = intval($_GET['id']); }
if (isset($_POST['id'])) { $id = intval($_POST['id']); }
if (isset($_GET['force']) && ($_GET['force'] == '1' || $_GET['force'] === 'true')) { $force = true; }
if (isset($_POST['force']) && ($_POST['force'] == '1' || $_POST['force'] === 'true')) { $force = true; }
// Debug: log raw inputs for troubleshooting
error_log('eliminar.php called raw: GET=' . var_export($_GET['id'] ?? null, true) . ' POST=' . var_export($_POST['id'] ?? null, true));
error_log('eliminar.php resolved id=' . var_export($id, true));

if (!$id) {
	// Si no pasa id y no es petición JSON, redirigir a listar
	if (!isset($_GET['format'])) {
		header('Location: listar.php');
		exit;
	}
	http_response_code(400);
	echo json_encode(['success' => false, 'error' => 'Falta id']);
	exit;
}

// Before deleting, check for related records in orden_detalle to provide informative error
$checkSql = "SELECT COUNT(*) AS cnt FROM orden_detalle WHERE id_producto = ?";
$chkStmt = $conn->prepare($checkSql);
if ($chkStmt) {
	$chkStmt->bind_param('i', $id);
	$chkStmt->execute();
	$chkRes = $chkStmt->get_result();
	$rowCount = 0;
	if ($chkRes) { $rowCount = (int)($chkRes->fetch_assoc()['cnt'] ?? 0); }
	$chkStmt->close();
	if ($rowCount > 0 && !$force) {
		// Si hay registros relacionados y no se pidió el borrado forzado, informar al cliente
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'No se puede eliminar el producto porque existen ' . $rowCount . ' registro(s) en orden_detalle relacionados.', 'related' => $rowCount]);
		$conn->close();
		exit;
	}
}

$sql = "DELETE FROM productos WHERE id_producto = ?";
error_log('eliminar.php: preparing SQL for id=' . var_export($id, true));
$stmt = $conn->prepare($sql);
if (!$stmt) {
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Error prepare: ' . $conn->error]);
	exit;
}
// Si se pidió fuerza, borramos registros en orden_detalle previamente en transacción
if ($force && $rowCount > 0) {
	// Iniciar transacción y eliminar campos relacionados
	$conn->begin_transaction();
	try {
		$delChild = $conn->prepare("DELETE FROM orden_detalle WHERE id_producto = ?");
		if ($delChild) {
			$delChild->bind_param('i', $id);
			$delChild->execute();
			$delChild->close();
		}
		$stmt = $conn->prepare($sql);
		if (!$stmt) throw new Exception('Error prepare: ' . $conn->error);
		$stmt->bind_param('i', $id);
		$ok = $stmt->execute();
		if (!$ok) throw new Exception('Error execute: ' . $stmt->error);
		$affected = $stmt->affected_rows;
		$stmt->close();
		$conn->commit();
	} catch (Exception $ex) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
		$conn->close();
		exit;
	}
} else {
	$stmt->bind_param("i", $id);
	$ok = $stmt->execute();
}
error_log('eliminar.php: execute result ok=' . var_export($ok, true) . ' stmt_error=' . var_export($stmt->error, true) . ' conn_errno=' . var_export($conn->errno, true) . ' conn_error=' . var_export($conn->error, true));
if (!$ok) {
	$errno = $conn->errno;
	$err = $conn->error;
	// Foreign key constraint or related record preventing delete
	if ($errno === 1451) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'No se puede eliminar el producto porque existen registros relacionados (ej. orden_detalle).']);
		exit;
	}
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => $err ?: $stmt->error]);
	exit;
}

$affected = $stmt->affected_rows;
error_log('eliminar.php: affected rows = ' . var_export($affected, true));
$stmt->close();

if ($affected === 0) {
	// No rows deleted, possibly missing id
	if (isset($_GET['format']) && $_GET['format'] === 'json') {
		http_response_code(404);
		echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
		$conn->close();
		exit;
	}
}

if (isset($_GET['format']) && $_GET['format'] === 'json') {
	echo json_encode(['success' => true, 'affected' => $affected, 'id' => $id]);
	$conn->close();
	exit;
}

// Default behaviour for browser: redirect back to listar.php
header('Location: listar.php');
	$conn->close();
	exit;
?>
