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
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · Estoque</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/login.css">
</head>
<body>

<div class="login-shell">

    <aside class="login-aside" aria-hidden="true">
        <div class="shelf-label">
            <div class="shelf-label-tag">SISTEMA</div>
            <h1>ESTOQUE</h1>
            <p>Controle de inventário para operações de armazém e gestão de stocks.</p>
            <div class="shelf-label-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </aside>

    <main class="login-main">
        <div class="login-card">

            <div class="login-card-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>

            <h2>Entrar</h2>
            <p class="sub">Use as suas credenciais para aceder ao sistema.</p>

            <?php if (isset($erro)): ?>
            <div class="alert-error" role="alert">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" autocomplete="on" novalidate>

                <div class="form-field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            placeholder="nome@empresa.com"
                            autocomplete="email"
                            required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                        >
                    </div>
                </div>

                <div class="form-field">
                    <label for="senha">Senha</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input
                            id="senha"
                            type="password"
                            name="senha"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="login-submit">
                    Entrar
                    <svg viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>

            </form>

            <p class="footer-note">Acesso restrito a funcionários e administradores.</p>

        </div>
    </main>

</div>

<script src="../public/js/bootstrap.bundle.min.js"></script>
</body>
</html>
