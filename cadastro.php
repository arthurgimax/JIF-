<?php
/**
 * JIF+ — Cadastro Público de Novos Usuários
 * Requisitos: RF01, RNF01, RNF02
 */
$pageTitle = 'Criar Conta — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/auth.php';

iniciarSessaoSegura();

if (usuarioEstaLogado()) {
    header('Location: index.php');
    exit;
}

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $perfil = 'atleta';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Todos os campos são de preenchimento obrigatório.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas informadas não coincidem.';
    } else {
        $resultado = cadastrarUsuario($nome, $email, $senha, $perfil);
        if ($resultado['sucesso']) {
            $_SESSION['mensagem_sucesso'] = 'Cadastro realizado com sucesso! Você já pode fazer login.';
            header('Location: login.php');
            exit;
        } else {
            $erro = $resultado['mensagem'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div class="form-card">
        <div style="text-align:center;margin-bottom:25px;">
            <div style="font-size:3rem;margin-bottom:10px;">📋</div>
            <h2 style="font-weight:800;color:var(--dark);">Criar Nova Conta</h2>
            <p style="color:var(--text-muted);font-size:0.9rem;">Cadastre-se para acompanhar e participar dos jogos</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            <div class="form-group">
                <label class="form-label" for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: Arthur Henrique Gimax" value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">E-mail Institucional ou Pessoal</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="ex.: arthur.gimax@calama.ifro.edu.br" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="senha">Senha de Acesso (mínimo 6 caracteres)</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="••••••••" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                Finalizar Cadastro
            </button>
        </form>

        <div style="margin-top:25px;padding-top:20px;border-top:1px solid var(--border);text-align:center;font-size:0.9rem;color:var(--text-muted);">
            Já possui cadastro? <a href="login.php" style="font-weight:700;color:var(--primary);">Faça login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>