<?php
/**
 * JIF+ — Listagem e Detalhes de Competições Esportivas
 * Requisito: RF05
 */
$pageTitle = 'Competições — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Consulta competições com dados da instituição organizadora
$stmt = $pdo->query("
    SELECT c.*, inst.nome AS instituicao_nome, inst.sigla AS instituicao_sigla, inst.campus AS instituicao_campus
    FROM competicoes c
    JOIN instituicoes inst ON c.instituicao_organizadora_id = inst.id
    ORDER BY c.ano DESC, c.data_inicio ASC
");
$competicoes = $stmt->fetchAll();
?>

<div class="container" style="padding: 40px 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">🏆 Competições dos Institutos Federais</h1>
            <p style="color:var(--text-muted);">Edições oficiais, cronogramas e etapas dos Jogos do IFRO e Região Norte</p>
        </div>
        <div>
            <input type="text" class="form-control" placeholder="🔍 Filtrar competições..." data-table-filter="#tableCompeticoes" style="width:250px;">
        </div>
    </div>

    <div class="grid-cards" style="margin-top:0;">
        <?php foreach ($competicoes as $c): ?>
            <?php
            // Modalidades desta competição
            $stmtMod = $pdo->prepare("SELECT nome, categoria FROM modalidades WHERE competicao_id = :id AND status = 'ativa'");
            $stmtMod->execute([':id' => $c['id']]);
            $mods = $stmtMod->fetchAll();

            $badgeStatus = match($c['status']) {
                'em_andamento' => ['classe' => 'badge-success', 'texto' => 'Em Andamento'],
                'planejada' => ['classe' => 'badge-warning', 'texto' => 'Planejada'],
                'encerrada' => ['classe' => 'badge-neutral', 'texto' => 'Encerrada'],
                default => ['classe' => 'badge-neutral', 'texto' => $c['status']]
            };
            ?>
            <div class="card" style="border-top:4px solid var(--primary);">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <span class="badge <?= $badgeStatus['classe'] ?>"><?= $badgeStatus['texto'] ?></span>
                    <span style="font-weight:800;color:var(--text-muted);font-size:1.1rem;"><?= $c['ano'] ?></span>
                </div>

                <h3 class="card-title"><?= htmlspecialchars($c['nome']) ?></h3>
                <div style="font-size:0.85rem;color:var(--primary-dark);font-weight:700;margin-bottom:10px;">
                    <?= htmlspecialchars($c['edicao']) ?>
                </div>

                <div style="font-size:0.9rem;color:var(--text-muted);margin-bottom:15px;line-height:1.7;">
                    <div>📍 <strong>Sede:</strong> <?= htmlspecialchars($c['local_sede']) ?></div>
                    <div>🏛️ <strong>Anfitrião:</strong> <?= htmlspecialchars($c['instituicao_sigla']) ?> - <?= htmlspecialchars($c['instituicao_campus']) ?></div>
                    <div>📅 <strong>Período:</strong> <?= date('d/m/Y', strtotime($c['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($c['data_fim'])) ?></div>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px;">
                    <div style="font-size:0.75rem;text-transform:uppercase;font-weight:700;color:var(--text-muted);margin-bottom:8px;">
                        Modalidades em Disputa (<?= count($mods) ?>)
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <?php foreach (array_slice($mods, 0, 4) as $m): ?>
                            <span class="badge badge-neutral" style="font-size:0.7rem;">
                                <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars(mb_substr($m['categoria'], 0, 1)) ?>)
                            </span>
                        <?php endforeach; ?>
                        <?php if (count($mods) > 4): ?>
                            <span class="badge badge-neutral" style="font-size:0.7rem;">+<?= count($mods) - 4 ?> mais</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <a href="partidas.php?competicao_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline" style="width:100%;">
                        Ver Jogos & Resultados &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>