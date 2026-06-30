<?php

class Usuario
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function buscarPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $query = $this->conn->prepare($sql);
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_assoc();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT id, nome, email, role, criado_em FROM usuarios WHERE id = ?";
        $query = $this->conn->prepare($sql);
        $query->bind_param("i", $id);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_assoc();
    }

    public function listarTodos()
    {
        $sql = "SELECT id, nome, email, role, criado_em FROM usuarios ORDER BY nome ASC";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function criar($nome, $email, $senha, $role)
    {
        $nome = trim($nome);
        $email = trim($email);

        if ($nome === '' || $email === '' || $senha === '') {
            throw new Exception("Preencha todos os campos.");
        }

        if (!in_array($role, ['admin', 'funcionario'], true)) {
            throw new Exception("Role inválido.");
        }

        $existente = $this->buscarPorEmail($email);
        if ($existente) {
            throw new Exception("Já existe um usuário com este email.");
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("ssss", $nome, $email, $hash, $role);

        if (!$query->execute()) {
            throw new Exception("Erro ao cadastrar usuário: " . $query->error);
        }

        return true;
    }

    public function atualizar($id, $nome, $email, $role)
    {
        $nome = trim($nome);
        $email = trim($email);

        if ($nome === '' || $email === '') {
            throw new Exception("Nome e email são obrigatórios.");
        }

        if (!in_array($role, ['admin', 'funcionario'], true)) {
            throw new Exception("Role inválido.");
        }

        $sql = "UPDATE usuarios SET nome = ?, email = ?, role = ? WHERE id = ?";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("sssi", $nome, $email, $role, $id);

        if (!$query->execute()) {
            throw new Exception("Erro ao atualizar usuário: " . $query->error);
        }

        return true;
    }

    public function atualizarSenha($id, $senha)
    {
        if ($senha === '') {
            throw new Exception("A senha não pode estar vazia.");
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);

        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("si", $hash, $id);

        if (!$query->execute()) {
            throw new Exception("Erro ao atualizar senha: " . $query->error);
        }

        return true;
    }

    public function deletar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $query = $this->conn->prepare($sql);

        if ($query === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        $query->bind_param("i", $id);

        if (!$query->execute()) {
            throw new Exception("Erro ao excluir usuário: " . $query->error);
        }

        return $query->affected_rows > 0;
    }

    public function contarPorRole($role)
    {
        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE role = ?";
        $query = $this->conn->prepare($sql);
        $query->bind_param("s", $role);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function contarTodos()
    {
        $sql = "SELECT COUNT(*) AS total FROM usuarios";
        $query = $this->conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
}
