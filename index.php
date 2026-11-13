<?php
/**
 * JIF+ — Página Inicial Pública do Sistema Esportivo
 * Requisitos: RF05, RF09, RF10, RF11, RF12, RF14, RF15, RF17
 */
$pageTitle = 'JIF+ — Plataforma Oficial de Gestão e Inteligência Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// 1. Estatísticas gerais do torneio
$totalAtletas = $pdo->query("SELECT COUNT(*) AS total FROM atletas")->fetch()['total'] ?? 0;
$totalPartidas = $pdo->query("SELECT COUNT(*) AS total FROM partidas")->fetch()['total'] ?? 0;
$totalGols = $pdo->query("SELECT COALESCE(SUM(valor), 0) AS total FROM estatisticas_atletas WHERE tipo_estatistica IN ('gol', 'ponto')")->fetch()['total'] ?? 0;
$totalCampi = $pdo->query("SELECT COUNT(*) AS total FROM instituicoes")->fetch()['total'] ?? 0;

// 2. Partidas Recentes e Finalizadas
$stmtRecentes = $pdo->query("
    SELECT p.*, 
           m.nome AS modalidade_nome, m.categoria AS modalidade_categoria,
           eq_a.nome AS equipe_a_nome, inst_a.campus AS campus_a,
           eq_b.nome AS equipe_b_nome, inst_b.campus AS campus_b
    FROM partidas p
    JOIN modalidades m ON p.modalidade_id = m.id
    JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
    JOIN instituicoes inst_a ON eq_a.instituicao_id = inst_a.id
    JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
    JOIN instituicoes inst_b ON eq_b.instituicao_id = inst_b.id
    WHERE p.situacao = 'encerrada'
    ORDER BY p.data_hora DESC
    LIMIT 4
");
$partidasRecentes = $stmtRecentes->fetchAll();

// 3. Próximas Partidas Agendadas
$stmtProximas = $pdo->query("
    SELECT p.*, 
           m.nome AS modalidade_nome, m.categoria AS modalidade_categoria,
           eq_a.nome AS equipe_a_nome, inst_a.campus AS campus_a,
           eq_b.nome AS equipe_b_nome, inst_b.campus AS campus_b
    FROM partidas p
    JOIN modalidades m ON p.modalidade_id = m.id
    JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
    JOIN instituicoes inst_a ON eq_a.instituicao_id = inst_a.id
    JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
    JOIN instituicoes inst_b ON eq_b.instituicao_id = inst_b.id
    WHERE p.situacao = 'agendada'
    ORDER BY p.data_hora ASC
    LIMIT 4
");
$proximasPartidas = $stmtProximas->fetchAll();

// 4. Top Artilheiros / Destaques Individuais (Inteligência Esportiva)
$stmtArtilheiros = $pdo->query("
    SELECT a.id, a.nome, inst.campus,
           SUM(ea.valor) AS total_gols,
           eq.nome AS equipe_nome
    FROM estatisticas_atletas ea
    JOIN atletas a ON ea.atleta_id = a.id
    JOIN instituicoes inst ON a.instituicao_id = inst.id
    JOIN equipes eq ON ea.equipe_id = eq.id
    WHERE ea.tipo_estatistica = 'gol'
    GROUP BY a.id, a.nome, inst.campus, eq.nome
    ORDER BY total_gols DESC
    LIMIT 5
");
$artilheiros = $stmtArtilheiros->fetchAll();
?>

<!-- HERO BANNER ESPORTIVO -->
<section class="hero-banner">
    <div class="container">
        <h1 class="hero-title">Jogos dos Institutos Federais <span>JIF+</span></h1>
        <p class="hero-subtitle">
            Central de inteligência esportiva, súmulas oficiais, classificação em tempo real e boletim de desempenho dos atletas do IFRO.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="partidas.php" class="btn btn-secondary">📅 Ver Tabela de Jogos</a>
            <a href="classificacao.php" class="btn btn-outline" style="color:#ffffff;border-color:#ffffff;">📊 Classificação Geral</a>
            <a href="atletas.php" class="btn btn-primary">🏃‍♂️ Boletim de Atletas (BID)</a>
        </div>
    </div>
</section>

<!-- PAINEL DE NÚMEROS DO TORNEIO -->
<section class="container" style="margin-top:-30px;position:relative;z-index:2;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:15px;">
        <div class="stat-box" style="background:#ffffff;box-shadow:var(--shadow-md);border-left:4px solid var(--primary);">
            <div class="stat-value"><?= $totalAtletas ?></div>
            <div class="stat-label">Atletas Inscritos</div>
        </div>
        <div class="stat-box" style="background:#ffffff;box-shadow:var(--shadow-md);border-left:4px solid var(--secondary);">
            <div class="stat-value"><?= $totalPartidas ?></div>
            <div class="stat-label">Partidas Programadas</div>
        </div>
        <div class="stat-box" style="background:#ffffff;box-shadow:var(--shadow-md);border-left:4px solid var(--primary-light);">
            <div class="stat-value"><?= $totalGols ?></div>
            <div class="stat-label">Gols / Pontos Marcados</div>
        </div>
        <div class="stat-box" style="background:#ffffff;box-shadow:var(--shadow-md);border-left:4px solid var(--dark);">
            <div class="stat-value"><?= $totalCampi ?></div>
            <div class="stat-label">Campi Representados</div>
        </div>
    </div>
</section>

<!-- CONTEÚDO PRINCIPAL: JOGOS RECENTES & ARTILHARIA -->
<div class="container" style="padding: 40px 20px;">
    <div style="display:grid;grid-template-columns: 2fr 1fr;gap:30px;">
        
        <!-- COLUNA DA ESQUERDA: PARTIDAS -->
        <div>
            <!-- ÚLTIMOS RESULTADOS -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                <h2 style="font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                    ⚡ Resultados Recentes
                </h2>
                <a href="partidas.php" style="font-weight:700;font-size:0.9rem;">Ver todos os jogos &rarr;</a>
            </div>

            <?php if (empty($partidasRecentes)): ?>
                <div class="card" style="text-align:center;color:var(--text-muted);padding:40px;">
                    Nenhuma partida encerrada registrada até o momento.
                </div>
            <?php else: ?>
                <?php foreach ($partidasRecentes as $p): ?>
                    <div class="match-card">
                        <div class="match-header">
                            <span><?= htmlspecialchars($p['modalidade_nome']) ?> (<?= htmlspecialchars($p['modalidade_categoria']) ?>) • <?= htmlspecialchars($p['fase_rodada']) ?></span>
                            <span class="badge badge-success">Encerrada</span>
                        </div>
                        <div class="match-teams">
                            <div class="team-box">
                                <div class="team-badge"><?= mb_substr($p['campus_a'], 0, 1) ?></div>
                                <div>
                                    <div><?= htmlspecialchars($p['equipe_a_nome']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_a']) ?></div>
                                </div>
                            </div>
                            <div class="match-score">
                                <?= $p['placar_a'] ?> x <?= $p['placar_b'] ?>
                            </div>
                            <div class="team-box away">
                                <div class="team-badge"><?= mb_substr($p['campus_b'], 0, 1) ?></div>
                                <div>
                                    <div><?= htmlspecialchars($p['equipe_b_nome']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_b']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="match-footer">
                            <span>📍 <?= htmlspecialchars($p['local_quadra']) ?></span>
                            <span>🕒 <?= date('d/m/Y H:i', strtotime($p['data_hora'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- PRÓXIMOS CONFRONTOS -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin:30px 0 15px 0;">
                <h2 style="font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                    📅 Próximos Confrontos Agendados
                </h2>
            </div>

            <?php if (empty($proximasPartidas)): ?>
                <div class="card" style="text-align:center;color:var(--text-muted);padding:40px;">
                    Não há partidas agendadas no momento.
                </div>
            <?php else: ?>
                <?php foreach ($proximasPartidas as $p): ?>
                    <div class="match-card" style="border-left:4px solid var(--secondary);">
                        <div class="match-header">
                            <span><?= htmlspecialchars($p['modalidade_nome']) ?> • <?= htmlspecialchars($p['fase_rodada']) ?></span>
                            <span class="badge badge-warning">Agendada</span>
                        </div>
                        <div class="match-teams">
                            <div class="team-box">
                                <div class="team-badge"><?= mb_substr($p['campus_a'], 0, 1) ?></div>
                                <div>
                                    <div><?= htmlspecialchars($p['equipe_a_nome']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_a']) ?></div>
                                </div>
                            </div>
                            <div class="match-score scheduled">VS</div>
                            <div class="team-box away">
                                <div class="team-badge"><?= mb_substr($p['campus_b'], 0, 1) ?></div>
                                <div>
                                    <div><?= htmlspecialchars($p['equipe_b_nome']) ?></div>
                                    <div style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_b']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="match-footer">
                            <span>📍 <?= htmlspecialchars($p['local_quadra']) ?></span>
                            <span>🕒 <?= date('d/m/Y H:i', strtotime($p['data_hora'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <!-- COLUNA DA DIREITA: DESTAQUES ESPORTIVOS & ARTILHARIA -->
        <div>
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header-sport">
                    <h3 style="font-size:1.15rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                        🎯 Artilharia do JIF+
                    </h3>
                </div>
                
                <?php if (empty($artilheiros)): ?>
                    <p style="color:var(--text-muted);font-size:0.9rem;">Nenhum gol registrado ainda.</p>
                <?php else: ?>
                    <ul style="list-style:none;">
                        <?php foreach ($artilheiros as $pos => $art): ?>
                            <li style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="font-weight:800;color:<?= $pos === 0 ? 'var(--secondary)' : 'var(--text-muted)' ?>;font-size:1.1rem;width:20px;">
                                        #<?= $pos + 1 ?>
                                    </span>
                                    <div>
                                        <a href="atletas.php?id=<?= $art['id'] ?>" style="font-weight:700;color:var(--dark);display:block;">
                                            <?= htmlspecialchars($art['nome']) ?>
                                        </a>
                                        <span style="font-size:0.75rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($art['campus']) ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="badge badge-success" style="font-size:0.85rem;padding:4px 8px;">
                                    <?= $art['total_gols'] ?> gols
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <div style="margin-top:15px;text-align:center;">
                    <a href="inteligencia.php" class="btn btn-sm btn-outline" style="width:100%;">
                        Ver Inteligência Completa &rarr;
                    </a>
                </div>
            </div>

            <!-- MODALIDADES EM DISPUTA -->
            <div class="card">
                <div class="card-header-sport">
                    <h3 style="font-size:1.15rem;font-weight:800;color:var(--dark);">
                        🏅 Modalidades Oficiais
                    </h3>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <span class="badge badge-neutral">⚽ Futsal</span>
                    <span class="badge badge-neutral">🏐 Voleibol</span>
                    <span class="badge badge-neutral">🏀 Basquetebol</span>
                    <span class="badge badge-neutral">🤾 Handebol</span>
                    <span class="badge badge-neutral">♟️ Xadrez</span>
                    <span class="badge badge-neutral">🏃 Atletismo</span>
                </div>
                <div style="margin-top:20px;">
                    <a href="modalidades.php" style="font-size:0.85rem;font-weight:700;">Conhecer regulamentos &rarr;</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>