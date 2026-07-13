<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    exit('Acesso negado.');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Movimentacao.php';

$db = new Database();
$conn = $db->conectar();
$movimentacao = new Movimentacao($conn);
$movimentacoes = $movimentacao->listarTodos(200);

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="movimentacoes.xls"');

echo "Tipo\tProduto\tQuantidade\tUtilizador\tData\n";
foreach ($movimentacoes as $item) {
    echo $item['tipo'] . "\t" . ($item['produto_nome'] ?? '—') . "\t" . $item['quantidade'] . "\t" . ($item['usuario_nome'] ?? '—') . "\t" . $item['data_movimento'] . "\n";
}
