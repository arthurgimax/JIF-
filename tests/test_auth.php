<?php
/**
 * Teste Automatizado de Autenticação, Senhas com Hash e Controle de Perfis (RBAC)
 */
require_once __DIR__ . '/../config/auth.php';

echo "============================================================\n";
echo "  TESTE DE AUTENTICAÇÃO E CONTROLE DE ACESSO (RBAC) — JIF+\n";
echo "============================================================\n";

$pdo = Database::getConnection();
$pdo->beginTransaction();
$adminEmailTeste = 'admin.auth.' . bin2hex(random_bytes(6)) . '@example.test';
$adminSenhaTeste = bin2hex(random_bytes(16));
$stmtAdminTeste = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES ('Admin Teste', :email, :hash, 'admin', 1)");
$stmtAdminTeste->execute([
    ':email' => $adminEmailTeste,
    ':hash' => password_hash($adminSenhaTeste, PASSWORD_BCRYPT),
]);

// 1. Teste de login com credenciais válidas de Administrador temporário
$loginAdmin = autenticarUsuario($adminEmailTeste, $adminSenhaTeste);
if ($loginAdmin['sucesso']) {
    echo "  [OK] Autenticação válida do Administrador com hash bcrypt.\n";
} else {
    echo "  [FALHA] Não foi possível autenticar o administrador: {$loginAdmin['mensagem']}\n";
    exit(1);
}

// 2. Teste de login com senha incorreta
$loginInvalido = autenticarUsuario($adminEmailTeste, 'senha_errada_xyz');
if (!$loginInvalido['sucesso']) {
    echo "  [OK] Bloqueio correto de login com senha incorreta.\n";
} else {
    echo "  [FALHA] Sistema permitiu login com senha incorreta!\n";
    exit(1);
}

// 3. Teste de cadastro de novo usuário estudante
$emailTeste = 'teste.estudante.' . time() . '@calama.ifro.edu.br';
$cadastro = cadastrarUsuario('Estudante Atleta Teste', $emailTeste, 'senhaForte123', 'admin');
$stmtPerfilTeste = $pdo->prepare('SELECT perfil FROM usuarios WHERE email = :email');
$stmtPerfilTeste->execute([':email' => $emailTeste]);
$perfilTeste = $stmtPerfilTeste->fetchColumn();
if ($cadastro['sucesso'] && $perfilTeste === 'atleta') {
    echo "  [OK] Cadastro com hash, validação de e-mail e bloqueio de elevação de privilégio.\n";
} else {
    echo "  [FALHA] Falha no cadastro: {$cadastro['mensagem']}\n";
    exit(1);
}

// 4. Teste de tentativa de duplicidade de e-mail
$cadastroDuplicado = cadastrarUsuario('Outro Nome', $emailTeste, 'outraSenha123', 'atleta');
if (!$cadastroDuplicado['sucesso']) {
    echo "  [OK] Bloqueio correto de duplicidade de e-mail.\n";
} else {
    echo "  [FALHA] Sistema permitiu e-mail duplicado!\n";
    exit(1);
}

// 5. Teste de login com o novo usuário recém-cadastrado
$loginNovo = autenticarUsuario($emailTeste, 'senhaForte123');
if ($loginNovo['sucesso']) {
    echo "  [OK] Login bem-sucedido com a conta recém-criada.\n";
} else {
    echo "  [FALHA] Falha no login do novo usuário: {$loginNovo['mensagem']}\n";
    exit(1);
}

encerrarSessao();
if ($pdo->inTransaction()) { $pdo->rollBack(); }
echo "------------------------------------------------------------\n";
echo "STATUS: TODOS OS TESTES DE AUTENTICAÇÃO E SEGURANÇA PASSARAM!\n";
echo "============================================================\n";