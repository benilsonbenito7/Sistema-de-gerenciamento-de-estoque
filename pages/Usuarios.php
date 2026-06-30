<?php
session_start();

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/Usuario.php";

$db = new Database();
$conn = $db->conectar();
$usuario = new Usuario($conn);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $role = $_POST['role'] ?? 'funcionario';

        try {
            $usuario->criar($nome, $email, $senha, $role);
            $message = 'Usuário criado com sucesso.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }

    if ($acao === 'editar') {
        $id = (int)($_POST['edit_id'] ?? 0);
        $nome = trim($_POST['edit_nome'] ?? '');
        $email = trim($_POST['edit_email'] ?? '');
        $role = $_POST['edit_role'] ?? 'funcionario';

        try {
            $usuario->atualizar($id, $nome, $email, $role);
            $message = 'Usuário atualizado.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }

    if ($acao === 'alterar_senha') {
        $id = (int)($_POST['senha_id'] ?? 0);
        $novaSenha = $_POST['nova_senha'] ?? '';

        try {
            $usuario->atualizarSenha($id, $novaSenha);
            $message = 'Senha alterada com sucesso.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }

    if ($acao === 'deletar') {
        $id = (int)($_POST['delete_id'] ?? 0);
        if ($id === (int)$_SESSION['id']) {
            $message = 'Não pode excluir o próprio usuário.';
            $messageType = 'error';
        } else {
            try {
                $usuario->deletar($id);
                $message = 'Usuário removido.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$usuarios = $usuario->listarTodos();
$totalUsuarios = count($usuarios);

$pageTitle = 'Usuários';
$currentPage = 'usuarios';
require_once "layouts/header.php";
?>

<div class="app-body">
    <div class="app-container">

        <div class="page-header">
            <h1>Usuários</h1>
            <p>Gerenciamento de acessos ao sistema</p>
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
                <div class="list-toolbar-right" style="margin-left:auto;">
                    <button type="button" class="btn-primary-app" data-bs-toggle="modal" data-bs-target="#criarModal">
                        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        Novo Usuário
                    </button>
                </div>
            </div>

            <?php if (empty($usuarios)): ?>

            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h3>Nenhum usuário registado</h3>
                <p>Crie o primeiro usuário para conceder acesso ao sistema.</p>
            </div>

            <?php else: ?>

            <div class="list-table-wrap">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Criado em</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u):
                            $isSelf = (int)$u['id'] === (int)$_SESSION['id'];
                        ?>
                        <tr>
                            <td class="cell-name">
                                <?= htmlspecialchars($u['nome']) ?>
                                <?php if ($isSelf): ?>
                                <span style="font-family:var(--font-tag);font-size:0.6rem;font-weight:700;letter-spacing:0.06em;color:var(--c-amber);margin-left:0.4rem;text-transform:uppercase;">VOCÊ</span>
                                <?php endif; ?>
                            </td>
                            <td class="cell-category"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="role-badge role-<?= $u['role'] ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Funcionário' ?></span>
                            </td>
                            <td class="cell-code"><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
                            <td>
                                <div class="action-group">
                                    <button type="button" class="action-btn edit" title="Editar" data-bs-toggle="modal" data-bs-target="#editarModal"
                                        data-id="<?= $u['id'] ?>"
                                        data-nome="<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                        data-role="<?= $u['role'] ?>">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button type="button" class="action-btn edit" title="Alterar Senha" data-bs-toggle="modal" data-bs-target="#senhaModal"
                                        data-id="<?= $u['id'] ?>"
                                        data-nome="<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>">
                                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </button>
                                    <?php if (!$isSelf): ?>
                                    <button type="button" class="action-btn delete" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="<?= $u['id'] ?>"
                                        data-nome="<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="list-summary">
                <span><strong><?= $totalUsuarios ?></strong> usuários</span>
            </div>

            <?php endif; ?>

        </div>

    </div>
</div>

<div class="modal fade" id="criarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--c-border-light);padding:0.5rem;">
            <div class="modal-body" style="padding:1.5rem 1.25rem;">
                <h6 style="font-weight:600;font-size:1.05rem;margin-bottom:0.15rem;">Novo Usuário</h6>
                <p style="font-size:0.82rem;color:var(--c-muted);margin-bottom:1.25rem;">Crie um novo acesso ao sistema.</p>
                <form method="POST">
                    <input type="hidden" name="acao" value="criar">
                    <div class="field-group">
                        <label for="criar_nome">Nome</label>
                        <input id="criar_nome" type="text" name="nome" required placeholder="Nome completo">
                    </div>
                    <div class="field-group">
                        <label for="criar_email">Email</label>
                        <input id="criar_email" type="email" name="email" required placeholder="email@exemplo.com">
                    </div>
                    <div class="field-group">
                        <label for="criar_senha">Senha</label>
                        <input id="criar_senha" type="password" name="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
                    </div>
                    <div class="field-group">
                        <label for="criar_role">Tipo de acesso</label>
                        <select id="criar_role" name="role">
                            <option value="funcionario">Funcionário</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1.25rem;">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="padding:0.45rem 1rem;border:1.5px solid var(--c-border);border-radius:8px;font-size:0.85rem;font-weight:500;">Cancelar</button>
                        <button type="submit" class="btn btn-sm" style="padding:0.45rem 1rem;background:var(--c-deep);color:white;border:none;border-radius:8px;font-size:0.85rem;font-weight:600;">Criar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--c-border-light);padding:0.5rem;">
            <div class="modal-body" style="padding:1.5rem 1.25rem;">
                <h6 style="font-weight:600;font-size:1.05rem;margin-bottom:0.15rem;">Editar Usuário</h6>
                <p style="font-size:0.82rem;color:var(--c-muted);margin-bottom:1.25rem;">Altere os dados e o tipo de acesso.</p>
                <form method="POST">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="edit_id" id="edit_id" value="">
                    <div class="field-group">
                        <label for="edit_nome">Nome</label>
                        <input id="edit_nome" type="text" name="edit_nome" required>
                    </div>
                    <div class="field-group">
                        <label for="edit_email">Email</label>
                        <input id="edit_email" type="email" name="edit_email" required>
                    </div>
                    <div class="field-group">
                        <label for="edit_role">Tipo de acesso</label>
                        <select id="edit_role" name="edit_role">
                            <option value="funcionario">Funcionário</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1.25rem;">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="padding:0.45rem 1rem;border:1.5px solid var(--c-border);border-radius:8px;font-size:0.85rem;font-weight:500;">Cancelar</button>
                        <button type="submit" class="btn btn-sm" style="padding:0.45rem 1rem;background:var(--c-deep);color:white;border:none;border-radius:8px;font-size:0.85rem;font-weight:600;">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="senhaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--c-border-light);padding:0.5rem;">
            <div class="modal-body" style="padding:1.5rem 1.25rem;">
                <h6 style="font-weight:600;font-size:1.05rem;margin-bottom:0.15rem;">Alterar Senha</h6>
                <p style="font-size:0.82rem;color:var(--c-muted);margin-bottom:1.25rem;" id="senhaModalText">Defina uma nova senha.</p>
                <form method="POST">
                    <input type="hidden" name="acao" value="alterar_senha">
                    <input type="hidden" name="senha_id" id="senha_id" value="">
                    <div class="field-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input id="nova_senha" type="password" name="nova_senha" required placeholder="Mínimo 6 caracteres" minlength="6">
                    </div>
                    <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1.25rem;">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="padding:0.45rem 1rem;border:1.5px solid var(--c-border);border-radius:8px;font-size:0.85rem;font-weight:500;">Cancelar</button>
                        <button type="submit" class="btn btn-sm" style="padding:0.45rem 1rem;background:var(--c-deep);color:white;border:none;border-radius:8px;font-size:0.85rem;font-weight:600;">Alterar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--c-border-light);padding:0.5rem;">
            <div class="modal-body" style="text-align:center;padding:1.5rem 1rem;">
                <svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:var(--c-error);fill:none;stroke-width:1.5;margin-bottom:0.75rem;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <h6 style="font-weight:600;margin-bottom:0.25rem;">Excluir usuário</h6>
                <p style="font-size:0.85rem;color:var(--c-muted);margin-bottom:1.25rem;" id="deleteModalText">Tem certeza?</p>
                <form method="POST" style="display:flex;gap:0.5rem;justify-content:center;">
                    <input type="hidden" name="acao" value="deletar">
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
    var editarModal = document.getElementById('editarModal');
    if (editarModal) {
        editarModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn) {
                document.getElementById('edit_id').value = btn.getAttribute('data-id');
                document.getElementById('edit_nome').value = btn.getAttribute('data-nome');
                document.getElementById('edit_email').value = btn.getAttribute('data-email');
                document.getElementById('edit_role').value = btn.getAttribute('data-role');
            }
        });
    }

    var senhaModal = document.getElementById('senhaModal');
    if (senhaModal) {
        senhaModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn) {
                document.getElementById('senha_id').value = btn.getAttribute('data-id');
                document.getElementById('senhaModalText').textContent = 'Nova senha para "' + btn.getAttribute('data-nome') + '".';
                document.getElementById('nova_senha').value = '';
            }
        });
    }

    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (btn) {
                document.getElementById('deleteIdInput').value = btn.getAttribute('data-id');
                document.getElementById('deleteModalText').textContent = 'Remover "' + btn.getAttribute('data-nome') + '"? Esta ação não pode ser desfeita.';
            }
        });
    }
})();
</script>

<?php require_once "layouts/footer.php"; ?>
