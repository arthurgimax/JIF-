<?php
/**
 * JIF+ — Painel Administrativo Geral (Dashboard de Gestão)
 * Requisitos: RF03, RNF05
 */
define('BASE_PATH', '../');
$pageTitle = 'Painel de Gestão — JIF+';
require_once __DIR__ . '/../config/auth.php';

// Exige perfil de admin, organizador ou treinador
exigirAutenticacao(['admin', 'organizador', 'treinador']);

$usuario = obterUsuarioLogado();
$pdo = Database::getConnection();

// Contadores do sistema
$totalInstituicoes = $pdo->query("SELECT COUNT(*) AS t FROM instituicoes")->fetch()['t'] ?? 0;
$totalCompeticoes = $pdo->query("SELECT COUNT(*) AS t FROM competicoes")->fetch()['t'] ?? 0;
$totalModalidades = $pdo->query("SELECT COUNT(*) AS t FROM modalidades")->fetch()['t'] ?? 0;
$totalEquipes = $pdo->query("SELECT COUNT(*) AS t FROM equipes")->fetch()['t'] ?? 0;
$totalAtletas = $pdo->query("SELECT COUNT(*) AS t FROM atletas")->fetch()['t'] ?? 0;
$totalPartidas = $pdo->query("SELECT COUNT(*) AS t FROM partidas")->fetch()['t'] ?? 0;
$partidasAgendadas = $pdo->query("SELECT COUNT(*) AS t FROM partidas WHERE situacao = 'agendada'")->fetch()['t'] ?? 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <span class="badge badge-warning" style="text-transform:uppercase;">Painel de Controle Restrito</span>
            <h1 style="font-size:2.2rem;font-weight:900;color:var(--dark);margin-top:6px;">
                Gestão Esportiva — JIF+
            </h1>
            <p style="color:var(--text-muted);">
                Sessão ativa: <strong><?= htmlspecialchars($usuario['nome']) ?></strong> (Perfil: <code><?= htmlspecialchars($usuario['perfil']) ?></code>)
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="../index.php" class="btn btn-outline">&larr; Ver Site Público</a>
            <a href="sumula.php" class="btn btn-secondary">⚡ Lançar Súmula de Jogo</a>
        </div>
    </div>

    <!-- CARDS DE CONTADORES E ATALHOS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:35px;">
        
        <div class="card" style="border-left:4px solid var(--primary);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Instituições & Campi</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalInstituicoes ?></div>
                </div>
                <div style="font-size:2rem;">🏛️</div>
            </div>
            <div style="margin-top:12px;">
                <a href="instituicoes.php" style="font-size:0.85rem;font-weight:700;">Gerenciar campi &rarr;</a>
            </div>
        </div>

        <div class="card" style="border-left:4px solid var(--secondary);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Competições</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalCompeticoes ?></div>
                </div>
                <div style="font-size:2rem;">🏆</div>
            </div>
            <div style="margin-top:12px;">
                <a href="competicoes.php" style="font-size:0.85rem;font-weight:700;">Gerenciar torneios &rarr;</a>
            </div>
        </div>

        <div class="card" style="border-left:4px solid var(--info);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Modalidades</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalModalidades ?></div>
                </div>
                <div style="font-size:2rem;">🏅</div>
            </div>
            <div style="margin-top:12px;">
                <a href="modalidades.php" style="font-size:0.85rem;font-weight:700;">Configurar modalidades &rarr;</a>
            </div>
        </div>

        <div class="card" style="border-left:4px solid #8b5cf6;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Equipes Inscritas</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalEquipes ?></div>
                </div>
                <div style="font-size:2rem;">🛡️</div>
            </div>
            <div style="margin-top:12px;">
                <a href="equipes.php" style="font-size:0.85rem;font-weight:700;">Gerenciar times &rarr;</a>
            </div>
        </div>

        <div class="card" style="border-left:4px solid #ec4899;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Estudantes-Atletas</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalAtletas ?></div>
                </div>
                <div style="font-size:2rem;">🏃‍♂️</div>
            </div>
            <div style="margin-top:12px;">
                <a href="atletas.php" style="font-size:0.85rem;font-weight:700;">Inscrever atletas &rarr;</a>
            </div>
        </div>

        <div class="card" style="border-left:4px solid var(--danger);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:0.8rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);">Partidas / Jogos</div>
                    <div style="font-size:1.8rem;font-weight:900;color:var(--dark);"><?= $totalPartidas ?></div>
                </div>
                <div style="font-size:2rem;">⏱️</div>
            </div>
            <div style="margin-top:12px;">
                <a href="partidas.php" style="font-size:0.85rem;font-weight:700;">Agendar partidas &rarr;</a>
            </div>
        </div>

    </div>

    <!-- MÓDULOS DE AÇÃO RÁPIDA -->
    <h2 style="font-size:1.4rem;font-weight:800;color:var(--dark);margin-bottom:18px;">
        🚀 Operações do Sistema
    </h2>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;">
        
        <div class="card">
            <h3 class="card-title">📝 Súmula Eletrônica & Resultados</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;">
                Lançar placares de jogos finalizados, registrar gols, cartões amarelos/vermelhos e faltas por atleta, e homologar resultados.
            </p>
            <a href="sumula.php" class="btn btn-primary btn-sm" style="width:100%;">Acessar Súmula &rarr;</a>
        </div>

        <div class="card">
            <h3 class="card-title">📅 Agendamento de Jogos</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;">
                Programar partidas oficiais entre equipes homologadas, definindo data, horário, quadra/local e fase do campeonato.
            </p>
            <a href="partidas.php" class="btn btn-outline btn-sm" style="width:100%;">Programar Jogos &rarr;</a>
        </div>

        <div class="card">
            <h3 class="card-title">🏃‍♂️ Inscrição de Atletas em Equipes</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;">
                Cadastrar novos estudantes-atletas do seu campus e associá-los às equipes com número da camisa e posição.
            </p>
            <a href="atletas.php" class="btn btn-outline btn-sm" style="width:100%;">Gerenciar Atletas &rarr;</a>
        </div>

        <?php if ($usuario['perfil'] === 'admin'): ?>
            <div class="card" style="background:#f8fafc;border:2px dashed #cbd5e1;">
                <h3 class="card-title">👥 Controle de Usuários (RBAC)</h3>
                <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;">
                    Exclusivo para Administrador: gerenciar contas e atribuir perfis de acesso (Organizador, Treinador, Atleta).
                </p>
                <a href="usuarios.php" class="btn btn-secondary btn-sm" style="width:100%;">Gerenciar Usuários &rarr;</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
