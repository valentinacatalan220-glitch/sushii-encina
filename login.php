<?php
session_start();
include("config/conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correo"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($password, $usuario["password"])) {
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["id_rol"] = $usuario["id_rol"];

            if ($usuario["id_rol"] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: catalogo.php");
            }
            exit;
        } else {
            $mensaje = "Contraseña incorrecta";
        }
    } else {
        $mensaje = "Correo no registrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Sushii Encina</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>

    <form method="POST">
        <input type="email" name="correo" placeholder="Correo electrónico" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>

        <button type="submit">Ingresar</button>
    </form>

    <p><?php echo $mensaje; ?></p>

    <a href="registro.php">Crear cuenta</a>
</body>
</html>