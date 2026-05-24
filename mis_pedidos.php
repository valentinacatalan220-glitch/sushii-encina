<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT p.id_pedido, p.fecha_pedido, p.tipo_entrega, p.direccion_entrega,
        p.costo_delivery, p.total, p.metodo_pago, e.nombre_estado
        FROM pedidos p
        INNER JOIN estados_pedido e ON p.id_estado = e.id_estado
        WHERE p.id_usuario = ?
        ORDER BY p.fecha_pedido DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos - Sushii Encina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header">
    <h1>Mis Pedidos</h1>
    <p>Seguimiento de pedidos - Sushii Encina</p>
</div>

<div class="container">

    <p>Cliente: <?php echo $_SESSION["nombre"]; ?></p>

    <a href="catalogo.php">Volver al catálogo</a> |
    <a href="logout.php">Cerrar sesión</a>

    <br><br>

    <?php if ($resultado->num_rows == 0) { ?>

        <div class="card">
            <p>Aún no tienes pedidos registrados.</p>
        </div>

    <?php } else { ?>

        <?php while ($pedido = $resultado->fetch_assoc()) { ?>

            <div class="card">
                <h3>Pedido N° <?php echo $pedido["id_pedido"]; ?></h3>

                <p><strong>Fecha:</strong> <?php echo $pedido["fecha_pedido"]; ?></p>
                <p><strong>Tipo de entrega:</strong> <?php echo $pedido["tipo_entrega"]; ?></p>
                <p><strong>Dirección:</strong> <?php echo $pedido["direccion_entrega"]; ?></p>
                <p><strong>Delivery:</strong> $<?php echo $pedido["costo_delivery"]; ?></p>
                <p><strong>Total:</strong> $<?php echo $pedido["total"]; ?></p>
                <p><strong>Método de pago:</strong> <?php echo $pedido["metodo_pago"]; ?></p>

                <h3>Estado: <?php echo $pedido["nombre_estado"]; ?></h3>
            </div>

        <?php } ?>

    <?php } ?>

</div>

</body>
</html>