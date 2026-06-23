<?php

class Produto {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function criarProduto($codigo, $nome, $categoria, $preco, $quantidade) {
        $sql = "INSERT INTO produtos (codigo, nome, categoria, preco, quantidade) VALUES (?, ?, ?, ?, ?)";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("sssdi", $codigo, $nome, $categoria, $preco, $quantidade);

        if (!$query->execute()) {
            throw new Exception("Erro ao cadastrar produto: " . $query->error);
        }

        return true;
    }
}