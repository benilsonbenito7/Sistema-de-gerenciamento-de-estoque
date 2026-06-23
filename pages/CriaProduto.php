<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Produto.php";

$db = new Database();
$conn = $db->conectar();

$produto = new Produto($conn);
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    if ($codigo !== '' && $nome !== '' && $categoria !== '' && $preco !== '' && $quantidade >= 0) {
        try {
            $produto->criarProduto($codigo, $nome, $categoria, $preco, $quantidade);
            $message = "Produto cadastrado com sucesso!";
        } catch (Exception $e) {
            $message = "Erro: " . $e->getMessage();
        }
    } else {
        $message = "Preencha todos os campos corretamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
</head>
<body>
    <h1>Cadastrar Produto</h1>

    <?php if ($message !== ''): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Código:</label><br>
        <input type="text" name="codigo" required><br><br>

        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Categoria:</label><br>
        <input type="text" name="categoria" required><br><br>

        <label>Preço:</label><br>
        <input type="text" name="preco" required><br><br>

        <label>Quantidade:</label><br>
        <input type="number" name="quantidade" min="0" required><br><br>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>