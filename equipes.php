<?php
/**
 * JIF+ — Consulta de Equipes, Elencos e Desempenho
 * Requisitos: RF07, RF13
 */
$pageTitle = 'Equipes — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

$equipeId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($equipeId) {
    // Visualização detalhada de uma equipe específica
    $stmtEquipe = $pdo->prepare("
        SELECT eq.*, 
               inst.nome AS instituicao_nome, inst.sigla AS instituicao_sigla, inst.campus AS instituicao_campus,
               comp.nome AS competicao_nome, comp.edicao AS competicao_edicao,
               mod.nome AS modalidade_nome, mod.categoria AS modalidade_categoria,
               u.nome AS responsavel_nome
        FROM equipes eq
        JOIN instituicoes inst ON eq.instituicao_id = inst.id
        JOIN competicoes comp ON eq.competicao_id = comp.id
        JOIN modalidades mod ON eq.modalidade_id = mod.id
        JOIN usuarios u ON eq.responsavel_id = u.id
        WHERE eq.id = :id
    ");
    $stmtEquipe->execute([':id' => $equipeId]);
    $equipe = $stmtEquipe->fetch();

    if (!$equipe) {
        $_SESSION['mensagem_erro'] = 'Equipe não encontrada.';
        header('Location: equipes.php');
        exit;
    }

    // Elenco de atletas inscritos
    $stmtAtletas = $pdo->prepare("
        SELECT a.id, a.nome, ea.numero_camisa, ea.posicao, ea.status_inscricao,
               COALESCE((SELECT SUM(valor) FROM estatisticas_atletas WHERE atleta_id = a.id AND equipe_id = eq.id AND tipo_estatistica = 'gol'), 0) AS gols
        FROM equipe_atletas ea
        JOIN atletas a ON ea.atleta_id = a.id
        JOIN equipes eq ON ea.equipe_id = eq.id
        WHERE ea.equipe_id = :equipe_id
        ORDER BY ea.numero_camisa ASC, a.nome ASC
    ");
    $stmtAtletas->execute([':equipe_id' => $equipeId]);
    $elenco = $stmtAtletas->fetchAll();

    // Histórico de partidas da equipe
    $stmtJogos = $pdo->prepare("
        SELECT p.*, 
               eq_a.nome AS time_a, eq_b.nome AS time_b
        FROM partidas p
        JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
        JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
        WHERE p.equipe_a_id = :id OR p.equipe_b_id = :id
        ORDER BY p.data_hora DESC
    ");
    $stmtJogos->execute([':id' => $equipeId]);
    $jogos = $stmtJogos->fetchAll();
} else {
    // Listagem geral de equipes com filtros
    $filtroCampus = isset($_GET['campus']) ? trim($_GET['campus']) : '';
    $sql = "
        SELECT eq.*, 
               inst.campus, inst.sigla,
               comp.nome AS competicao_nome,
               mod.nome AS modalidade_nome, mod.categoria AS modalidade_categoria,
               u.nome AS responsavel_nome,
               (SELECT COUNT(*) FROM equipe_atletas ea WHERE ea.equipe_id = eq.id) AS total_atletas
        FROM equipes eq
        JOIN instituicoes inst ON eq.instituicao_id = inst.id
        JOIN competicoes comp ON eq.competicao_id = comp.id
        JOIN modalidades mod ON eq.modalidade_id = mod.id
        JOIN usuarios u ON eq.responsavel_id = u.id
    ";
    $params = [];
    if (!empty($filtroCampus)) {
        $sql .= " WHERE inst.campus LIKE :campus ";
        $params[':campus'] = "%{$filtroCampus}%";
    }
    $sql .= " ORDER BY inst.campus ASC, mod.nome ASC";

    $stmtLista = $pdo->prepare($sql);
    $stmtLista->execute($params);
    $equipes = $stmtLista->fetchAll();
}
?>

<div class="container" style="padding: 40px 20px;">

<?php if ($equipeId && isset($equipe)): ?>
    <!-- DETALHES DE UMA EQUIPE ESPECÍFICA -->
    <div style="margin-bottom:20px;">
        <a href="equipes.php" style="font-weight:700;">&larr; Voltar para todas as equipes</a>
    </div>

    <div class="card" style="margin-bottom:30px;border-left:5px solid var(--primary);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:15px;">
            <div>
                <span class="badge badge-info"><?= htmlspecialchars($equipe['modalidade_nome']) ?> (<?= htmlspecialchars($equipe['modalidade_categoria']) ?>)</span>
                <h1 style="font-size:2rem;font-weight:900;color:var(--dark);margin-top:6px;"><?= htmlspecialchars($equipe['nome']) ?></h1>
                <div style="font-size:1rem;color:var(--text-muted);margin-top:4px;">
                    🏛️ <strong>Campus:</strong> <?= htmlspecialchars($equipe['instituicao_sigla']) ?> - <?= htmlspecialchars($equipe['instituicao_campus']) ?> | 
                    🏆 <strong>Competição:</strong> <?= htmlspecialchars($equipe['competicao_nome']) ?>
                </div>
            </div>
            <div style="background:#f8fafc;padding:12px 18px;border-radius:var(--radius-sm);border:1px solid var(--border);text-align:right;">
                <div style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);font-weight:700;">Comissão Técnica</div>
                <div style="font-weight:800;color:var(--dark);"><?= htmlspecialchars($equipe['responsavel_nome']) ?></div>
            </div>
        </div>
    </div>

    <!-- ELENCO DE ATLETAS -->
    <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:15px;color:var(--dark);display:flex;align-items:center;gap:8px;">
        👥 Elenco Inscrito (<?= count($elenco) ?> atletas)
    </h2>

    <div class="table-responsive" style="margin-bottom:40px;">
        <table class="sports-table">
            <thead>
                <tr>
                    <th class="center" style="width:70px;">Camisa</th>
                    <th>Nome do Estudante-Atleta</th>
                    <th>Posição / Especialidade</th>
                    <th class="center">Gols</th>
                    <th class="center">Status</th>
                    <th class="center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($elenco)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">
                            Nenhum atleta inscrito nesta equipe ainda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($elenco as $at): ?>
                        <tr>
                            <td class="center" style="font-weight:900;font-size:1.1rem;color:var(--primary-dark);">
                                <?= $at['numero_camisa'] ? '#' . $at['numero_camisa'] : '-' ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($at['nome']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($at['posicao'] ?? 'Geral') ?></td>
                            <td class="center" style="font-weight:700;color:var(--primary);">
                                <?= $at['gols'] ?>
                            </td>
                            <td class="center">
                                <span class="badge badge-success"><?= htmlspecialchars($at['status_inscricao']) ?></span>
                            </td>
                            <td class="center">
                                <a href="atletas.php?id=<?= $at['id'] ?>" class="btn btn-sm btn-outline">
                                    Ver BID &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PARTIDAS DA EQUIPE -->
    <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:15px;color:var(--dark);display:flex;align-items:center;gap:8px;">
        📅 Histórico de Partidas
    </h2>

    <?php if (empty($jogos)): ?>
        <p style="color:var(--text-muted);">Nenhum jogo registrado para esta equipe.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:15px;">
            <?php foreach ($jogos as $j): ?>
                <div class="match-card" style="margin-bottom:0;">
                    <div class="match-header">
                        <span><?= htmlspecialchars($j['fase_rodada']) ?></span>
                        <span class="badge <?= $j['situacao'] === 'encerrada' ? 'badge-success' : 'badge-warning' ?>">
                            <?= ucfirst($j['situacao']) ?>
                        </span>
                    </div>
                    <div style="text-align:center;font-weight:800;font-size:1.1rem;margin:12px 0;">
                        <?= htmlspecialchars($j['time_a']) ?>
                        <span style="background:#1e293b;color:#fff;padding:2px 10px;border-radius:4px;margin:0 6px;">
                            <?= $j['placar_a'] !== null ? $j['placar_a'] . ' x ' . $j['placar_b'] : 'vs' ?>
                        </span>
                        <?= htmlspecialchars($j['time_b']) ?>
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-muted);text-align:center;">
                        📍 <?= htmlspecialchars($j['local_quadra']) ?> | 🕒 <?= date('d/m/Y H:i', strtotime($j['data_hora'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- LISTAGEM GERAL DE EQUIPES -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">🛡️ Equipes Participantes</h1>
            <p style="color:var(--text-muted);">Delegações e times inscritos nos Jogos dos Institutos Federais</p>
        </div>
        <div>
            <input type="text" class="form-control" placeholder="🔍 Filtrar equipe ou campus..." data-table-filter="#tableEquipes" style="width:260px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="sports-table" id="tableEquipes">
            <thead>
                <tr>
                    <th>Equipe</th>
                    <th>Campus / Instituto</th>
                    <th>Modalidade</th>
                    <th>Técnico / Responsável</th>
                    <th class="center">Atletas</th>
                    <th class="center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipes as $eq): ?>
                    <tr>
                        <td>
                            <strong style="font-size:1rem;color:var(--dark);"><?= htmlspecialchars($eq['nome']) ?></strong>
                        </td>
                        <td>
                            <span><?= htmlspecialchars($eq['sigla']) ?> - <?= htmlspecialchars($eq['campus']) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-info"><?= htmlspecialchars($eq['modalidade_nome']) ?> (<?= htmlspecialchars($eq['modalidade_categoria']) ?>)</span>
                        </td>
                        <td>
                            <?= htmlspecialchars($eq['responsavel_nome']) ?>
                        </td>
                        <td class="center" style="font-weight:700;">
                            <?= $eq['total_atletas'] ?>
                        </td>
                        <td class="center">
                            <a href="equipes.php?id=<?= $eq['id'] ?>" class="btn btn-sm btn-outline">
                                Ver Elenco & Detalhes &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>