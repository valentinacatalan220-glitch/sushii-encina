<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

function limpiarTexto($texto) {
    $texto = strtolower($texto);
    $texto = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n'],
        $texto
    );
    return $texto;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $precio_base = intval($_POST["precio"]);
    $cantidad = intval($_POST["cantidad"]);
    $observacion_original = $_POST["observacion"];
    $observacion = limpiarTexto($observacion_original);

    $extra = 0;
    $detalle_extra = [];

    // Cambio de pollo o kanikama por camarón: $500 extra
    if (
        (strpos($observacion, "pollo") !== false || strpos($observacion, "kanikama") !== false) &&
        strpos($observacion, "camaron") !== false &&
        (
            strpos($observacion, "cambio") !== false ||
            strpos($observacion, "cambiar") !== false ||
            strpos($observacion, "por") !== false
        )
    ) {
        $extra += 500;
        $detalle_extra[] = "Cambio a camarón: $500";
    }

    // Envuelto en palta o queso crema: $1000 extra
    if (
        strpos($observacion, "envuelto en palta") !== false ||
        strpos($observacion, "envuelto palta") !== false ||
        strpos($observacion, "envuelto en queso crema") !== false ||
        strpos($observacion, "envuelto queso crema") !== false
    ) {
        $extra += 1000;
        $detalle_extra[] = "Cambio de envoltura especial: $1000";
    }

    // Agregar ingrediente adicional: $500 extra
    if (
        strpos($observacion, "agregar") !== false ||
        strpos($observacion, "agrega") !== false ||
        strpos($observacion, "añadir") !== false ||
        strpos($observacion, "anadir") !== false ||
        strpos($observacion, "extra") !== false ||
        strpos($observacion, "con pimenton") !== false ||
        strpos($observacion, "con cebollin") !== false ||
        strpos($observacion, "con palta") !== false ||
        strpos($observacion, "con queso crema") !== false
    ) {
        $extra += 500;
        $detalle_extra[] = "Ingrediente adicional: $500";
    }

    $precio_unitario_final = $precio_base + $extra;
    $subtotal = $precio_unitario_final * $cantidad;

    if (empty($detalle_extra)) {
        $detalle_extra_texto = "Sin costo extra";
    } else {
        $detalle_extra_texto = implode(" | ", $detalle_extra);
    }

    $producto = [
        "id_producto" => $_POST["id_producto"],
        "nombre_producto" => $_POST["nombre_producto"],
        "precio" => $precio_base,
        "cantidad" => $cantidad,
        "observacion" => $observacion_original,
        "extra" => $extra,
        "detalle_extra" => $detalle_extra_texto,
        "precio_unitario_final" => $precio_unitario_final,
        "subtotal" => $subtotal
    ];

    $_SESSION["carrito"][] = $producto;
}

$total = 0;

foreach ($_SESSION["carrito"] as $item) {
    $total += $item["subtotal"];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito - Sushii Encina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header">
    <h1>Carrito de Compras</h1>
    <p>Sushii Encina</p>
</div>

<div class="container">

    <?php if (empty($_SESSION["carrito"])) { ?>

        <div class="card">
            <p>El carrito está vacío.</p>
        </div>

    <?php } else { ?>

        <?php foreach ($_SESSION["carrito"] as $item) { ?>

            <div class="card">
                <h3><?php echo $item["nombre_producto"]; ?></h3>

                <p><strong>Precio base:</strong> $<?php echo $item["precio"]; ?></p>
                <p><strong>Cantidad:</strong> <?php echo $item["cantidad"]; ?></p>
                <p><strong>Observación:</strong> <?php echo $item["observacion"]; ?></p>
                <p><strong>Detalle extra:</strong> <?php echo $item["detalle_extra"]; ?></p>
                <p><strong>Costo extra:</strong> $<?php echo $item["extra"]; ?></p>
                <p><strong>Precio unitario final:</strong> $<?php echo $item["precio_unitario_final"]; ?></p>
                <p><strong>Subtotal:</strong> $<?php echo $item["subtotal"]; ?></p>
            </div>

        <?php } ?>

        <div class="resumen">
            <h2>Total productos: $<?php echo $total; ?></h2>
        </div>

        <br>

        <a class="btn" href="confirmar_pedido.php">Confirmar pedido</a>

    <?php } ?>

    <br><br>

    <a href="catalogo.php">Seguir comprando</a> |
    <a href="mis_pedidos.php">Ver mis pedidos</a>

</div>

</body>
</html>