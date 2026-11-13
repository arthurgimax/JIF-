<?php
/**
 * JIF+ — Cabeçalho Institucional e Navegação Contextual por Perfil
 */
require_once __DIR__ . '/../config/auth.php';
iniciarSessaoSegura();
$usuarioLogado = obterUsuarioLogado();

// Determina o caminho relativo para assets e páginas dependendo se está em subdiretório (ex.: /admin/)
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'JIF+ — Gestão e Inteligência Esportiva', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220%22 width=%2232%22 height=%2232%22><text y=%2226%22 font-size=%2228%22>🏆</text></svg>">
</head>
<body>

<header class="navbar">
    <div class="nav-container">
        <a href="<?= $basePath ?>index.php" class="brand-logo">
            <span>🏆 JIF+</span>
            <span class="brand-badge">IFRO</span>
        </a>

        <ul class="nav-links">
            <li><a href="<?= $basePath ?>index.php">Início</a></li>
            <li><a href="<?= $basePath ?>competicoes.php">Competições</a></li>
            <li><a href="<?= $basePath ?>modalidades.php">Modalidades</a></li>
            <li><a href="<?= $basePath ?>equipes.php">Equipes</a></li>
            <li><a href="<?= $basePath ?>atletas.php">Atletas (BID)</a></li>
            <li><a href="<?= $basePath ?>partidas.php">Partidas</a></li>
            <li><a href="<?= $basePath ?>classificacao.php">Classificação</a></li>
            <li><a href="<?= $basePath ?>inteligencia.php">Inteligência</a></li>
            
            <?php if ($usuarioLogado && in_array($usuarioLogado['perfil'], ['admin', 'organizador', 'treinador'], true)): ?>
                <li>
                    <a href="<?= $basePath ?>admin/index.php" style="background:rgba(245, 158, 11, 0.2);color:#fef08a;border:1px solid rgba(245, 158, 11, 0.4);">
                        ⚙️ Painel de Gestão
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <?php if ($usuarioLogado): ?>
                <div class="user-badge">
                    <span>👤 <?= htmlspecialchars(explode(' ', $usuarioLogado['nome'])[0], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="role-tag"><?= htmlspecialchars($usuarioLogado['perfil'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <a href="<?= $basePath ?>logout.php" class="btn btn-sm btn-danger" title="Encerrar sessão segura">Sair</a>
            <?php else: ?>
                <a href="<?= $basePath ?>login.php" class="btn btn-sm btn-outline" style="color:#ffffff;border-color:#ffffff;">Entrar</a>
                <a href="<?= $basePath ?>cadastro.php" class="btn btn-sm btn-secondary">Criar Conta</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php require __DIR__ . '/alerts.php'; ?>
<main style="flex: 1;">
