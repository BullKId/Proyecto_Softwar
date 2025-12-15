<?php
include('../funciones/conexion.php');

$id = $_GET['id'];

$sql = "SELECT * FROM productos WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];

    $update = "UPDATE productos SET nombre=?, descripcion=?, cantidad=?, precio=? WHERE id_producto=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssidi", $nombre, $descripcion, $cantidad, $precio, $id);
    $stmt->execute();

    header("Location: listar.php");
    exit;
}
?>

<h2>Editar producto</h2>

<form method="POST">
    Nombre: <input type="text" name="nombre" value="<?= $producto['nombre'] ?>" required><br><br>
    Descripción: <textarea name="descripcion"><?= $producto['descripcion'] ?></textarea><br><br>
    Cantidad: <input type="number" name="cantidad" value="<?= $producto['cantidad'] ?>" required><br><br>
    Precio: <input type="text" name="precio" value="<?= $producto['precio'] ?>" required><br><br>

    <button type="submit">Actualizar</button>
</form>

<br>
<a href="listar.php">Regresar</a>
