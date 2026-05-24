<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM productos WHERE estado = 1";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Sushii Encina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
      <div class="header">
        <h1>Sushii Encina</h1>
        <p>Catálogo de productos</p>
    </div>

    <div class="container">
        <p>Bienvenida/o, <?php echo $_SESSION["nombre"]; ?></p>
        <a href="mis_pedidos.php">Ver mis pedidos</a> |
        <a href="carrito.php">Ver carrito</a> |
        <a href="logout.php">Cerrar sesión</a>
    </div>
    <div class="card">
        <h3>Contacto del local</h3>
        <p>Ante dudas o consultas puedes comunicarte directamente con Sushii Encina.</p>
        <p><strong>WhatsApp:</strong> <a href="https://wa.me/56979708981" target="_blank">+56 9 7970 8981</a></p>
        <p><strong>Correo:</strong> <a href="mailto:sushiiencina@gmail.com">sushiiencina@gmail.com</a></p>
    </div>
    <?php while ($producto = $resultado->fetch_assoc()) { ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px;">
            <h3><?php echo $producto["nombre_producto"]; ?></h3>
            <p><?php echo $producto["descripcion"]; ?></p>
            <p>Precio: $<?php echo $producto["precio"]; ?></p>

            <form method="POST" action="carrito.php">
                <input type="hidden" name="id_producto" value="<?php echo $producto["id_producto"]; ?>">
                <input type="hidden" name="nombre_producto" value="<?php echo $producto["nombre_producto"]; ?>">
                <input type="hidden" name="precio" value="<?php echo $producto["precio"]; ?>">
                <input type="number" name="cantidad" value="1" min="1">
                <br><br>
                <textarea name="observacion" placeholder="Observación o ingredientes a modificar"></textarea>
                <br><br>
                <button type="submit">Agregar al carrito</button>
            </form>
        </div>
    <?php } ?>
    </div>
</body>
</html>