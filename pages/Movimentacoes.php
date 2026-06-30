<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Produto.php";
require_once __DIR__ . "/../model/Movimentacao.php";

$db = new Database();
$conn = $db->conectar();

$produto = new Produto($conn);
$movimentacao = new Movimentacao($conn);

$message = '';
$messageType = '';

$produtos = $produto->listar();
$tipos = ['entrada' => 'Entrada', 'saida' => 'Saída'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = isset($_POST['produto_id']) ? (int)$_POST['produto_id'] : 0;
    $tipo = trim($_POST['tipo'] ?? '');
    $quantidade = $_POST['quantidade'] ?? 0;
    $descricao = trim($_POST['descricao'] ?? '');

    try {
        $movimentacao->registrarMovimentacao($produtoId, (int)$_SESSION['id'], $tipo, $quantidade, $descricao !== '' ? $descricao : null);
        $message = 'Movimentação registada com sucesso.';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Erro ao registrar movimentação: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$ultimasMovimentacoes = $movimentacao->listarUltimasMovimentacoes(20);

$pageTitle = 'Movimentações';
$currentPage = 'movimentacoes';
require_once 'layouts/header.php';
?>

<div class="app-body">
    <div class="app-container">

        <div class="page-header">
            <h1>Movimentações</h1>
            <p>Registe entradas e saídas de stock para produtos.</p>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-app alert-app-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert">
            <?php if ($messageType === 'success'): ?>
            <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php else: ?>
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php endif; ?>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="card-app">
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="card-section-head">
                        <div class="card-section-tag">Registrar Movimentação</div>
                    </div>
                    <form method="POST" class="form-app">
                        <div class="field-group">
                            <label for="produto_id">Produto</label>
                            <select id="produto_id" name="produto_id" required>
                                <option value="">Selecione um produto...</option>
                                <?php foreach ($produtos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' — ' . $p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecione tipo...</option>
                                <?php foreach ($tipos as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="quantidade">Quantidade</label>
                            <input id="quantidade" type="number" name="quantidade" min="1" required placeholder="0">
                        </div>

                        <div class="field-group">
                            <label for="descricao">Descrição (opcional)</label>
                            <input id="descricao" type="text" name="descricao" placeholder="Motivo da movimentação">
                        </div>

                        <button type="submit" class="btn-primary-app">Registrar</button>
                    </form>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card-section-head">
                        <div class="card-section-tag">Últimas movimentações</div>
                    </div>
                    <?php if (empty($ultimasMovimentacoes)): ?>
                    <div class="card-section-empty">
                        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <p>Nenhuma movimentação registada ainda.</p>
                    </div>
                    <?php else: ?>
                    <div class="list-table-wrap">
                        <table class="list-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Qtd</th>
                                    <th>Usuário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasMovimentacoes as $m): ?>
                                <tr>
                                    <td class="cell-code"><?= date('d/m H:i', strtotime($m['data_movimento'])) ?></td>
                                    <td class="cell-name"><?= htmlspecialchars($m['produto_nome'] ?? '—') ?></td>
                                    <td>
                                        <span class="mov-badge mov-<?= $m['tipo'] ?>"><?= $m['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span>
                                    </td>
                                    <td class="cell-code"><?= (int)$m['quantidade'] ?></td>
                                    <td class="cell-name"><?= htmlspecialchars($m['usuario_nome'] ?? '—') ?></td>
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

<?php require_once 'layouts/footer.php'; ?>