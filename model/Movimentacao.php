<?php

class Movimentacao
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function validarTipo(string $tipo): string
    {
        $tipo = trim(strtolower($tipo));
        if (!in_array($tipo, ['entrada', 'saida'], true)) {
            throw new Exception('Tipo de movimentação inválido. Use entrada ou saida.');
        }
        return $tipo;
    }

    private function validarQuantidade($quantidade): int
    {
        if (!is_numeric($quantidade) || (int)$quantidade <= 0) {
            throw new Exception('Quantidade deve ser um número inteiro maior que zero.');
        }
        return (int)$quantidade;
    }

    public function registrarMovimentacao(int $produtoId, int $usuarioId, string $tipo, $quantidade, ?string $descricao = null): bool
    {
        $tipo = $this->validarTipo($tipo);
        $quantidade = $this->validarQuantidade($quantidade);

        $this->conn->begin_transaction();
        try {
            if ($tipo === 'entrada') {
                $sqlUpdate = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
                $stmt = $this->conn->prepare($sqlUpdate);
                if ($stmt === false) {
                    throw new Exception('Erro ao preparar atualização de stock: ' . $this->conn->error);
                }
                $stmt->bind_param('ii', $quantidade, $produtoId);
                $stmt->execute();
                if ($stmt->affected_rows === 0) {
                    $stmt->close();
                    throw new Exception('Produto não encontrado para entrada.');
                }
                $stmt->close();
            } else {
                $sqlUpdate = "UPDATE produtos SET quantidade = quantidade - ? WHERE id = ? AND quantidade >= ?";
                $stmt = $this->conn->prepare($sqlUpdate);
                if ($stmt === false) {
                    throw new Exception('Erro ao preparar atualização de stock: ' . $this->conn->error);
                }
                $stmt->bind_param('iii', $quantidade, $produtoId, $quantidade);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    $stmt->close();
                    $check = $this->conn->prepare("SELECT quantidade FROM produtos WHERE id = ?");
                    if ($check === false) {
                        throw new Exception('Erro ao verificar produto: ' . $this->conn->error);
                    }
                    $check->bind_param('i', $produtoId);
                    $check->execute();
                    $result = $check->get_result();
                    $produto = $result->fetch_assoc();
                    $check->close();

                    if (!$produto) {
                        throw new Exception('Produto não encontrado para saída.');
                    }

                    throw new Exception('Stock insuficiente para realizar a saída.');
                }
                $stmt->close();
            }

            $sqlInsert = "INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, descricao) VALUES (?, ?, ?, ?, ?)";
            $insert = $this->conn->prepare($sqlInsert);
            if ($insert === false) {
                throw new Exception('Erro ao preparar registro de movimentação: ' . $this->conn->error);
            }
            $insert->bind_param('iisis', $produtoId, $usuarioId, $tipo, $quantidade, $descricao);
            if (!$insert->execute()) {
                $insert->close();
                throw new Exception('Erro ao registrar movimentação: ' . $this->conn->error);
            }
            $insert->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function listarUltimasMovimentacoes(int $limite = 8): array
    {
        $sql = "SELECT m.*, p.nome AS produto_nome, u.nome AS usuario_nome FROM movimentacoes m LEFT JOIN produtos p ON m.produto_id = p.id LEFT JOIN usuarios u ON m.usuario_id = u.id ORDER BY m.data_movimento DESC LIMIT ?";
        $query = $this->conn->prepare($sql);
        if ($query === false) {
            throw new Exception('Erro ao preparar consulta de movimentações: ' . $this->conn->error);
        }
        $query->bind_param('i', $limite);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function listarPorProduto(int $produtoId, int $limite = 50): array
    {
        $sql = "SELECT m.*, p.nome AS produto_nome, u.nome AS usuario_nome FROM movimentacoes m LEFT JOIN produtos p ON m.produto_id = p.id LEFT JOIN usuarios u ON m.usuario_id = u.id WHERE m.produto_id = ? ORDER BY m.data_movimento DESC LIMIT ?";
        $query = $this->conn->prepare($sql);
        if ($query === false) {
            throw new Exception('Erro ao preparar consulta de movimentações por produto: ' . $this->conn->error);
        }
        $query->bind_param('ii', $produtoId, $limite);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obterPorId(int $id): ?array
    {
        $sql = "SELECT * FROM movimentacoes WHERE id = ?";
        $query = $this->conn->prepare($sql);
        if ($query === false) {
            throw new Exception('Erro ao preparar consulta de movimentação: ' . $this->conn->error);
        }
        $query->bind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_assoc() ?: null;
    }
}
