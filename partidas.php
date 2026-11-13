<?php
/**
 * JIF+ — Agendamento e Programação de Partidas
 * Requisito: RF10
 */
define('BASE_PATH', '../');
$pageTitle = 'Agendar Partidas — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador']);

$pdo = Database::getConnection();
$erro = null;

// AGENDAR NOVA PARTIDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'agendar') {
    $competicaoId = (int)($_POST['competicao_id'] ?? 0);
    $modalidadeId = (int)($_POST['modalidade_id'] ?? 0);
    $equipeAId = (int)($_POST['equipe_a_id'] ?? 0);
    $equipeBId = (int)($_POST['equipe_b_id'] ?? 0);
    $faseRodada = trim($_POST['fase_rodada'] ?? '');
    $dataHora = trim($_POST['data_hora'] ?? '');
    $localQuadra = trim($_POST['local_quadra'] ?? '');

    if ($competicaoId <= 0 || $modalidadeId <= 0 || $equipeAId <= 0 || $equipeBId <= 0 || empty($faseRodada) || empty($dataHora) || empty($localQuadra)) {
        $erro = 'Todos os campos são obrigatórios para agendar a partida.';
    } elseif ($equipeAId === $equipeBId) {
        $erro = 'A equipe mandante e visitante não podem ser a mesma equipe.';
    } else {
        // Validação de regras de negócio: certifica que as equipes pertencem à mesma modalidade
        $stmtValida = $pdo->prepare("SELECT id FROM equipes WHERE id IN (:a, :b) AND modalidade_id = :mod");
        $stmtValida->execute([':a' => $equipeAId, ':b' => $equipeBId, ':mod' => $modalidadeId]);
        if (count($stmtValida->fetchAll()) < 2) {
            $erro = 'As equipes selecionadas devem pertencer à modalidade escolhida.';
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO partidas (competicao_id, modalidade_id, equipe_a_id, equipe_b_id, fase_rodada, data_hora, local_quadra, situacao)
                VALUES (:comp, :mod, :ea, :eb, :fase, :data_hora, :local, 'agendada')
            ");
            $stmtInsert->execute([
                ':comp' => $competicaoId,
                ':mod' => $modalidadeId,
                ':ea' => $equipeAId,
                ':eb' => $equipeBId,
                ':fase' => $faseRodada,
                ':data_hora' => $dataHora,
                ':local' => $localQuadra
            ]);
            $_SESSION['mensagem_sucesso'] = "Partida agendada com sucesso!";
            header('Location: partidas.php');
            exit;
        }
    }
}

$competicoes = $pdo->query("SELECT id, nome, edicao FROM competicoes WHERE status != 'encerrada' ORDER BY ano DESC")->fetchAll();
$modalidades = $pdo->query("SELECT id, nome, categoria FROM modalidades WHERE status = 'ativa' ORDER BY nome ASC")->fetchAll();
$equipes = $pdo->query("
    SELECT eq.id, eq.nome, eq.modalidade_id, inst.campus, m.nome AS modalidade_nome
    FROM equipes eq
    JOIN instituicoes inst ON eq.instituicao_id = inst.id
    JOIN modalidades m ON eq.modalidade_id = m.id
    ORDER BY eq.nome ASC
")->fetchAll();

$partidas = $pdo->query("
    SELECT p.*,
           c.nome AS comp_nome,
           m.nome AS mod_nome, m.categoria AS mod_cat,
           eq_a.nome AS time_a, eq_b.nome AS time_b
    FROM partidas p
    JOIN competicoes c ON p.competicao_id = c.id
    JOIN modalidades m ON p.modalidade_id = m.id
    JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
    JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
    ORDER BY p.data_hora DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">⏱️ Programação de Partidas</h1>
            <p style="color:var(--text-muted);">Agendamento oficial de confrontos, fases e horários dos jogos</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 2fr;gap:30px;">
        
        <!-- FORMULÁRIO DE AGENDAMENTO -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">Agendar Nova Partida</h3>
            <form method="POST" action="partidas.php">
                <input type="hidden" name="acao" value="agendar">

                <div class="form-group">
                    <label class="form-label" for="competicao_id">Competição</label>
                    <select id="competicao_id" name="competicao_id" class="form-control" required>
                        <?php foreach ($competicoes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="modalidade_id">Modalidade</label>
                    <select id="modalidade_id" name="modalidade_id" class="form-control" required>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['categoria']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="equipe_a_id">Equipe Mandante</label>
                    <select id="equipe_a_id" name="equipe_a_id" class="form-control" required>
                        <option value="">-- Selecione o time mandante --</option>
                        <?php foreach ($equipes as $eq): ?>
                            <option value="<?= $eq['id'] ?>" data-mod="<?= $eq['modalidade_id'] ?>">
                                <?= htmlspecialchars($eq['nome']) ?> (<?= htmlspecialchars($eq['modalidade_nome']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="equipe_b_id">Equipe Visitante</label>
                    <select id="equipe_b_id" name="equipe_b_id" class="form-control" required>
                        <option value="">-- Selecione o time visitante --</option>
                        <?php foreach ($equipes as $eq): ?>
                            <option value="<?= $eq['id'] ?>" data-mod="<?= $eq['modalidade_id'] ?>">
                                <?= htmlspecialchars($eq['nome']) ?> (<?= htmlspecialchars($eq['modalidade_nome']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fase_rodada">Fase / Rodada</label>
                    <input type="text" id="fase_rodada" name="fase_rodada" class="form-control" placeholder="ex.: Fase de Grupos - Rodada 1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_hora">Data e Horário</label>
                    <input type="datetime-local" id="data_hora" name="data_hora" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="local_quadra">Local / Quadra</label>
                    <input type="text" id="local_quadra" name="local_quadra" class="form-control" placeholder="ex.: Ginásio Poliesportivo Calama" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Confirmar Agendamento
                </button>
            </form>
        </div>

        <!-- LISTAGEM DE JOGOS CADASTRADOS -->
        <div>
            <div class="table-responsive" style="margin-top:0;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Partida / Confronto</th>
                            <th>Modalidade / Fase</th>
                            <th>Data e Hora</th>
                            <th class="center">Placar</th>
                            <th class="center">Status</th>
                            <th class="center">Súmula</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partidas as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['time_a']) ?></strong><br>
                                    <span style="font-size:0.8rem;color:var(--text-muted);">vs <?= htmlspecialchars($p['time_b']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['mod_nome']) ?> (<?= htmlspecialchars($p['mod_cat']) ?>)<br>
                                    <span style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($p['fase_rodada']) ?></span>
                                </td>
                                <td style="font-size:0.85rem;">
                                    <?= date('d/m/Y H:i', strtotime($p['data_hora'])) ?>
                                </td>
                                <td class="center" style="font-weight:800;">
                                    <?= $p['placar_a'] !== null ? $p['placar_a'] . ' x ' . $p['placar_b'] : '-' ?>
                                </td>
                                <td class="center">
                                    <span class="badge <?= $p['situacao'] === 'encerrada' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ucfirst($p['situacao']) ?>
                                    </span>
                                </td>
                                <td class="center">
                                    <a href="sumula.php?partida_id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">
                                        Súmula
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
