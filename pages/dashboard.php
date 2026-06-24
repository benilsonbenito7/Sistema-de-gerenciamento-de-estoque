<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

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
                <div class="card-app">
                    <div style="font-family:var(--font-tag);font-size:0.65rem;letter-spacing:0.08em;color:var(--c-amber);margin-bottom:0.5rem;">PRODUTOS</div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--c-deep);line-height:1;">0</div>
                    <div style="font-size:0.75rem;color:var(--c-muted);margin-top:0.3rem;">em stock</div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-app">
                    <div style="font-family:var(--font-tag);font-size:0.65rem;letter-spacing:0.08em;color:var(--c-amber);margin-bottom:0.5rem;">BAIXO STOCK</div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--c-deep);line-height:1;">0</div>
                    <div style="font-size:0.75rem;color:var(--c-muted);margin-top:0.3rem;">unidades mínimas</div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-app">
                    <div style="font-family:var(--font-tag);font-size:0.65rem;letter-spacing:0.08em;color:var(--c-amber);margin-bottom:0.5rem;">CATEGORIAS</div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--c-deep);line-height:1;">0</div>
                    <div style="font-size:0.75rem;color:var(--c-muted);margin-top:0.3rem;">registadas</div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-app">
                    <div style="font-family:var(--font-tag);font-size:0.65rem;letter-spacing:0.08em;color:var(--c-amber);margin-bottom:0.5rem;">VALOR TOTAL</div>
                    <div style="font-size:1.8rem;font-weight:700;color:var(--c-deep);line-height:1;">0</div>
                    <div style="font-size:0.75rem;color:var(--c-muted);margin-top:0.3rem;">em inventário</div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once "layouts/footer.php"; ?>
