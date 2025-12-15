<?php
include('conexion.php');
header('Content-Type: text/html; charset=utf-8');
$sql = "SELECT id_producto, nombre, descripcion, cantidad, existencia, precio FROM productos";
$res = $conn->query($sql);
$rows = [];
if ($res) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Debug Productos</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head>
<body>
<h2>Productos (tabla `productos`)</h2>
<p>Filas encontradas: <?= count($rows) ?></p>
<table>
<thead><tr><th>id_producto</th><th>nombre</th><th>descripcion</th><th>cantidad</th><th>existencia</th><th>precio</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?= htmlspecialchars($r['id_producto']) ?></td>
<td><?= htmlspecialchars($r['nombre']) ?></td>
<td><?= htmlspecialchars($r['descripcion']) ?></td>
<td><?= htmlspecialchars($r['cantidad'] ?? '') ?></td>
<td><?= htmlspecialchars($r['existencia'] ?? '') ?></td>
<td><?= htmlspecialchars($r['precio'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<hr>
<h3>JSON raw</h3>
<pre><?= json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?></pre>
</body>
</html>