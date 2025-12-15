<?php
include('../funciones/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Leer y sanitizar datos básicos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $variante = isset($_POST['variante']) ? trim($_POST['variante']) : '';
    $precio = isset($_POST['precio']) ? trim($_POST['precio']) : '0';
    $existencia = isset($_POST['existencia']) ? trim($_POST['existencia']) : 'Activo';
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    // Validaciones mínimas
    if ($nombre === '' || $categoria === '' || $codigo === '') {
        $err = 'Nombre, categoría y código son obligatorios.';
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $err]);
            exit;
        }
        die($err);
    }

    $sql = "INSERT INTO productos 
    (nombre, categoria, codigo, variante, precio, existencia, stock) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $err = 'Error preparando la consulta: ' . $conn->error;
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $err]);
            exit;
        }
        die($err);
    }

    $stmt->bind_param(
        "ssssssi",
        $nombre,
        $categoria,
        $codigo,
        $variante,
        $precio,
        $existencia,
        $stock
    );

    $ok = $stmt->execute();

    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json');
        if ($ok) {
            $id = $conn->insert_id;
            echo json_encode(['success' => true, 'id' => $id, 'affected' => $stmt->affected_rows]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $stmt->error]);
            exit;
        }
    }

    if ($ok) {
        header("Location: listar.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<h2>Agregar producto</h2>

<form method="POST">

    Nombre: <input type="text" name="nombre" required><br><br>

    Categoria: <input type="text" name="categoria" required><br><br>

    Código: <input type="text" name="codigo" required><br><br>

    Variante: <input type="text" name="variante"><br><br>

    Precio: <input type="number" step="0.01" name="precio" required><br><br>

    Existencia:
    <select name="existencia">
        <option value="Activo">Activo</option>
        <option value="Inactivo">Inactivo</option>
    </select><br><br>

    Stock: <input type="number" name="stock" value="0"><br><br>

    <button type="submit">Guardar</button>
</form>

<br>
<a href="listar.php">Regresar</a>
