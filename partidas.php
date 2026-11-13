<?php
/**
 * JIF+ — Calendário Oficial de Partidas e Súmula Eletrônica
 * Requisitos: RF10, RF11, RF12
 */
$pageTitle = 'Partidas e Súmulas — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

$partidaId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($partidaId) {
    // 1. Dados completos da partida
    $stmtPartida = $pdo->prepare("
        SELECT p.*,
               c.nome AS competicao_nome, c.edicao AS competicao_edicao,
               m.nome AS modalidade_nome, m.categoria AS modalidade_categoria,
               eq_a.nome AS equipe_a_nome, inst_a.campus AS campus_a, inst_a.sigla AS sigla_a,
               eq_b.nome AS equipe_b_nome, inst_b.campus AS campus_b, inst_b.sigla AS sigla_b
        FROM partidas p
        JOIN competicoes c ON p.competicao_id = c.id
        JOIN modalidades m ON p.modalidade_id = m.id
        JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
        JOIN instituicoes inst_a ON eq_a.instituicao_id = inst_a.id
        JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
        JOIN instituicoes inst_b ON eq_b.instituicao_id = inst_b.id
        WHERE p.id = :id
    ");
    $stmtPartida->execute([':id' => $partidaId]);
    $partida = $stmtPartida->fetch();

    if (!$partida) {
        $_SESSION['mensagem_erro'] = 'Partida não encontrada.';
        header('Location: partidas.php');
        exit;
    }

    // 2. Eventos da súmula (Gols, cartões, faltas)
    $stmtEventos = $pdo->prepare("
        SELECT ea.*, a.nome AS atleta_nome, eq.nome AS equipe_nome
        FROM estatisticas_atletas ea
        JOIN atletas a ON ea.atleta_id = a.id
        JOIN equipes eq ON ea.equipe_id = eq.id
        WHERE ea.partida_id = :partida_id
        ORDER BY ea.id ASC
    ");
    $stmtEventos->execute([':partida_id' => $partidaId]);
    $eventos = $stmtEventos->fetchAll();

    // 3. Atletas escalados em cada time
    $stmtElencoA = $pdo->prepare("
        SELECT a.id, a.nome, ea.numero_camisa, ea.posicao
        FROM equipe_atletas ea
        JOIN atletas a ON ea.atleta_id = a.id
        WHERE ea.equipe_id = :eq_id
        ORDER BY ea.numero_camisa ASC
    ");
    $stmtElencoA->execute([':eq_id' => $partida['equipe_a_id']]);
    $elencoA = $stmtElencoA->fetchAll();

    $stmtElencoB = $pdo->prepare("
        SELECT a.id, a.nome, ea.numero_camisa, ea.posicao
        FROM equipe_atletas ea
        JOIN atletas a ON ea.atleta_id = a.id
        WHERE ea.equipe_id = :eq_id
        ORDER BY ea.numero_camisa ASC
    ");
    $stmtElencoB->execute([':eq_id' => $partida['equipe_b_id']]);
    $elencoB = $stmtElencoB->fetchAll();

} else {
    // Listagem com filtros
    $filtroModalidade = isset($_GET['modalidade_id']) ? (int)$_GET['modalidade_id'] : 0;
    $filtroSituacao = isset($_GET['situacao']) ? trim($_GET['situacao']) : '';

    $sql = "
        SELECT p.*,
               c.nome AS competicao_nome,
               m.nome AS modalidade_nome, m.categoria AS modalidade_categoria,
               eq_a.nome AS equipe_a_nome, inst_a.campus AS campus_a,
               eq_b.nome AS equipe_b_nome, inst_b.campus AS campus_b
        FROM partidas p
        JOIN competicoes c ON p.competicao_id = c.id
        JOIN modalidades m ON p.modalidade_id = m.id
        JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
        JOIN instituicoes inst_a ON eq_a.instituicao_id = inst_a.id
        JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
        JOIN instituicoes inst_b ON eq_b.instituicao_id = inst_b.id
        WHERE 1=1
    ";
    $params = [];

    if ($filtroModalidade > 0) {
        $sql .= " AND p.modalidade_id = :mod_id ";
        $params[':mod_id'] = $filtroModalidade;
    }

    if (!empty($filtroSituacao)) {
        $sql .= " AND p.situacao = :situacao ";
        $params[':situacao'] = $filtroSituacao;
    }

    $sql .= " ORDER BY p.data_hora DESC";

    $stmtLista = $pdo->prepare($sql);
    $stmtLista->execute($params);
    $partidas = $stmtLista->fetchAll();

    // Modalidades para o filtro
    $todasModalidades = $pdo->query("SELECT id, nome, categoria FROM modalidades ORDER BY nome ASC")->fetchAll();
}
?>

<div class="container" style="padding: 40px 20px;">

<?php if ($partidaId && isset($partida)): ?>
    <!-- VISUALIZAÇÃO DETALHADA DA SÚMULA ELETRÔNICA -->
    <div style="margin-bottom:20px;">
        <a href="partidas.php" style="font-weight:700;">&larr; Voltar para calendário de partidas</a>
    </div>

    <div class="card" style="margin-bottom:30px;">
        <div style="text-align:center;margin-bottom:20px;">
            <span class="badge badge-info" style="font-size:0.85rem;">
                <?= htmlspecialchars($partida['modalidade_nome']) ?> (<?= htmlspecialchars($partida['modalidade_categoria']) ?>) • <?= htmlspecialchars($partida['fase_rodada']) ?>
            </span>
            <div style="font-size:0.9rem;color:var(--text-muted);margin-top:6px;">
                🏆 <?= htmlspecialchars($partida['competicao_nome']) ?> • <?= htmlspecialchars($partida['competicao_edicao']) ?>
            </div>
        </div>

        <div class="match-teams" style="max-width:800px;margin:20px auto;">
            <div class="team-box" style="flex:1;justify-content:flex-end;">
                <div>
                    <div style="font-size:1.3rem;"><?= htmlspecialchars($partida['equipe_a_nome']) ?></div>
                    <div style="font-size:0.85rem;color:var(--text-muted);"><?= htmlspecialchars($partida['campus_a']) ?></div>
                </div>
                <div class="team-badge" style="width:55px;height:55px;font-size:1.5rem;"><?= mb_substr($partida['campus_a'], 0, 1) ?></div>
            </div>

            <div class="match-score" style="font-size:2.5rem;padding:10px 30px;">
                <?= $partida['placar_a'] !== null ? $partida['placar_a'] . ' x ' . $partida['placar_b'] : 'vs' ?>
            </div>

            <div class="team-box away" style="flex:1;justify-content:flex-end;">
                <div>
                    <div style="font-size:1.3rem;"><?= htmlspecialchars($partida['equipe_b_nome']) ?></div>
                    <div style="font-size:0.85rem;color:var(--text-muted);"><?= htmlspecialchars($partida['campus_b']) ?></div>
                </div>
                <div class="team-badge" style="width:55px;height:55px;font-size:1.5rem;"><?= mb_substr($partida['campus_b'], 0, 1) ?></div>
            </div>
        </div>

        <div style="text-align:center;margin-top:20px;padding-top:15px;border-top:1px solid var(--border);color:var(--text-muted);font-size:0.9rem;">
            <span>📍 <strong>Local:</strong> <?= htmlspecialchars($partida['local_quadra']) ?></span> |
            <span>🕒 <strong>Data e Hora:</strong> <?= date('d/m/Y \à\s H:i', strtotime($partida['data_hora'])) ?></span> |
            <span>🟢 <strong>Status:</strong> <?= ucfirst($partida['situacao']) ?></span>
        </div>
    </div>

    <!-- EVENTOS DA SÚMULA ELETRÔNICA (GOLS E CARTÕES) -->
    <h2 style="font-size:1.3rem;font-weight:800;margin:30px 0 15px 0;color:var(--dark);display:flex;align-items:center;gap:8px;">
        📝 Eventos e Estatísticas Registradas na Súmula
    </h2>

    <div class="table-responsive" style="margin-bottom:30px;">
        <table class="sports-table">
            <thead>
                <tr>
                    <th>Evento / Tipo</th>
                    <th>Estudante-Atleta</th>
                    <th>Equipe Representada</th>
                    <th class="center">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($eventos)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;color:var(--text-muted);padding:30px;">
                            Nenhum evento individual (gol, cartão ou falta) lançado nesta partida ainda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($eventos as $ev): ?>
                        <?php
                        $badgeEv = match($ev['tipo_estatistica']) {
                            'gol' => ['icone' => '⚽ Gol Marcado', 'classe' => 'badge-success'],
                            'ponto' => ['icone' => '🏀 Ponto Marcado', 'classe' => 'badge-success'],
                            'cartao_amarelo' => ['icone' => '🟨 Cartão Amarelo', 'classe' => 'badge-warning'],
                            'cartao_vermelho' => ['icone' => '🟥 Cartão Vermelho', 'classe' => 'badge-danger'],
                            'falta' => ['icone' => '⚠️ Falta Individual', 'classe' => 'badge-neutral'],
                            default => ['icone' => $ev['tipo_estatistica'], 'classe' => 'badge-neutral']
                        };
                        ?>
                        <tr>
                            <td>
                                <span class="badge <?= $badgeEv['classe'] ?>"><?= $badgeEv['icone'] ?></span>
                            </td>
                            <td>
                                <strong><a href="atletas.php?id=<?= $ev['atleta_id'] ?>"><?= htmlspecialchars($ev['atleta_nome']) ?></a></strong>
                            </td>
                            <td><?= htmlspecialchars($ev['equipe_nome']) ?></td>
                            <td class="center" style="font-weight:800;"><?= $ev['valor'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ESCALAÇÃO DAS DUAS EQUIPES -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:30px;">
        <div class="card">
            <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:12px;color:var(--dark);">
                Escalação: <?= htmlspecialchars($partida['equipe_a_nome']) ?>
            </h3>
            <ul style="list-style:none;line-height:2;font-size:0.9rem;">
                <?php foreach ($elencoA as $ea): ?>
                    <li style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:4px 0;">
                        <span><strong>#<?= $ea['numero_camisa'] ?? '-' ?></strong> <?= htmlspecialchars($ea['nome']) ?></span>
                        <span style="color:var(--text-muted);"><?= htmlspecialchars($ea['posicao'] ?? 'Geral') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:12px;color:var(--dark);">
                Escalação: <?= htmlspecialchars($partida['equipe_b_nome']) ?>
            </h3>
            <ul style="list-style:none;line-height:2;font-size:0.9rem;">
                <?php foreach ($elencoB as $eb): ?>
                    <li style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:4px 0;">
                        <span><strong>#<?= $eb['numero_camisa'] ?? '-' ?></strong> <?= htmlspecialchars($eb['nome']) ?></span>
                        <span style="color:var(--text-muted);"><?= htmlspecialchars($eb['posicao'] ?? 'Geral') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

<?php else: ?>
    <!-- LISTAGEM GERAL DE PARTIDAS COM FILTROS -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">📅 Calendário e Súmulas de Jogos</h1>
            <p style="color:var(--text-muted);">Tabela oficial de partidas dos Jogos dos Institutos Federais</p>
        </div>

        <!-- FILTROS COMBINADOS -->
        <form method="GET" action="partidas.php" style="display:flex;gap:10px;flex-wrap:wrap;">
            <select name="modalidade_id" class="form-control" style="width:180px;" onchange="this.form.submit()">
                <option value="0">Todas Modalidades</option>
                <?php foreach ($todasModalidades as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $filtroModalidade === (int)$m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['categoria']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="situacao" class="form-control" style="width:160px;" onchange="this.form.submit()">
                <option value="">Todas Situações</option>
                <option value="encerrada" <?= $filtroSituacao === 'encerrada' ? 'selected' : '' ?>>Encerradas</option>
                <option value="agendada" <?= $filtroSituacao === 'agendada' ? 'selected' : '' ?>>Agendadas</option>
                <option value="em_andamento" <?= $filtroSituacao === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
            </select>
        </form>
    </div>

    <?php if (empty($partidas)): ?>
        <div class="card" style="text-align:center;padding:50px;color:var(--text-muted);">
            Nenhuma partida encontrada para os filtros selecionados.
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(360px, 1fr));gap:20px;">
            <?php foreach ($partidas as $p): ?>
                <?php
                $badgeSit = match($p['situacao']) {
                    'encerrada' => ['classe' => 'badge-success', 'texto' => 'Encerrada'],
                    'agendada' => ['classe' => 'badge-warning', 'texto' => 'Agendada'],
                    'em_andamento' => ['classe' => 'badge-danger', 'texto' => 'Ao Vivo'],
                    default => ['classe' => 'badge-neutral', 'texto' => ucfirst($p['situacao'])]
                };
                ?>
                <div class="match-card">
                    <div class="match-header">
                        <span><?= htmlspecialchars($p['modalidade_nome']) ?> • <?= htmlspecialchars($p['fase_rodada']) ?></span>
                        <span class="badge <?= $badgeSit['classe'] ?>"><?= $badgeSit['texto'] ?></span>
                    </div>

                    <div class="match-teams">
                        <div class="team-box">
                            <div class="team-badge"><?= mb_substr($p['campus_a'], 0, 1) ?></div>
                            <div>
                                <div style="font-size:1rem;"><?= htmlspecialchars($p['equipe_a_nome']) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_a']) ?></div>
                            </div>
                        </div>

                        <div class="match-score <?= $p['situacao'] === 'agendada' ? 'scheduled' : '' ?>">
                            <?= $p['placar_a'] !== null ? $p['placar_a'] . ' x ' . $p['placar_b'] : 'VS' ?>
                        </div>

                        <div class="team-box away">
                            <div class="team-badge"><?= mb_substr($p['campus_b'], 0, 1) ?></div>
                            <div>
                                <div style="font-size:1rem;"><?= htmlspecialchars($p['equipe_b_nome']) ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-weight:normal;"><?= htmlspecialchars($p['campus_b']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="match-footer">
                        <span>📍 <?= htmlspecialchars($p['local_quadra']) ?></span>
                        <span>🕒 <?= date('d/m/Y H:i', strtotime($p['data_hora'])) ?></span>
                    </div>

                    <div style="margin-top:12px;">
                        <a href="partidas.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="width:100%;">
                            Ver Súmula Completa &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>