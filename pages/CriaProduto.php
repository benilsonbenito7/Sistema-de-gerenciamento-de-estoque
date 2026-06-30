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
$categorias = $produto->obterCategorias();
$message = "";
$messageType = "";

function gerateUniqueCode($conn) {
    $prefix = 'SKU-';
    $uniqueCode = '';
    do {
        $uniqueCode = $prefix . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM produtos WHERE codigo = ?");
        if ($stmt === false) {
            throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $uniqueCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['total'] ?? 0;
        $stmt->close();
    } while ($count > 0);
    return $uniqueCode;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = gerateUniqueCode($conn);
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $nova_categoria = trim($_POST['nova_categoria'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    try {
        if ($nova_categoria !== '') {
            $categoria_id = $produto->criarOuBuscarCategoria($nova_categoria);
        }

        if ($codigo !== '' && $nome !== '' && $categoria_id > 0 && $preco !== '' && $quantidade >= 0) {
            $produto->criarProduto($codigo, $nome, $categoria_id, $preco, $quantidade);
            $message = "Produto cadastrado com sucesso.";
            $messageType = "success";
            // Recarrega categorias caso a nova tenha sido criada
            $categorias = $produto->obterCategorias();
        } else {
            $message = "Preencha todos os campos corretamente.";
            $messageType = "error";
        }
    } catch (Exception $e) {
        $message = "Erro ao cadastrar: " . $e->getMessage();
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
                            <label for="nome">Nome</label>
                            <input id="nome" type="text" name="nome" required placeholder="Nome do produto">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="categoria_id">Categoria existente</label>
                            <select id="categoria_id" name="categoria_id">
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="field-group">
                            <label for="nova_categoria">Ou nova categoria</label>
                            <input id="nova_categoria" type="text" name="nova_categoria" placeholder="Digite nova categoria...">
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
