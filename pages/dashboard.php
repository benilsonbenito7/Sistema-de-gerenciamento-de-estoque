<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="#">
            Sistema de Estoque
        </a>

        <div class="ms-auto">

            <span class="text-white me-3">
                <?= $_SESSION['nome'] ?>
            </span>

            <a href="../logout.php" class="btn btn-danger btn-sm">
                Sair
            </a>

        </div>

    </div>
</nav>

<div class="container mt-4">

    <h2>Dashboard</h2>

    <p>
        Bem-vindo,
        <strong><?= $_SESSION['nome'] ?></strong>
    </p>

    

</div>

<script src="../public/js/bootstrap.bundle.min.js"></script>

</body>
</html>