<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Produto.php";
require_once __DIR__ . "/../model/Movimentacao.php";

$db = new Database();
$conn = $db->conectar();
$produto = new Produto($conn);
$movimentacao = new Movimentacao($conn);

$produtos = $produto->listar();
$movimentacoes = $movimentacao->listarTodos(200);

$pageTitle = 'Relatórios';
$currentPage = 'relatorios';
require_once "layouts/header.php";
?>

<div class="app-body">
    <div class="app-container">
        <div class="page-header">
            <h1>Relatórios</h1>
            <p>Exporte o inventário e as movimentações em PDF ou Excel</p>
        </div>

        <div class="report-actions">
            <a href="/pages/relatorios/inventario_pdf.php" class="btn-primary-app" target="_blank">
                <svg viewBox="0 0 24 24"><path d="M6 2h8l4 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v4h4"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="12" y2="17"/></svg>
                PDF do Inventário
            </a>
            <a href="/pages/relatorios/inventario_excel.php" class="btn-primary-app" target="_blank">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                Excel do Inventário
            </a>
            <a href="/pages/relatorios/movimentacoes_pdf.php" class="btn-primary-app" target="_blank">
                <svg viewBox="0 0 24 24"><path d="M6 2h8l4 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v4h4"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="12" y2="17"/></svg>
                PDF das Movimentações
            </a>
            <a href="/pages/relatorios/movimentacoes_excel.php" class="btn-primary-app" target="_blank">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                Excel das Movimentações
            </a>
        </div>

        <div class="report-grid">
            <div class="report-panel">
                <div class="report-panel-head">
                    <h3>Inventário</h3>
                    <span class="report-badge"><?= count($produtos) ?> itens</span>
                </div>
                <p>Resumo com código, nome, categoria, preço, stock e valor estimado.</p>
                <div class="list-table-wrap">
                    <table class="list-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($produtos, 0, 6) as $produtoItem): ?>
                            <tr>
                                <td class="cell-code"><?= htmlspecialchars($produtoItem['codigo']) ?></td>
                                <td class="cell-name"><?= htmlspecialchars($produtoItem['nome']) ?></td>
                                <td class="cell-price"><?= (int)$produtoItem['quantidade'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-panel">
                <div class="report-panel-head">
                    <h3>Movimentações</h3>
                    <span class="report-badge"><?= count($movimentacoes) ?> registos</span>
                </div>
                <p>Últimos movimentos com tipo, produto, quantidade, utilizador e data.</p>
                <div class="list-table-wrap">
                    <table class="list-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Produto</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($movimentacoes, 0, 6) as $mov): ?>
                            <tr>
                                <td class="cell-name"><?= htmlspecialchars($mov['tipo']) ?></td>
                                <td class="cell-code"><?= htmlspecialchars($mov['produto_nome'] ?? '—') ?></td>
                                <td class="cell-price"><?= date('d/m/Y H:i', strtotime($mov['data_movimento'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "layouts/footer.php"; ?>
