<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["id_rol"] != 1) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT p.id_pedido, u.nombre, p.fecha_pedido, p.tipo_entrega, 
        p.direccion_entrega, p.costo_delivery, p.total, p.metodo_pago, 
        e.nombre_estado
        FROM pedidos p
        INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
        INNER JOIN estados_pedido e ON p.id_estado = e.id_estado
        ORDER BY p.fecha_pedido DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Sushii Encina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header">
    <h1>Panel Administrador</h1>
    <p>Gestión de pedidos - Sushii Encina</p>
</div>

<div class="container">
    <h2>Pedidos registrados</h2>

    <?php while ($pedido = $resultado->fetch_assoc()) { ?>
        <div class="card">
            <h3>Pedido N° <?php echo $pedido["id_pedido"]; ?></h3>
            <p><strong>Cliente:</strong> <?php echo $pedido["nombre"]; ?></p>
            <p><strong>Fecha:</strong> <?php echo $pedido["fecha_pedido"]; ?></p>
            <p><strong>Entrega:</strong> <?php echo $pedido["tipo_entrega"]; ?></p>
            <p><strong>Dirección:</strong> <?php echo $pedido["direccion_entrega"]; ?></p>
            <p><strong>Delivery:</strong> $<?php echo $pedido["costo_delivery"]; ?></p>
            <p><strong>Total:</strong> $<?php echo $pedido["total"]; ?></p>
            <p><strong>Pago:</strong> <?php echo $pedido["metodo_pago"]; ?></p>
            <p><strong>Estado actual:</strong> <?php echo $pedido["nombre_estado"]; ?></p>

            <form method="POST" action="actualizar_estado.php">
                <input type="hidden" name="id_pedido" value="<?php echo $pedido["id_pedido"]; ?>">

                <label>Cambiar estado:</label>
                <select name="id_estado">
                    <option value="1">Pendiente</option>
                    <option value="2">Preparando</option>
                    <option value="3">En camino</option>
                    <option value="4">Entregado</option>
                </select>

                <br><br>
                <button type="submit">Actualizar estado</button>
            </form>
        </div>
    <?php } ?>

    <br>
    <a href="logout.php">Cerrar sesión</a>
</div>

</body>
</html>