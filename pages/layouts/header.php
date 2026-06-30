<?php
$pageTitle = $pageTitle ?? 'Estoque';
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> · Estoque</title>
    <link rel="stylesheet" href="/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>

<div class="app-shell">

<header class="app-header">
    <div class="app-header-inner">

        <a href="/pages/dashboard.php" class="app-brand">
            <span class="app-brand-tag">SYS</span>
            <span class="app-brand-name">ESTOQUE</span>
        </a>

        <button class="mobile-menu-toggle" type="button" aria-label="Abrir menu" onclick="document.querySelector('.app-nav').classList.toggle('open')">
            <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>

        <nav class="app-nav">
            <a href="/pages/dashboard.php" <?= $currentPage === 'dashboard' ? 'class="active"' : '' ?>>Painel</a>
            <a href="/pages/ListarProdutos.php" <?= $currentPage === 'listar' ? 'class="active"' : '' ?>>Inventário</a>
            <a href="/pages/Movimentacoes.php" <?= $currentPage === 'movimentacoes' ? 'class="active"' : '' ?>>Movimentações</a>
            <a href="/pages/CriaProduto.php" <?= $currentPage === 'produto' ? 'class="active"' : '' ?>>Registar</a>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="/pages/Usuarios.php" <?= $currentPage === 'usuarios' ? 'class="active"' : '' ?>>Usuários</a>
            <?php endif; ?>
        </nav>

        <div class="app-header-right">
            <div class="app-user">
                <div class="app-user-avatar">
                    <?= strtoupper(substr($_SESSION['nome'] ?? 'U', 0, 2)) ?>
                </div>
                <div>
                    <div class="app-user-name"><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></div>
                    <div class="app-user-role"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
                </div>
            </div>
            <a href="/logout.php" class="app-logout">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </div>

    </div>
</header>
