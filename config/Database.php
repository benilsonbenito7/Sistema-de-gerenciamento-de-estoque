<?php

class Database {

    public function conectar(){

        $conn = new mysqli("127.0.0.1", "root", "", "estoque_db", 3306);

        if ($conn->connect_error){
            die("Erro na conexao: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");

        return $conn;

    }
}

?>