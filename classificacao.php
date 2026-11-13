<?php
/**
 * JIF+ — Tabelas de Classificação e Pontuação por Modalidade
 * Requisitos: RF15, UC11
 */
$pageTitle = 'Classificação — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Modalidades disponíveis para classificação
$modalidades = $pdo->query("SELECT id, nome, categoria FROM modalidades WHERE status = 'ativa' ORDER BY nome ASC")->fetchAll();
$modalidadeSelecionada = isset($_GET['modalidade_id']) ? (int)$_GET['modalidade_id'] : ($modalidades[0]['id'] ?? 0);

// Busca detalhes da modalidade atual
$stmtMod = $pdo->prepare("SELECT * FROM modalidades WHERE id = :id");
$stmtMod->execute([':id' => $modalidadeSelecionada]);
$modalidadeAtual = $stmtMod->fetch();

// Busca todas as equipes dessa modalidade
$stmtEquipes = $pdo->prepare("
    SELECT eq.id, eq.nome, inst.campus, inst.sigla
    FROM equipes eq
    JOIN instituicoes inst ON eq.instituicao_id = inst.id
    WHERE eq.modalidade_id = :mod_id
");
$stmtEquipes->execute([':mod_id' => $modalidadeSelecionada]);
$equipes = $stmtEquipes->fetchAll();

// Busca todas as partidas encerradas da modalidade para cálculo dinâmico da tabela
$stmtPartidas = $pdo->prepare("
    SELECT equipe_a_id, equipe_b_id, placar_a, placar_b
    FROM partidas
    WHERE modalidade_id = :mod_id AND situacao = 'encerrada' AND placar_a IS NOT NULL AND placar_b IS NOT NULL
");
$stmtPartidas->execute([':mod_id' => $modalidadeSelecionada]);
$partidasEncerradas = $stmtPartidas->fetchAll();

// Estrutura de dados para o cálculo oficial
$tabela = [];
foreach ($equipes as $eq) {
    $tabela[$eq['id']] = [
        'id' => $eq['id'],
        'nome' => $eq['nome'],
        'campus' => $eq['campus'],
        'sigla' => $eq['sigla'],
        'jogos' => 0,
        'vitorias' => 0,
        'empates' => 0,
        'derrotas' => 0,
        'gols_pro' => 0,
        'gols_contra' => 0,
        'saldo_gols' => 0,
        'pontos' => 0,
        'aproveitamento' => 0
    ];
}

// Processamento dos resultados esportivos
foreach ($partidasEncerradas as $p) {
    $timeA = $p['equipe_a_id'];
    $timeB = $p['equipe_b_id'];
    $golsA = (int)$p['placar_a'];
    $golsB = (int)$p['placar_b'];

    if (isset($tabela[$timeA])) {
        $tabela[$timeA]['jogos']++;
        $tabela[$timeA]['gols_pro'] += $golsA;
        $tabela[$timeA]['gols_contra'] += $golsB;

        if ($golsA > $golsB) {
            $tabela[$timeA]['vitorias']++;
            $tabela[$timeA]['pontos'] += 3;
        } elseif ($golsA === $golsB) {
            $tabela[$timeA]['empates']++;
            $tabela[$timeA]['pontos'] += 1;
        } else {
            $tabela[$timeA]['derrotas']++;
        }
    }

    if (isset($tabela[$timeB])) {
        $tabela[$timeB]['jogos']++;
        $tabela[$timeB]['gols_pro'] += $golsB;
        $tabela[$timeB]['gols_contra'] += $golsA;

        if ($golsB > $golsA) {
            $tabela[$timeB]['vitorias']++;
            $tabela[$timeB]['pontos'] += 3;
        } elseif ($golsB === $golsA) {
            $tabela[$timeB]['empates']++;
            $tabela[$timeB]['pontos'] += 1;
        } else {
            $tabela[$timeB]['derrotas']++;
        }
    }
}

// Finaliza cálculo de saldo e aproveitamento
foreach ($tabela as &$t) {
    $t['saldo_gols'] = $t['gols_pro'] - $t['gols_contra'];
    $pontosPossiveis = $t['jogos'] * 3;
    $t['aproveitamento'] = $pontosPossiveis > 0 ? round(($t['pontos'] / $pontosPossiveis) * 100, 1) : 0;
}
unset($t);

// Ordenação oficial: Pontos DESC, Vitórias DESC, Saldo de Gols DESC, Gols Pró DESC
uasort($tabela, function($a, $b) {
    if ($b['pontos'] !== $a['pontos']) return $b['pontos'] <=> $a['pontos'];
    if ($b['vitorias'] !== $a['vitorias']) return $b['vitorias'] <=> $a['vitorias'];
    if ($b['saldo_gols'] !== $a['saldo_gols']) return $b['saldo_gols'] <=> $a['saldo_gols'];
    return $b['gols_pro'] <=> $a['gols_pro'];
});
?>

<div class="container" style="padding: 40px 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">📊 Tabela Oficial de Classificação</h1>
            <p style="color:var(--text-muted);">Pontuação em tempo real, critérios de desempate e saldo de gols</p>
        </div>

        <!-- SELETOR DE MODALIDADES -->
        <form method="GET" action="classificacao.php">
            <select name="modalidade_id" class="form-control" style="width:240px;font-weight:700;" onchange="this.form.submit()">
                <?php foreach ($modalidades as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $modalidadeSelecionada === (int)$m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['categoria']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($modalidadeAtual): ?>
        <div style="background:#ffffff;border:1px solid var(--border);border-radius:var(--radius-md);padding:15px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong style="font-size:1.1rem;color:var(--dark);"><?= htmlspecialchars($modalidadeAtual['nome']) ?></strong>
                <span class="badge badge-info" style="margin-left:8px;"><?= ucfirst($modalidadeAtual['categoria']) ?></span>
            </div>
            <div style="font-size:0.85rem;color:var(--text-muted);">
                Critérios: <strong>P</strong>ontos > <strong>V</strong>itórias > <strong>S</strong>aldo de <strong>G</strong>ols > <strong>G</strong>ols <strong>P</strong>ró
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="sports-table">
            <thead>
                <tr>
                    <th class="center" style="width:60px;">Pos</th>
                    <th>Equipe / Campus</th>
                    <th class="center" style="background:#004d26;">P</th>
                    <th class="center">J</th>
                    <th class="center">V</th>
                    <th class="center">E</th>
                    <th class="center">D</th>
                    <th class="center">GP</th>
                    <th class="center">GC</th>
                    <th class="center">SG</th>
                    <th class="center">%</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tabela)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:40px;color:var(--text-muted);">
                            Nenhuma equipe registrada nesta modalidade ainda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $posicao = 1; foreach ($tabela as $row): ?>
                        <tr class="<?= $posicao === 1 ? 'leader' : '' ?>">
                            <td class="center" style="font-weight:900;color:<?= $posicao === 1 ? 'var(--primary)' : 'var(--text-muted)' ?>;">
                                <?= $posicao === 1 ? '🥇 ' . $posicao : $posicao ?>º
                            </td>
                            <td>
                                <strong><a href="equipes.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['nome']) ?></a></strong>
                                <span style="font-size:0.8rem;color:var(--text-muted);display:block;">
                                    <?= htmlspecialchars($row['sigla']) ?> - <?= htmlspecialchars($row['campus']) ?>
                                </span>
                            </td>
                            <td class="center" style="font-weight:900;font-size:1.15rem;color:var(--primary-dark);background:#f0fdf4;">
                                <?= $row['pontos'] ?>
                            </td>
                            <td class="center"><?= $row['jogos'] ?></td>
                            <td class="center"><?= $row['vitorias'] ?></td>
                            <td class="center"><?= $row['empates'] ?></td>
                            <td class="center"><?= $row['derrotas'] ?></td>
                            <td class="center"><?= $row['gols_pro'] ?></td>
                            <td class="center"><?= $row['gols_contra'] ?></td>
                            <td class="center" style="font-weight:700;color:<?= $row['saldo_gols'] >= 0 ? '#166534' : '#991b1b' ?>;">
                                <?= ($row['saldo_gols'] > 0 ? '+' : '') . $row['saldo_gols'] ?>
                            </td>
                            <td class="center" style="font-size:0.85rem;color:var(--text-muted);">
                                <?= $row['aproveitamento'] ?>%
                            </td>
                        </tr>
                    <?php $posicao++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- LEGENDA TÉCNICA -->
    <div style="margin-top:20px;font-size:0.8rem;color:var(--text-muted);display:flex;flex-wrap:wrap;gap:15px;background:#f8fafc;padding:12px 18px;border-radius:var(--radius-sm);border:1px solid var(--border);">
        <span><strong>P:</strong> Pontos Ganhos</span>
        <span><strong>J:</strong> Jogos Disputados</span>
        <span><strong>V:</strong> Vitórias (3 pts)</span>
        <span><strong>E:</strong> Empates (1 pt)</span>
        <span><strong>D:</strong> Derrotas (0 pt)</span>
        <span><strong>GP:</strong> Gols Pró</span>
        <span><strong>GC:</strong> Gols Contra</span>
        <span><strong>SG:</strong> Saldo de Gols</span>
        <span><strong>%:</strong> Aproveitamento</span>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>