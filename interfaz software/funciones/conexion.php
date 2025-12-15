<?php
// Configuración de la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$nombre_base_datos = "inventario";
$puerto = 3306;

// ========== Crear conexión ==========
$conn = new mysqli($host, $usuario, $clave, $nombre_base_datos, $puerto);

// ========== Verificar conexión ==========
if ($conn->connect_error) {
    // Registrar error en el log de PHP (xampp/php/logs)
    error_log("Error de conexión a BD: " . $conn->connect_error);

    // Mostrar mensaje al usuario (sin exponer información sensible)
    die("
        <h3>Error de Conexión</h3>
        <p>No se pudo conectar a la base de datos.</p>

        <p><strong>Posibles causas:</strong></p>
        <ul>
            <li>MySQL no está ejecutándose</li>
            <li>Usuario o contraseña incorrectos</li>
            <li>No existe la base de datos <strong>inventario</strong></li>
            <li>El puerto 3306 está ocupado o cambiado</li>
        </ul>

        <p><strong>Error técnico:</strong> " . htmlspecialchars($conn->connect_error) . "</p>
    ");
}

// Configurar charset recomendado
$conn->set_charset("utf8mb4");
?>

