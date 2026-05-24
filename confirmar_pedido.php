<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$carrito = $_SESSION["carrito"] ?? [];
$pedido_registrado = false;

if (empty($carrito) && $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: catalogo.php");
    exit;
}

$total_productos = 0;

foreach ($carrito as $item) {
    $total_productos += $item["subtotal"];
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipo_entrega = $_POST["tipo_entrega"];
    $direccion_entrega = $_POST["direccion_entrega"];
    $metodo_pago = $_POST["metodo_pago"];
    $costo_delivery = intval($_POST["costo_delivery"]);

    if ($tipo_entrega == "Retiro en local") {
        $costo_delivery = 0;
        $direccion_entrega = "Retiro en local";
    }

    $total_final = $total_productos + $costo_delivery;

    $id_usuario = $_SESSION["id_usuario"];
    $id_estado = 1;

    $sql = "INSERT INTO pedidos 
    (id_usuario, id_estado, tipo_entrega, direccion_entrega, costo_delivery, total, metodo_pago)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "iissiis",
        $id_usuario,
        $id_estado,
        $tipo_entrega,
        $direccion_entrega,
        $costo_delivery,
        $total_final,
        $metodo_pago
    );

    if ($stmt->execute()) {

        $id_pedido = $stmt->insert_id;

        foreach ($carrito as $item) {

            $observacion_final = $item["observacion"] . " | Extra modificación: $" . $item["extra"];

            $sql_detalle = "INSERT INTO detalle_pedido
            (id_pedido, id_producto, cantidad, subtotal, observacion)
            VALUES (?, ?, ?, ?, ?)";

            $stmt_detalle = $conexion->prepare($sql_detalle);

            $stmt_detalle->bind_param(
                "iiiis",
                $id_pedido,
                $item["id_producto"],
                $item["cantidad"],
                $item["subtotal"],
                $observacion_final
            );

            $stmt_detalle->execute();
        }

        unset($_SESSION["carrito"]);
        $pedido_registrado = true;

        $mensaje = "Pedido registrado correctamente. Número de pedido: " . $id_pedido;

    } else {

        $mensaje = "Error al registrar pedido";

    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Pedido - Sushii Encina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="header">
    <h1>Confirmar Pedido</h1>
    <p>Sushii Encina</p>
</div>

<div class="container">

<?php if (!$pedido_registrado) { ?>

    <h2>Resumen del pedido</h2>

    <?php foreach ($carrito as $item) { ?>

        <div class="card">

            <h3><?php echo $item["nombre_producto"]; ?></h3>

            <p><strong>Precio base:</strong> $<?php echo $item["precio"]; ?></p>
            <p><strong>Cantidad:</strong> <?php echo $item["cantidad"]; ?></p>
            <p><strong>Observación:</strong> <?php echo $item["observacion"]; ?></p>
            <p><strong>Costo extra:</strong> $<?php echo $item["extra"]; ?></p>
            <p><strong>Subtotal:</strong> $<?php echo $item["subtotal"]; ?></p>

        </div>

    <?php } ?>

    <div class="resumen">
        <h3>Total productos: $<?php echo $total_productos; ?></h3>
    </div>

    <br>

    <form method="POST">

        <label>Tipo de entrega:</label><br>

        <select name="tipo_entrega" id="tipo_entrega" onchange="cambiarEntrega()" required>
            <option value="Delivery">Delivery</option>
            <option value="Retiro en local">Retiro en local</option>
        </select>

        <br><br>

        <div id="delivery_box">

            <label>Dirección:</label><br>

            <input type="text" name="direccion_entrega" id="direccion_entrega" placeholder="Ej: Sector Palmilla, casa azul">

            <br><br>

            <button type="button" onclick="obtenerUbicacion()">
                Usar mi ubicación actual
            </button>

            <p id="ubicacion_texto">Ubicación no registrada</p>
            <p id="distancia_texto">Distancia estimada: 0 km</p>
            <p id="delivery_texto">Costo delivery: $0</p>

        </div>

        <input type="hidden" name="costo_delivery" id="costo_delivery" value="0">

        <br>

        <label>Método de pago:</label><br>

        <select name="metodo_pago" id="metodo_pago" onchange="mostrarTransferencia()" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia">Transferencia</option>
        </select>
        <div id="datos_retiro" class="card" style="display:none;">
            <h3>Datos para retiro en local</h3>
            <p><strong>Dirección:</strong> Sushii Encina, sector San Antonio Encina, Linares.</p>
            <p><strong>Ubicación referencial:</strong> -35.854556, -71.499702</p>
            <p><strong>WhatsApp:</strong> +56 9 7970 8981</p>
            <p><strong>Correo:</strong> sushiiencina@gmail.com</p>
        </div>
        <div id="datos_transferencia" class="card" style="display:none;">
            <h3>Datos para transferencia</h3>
            <p><strong>Nombre:</strong> Sushii Encina</p>
            <p><strong>Banco:</strong> Banco Estado</p>
            <p><strong>Tipo de cuenta:</strong> Cuenta RUT</p>
            <p><strong>RUT:</strong> 12.345.678-9</p>
            <p><strong>N° de cuenta:</strong> 12345678</p>
            <p><strong>Correo:</strong> pagos@sushiiencina.cl</p>
        </div>

        <br>

        <h2 id="total_final_texto">
            Total final: $<?php echo $total_productos; ?>
        </h2>

        <button type="submit">
            Registrar pedido
        </button>

    </form>

<?php } ?>

<?php if ($mensaje != "") { ?>

    <div class="card">
        <h2><?php echo $mensaje; ?></h2>
        <a class="btn" href="mis_pedidos.php">Ver mis pedidos</a>
    </div>

<?php } ?>

<br>

<a href="catalogo.php">Volver al catálogo</a>

</div>

<script>

const totalProductos = <?php echo $total_productos; ?>;

const localLat = -35.854695;
const localLon = -71.499702;

function obtenerUbicacion() {

    if (!navigator.geolocation) {
        alert("Tu navegador no permite geolocalización");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(posicion) {

            const clienteLat = posicion.coords.latitude;
            const clienteLon = posicion.coords.longitude;

            const distancia = calcularDistancia(localLat, localLon, clienteLat, clienteLon);
            const costo = calcularDelivery(distancia);
            const totalFinal = totalProductos + costo;

            document.getElementById("ubicacion_texto").innerText =
                "Ubicación registrada correctamente";

            document.getElementById("distancia_texto").innerText =
                "Distancia estimada: " + distancia.toFixed(2) + " km";

            document.getElementById("delivery_texto").innerText =
                "Costo delivery: $" + costo;

            document.getElementById("total_final_texto").innerText =
                "Total final: $" + totalFinal;

            document.getElementById("costo_delivery").value = costo;

        },
        function() {
            alert("Debes permitir acceso a la ubicación");
        }
    );
}

function calcularDistancia(lat1, lon1, lat2, lon2) {

    const radio = 6371;

    const dLat = gradosARadianes(lat2 - lat1);
    const dLon = gradosARadianes(lon2 - lon1);

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(gradosARadianes(lat1)) *
        Math.cos(gradosARadianes(lat2)) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return radio * c;
}

function gradosARadianes(grados) {
    return grados * (Math.PI / 180);
}

function calcularDelivery(distancia) {

    if (distancia <= 2.9) {
        return 1000;
    }

    const kmRedondeado = Math.ceil(distancia);

    return 1000 + ((kmRedondeado - 2) * 500);
}

function cambiarEntrega() {

   function cambiarEntrega() {

    const tipo = document.getElementById("tipo_entrega").value;
    const datosRetiro = document.getElementById("datos_retiro");

    if (tipo == "Retiro en local") {

        document.getElementById("delivery_box").style.display = "none";
        datosRetiro.style.display = "block";

        document.getElementById("delivery_texto").innerText = "Costo delivery: $0";
        document.getElementById("total_final_texto").innerText = "Total final: $" + totalProductos;
        document.getElementById("costo_delivery").value = 0;

    } else {

        document.getElementById("delivery_box").style.display = "block";
        datosRetiro.style.display = "none";

    }
}
}

function mostrarTransferencia() {

    const metodo = document.getElementById("metodo_pago").value;
    const datos = document.getElementById("datos_transferencia");

    if (metodo == "Transferencia") {
        datos.style.display = "block";
    } else {
        datos.style.display = "none";
    }
}

</script>

</body>
</html>