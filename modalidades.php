<?php
/**
 * JIF+ — Consulta de Modalidades Esportivas
 * Requisito: RF06
 */
$pageTitle = 'Modalidades — JIF+ Gestão Esportiva';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

$stmt = $pdo->query("
    SELECT m.*, c.nome AS competicao_nome, c.edicao AS competicao_edicao,
           (SELECT COUNT(*) FROM equipes eq WHERE eq.modalidade_id = m.id) AS total_equipes
    FROM modalidades m
    JOIN competicoes c ON m.competicao_id = c.id
    ORDER BY m.nome ASC, m.categoria ASC
");
$modalidades = $stmt->fetchAll();
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:30px;">
        <h1 style="font-size:2rem;font-weight:900;color:var(--dark);">🏅 Modalidades Esportivas Oficiais</h1>
        <p style="color:var(--text-muted);">Esportes coletivos e individuais homologados para os Jogos dos Institutos Federais</p>
    </div>

    <div class="grid-cards">
        <?php foreach ($modalidades as $m): ?>
            <?php
            $icone = match(mb_strtolower($m['nome'])) {
                'futsal' => '⚽',
                'voleibol' => '🏐',
                'basquetebol' => '🏀',
                'handebol' => '🤾',
                'xadrez' => '♟️',
                'atletismo 100m', 'atletismo' => '🏃',
                default => '🏅'
            };
            ?>
            <div class="card">
                <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px;">
                    <div style="width:50px;height:50px;background:#f1f5f9;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                        <?= $icone ?>
                    </div>
                    <div>
                        <h3 class="card-title" style="margin-bottom:2px;"><?= htmlspecialchars($m['nome']) ?></h3>
                        <span class="badge badge-info" style="text-transform:capitalize;">
                            Categoria <?= htmlspecialchars($m['categoria']) ?>
                        </span>
                    </div>
                </div>

                <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:15px;line-height:1.7;">
                    <div>🏆 <strong>Competição:</strong> <?= htmlspecialchars($m['competicao_nome']) ?></div>
                    <div>👥 <strong>Tipo:</strong> Modalidade <?= htmlspecialchars(ucfirst($m['tipo'])) ?></div>
                    <div>🛡️ <strong>Equipes Inscritas:</strong> <?= $m['total_equipes'] ?> times</div>
                </div>

                <div style="display:flex;gap:8px;margin-top:15px;">
                    <a href="classificacao.php?modalidade_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline" style="flex:1;">
                        Classificação
                    </a>
                    <a href="partidas.php?modalidade_id=<?= $m['id'] ?>" class="btn btn-sm btn-primary" style="flex:1;">
                        Partidas
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>