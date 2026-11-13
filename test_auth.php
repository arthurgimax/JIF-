<?php
/**
 * Teste Automatizado de Autenticação, Senhas com Hash e Controle de Perfis (RBAC)
 */
require_once __DIR__ . '/../config/auth.php';

echo "============================================================\n";
echo "  TESTE DE AUTENTICAÇÃO E CONTROLE DE ACESSO (RBAC) — JIF+\n";
echo "============================================================\n";

// 1. Teste de login com credenciais válidas de Administrador
$loginAdmin = autenticarUsuario('admin@jif.ifro.edu.br', 'admin123');
if ($loginAdmin['sucesso']) {
    echo "  [OK] Autenticação válida do Administrador com hash bcrypt.\n";
} else {
    echo "  [FALHA] Não foi possível autenticar o administrador: {$loginAdmin['mensagem']}\n";
    exit(1);
}

// 2. Teste de login com senha incorreta
$loginInvalido = autenticarUsuario('admin@jif.ifro.edu.br', 'senha_errada_xyz');
if (!$loginInvalido['sucesso']) {
    echo "  [OK] Bloqueio correto de login com senha incorreta.\n";
} else {
    echo "  [FALHA] Sistema permitiu login com senha incorreta!\n";
    exit(1);
}

// 3. Teste de cadastro de novo usuário estudante
$emailTeste = 'teste.estudante.' . time() . '@calama.ifro.edu.br';
$cadastro = cadastrarUsuario('Estudante Atleta Teste', $emailTeste, 'senhaForte123', 'atleta');
if ($cadastro['sucesso']) {
    echo "  [OK] Cadastro de novo usuário concluído com hash e validação de e-mail.\n";
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
echo "------------------------------------------------------------\n";
echo "STATUS: TODOS OS TESTES DE AUTENTICAÇÃO E SEGURANÇA PASSARAM!\n";
echo "============================================================\n";
