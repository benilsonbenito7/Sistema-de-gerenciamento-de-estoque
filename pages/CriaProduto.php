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
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    if ($codigo !== '' && $nome !== '' && $categoria !== '' && $preco !== '' && $quantidade >= 0) {
        try {
            $produto->criarProduto($codigo, $nome, $categoria, $preco, $quantidade);
            $message = "Produto cadastrado com sucesso.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Erro ao cadastrar: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Preencha todos os campos corretamente.";
        $messageType = "error";
    }
}

$pageTitle = 'Cadastrar Produto';
$currentPage = 'produto';
require_once "layouts/header.php";
?>

<div class="app-body">
    <div class="app-container">

        <div class="page-header">
            <h1>Cadastrar Produto</h1>
            <p>Adicione um novo produto ao inventário.</p>
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

        <div class="card-app" style="max-width:560px;">
            <form method="POST">

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="codigo">Código</label>
                            <input id="codigo" type="text" name="codigo" required placeholder="SKU-001">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="nome">Nome</label>
                            <input id="nome" type="text" name="nome" required placeholder="Nome do produto">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="categoria">Categoria</label>
                            <input id="categoria" type="text" name="categoria" required placeholder="Eletrónica, Roupa...">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="preco">Preço</label>
                            <input id="preco" type="text" name="preco" required placeholder="0,00">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="quantidade">Quantidade</label>
                            <input id="quantidade" type="number" name="quantidade" min="0" required placeholder="0">
                        </div>
                    </div>
                </div>

                <div style="margin-top:1.5rem;">
                    <button type="submit" class="btn-primary-app">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Salvar produto
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<?php require_once "layouts/footer.php"; ?>
