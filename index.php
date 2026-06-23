<?php
    require_once __DIR__ . "/config/Database.php";

    $database = new Database();
    $conn = $database->conectar();

    echo "conectado com sucesso";
?>