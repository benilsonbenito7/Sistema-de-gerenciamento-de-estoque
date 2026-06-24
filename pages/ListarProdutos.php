<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Produto.php";

$db = new Database();
$conn = $db->conectar();
$produto = new Produto($conn);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $produto->deletar((int)$_POST['delete_id']);
        $message = 'Produto removido do inventário.';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Erro ao remover: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$produtos = $produto->listar();

$categorias = [];
foreach ($produtos as $p) {
    if (!empty($p['categoria']) && !in_array($p['categoria'], $categorias)) {
        $categorias[] = $p['categoria'];
    }
}
sort($categorias);

$totalCount = count($produtos);

$pageTitle = 'Inventário';
$currentPage = 'listar';
require_once "layouts/header.php";
?>

<div class="app-body">
    <div class="app-container">

        <div class="page-header">
            <h1>Inventário</h1>
            <p>Consulta e gestão do stock disponível</p>
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

            <div class="list-toolbar">
                <div class="list-search-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="list-search" id="searchInput" placeholder="Procurar produto..." autocomplete="off">
                </div>
                <div class="list-toolbar-right">
                    <select class="list-filter" id="categoryFilter">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a href="CriaProduto.php" class="btn-primary-app">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Novo Produto
                    </a>
                </div>
            </div>

            <?php if (empty($produtos)): ?>

            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <h3>Nenhum produto registado</h3>
                <p>O inventário está vazio. Adicione o primeiro produto.</p>
                <a href="CriaProduto.php" class="btn-primary-app">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Adicionar Produto
                </a>
            </div>

            <?php else: ?>

            <div class="list-table-wrap">
                <table class="list-table" id="productTable">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p):
                            $qty = (int)$p['quantidade'];
                            if ($qty > 50) {
                                $stockLevel = 'ok';
                                $stockLabel = 'OK';
                            } elseif ($qty >= 10) {
                                $stockLevel = 'low';
                                $stockLabel = 'Baixo';
                            } else {
                                $stockLevel = 'critical';
                                $stockLabel = 'Crítico';
                            }
                            $barWidth = min(($qty / 100) * 100, 100);
                        ?>
                        <tr>
                            <td class="cell-code"><?= htmlspecialchars($p['codigo']) ?></td>
                            <td class="cell-name"><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="cell-category"><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                            <td class="cell-price">R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                            <td>
                                <div class="stock-wrap">
                                    <div class="stock-bar">
                                        <div class="stock-bar-fill <?= $stockLevel ?>" style="width:<?= $barWidth ?>%"></div>
                                    </div>
                                    <span class="stock-label <?= $stockLevel ?>"><?= $stockLabel ?></span>
                                    <span class="stock-qty"><?= $qty ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="CriaProduto.php?editar=<?= $p['id'] ?>" class="action-btn edit" title="Editar">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <button type="button" class="action-btn delete" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="empty-state empty-search" id="emptySearch" style="display:none;">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                <h3>Nenhum resultado</h3>
                <p>Tente ajustar a pesquisa ou o filtro selecionado.</p>
            </div>

            <div class="list-summary">
                <span><strong id="visibleCount"><?= $totalCount ?></strong> de <strong><?= $totalCount ?></strong> produtos</span>
                <span><?= count($categorias) ?> categorias</span>
            </div>

            <?php endif; ?>

        </div>

    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--c-border-light);padding:0.5rem;">
            <div class="modal-body" style="text-align:center;padding:1.5rem 1rem;">
                <svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:var(--c-error);fill:none;stroke-width:1.5;margin-bottom:0.75rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <h6 style="font-weight:600;margin-bottom:0.25rem;">Excluir produto</h6>
                <p style="font-size:0.85rem;color:var(--c-muted);margin-bottom:1.25rem;" id="deleteModalText">Tem certeza?</p>
                <form method="POST" style="display:flex;gap:0.5rem;justify-content:center;">
                    <input type="hidden" name="delete_id" id="deleteIdInput" value="">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="padding:0.45rem 1rem;border:1.5px solid var(--c-border);border-radius:8px;font-size:0.85rem;font-weight:500;">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="padding:0.45rem 1rem;background:var(--c-error);color:white;border:none;border-radius:8px;font-size:0.85rem;font-weight:600;">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var searchInput = document.getElementById('searchInput');
    var categoryFilter = document.getElementById('categoryFilter');
    var table = document.getElementById('productTable');
    var visibleCount = document.getElementById('visibleCount');
    var emptySearch = document.getElementById('emptySearch');
    var listSummary = document.querySelector('.list-summary');

    function filterProducts() {
        var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        searchTerm = searchTerm.normalize ? searchTerm.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : searchTerm;
        var category = categoryFilter ? categoryFilter.value.toLowerCase() : '';
        var rows = table ? table.querySelectorAll('tbody tr') : [];
        var visible = 0;

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var code = row.querySelector('.cell-code');
            var name = row.querySelector('.cell-name');
            var cat = row.querySelector('.cell-category');
            var searchable = (code ? code.textContent : '') + ' ' + (name ? name.textContent : '') + ' ' + (cat ? cat.textContent : '');
            searchable = searchable.toLowerCase();
            if (searchable.normalize) {
                searchable = searchable.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }
            var matchSearch = !searchTerm || searchable.indexOf(searchTerm) !== -1;
            var matchCategory = !category || (cat && cat.textContent.toLowerCase().trim() === category);
            row.style.display = (matchSearch && matchCategory) ? '' : 'none';
            if (matchSearch && matchCategory) visible++;
        }

        if (visibleCount) visibleCount.textContent = visible;

        if (emptySearch) {
            emptySearch.style.display = visible === 0 ? '' : 'none';
        }
        if (table) {
            table.style.display = visible === 0 ? 'none' : '';
        }
        if (listSummary) {
            listSummary.style.display = visible === 0 ? 'none' : '';
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterProducts);
    if (categoryFilter) categoryFilter.addEventListener('change', filterProducts);

    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (btn) {
                var id = btn.getAttribute('data-id');
                var name = btn.getAttribute('data-name');
                document.getElementById('deleteIdInput').value = id;
                document.getElementById('deleteModalText').textContent = 'Remover "' + name + '" do invent\u00e1rio? Esta a\u00e7\u00e3o n\u00e3o pode ser desfeita.';
            }
        });
    }
})();
</script>

<?php require_once "layouts/footer.php"; ?>
