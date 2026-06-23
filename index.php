<?php 
    require_once "./config/Database.php";

    $database = new Database();
    $conn = $database->conectar();

    echo "conectado com sucesso"
?>