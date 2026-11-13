<?php
/**
 * JIF+ — Módulo de Autenticação, Sessão Segura e Controle de Perfis (RBAC)
 * Conformidade com RF01, RF02, RF03, RNF01, RNF05 e Anexo B da Apostila.
 */

require_once __DIR__ . '/database.php';

/**
 * Inicializa a sessão com parâmetros seguros.
 */
function iniciarSessaoSegura(): void {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            // Diretivas de segurança de cookie (Anexo B)
            $cookieParams = [
                'lifetime' => 0, // Expira ao fechar navegador
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true, // Impede acesso via JavaScript (anti-XSS)
                'samesite' => 'Lax'
            ];
            session_set_cookie_params($cookieParams);
            session_start();
        } else {
            @session_start();
        }
    }
}

/**
 * Autentica o usuário com e-mail e senha.
 */
function autenticarUsuario(string $email, string $senha): array {
    iniciarSessaoSegura();
    $pdo = Database::getConnection();

    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    // Prepared Statement obrigatório contra SQL Injection (RNF03)
    $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, perfil, ativo FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        return ['sucesso' => false, 'mensagem' => 'Credenciais inválidas.'];
    }

    if ((int)$usuario['ativo'] !== 1) {
        return ['sucesso' => false, 'mensagem' => 'Esta conta está inativa. Contate o administrador.'];
    }

    // Verificação segura do hash bcrypt (RNF01)
    if (!password_verify($senha, $usuario['senha_hash'])) {
        return ['sucesso' => false, 'mensagem' => 'Credenciais inválidas.'];
    }

    // Regenera o ID de sessão para prevenir Session Fixation (se sessão ativa)
    if (session_id() && !headers_sent()) {
        @session_regenerate_id(true);
    }

    $_SESSION['usuario_id'] = (int)$usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_perfil'] = $usuario['perfil'];

    return ['sucesso' => true, 'usuario' => $usuario];
}

/**
 * Realiza o cadastro de um novo usuário com senha protegida por hash bcrypt.
 */
function cadastrarUsuario(string $nome, string $email, string $senha, string $perfil = 'atleta'): array {
    $pdo = Database::getConnection();

    $nome = trim(strip_tags($nome));
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    if (empty($nome) || mb_strlen($nome) < 3) {
        return ['sucesso' => false, 'mensagem' => 'Informe o nome completo (mínimo de 3 caracteres).'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['sucesso' => false, 'mensagem' => 'Informe um endereço de e-mail válido.'];
    }

    if (mb_strlen($senha) < 6) {
        return ['sucesso' => false, 'mensagem' => 'A senha deve conter no mínimo 6 caracteres.'];
    }

    // Restringe os perfis permitidos em cadastro público (visitantes só podem criar conta como atleta/visitante)
    $perfisPermitidos = ['atleta', 'treinador'];
    if (!in_array($perfil, $perfisPermitidos, true)) {
        $perfil = 'atleta';
    }

    // Verifica unicidade do e-mail via prepared statement
    $stmtVerifica = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmtVerifica->execute([':email' => $email]);
    if ($stmtVerifica->fetch()) {
        return ['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado no sistema.'];
    }

    // Geração do hash robusto bcrypt (RNF01)
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);

    $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, :perfil, 1)");
    $stmtInsert->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha_hash' => $senhaHash,
        ':perfil' => $perfil
    ]);

    $novoId = (int)$pdo->lastInsertId();

    return ['sucesso' => true, 'id' => $novoId, 'mensagem' => 'Cadastro realizado com sucesso!'];
}

/**
 * Verifica se o usuário atual está logado.
 */
function usuarioEstaLogado(): bool {
    iniciarSessaoSegura();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Retorna os dados do usuário autenticado na sessão.
 */
function obterUsuarioLogado(): ?array {
    if (!usuarioEstaLogado()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'perfil' => $_SESSION['usuario_perfil']
    ];
}

/**
 * Valida se o usuário logado possui um dos perfis exigidos (RBAC).
 */
function verificarPerfil(array $perfisAutorizados): bool {
    $usuario = obterUsuarioLogado();
    if (!$usuario) return false;
    // Administrador tem acesso geral
    if ($usuario['perfil'] === 'admin') return true;
    return in_array($usuario['perfil'], $perfisAutorizados, true);
}

/**
 * Intercepta e protege rotas que exigem autenticação ou perfis específicos.
 */
function exigirAutenticacao(array $perfisAutorizados = []): void {
    iniciarSessaoSegura();

    if (!usuarioEstaLogado()) {
        $_SESSION['mensagem_aviso'] = 'Você precisa fazer login para acessar esta página.';
        header('Location: ../login.php');
        exit;
    }

    if (!empty($perfisAutorizados) && !verificarPerfil($perfisAutorizados)) {
        http_response_code(403);
        die("<div style='font-family:sans-serif;max-width:550px;margin:80px auto;padding:30px;background:#fff5f5;border:1px solid #fed7d7;border-radius:8px;text-align:center;'>"
            . "<h2 style='color:#c53030;margin-top:0;'>403 — Acesso Restrito</h2>"
            . "<p style='color:#4a5568;'>Seu perfil de usuário (" . htmlspecialchars($_SESSION['usuario_perfil']) . ") não tem permissão para acessar esta área.</p>"
            . "<a href='../index.php' style='display:inline-block;margin-top:15px;padding:10px 20px;background:#006633;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Voltar para a Página Inicial</a>"
            . "</div>");
    }
}

/**
 * Encerra a sessão do usuário de maneira segura.
 */
function encerrarSessao(): void {
    iniciarSessaoSegura();
    $_SESSION = [];
    if (!headers_sent() && ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    if (session_id()) {
        @session_destroy();
    }
}
