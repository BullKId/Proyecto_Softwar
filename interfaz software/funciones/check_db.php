<?php
include('conexion.php');
header('Content-Type: text/plain; charset=utf-8');

// 1. Mostrar estructura de la tabla
echo "=== ESTRUCTURA DE LA TABLA PRODUCTOS ===\n";
$colRes = $conn->query("SHOW COLUMNS FROM productos");
if ($colRes) {
    while ($c = $colRes->fetch_assoc()) {
        echo $c['Field'] . " (" . $c['Type'] . ")\n";
    }
}

// 2. Mostrar primeros 5 registros
echo "\n=== PRIMEROS 5 REGISTROS ===\n";
$sqlAll = "SELECT * FROM productos LIMIT 5";
$resAll = $conn->query($sqlAll);
if ($resAll) {
    while ($r = $resAll->fetch_assoc()) {
        echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>