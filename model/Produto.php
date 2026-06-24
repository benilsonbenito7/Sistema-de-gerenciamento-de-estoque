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

    public function listar() {
        $sql = "SELECT * FROM produtos ORDER BY nome ASC";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $resultado = $query->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function deletar($id) {
        $this->conn->begin_transaction();
        try {
            $sql1 = "DELETE FROM movimentacoes WHERE produto_id = ?";
            $q1 = $this->conn->prepare($sql1);
            $q1->bind_param("i", $id);
            $q1->execute();
            $q1->close();

            $sql2 = "DELETE FROM produtos WHERE id = ?";
            $q2 = $this->conn->prepare($sql2);
            $q2->bind_param("i", $id);
            if (!$q2->execute()) {
                throw new Exception("Erro ao excluir produto: " . $q2->error);
            }
            $q2->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}