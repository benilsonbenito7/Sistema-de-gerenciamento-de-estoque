<?php
session_start();

require_once "../config/Database.php";
require_once "../model/Usuario.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $db = new Database();
    $conn = $db->conectar();

    $usuarioModel = new Usuario($conn);

    $usuario = $usuarioModel->buscarPorEmail($_POST['email']);

    if (
        $usuario &&
        password_verify($_POST['senha'], $usuario['senha'])
    ) {

        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['role'] = $usuario['role'];

        header("Location: dashboard.php");
        exit;
    }

    $erro = "Email ou senha inválidos.";
}
?>

<h2>Login</h2>

<?php if(isset($erro)): ?>
<p><?= $erro ?></p>
<?php endif; ?>

<form method="POST">

    <input
        type="email"
        name="email"
        placeholder="Email"
        required
    >

    <br><br>

    <input
        type="password"
        name="senha"
        placeholder="Senha"
        required
    >

    <br><br>

    <button type="submit">
        Entrar
    </button>

</form>