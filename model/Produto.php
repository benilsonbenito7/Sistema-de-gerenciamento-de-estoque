<?php

class Produto {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function criarProduto($codigo, $nome, $categoria_id, $preco, $quantidade) {
        $sql = "INSERT INTO produtos (codigo, nome, categoria_id, preco, quantidade) VALUES (?, ?, ?, ?, ?)";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("ssidi", $codigo, $nome, $categoria_id, $preco, $quantidade);

        if (!$query->execute()) {
            throw new Exception("Erro ao cadastrar produto: " . $query->error);
        }

        return true;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM produtos WHERE id = ?";
        $query = $this->conn->prepare($sql);
        $query->bind_param("i", $id);
        $query->execute();
        $resultado = $query->get_result();
        return $resultado->fetch_assoc();
    }

    public function atualizar($id, $codigo, $nome, $categoria_id, $preco, $quantidade) {
        $sql = "UPDATE produtos SET codigo = ?, nome = ?, categoria_id = ?, preco = ?, quantidade = ? WHERE id = ?";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("ssidii", $codigo, $nome, $categoria_id, $preco, $quantidade, $id);

        if (!$query->execute()) {
            throw new Exception("Erro ao actualizar produto: " . $query->error);
        }

        return true;
    }

    public function listar() {
        $sql = "SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.nome ASC";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $resultado = $query->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obterCategorias() {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $resultado = $query->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obterCategoriaPorNome($nome) {
        $sql = "SELECT id FROM categorias WHERE nome = ? LIMIT 1";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("s", $nome);
        $query->execute();
        $resultado = $query->get_result();
        $categoria = $resultado->fetch_assoc();
        return $categoria ? (int)$categoria['id'] : null;
    }

    public function criarCategoria($nome) {
        $nome = trim($nome);
        if ($nome === '') {
            throw new Exception("Nome de categoria inválido.");
        }

        $sql = "INSERT INTO categorias (nome) VALUES (?)";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("s", $nome);

        if (!$query->execute()) {
            throw new Exception("Erro ao criar categoria: " . $query->error);
        }

        return $query->insert_id;
    }

    public function criarOuBuscarCategoria($nome) {
        $nome = trim($nome);
        if ($nome === '') {
            throw new Exception("Nome de categoria inválido.");
        }

        $categoriaId = $this->obterCategoriaPorNome($nome);
        if ($categoriaId !== null) {
            return $categoriaId;
        }

        return $this->criarCategoria($nome);
    }

    public function contarProdutos()
    {
        $sql = "SELECT COUNT(*) AS total FROM produtos";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function contarBaixoStock($limite = 10)
    {
        $sql = "SELECT COUNT(*) AS total FROM produtos WHERE quantidade < ?";
        $query = $this->conn->prepare($sql);
        $query->bind_param("i", $limite);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function contarCategorias()
    {
        $sql = "SELECT COUNT(*) AS total FROM categorias";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function calcularValorTotal()
    {
        $sql = "SELECT COALESCE(SUM(preco * quantidade), 0) AS total FROM produtos";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (float)$row['total'];
    }

    public function listarBaixoStock($limite = 10)
    {
        $sql = "SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.quantidade < ? ORDER BY p.quantidade ASC LIMIT 10";
        $query = $this->conn->prepare($sql);
        $query->bind_param("i", $limite);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
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