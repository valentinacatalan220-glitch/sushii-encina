<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["id_rol"] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_pedido = $_POST["id_pedido"];
    $id_estado = $_POST["id_estado"];

    $sql = "UPDATE pedidos 
            SET id_estado = ? 
            WHERE id_pedido = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param("ii", $id_estado, $id_pedido);

    $stmt->execute();
}

header("Location: admin.php");
exit;
?>