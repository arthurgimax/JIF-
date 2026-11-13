<?php
/**
 * JIF+ — Gerenciamento de Usuários e Controle de Perfis (RBAC)
 * Requisitos: RF03, RNF05
 */
define('BASE_PATH', '../');
$pageTitle = 'Controle de Usuários — JIF+';
require_once __DIR__ . '/../config/auth.php';

// Exclusivo para Administrador Geral
exigirAutenticacao(['admin']);

$usuarioLogado = obterUsuarioLogado();
$pdo = Database::getConnection();

// 1. ALTERAÇÃO DE PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_perfil') {
    $uId = (int)$_POST['usuario_id'];
    $novoPerfil = $_POST['perfil'] ?? 'atleta';

    $perfisValidos = ['admin', 'organizador', 'treinador', 'atleta'];
    if (in_array($novoPerfil, $perfisValidos, true)) {
        $stmtUp = $pdo->prepare("UPDATE usuarios SET perfil = :perfil WHERE id = :id");
        $stmtUp->execute([':perfil' => $novoPerfil, ':id' => $uId]);
        $_SESSION['mensagem_sucesso'] = "Perfil de acesso do usuário atualizado para '{$novoPerfil}'!";
    }
    header('Location: usuarios.php');
    exit;
}

// 2. ALTERAR STATUS (ATIVAR / DESATIVAR)
if (isset($_GET['toggle_status'])) {
    $uId = (int)$_GET['toggle_status'];
    
    // Impede que o administrador desative a si mesmo
    if ($uId === $usuarioLogado['id']) {
        $_SESSION['mensagem_erro'] = "Você não pode desativar sua própria conta de administrador.";
    } else {
        $stmtStatus = $pdo->prepare("UPDATE usuarios SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END WHERE id = :id");
        $stmtStatus->execute([':id' => $uId]);
        $_SESSION['mensagem_sucesso'] = "Status de ativação do usuário modificado!";
    }
    header('Location: usuarios.php');
    exit;
}

$usuarios = $pdo->query("SELECT id, nome, email, perfil, ativo, criado_em FROM usuarios ORDER BY criado_em DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">👥 Controle de Usuários e Perfis (RBAC)</h1>
            <p style="color:var(--text-muted);">Gerenciamento de credenciais, permissões de organizadores e técnicos</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="sports-table">
            <thead>
                <tr>
                    <th>Nome do Usuário</th>
                    <th>E-mail</th>
                    <th class="center">Perfil Atual</th>
                    <th class="center">Alterar Perfil</th>
                    <th class="center">Status</th>
                    <th class="center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($u['nome']) ?></strong>
                            <?php if ($u['id'] === $usuarioLogado['id']): ?>
                                <span class="badge badge-warning" style="font-size:0.65rem;">Você</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td class="center">
                            <span class="badge badge-info" style="text-transform:uppercase;">
                                <?= htmlspecialchars($u['perfil']) ?>
                            </span>
                        </td>
                        <td class="center">
                            <form method="POST" action="usuarios.php" style="display:inline-flex;gap:6px;align-items:center;">
                                <input type="hidden" name="acao" value="alterar_perfil">
                                <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                <select name="perfil" class="form-control" style="padding:4px 8px;font-size:0.8rem;width:130px;">
                                    <option value="admin" <?= $u['perfil'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="organizador" <?= $u['perfil'] === 'organizador' ? 'selected' : '' ?>>Organizador</option>
                                    <option value="treinador" <?= $u['perfil'] === 'treinador' ? 'selected' : '' ?>>Treinador</option>
                                    <option value="atleta" <?= $u['perfil'] === 'atleta' ? 'selected' : '' ?>>Atleta</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline" style="padding:4px 8px;" title="Salvar Perfil">💾</button>
                            </form>
                        </td>
                        <td class="center">
                            <span class="badge <?= (int)$u['ativo'] === 1 ? 'badge-success' : 'badge-danger' ?>">
                                <?= (int)$u['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td class="center">
                            <?php if ($u['id'] !== $usuarioLogado['id']): ?>
                                <a href="usuarios.php?toggle_status=<?= $u['id'] ?>" class="btn btn-sm <?= (int)$u['ativo'] === 1 ? 'btn-danger' : 'btn-primary' ?>" data-confirm="Deseja alterar a ativação deste usuário?">
                                    <?= (int)$u['ativo'] === 1 ? 'Desativar' : 'Ativar' ?>
                                </a>
                            <?php else: ?>
                                <span style="font-size:0.8rem;color:var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>