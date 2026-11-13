<?php
/**
 * JIF+ — Painéis de Inteligência Esportiva e Indicadores Analíticos
 * Requisitos: RF15, UC11
 */
$pageTitle = 'Inteligência Esportiva — JIF+';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// 1. Ranking de Artilheiros Geral
$stmtArtilharia = $pdo->query("
    SELECT a.id, a.nome, a.matricula_estudantil, inst.campus,
           eq.nome AS equipe_nome,
           SUM(ea.valor) AS total_gols
    FROM estatisticas_atletas ea
    JOIN atletas a ON ea.atleta_id = a.id
    JOIN instituicoes inst ON a.instituicao_id = inst.id
    JOIN equipes eq ON ea.equipe_id = eq.id
    WHERE ea.tipo_estatistica = 'gol'
    GROUP BY a.id, a.nome, a.matricula_estudantil, inst.campus, eq.nome
    ORDER BY total_gols DESC
    LIMIT 10
");
$artilharia = $stmtArtilharia->fetchAll();

// 2. Ranking de Eficiência por Campus (Quadro Geral de Desempenho)
$stmtCampi = $pdo->query("
    SELECT inst.id, inst.sigla, inst.campus,
           COUNT(DISTINCT eq.id) AS total_equipes,
           COUNT(DISTINCT a.id) AS total_atletas,
           COALESCE(SUM(ea.valor), 0) AS total_gols_campus
    FROM instituicoes inst
    LEFT JOIN equipes eq ON eq.instituicao_id = inst.id
    LEFT JOIN atletas a ON a.instituicao_id = inst.id
    LEFT JOIN estatisticas_atletas ea ON ea.atleta_id = a.id AND ea.tipo_estatistica = 'gol'
    GROUP BY inst.id, inst.sigla, inst.campus
    ORDER BY total_gols_campus DESC, total_equipes DESC
");
$desempenhoCampi = $stmtCampi->fetchAll();

// 3. Disciplina / Fair Play (Cartões por Equipe)
$stmtDisciplina = $pdo->query("
    SELECT eq.nome AS equipe_nome, inst.campus,
           SUM(CASE WHEN ea.tipo_estatistica = 'cartao_amarelo' THEN ea.valor ELSE 0 END) AS cartoes_amarelos,
           SUM(CASE WHEN ea.tipo_estatistica = 'cartao_vermelho' THEN ea.valor ELSE 0 END) AS cartoes_vermelhos,
           SUM(CASE WHEN ea.tipo_estatistica = 'falta' THEN ea.valor ELSE 0 END) AS faltas_cometidas
    FROM equipes eq
    JOIN instituicoes inst ON eq.instituicao_id = inst.id
    LEFT JOIN estatisticas_atletas ea ON ea.equipe_id = eq.id
    GROUP BY eq.id, eq.nome, inst.campus
    ORDER BY cartoes_vermelhos ASC, cartoes_amarelos ASC
    LIMIT 6
");
$disciplina = $stmtDisciplina->fetchAll();
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:30px;">
        <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">🧠 Painéis de Inteligência Esportiva</h1>
        <p style="color:var(--text-muted);">Métricas consolidadas, rankings individuais e análise analítica de desempenho dos Jogos</p>
    </div>

    <div style="display:grid;grid-template-columns: 1fr 1fr;gap:30px;margin-bottom:40px;">
        
        <!-- CARD 1: TOP ARTILHEIROS -->
        <div class="card">
            <div class="card-header-sport">
                <h3 style="font-size:1.2rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                    🎯 Chuteira de Ouro — Artilharia Geral
                </h3>
            </div>
            
            <div class="table-responsive" style="box-shadow:none;border:none;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th class="center" style="width:40px;">#</th>
                            <th>Atleta</th>
                            <th>Campus</th>
                            <th class="center">Gols</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($artilharia as $i => $art): ?>
                            <tr>
                                <td class="center" style="font-weight:900;color:<?= $i === 0 ? 'var(--secondary)' : 'var(--text-muted)' ?>;">
                                    <?= $i + 1 ?>º
                                </td>
                                <td>
                                    <strong><a href="atletas.php?id=<?= $art['id'] ?>"><?= htmlspecialchars($art['nome']) ?></a></strong>
                                </td>
                                <td style="font-size:0.85rem;color:var(--text-muted);"><?= htmlspecialchars($art['campus']) ?></td>
                                <td class="center" style="font-weight:900;color:var(--primary);font-size:1.1rem;">
                                    <?= $art['total_gols'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CARD 2: QUADRO GERAL POR CAMPUS -->
        <div class="card">
            <div class="card-header-sport">
                <h3 style="font-size:1.2rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                    🏛️ Eficiência Ofensiva por Campus
                </h3>
            </div>

            <div class="table-responsive" style="box-shadow:none;border:none;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Campus</th>
                            <th class="center">Equipes</th>
                            <th class="center">Atletas</th>
                            <th class="center">Total Gols</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($desempenhoCampi as $dc): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($dc['sigla']) ?> - <?= htmlspecialchars($dc['campus']) ?></strong>
                                </td>
                                <td class="center"><?= $dc['total_equipes'] ?></td>
                                <td class="center"><?= $dc['total_atletas'] ?></td>
                                <td class="center" style="font-weight:800;color:var(--primary);">
                                    <?= $dc['total_gols_campus'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- CARD 3: FAIR PLAY E DISCIPLINA -->
    <div class="card">
        <div class="card-header-sport">
            <h3 style="font-size:1.2rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:8px;">
                🤝 Troféu Fair Play & Disciplina Esportiva
            </h3>
        </div>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;">
            Acompanhamento do índice disciplinar de cada equipe nos jogos (cartões amarelos, vermelhos e faltas).
        </p>

        <div class="table-responsive">
            <table class="sports-table">
                <thead>
                    <tr>
                        <th>Equipe</th>
                        <th>Campus</th>
                        <th class="center">🟨 Cartões Amarelos</th>
                        <th class="center">🟥 Cartões Vermelhos</th>
                        <th class="center">Faltas Registradas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplina as $disc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($disc['equipe_nome']) ?></strong></td>
                            <td><?= htmlspecialchars($disc['campus']) ?></td>
                            <td class="center" style="font-weight:700;color:#d97706;"><?= $disc['cartoes_amarelos'] ?></td>
                            <td class="center" style="font-weight:700;color:#dc2626;"><?= $disc['cartoes_vermelhos'] ?></td>
                            <td class="center"><?= $disc['faltas_cometidas'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
