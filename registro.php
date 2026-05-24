<?php
include("config/conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $telefono = $_POST["telefono"];
    $correo = $_POST["correo"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $id_rol = 2;

    $sql = "INSERT INTO usuarios (id_rol, nombre, telefono, correo, password)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issss", $id_rol, $nombre, $telefono, $correo, $password);

    if ($stmt->execute()) {
        $mensaje = "Usuario registrado correctamente";
    } else {
        $mensaje = "Error al registrar usuario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Sushii Encina</title>
</head>
<body>
    <h1>Registro de Cliente</h1>

    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required><br><br>
        <input type="text" name="telefono" placeholder="Teléfono" required><br><br>
        <input type="email" name="correo" placeholder="Correo electrónico" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>

        <button type="submit">Registrarse</button>
    </form>

    <p><?php echo $mensaje; ?></p>

    <a href="index.php">Volver al inicio</a>
</body>
</html>