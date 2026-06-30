<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Produto.php";
require_once __DIR__ . "/../model/Usuario.php";

$db = new Database();
$conn = $db->conectar();

$produto = new Produto($conn);
$usuarioModel = new Usuario($conn);

$totalProdutos = $produto->contarProdutos();
$baixoStock = $produto->contarBaixoStock(10);
$totalCategorias = $produto->contarCategorias();
$valorTotal = $produto->calcularValorTotal();
$produtosBaixoStock = $produto->listarBaixoStock(10);
$ultimasMovimentacoes = $produto->listarUltimasMovimentacoes(8);
$totalUsuarios = $usuarioModel->contarTodos();

$pageTitle = 'Painel';
$currentPage = 'dashboard';
require_once "layouts/header.php";
?>

<div class="app-body">
    <div class="app-container">

        <div class="page-header">
            <h1>Painel</h1>
            <p>Bem-vindo, <strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></p>
        </div>

        <div class="row g-4">

            <div class="col-12 col-md-6 col-lg-3">
                <a href="/pages/ListarProdutos.php" class="card-app card-kpi">
                    <div class="card-kpi-tag">PRODUTOS</div>
                    <div class="card-kpi-value"><?= $totalProdutos ?></div>
                    <div class="card-kpi-label">em stock</div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="#baixo-stock" class="card-app card-kpi <?= $baixoStock > 0 ? 'card-kpi-warn' : '' ?>">
                    <div class="card-kpi-tag">BAIXO STOCK</div>
                    <div class="card-kpi-value"><?= $baixoStock ?></div>
                    <div class="card-kpi-label">unidades mínimas</div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-app card-kpi">
                    <div class="card-kpi-tag">CATEGORIAS</div>
                    <div class="card-kpi-value"><?= $totalCategorias ?></div>
                    <div class="card-kpi-label">registadas</div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-app card-kpi">
                    <div class="card-kpi-tag">VALOR TOTAL</div>
                    <div class="card-kpi-value"><?= $valorTotal >= 1000 ? 'Kz ' . number_format($valorTotal, 0, ',', '.') : 'Kz ' . number_format($valorTotal, 2, ',', '.') ?></div>
                    <div class="card-kpi-label">em inventário</div>
                </div>
            </div>

        </div>

        <div class="row g-4" style="margin-top:0.5rem;">

            <div class="col-12 col-lg-6" id="baixo-stock">
                <div class="card-app">
                    <div class="card-section-head">
                        <div class="card-section-tag">Produtos em Baixo</div>
                        <?php if (!empty($produtosBaixoStock)): ?>
                        <a href="/pages/ListarProdutos.php" class="card-section-link">Ver todos</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($produtosBaixoStock)): ?>
                    <div class="card-section-empty">
                        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <p>Todos os produtos com stock suficiente.</p>
                    </div>
                    <?php else: ?>
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
                                <?php foreach ($produtosBaixoStock as $p):
                                    $qty = (int)$p['quantidade'];
                                    if ($qty >= 10) { $stockLevel = 'low'; } else { $stockLevel = 'critical'; }
                                ?>
                                <tr>
                                    <td class="cell-code"><?= htmlspecialchars($p['codigo']) ?></td>
                                    <td class="cell-name"><?= htmlspecialchars($p['nome']) ?></td>
                                    <td>
                                        <div class="stock-wrap">
                                            <div class="stock-bar">
                                                <div class="stock-bar-fill <?= $stockLevel ?>" style="width:<?= min(($qty / 10) * 100, 100) ?>%"></div>
                                            </div>
                                            <span class="stock-label <?= $stockLevel ?>"><?= $qty ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card-app">
                    <div class="card-section-head">
                        <div class="card-section-tag">Últimas Movimentações</div>
                    </div>
                    <?php if (empty($ultimasMovimentacoes)): ?>
                    <div class="card-section-empty">
                        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <p>Nenhuma movimentação registada.</p>
                    </div>
                    <?php else: ?>
                    <div class="list-table-wrap">
                        <table class="list-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasMovimentacoes as $m): ?>
                                <tr>
                                    <td>
                                        <span class="mov-badge mov-<?= $m['tipo'] ?>"><?= $m['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span>
                                    </td>
                                    <td class="cell-name"><?= htmlspecialchars($m['produto_nome'] ?? '—') ?></td>
                                    <td class="cell-code"><?= (int)$m['quantidade'] ?></td>
                                    <td class="cell-code"><?= date('d/m H:i', strtotime($m['data_movimento'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once "layouts/footer.php"; ?>
