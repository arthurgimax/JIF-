<?php
/**
 * JIF+ — Tela de Autenticação Segura (Login)
 * Requisitos: RF02, RNF01, RNF02, RNF05
 */
$pageTitle = 'Login — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/auth.php';

iniciarSessaoSegura();

// Se já estiver logado, redireciona para a página principal
if (usuarioEstaLogado()) {
    header('Location: index.php');
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha o e-mail e a senha.';
    } else {
        $resultado = autenticarUsuario($email, $senha);
        if ($resultado['sucesso']) {
            $_SESSION['mensagem_sucesso'] = "Bem-vindo de volta, " . htmlspecialchars($resultado['usuario']['nome']) . "!";
            
            // Redirecionamento baseado no perfil de acesso
            if (in_array($resultado['usuario']['perfil'], ['admin', 'organizador', 'treinador'], true)) {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
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
            <div style="font-size:3rem;margin-bottom:10px;">🔐</div>
            <h2 style="font-weight:800;color:var(--dark);">Acesso à Plataforma</h2>
            <p style="color:var(--text-muted);font-size:0.9rem;">Insira suas credenciais institucionais para continuar</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label" for="email">E-mail Cadastrado</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="ex.: nome@ifro.edu.br" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="senha">Senha de Acesso</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                Entrar no Sistema
            </button>
        </form>

        <div style="margin-top:25px;padding-top:20px;border-top:1px solid var(--border);text-align:center;font-size:0.9rem;color:var(--text-muted);">
            Não possui uma conta? <a href="cadastro.php" style="font-weight:700;color:var(--primary);">Cadastre-se gratuitamente</a>
        </div>

        <div style="margin-top:20px;background:#f8fafc;padding:15px;border-radius:var(--radius-sm);border:1px solid #e2e8f0;font-size:0.8rem;color:#64748b;">
            <strong>Contas de Demonstração (Seed Oficial):</strong><br>
            • <em>Admin:</em> admin@jif.ifro.edu.br / <code>admin123</code><br>
            • <em>Coordenador:</em> coordenador@jif.ifro.edu.br / <code>organizador123</code><br>
            • <em>Treinador:</em> treinador.calama@ifro.edu.br / <code>treinador123</code><br>
            • <em>Atleta:</em> arthur.gimax@calama.ifro.edu.br / <code>atleta123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
