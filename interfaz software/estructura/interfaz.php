<?php
session_start();
include('../funciones/conexion.php');


$login_exitoso = false;
$error_login = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recibir datos del formulario
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    
    // Validar que no estén vacíos
    if ($usuario !== '' && $contrasena !== '') {

        $sql = "SELECT id, nombre_usuario, contrasena FROM usuarios WHERE nombre_usuario = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();

            // Verificar si existe
            if ($resultado->num_rows > 0) {

                $fila = $resultado->fetch_assoc();

                // Comparar contraseña tal cual
                if ($contrasena === $fila['contrasena']) {

                    // Login correcto
                    $_SESSION['usuario_id'] = $fila['id'];
                    $_SESSION['usuario_nombre'] = $fila['nombre_usuario'];
                    $login_exitoso = true;

                } else {
                    $error_login = true;
                }

            } else {
                $error_login = true;
            }

            $stmt->close();
        }
    } else {
        $error_login = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" href="../diseños/diseño.css">
</head>
<body>
    <div>
        <form method="POST" action="">
            <div class="hoja-login">
                <h1 class="titulo">LOGIN</h1><br>

                <!-- Mostrar error si las credenciales están mal -->
                <?php if ($error_login): ?>
                    <p style="color: red; text-align:center;">Usuario o contraseña incorrectos</p>
                <?php endif; ?>

                <!-- Mostrar mensaje y redirigir si el login fue exitoso -->
                <?php if ($login_exitoso): ?>
                    <p style="color: green; text-align:center;">Acceso válido, redirigiendo...</p>
                    <script>
                        window.location.href = 'base.php';
                    </script>
                <?php else: ?>

                    <!-- Formulario -->
                    <input class="datos" type="text" name="usuario" placeholder="usuario" required>   
                    <input class="datos" type="password" name="contrasena" placeholder="contraseña" required>

                    <p><a class="olvide" href="#">Olvidé contraseña</a></p>

                    <div class="btn-ingresar">
                        <button class="btn" type="submit">Ingresar</button>
                    </div>

                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="../funciones/funcion.js"></script>
</body>
</html>
