<?php
/**
 * JIF+ — Perfil Esportivo do Atleta (BID) e Consulta Geral
 * Requisitos: RF08, RF09, RNF06 (Privacidade e dados esportivos)
 */
$pageTitle = 'Atletas (BID) — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

$atletaId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($atletaId) {
    // 1. Dados cadastrais e institucionais do Atleta
    $stmtAtleta = $pdo->prepare("
        SELECT a.id, a.nome, a.foto_url, a.criado_em, inst.nome AS instituicao_nome, inst.sigla AS instituicao_sigla, inst.campus AS instituicao_campus
        FROM atletas a
        JOIN instituicoes inst ON a.instituicao_id = inst.id
        WHERE a.id = :id
    ");
    $stmtAtleta->execute([':id' => $atletaId]);
    $atleta = $stmtAtleta->fetch();

    if (!$atleta) {
        $_SESSION['mensagem_erro'] = 'Atleta não encontrado no sistema.';
        header('Location: atletas.php');
        exit;
    }

    // 2. Vínculos e Equipes do Atleta
    $stmtEquipes = $pdo->prepare("
        SELECT eq.id AS equipe_id, eq.nome AS equipe_nome,
               m.nome AS modalidade_nome, m.categoria AS modalidade_categoria,
               c.nome AS competicao_nome,
               ea.numero_camisa, ea.posicao, ea.status_inscricao
        FROM equipe_atletas ea
        JOIN equipes eq ON ea.equipe_id = eq.id
        JOIN modalidades m ON eq.modalidade_id = m.id
        JOIN competicoes c ON eq.competicao_id = c.id
        WHERE ea.atleta_id = :atleta_id
    ");
    $stmtEquipes->execute([':atleta_id' => $atletaId]);
    $equipesAtleta = $stmtEquipes->fetchAll();

    // 3. Indicadores Estatísticos Consolidados
    $stmtStats = $pdo->prepare("
        SELECT tipo_estatistica, SUM(valor) AS total
        FROM estatisticas_atletas
        WHERE atleta_id = :atleta_id
        GROUP BY tipo_estatistica
    ");
    $stmtStats->execute([':atleta_id' => $atletaId]);
    $statsRaw = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalGols = $statsRaw['gol'] ?? 0;
    $totalPontos = $statsRaw['ponto'] ?? 0;
    $cartoesAmarelos = $statsRaw['cartao_amarelo'] ?? 0;
    $cartoesVermelhos = $statsRaw['cartao_vermelho'] ?? 0;
    $totalFaltas = $statsRaw['falta'] ?? 0;

    // 4. Histórico de Partidas Disputadas
    $stmtPartidas = $pdo->prepare("
        SELECT DISTINCT p.id, p.fase_rodada, p.data_hora, p.local_quadra, p.placar_a, p.placar_b, p.situacao,
               m.nome AS modalidade_nome,
               eq_a.nome AS equipe_a, eq_b.nome AS equipe_b,
               (SELECT SUM(valor) FROM estatisticas_atletas WHERE partida_id = p.id AND atleta_id = :atleta_id AND tipo_estatistica = 'gol') AS gols_na_partida
        FROM partidas p
        JOIN modalidades m ON p.modalidade_id = m.id
        JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
        JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
        JOIN equipe_atletas ea ON (ea.equipe_id = p.equipe_a_id OR ea.equipe_id = p.equipe_b_id)
        WHERE ea.atleta_id = :atleta_id AND p.situacao = 'encerrada'
        ORDER BY p.data_hora DESC
    ");
    $stmtPartidas->execute([':atleta_id' => $atletaId]);
    $partidasAtleta = $stmtPartidas->fetchAll();
    $totalJogos = count($partidasAtleta);
} else {
    // Listagem geral de atletas com busca
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $sql = "
        SELECT a.id, a.nome, a.criado_em, inst.campus, inst.sigla,
               (SELECT COUNT(*) FROM equipe_atletas ea WHERE ea.atleta_id = a.id) AS total_equipes,
               (SELECT COALESCE(SUM(valor), 0) FROM estatisticas_atletas ea WHERE ea.atleta_id = a.id AND ea.tipo_estatistica = 'gol') AS total_gols
        FROM atletas a
        JOIN instituicoes inst ON a.instituicao_id = inst.id
    ";
    $params = [];
    if (!empty($busca)) {
        $sql .= " WHERE a.nome LIKE :busca OR inst.campus LIKE :busca ";
        $params[':busca'] = "%{$busca}%";
    }
    $sql .= " ORDER BY a.nome ASC";

    $stmtLista = $pdo->prepare($sql);
    $stmtLista->execute($params);
    $atletas = $stmtLista->fetchAll();
}
?>

<div class="container" style="padding: 40px 20px;">

<?php if ($atletaId && isset($atleta)): ?>
    <!-- BOLETIM INFORMATIVO DO ATLETA (BID INDIVIDUAL) -->
    <div style="margin-bottom:20px;">
        <a href="atletas.php" style="font-weight:700;">&larr; Voltar para busca de atletas</a>
    </div>

    <div class="athlete-profile">
        <div class="athlete-avatar">
            <?= mb_substr($atleta['nome'], 0, 1) ?>
        </div>

        <div class="athlete-details">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <span class="badge badge-success">Atleta Homologado</span>
                    <h1 class="athlete-name"><?= htmlspecialchars($atleta['nome']) ?></h1>
                </div>

            </div>

            <div class="athlete-meta">
                <span>🏛️ <strong>Campus:</strong> <?= htmlspecialchars($atleta['instituicao_sigla']) ?> - <?= htmlspecialchars($atleta['instituicao_campus']) ?></span>
                <span>📅 <strong>Inscrição no JIF:</strong> <?= date('Y', strtotime($atleta['criado_em'])) ?></span>
            </div>

            <!-- ESTATÍSTICAS ESPORTIVAS CONSOLIDADAS -->
            <div class="athlete-stats-grid">
                <div class="stat-box" style="border-top:3px solid var(--primary);">
                    <div class="stat-value"><?= $totalJogos ?></div>
                    <div class="stat-label">Jogos Disputados</div>
                </div>
                <div class="stat-box" style="border-top:3px solid var(--secondary);">
                    <div class="stat-value"><?= $totalGols ?></div>
                    <div class="stat-label">Gols Marcados</div>
                </div>
                <div class="stat-box" style="border-top:3px solid var(--warning);">
                    <div class="stat-value"><?= $cartoesAmarelos ?></div>
                    <div class="stat-label">Cartões Amarelos</div>
                </div>
                <div class="stat-box" style="border-top:3px solid var(--danger);">
                    <div class="stat-value"><?= $cartoesVermelhos ?></div>
                    <div class="stat-label">Cartões Vermelhos</div>
                </div>
                <div class="stat-box" style="border-top:3px solid var(--dark);">
                    <div class="stat-value"><?= $totalFaltas ?></div>
                    <div class="stat-label">Faltas Cometidas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- EQUIPES E MODALIDADES QUE INTEGRA -->
    <h2 style="font-size:1.3rem;font-weight:800;margin:30px 0 15px 0;color:var(--dark);display:flex;align-items:center;gap:8px;">
        🛡️ Equipes e Modalidades Vinculadas
    </h2>

    <div class="grid-cards" style="margin-top:0;">
        <?php foreach ($equipesAtleta as $eq): ?>
            <div class="card" style="border-left:4px solid var(--primary);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="badge badge-info"><?= htmlspecialchars($eq['modalidade_nome']) ?></span>
                    <span style="font-weight:900;font-size:1.2rem;color:var(--primary-dark);">
                        <?= $eq['numero_camisa'] ? '#' . $eq['numero_camisa'] : '-' ?>
                    </span>
                </div>
                <h3 class="card-title"><?= htmlspecialchars($eq['equipe_nome']) ?></h3>
                <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:10px;">
                    <div>📍 <strong>Posição:</strong> <?= htmlspecialchars($eq['posicao'] ?? 'Não especificada') ?></div>
                    <div>🏆 <strong>Competição:</strong> <?= htmlspecialchars($eq['competicao_nome']) ?></div>
                    <div>🟢 <strong>Situação:</strong> <?= ucfirst($eq['status_inscricao']) ?></div>
                </div>
                <a href="equipes.php?id=<?= $eq['equipe_id'] ?>" class="btn btn-sm btn-outline" style="width:100%;">
                    Ver Detalhes da Equipe &rarr;
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- HISTÓRICO DE PARTIDAS -->
    <h2 style="font-size:1.3rem;font-weight:800;margin:35px 0 15px 0;color:var(--dark);display:flex;align-items:center;gap:8px;">
        📜 Histórico de Partidas Disputadas
    </h2>

    <?php if (empty($partidasAtleta)): ?>
        <p style="color:var(--text-muted);">Nenhuma partida concluída registrada para este atleta.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="sports-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Confronto</th>
                        <th>Modalidade / Fase</th>
                        <th class="center">Placar Final</th>
                        <th class="center">Gols do Atleta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partidasAtleta as $p): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['data_hora'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['equipe_a']) ?></strong> vs 
                                <strong><?= htmlspecialchars($p['equipe_b']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($p['modalidade_nome']) ?> (<?= htmlspecialchars($p['fase_rodada']) ?>)</td>
                            <td class="center">
                                <span class="badge badge-neutral" style="font-size:0.85rem;">
                                    <?= $p['placar_a'] ?> x <?= $p['placar_b'] ?>
                                </span>
                            </td>
                            <td class="center" style="font-weight:800;color:var(--primary);">
                                <?= $p['gols_na_partida'] > 0 ? '⚽ ' . $p['gols_na_partida'] : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- LISTAGEM GERAL DE ATLETAS (BID) -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">🏃‍♂️ Boletim Informativo de Atletas (BID)</h1>
            <p style="color:var(--text-muted);">Consulta unificada de estudantes-atletas participantes dos Jogos dos Institutos Federais</p>
        </div>
        <form method="GET" action="atletas.php" style="display:flex;gap:10px;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou campus..." value="<?= htmlspecialchars($busca ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width:280px;">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="sports-table">
            <thead>
                <tr>
                    <th>Nome do Estudante-Atleta</th>
                    <th>Campus / Instituto</th>
                    <th class="center">Equipes</th>
                    <th class="center">Gols Marcados</th>
                    <th class="center">Perfil Esportivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($atletas)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px;">
                            Nenhum atleta encontrado com o termo pesquisado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($atletas as $at): ?>
                        <tr>
                            <td>
                                <strong style="font-size:1rem;color:var(--dark);"><?= htmlspecialchars($at['nome']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($at['sigla']) ?> - <?= htmlspecialchars($at['campus']) ?>
                            </td>
                            <td class="center" style="font-weight:700;">
                                <?= $at['total_equipes'] ?>
                            </td>
                            <td class="center" style="font-weight:800;color:var(--primary);">
                                <?= $at['total_gols'] ?>
                            </td>
                            <td class="center">
                                <a href="atletas.php?id=<?= $at['id'] ?>" class="btn btn-sm btn-outline">
                                    Ver Perfil Completo (BID) &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>