<?php
session_start();
if (!isset($_SESSION['id'])) {
    exit('Acesso negado.');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Produto.php';

$db = new Database();
$conn = $db->conectar();
$produto = new Produto($conn);
$produtos = $produto->listar();

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="inventario.xls"');

echo "Código\tNome\tCategoria\tPreço\tStock\n";
foreach ($produtos as $item) {
    echo $item['codigo'] . "\t" . $item['nome'] . "\t" . ($item['categoria'] ?? '—') . "\t" . number_format($item['preco'], 2, ',', '.') . "\t" . $item['quantidade'] . "\n";
}
