<?php
/**
 * JIF+ — Súmula Eletrônica Oficial e Registro de Estatísticas
 * Requisitos: RF11, RF12, RF16, RF17, UC08
 */
define('BASE_PATH', '../');
$pageTitle = 'Súmula Eletrônica — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador']);

$pdo = Database::getConnection();
$erro = null;

$partidaId = isset($_REQUEST['partida_id']) ? (int)$_REQUEST['partida_id'] : 0;

// 1. ATUALIZAR PLACAR E SITUAÇÃO DA PARTIDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'salvar_placar') {
    $pId = (int)$_POST['partida_id'];
    $placarA = is_numeric($_POST['placar_a'] ?? '') ? (int)$_POST['placar_a'] : null;
    $placarB = is_numeric($_POST['placar_b'] ?? '') ? (int)$_POST['placar_b'] : null;
    $situacao = $_POST['situacao'] ?? 'agendada';

    $stmtUp = $pdo->prepare("
        UPDATE partidas
        SET placar_a = :pa, placar_b = :pb, situacao = :sit
        WHERE id = :id
    ");
    $stmtUp->execute([
        ':pa' => $placarA,
        ':pb' => $placarB,
        ':sit' => $situacao,
        ':id' => $pId
    ]);

    $_SESSION['mensagem_sucesso'] = "Placar e situação da partida atualizados com sucesso!";
    header("Location: sumula.php?partida_id={$pId}");
    exit;
}

// 2. LANÇAR NOVO EVENTO INDIVIDUAL (GOL, CARTÃO, FALTA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'lancar_evento') {
    $pId = (int)$_POST['partida_id'];
    $atletaId = (int)$_POST['atleta_id'];
    $equipeId = (int)$_POST['equipe_id'];
    $tipo = $_POST['tipo_estatistica'];
    $valor = (int)($_POST['valor'] ?? 1);

    if ($atletaId <= 0 || $equipeId <= 0 || empty($tipo)) {
        $erro = 'Selecione a equipe, o atleta e o tipo de evento.';
    } else {
        $stmtEv = $pdo->prepare("
            INSERT INTO estatisticas_atletas (partida_id, atleta_id, equipe_id, tipo_estatistica, valor)
            VALUES (:pid, :aid, :eid, :tipo, :val)
        ");
        $stmtEv->execute([
            ':pid' => $pId,
            ':aid' => $atletaId,
            ':eid' => $equipeId,
            ':tipo' => $tipo,
            ':val' => $valor
        ]);

        $_SESSION['mensagem_sucesso'] = "Evento esportivo registrado na súmula com sucesso!";
        header("Location: sumula.php?partida_id={$pId}");
        exit;
    }
}

// 3. REMOVER EVENTO DA SÚMULA
if (isset($_GET['remover_evento']) && $partidaId > 0) {
    $eventoId = (int)$_GET['remover_evento'];
    $stmtDel = $pdo->prepare("DELETE FROM estatisticas_atletas WHERE id = :id AND partida_id = :pid");
    $stmtDel->execute([':id' => $eventoId, ':pid' => $partidaId]);

    $_SESSION['mensagem_sucesso'] = "Evento removido da súmula.";
    header("Location: sumula.php?partida_id={$partidaId}");
    exit;
}

// Busca todas as partidas para a seleção
$todasPartidas = $pdo->query("
    SELECT p.id, p.data_hora, p.fase_rodada,
           m.nome AS mod_nome,
           eq_a.nome AS time_a, eq_b.nome AS time_b
    FROM partidas p
    JOIN modalidades m ON p.modalidade_id = m.id
    JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
    JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
    ORDER BY p.data_hora DESC
")->fetchAll();

// Se uma partida foi selecionada, carrega seus dados e elencos
$partidaAtual = null;
$eventosSúmula = [];
$atletasTimeA = [];
$atletasTimeB = [];

if ($partidaId > 0) {
    $stmtP = $pdo->prepare("
        SELECT p.*,
               c.nome AS comp_nome,
               m.nome AS mod_nome, m.categoria AS mod_cat,
               eq_a.id AS id_a, eq_a.nome AS time_a, inst_a.campus AS campus_a,
               eq_b.id AS id_b, eq_b.nome AS time_b, inst_b.campus AS campus_b
        FROM partidas p
        JOIN competicoes c ON p.competicao_id = c.id
        JOIN modalidades m ON p.modalidade_id = m.id
        JOIN equipes eq_a ON p.equipe_a_id = eq_a.id
        JOIN instituicoes inst_a ON eq_a.instituicao_id = inst_a.id
        JOIN equipes eq_b ON p.equipe_b_id = eq_b.id
        JOIN instituicoes inst_b ON eq_b.instituicao_id = inst_b.id
        WHERE p.id = :id
    ");
    $stmtP->execute([':id' => $partidaId]);
    $partidaAtual = $stmtP->fetch();

    if ($partidaAtual) {
        // Eventos já registrados
        $stmtEvs = $pdo->prepare("
            SELECT ea.*, a.nome AS atleta_nome, a.matricula_estudantil, eq.nome AS equipe_nome
            FROM estatisticas_atletas ea
            JOIN atletas a ON ea.atleta_id = a.id
            JOIN equipes eq ON ea.equipe_id = eq.id
            WHERE ea.partida_id = :pid
            ORDER BY ea.id DESC
        ");
        $stmtEvs->execute([':pid' => $partidaId]);
        $eventosSúmula = $stmtEvs->fetchAll();

        // Atletas de cada equipe
        $stmtAtA = $pdo->prepare("
            SELECT a.id, a.nome, ea.numero_camisa
            FROM equipe_atletas ea
            JOIN atletas a ON ea.atleta_id = a.id
            WHERE ea.equipe_id = :eid
            ORDER BY ea.numero_camisa ASC, a.nome ASC
        ");
        $stmtAtA->execute([':eid' => $partidaAtual['id_a']]);
        $atletasTimeA = $stmtAtA->fetchAll();

        $stmtAtB = $pdo->prepare("
            SELECT a.id, a.nome, ea.numero_camisa
            FROM equipe_atletas ea
            JOIN atletas a ON ea.atleta_id = a.id
            WHERE ea.equipe_id = :eid
            ORDER BY ea.numero_camisa ASC, a.nome ASC
        ");
        $stmtAtB->execute([':eid' => $partidaAtual['id_b']]);
        $atletasTimeB = $stmtAtB->fetchAll();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">📝 Súmula Eletrônica e Homologação</h1>
            <p style="color:var(--text-muted);">Registro oficial de placar, gols, advertências disciplinares e encerramento de partidas</p>
        </div>

        <!-- SELETOR DE PARTIDA -->
        <form method="GET" action="sumula.php" style="display:flex;gap:10px;">
            <select name="partida_id" class="form-control" style="width:340px;font-weight:700;" onchange="this.form.submit()">
                <option value="0">-- Selecione a Partida --</option>
                <?php foreach ($todasPartidas as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $partidaId === (int)$p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['time_a']) ?> vs <?= htmlspecialchars($p['time_b']) ?> (<?= date('d/m', strtotime($p['data_hora'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <?php if (!$partidaAtual): ?>
        <div class="card" style="text-align:center;padding:60px 20px;color:var(--text-muted);">
            <div style="font-size:3rem;margin-bottom:10px;">📋</div>
            <h3>Selecione uma partida acima para abrir a súmula eletrônica oficial.</h3>
        </div>
    <?php else: ?>

        <!-- CABEÇALHO DA PARTIDA E FORMULÁRIO DE PLACAR -->
        <div class="card" style="margin-bottom:30px;border-top:4px solid var(--primary);">
            <div style="text-align:center;margin-bottom:20px;">
                <span class="badge badge-info"><?= htmlspecialchars($partidaAtual['mod_nome']) ?> • <?= htmlspecialchars($partidaAtual['fase_rodada']) ?></span>
                <h2 style="font-size:1.6rem;font-weight:900;color:var(--dark);margin-top:6px;">
                    <?= htmlspecialchars($partidaAtual['time_a']) ?> vs <?= htmlspecialchars($partidaAtual['time_b']) ?>
                </h2>
                <div style="font-size:0.85rem;color:var(--text-muted);">
                    📍 <?= htmlspecialchars($partidaAtual['local_quadra']) ?> • 🕒 <?= date('d/m/Y H:i', strtotime($partidaAtual['data_hora'])) ?>
                </div>
            </div>

            <!-- FORMULÁRIO DO PLACAR -->
            <form method="POST" action="sumula.php" style="max-width:650px;margin:0 auto;background:#f8fafc;padding:20px;border-radius:var(--radius-md);border:1px solid var(--border);">
                <input type="hidden" name="acao" value="salvar_placar">
                <input type="hidden" name="partida_id" value="<?= $partidaAtual['id'] ?>">

                <div style="display:flex;align-items:center;justify-content:center;gap:20px;">
                    <div style="text-align:center;">
                        <label class="form-label" style="font-size:0.8rem;"><?= htmlspecialchars($partidaAtual['time_a']) ?></label>
                        <input type="number" name="placar_a" class="form-control" style="font-size:1.8rem;font-weight:900;text-align:center;width:90px;" value="<?= $partidaAtual['placar_a'] !== null ? $partidaAtual['placar_a'] : '' ?>" min="0">
                    </div>

                    <div style="font-size:2rem;font-weight:900;color:var(--text-muted);padding-top:20px;">X</div>

                    <div style="text-align:center;">
                        <label class="form-label" style="font-size:0.8rem;"><?= htmlspecialchars($partidaAtual['time_b']) ?></label>
                        <input type="number" name="placar_b" class="form-control" style="font-size:1.8rem;font-weight:900;text-align:center;width:90px;" value="<?= $partidaAtual['placar_b'] !== null ? $partidaAtual['placar_b'] : '' ?>" min="0">
                    </div>
                </div>

                <div style="display:flex;justify-content:center;align-items:center;gap:15px;margin-top:20px;">
                    <label style="font-size:0.9rem;font-weight:700;">Situação da Partida:</label>
                    <select name="situacao" class="form-control" style="width:180px;">
                        <option value="agendada" <?= $partidaAtual['situacao'] === 'agendada' ? 'selected' : '' ?>>Agendada</option>
                        <option value="em_andamento" <?= $partidaAtual['situacao'] === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="encerrada" <?= $partidaAtual['situacao'] === 'encerrada' ? 'selected' : '' ?>>Encerrada (Homologar)</option>
                        <option value="cancelada" <?= $partidaAtual['situacao'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm">
                        💾 Atualizar Placar
                    </button>
                </div>
            </form>
        </div>

        <!-- LANÇAMENTO DE EVENTOS DA SÚMULA (GOLS E CARTÕES) -->
        <div style="display:grid;grid-template-columns: 1fr 1.5fr;gap:30px;">
            
            <!-- FORMULÁRIO DE NOVO EVENTO -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:15px;">Lançar Evento Esportivo</h3>
                <form method="POST" action="sumula.php">
                    <input type="hidden" name="acao" value="lancar_evento">
                    <input type="hidden" name="partida_id" value="<?= $partidaAtual['id'] ?>">

                    <div class="form-group">
                        <label class="form-label" for="equipe_id">Equipe do Evento</label>
                        <select id="equipe_id" name="equipe_id" class="form-control" required onchange="atualizarAtletas(this.value)">
                            <option value="">-- Selecione a Equipe --</option>
                            <option value="<?= $partidaAtual['id_a'] ?>"><?= htmlspecialchars($partidaAtual['time_a']) ?></option>
                            <option value="<?= $partidaAtual['id_b'] ?>"><?= htmlspecialchars($partidaAtual['time_b']) ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="atleta_id">Estudante-Atleta</label>
                        <select id="atleta_id" name="atleta_id" class="form-control" required>
                            <option value="">-- Escolha o atleta --</option>
                            <!-- Populado dinamicamente ou com todos os atletas dos 2 times -->
                            <optgroup label="<?= htmlspecialchars($partidaAtual['time_a']) ?>">
                                <?php foreach ($atletasTimeA as $at): ?>
                                    <option value="<?= $at['id'] ?>" data-equipe="<?= $partidaAtual['id_a'] ?>">
                                        #<?= $at['numero_camisa'] ?? '-' ?> <?= htmlspecialchars($at['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="<?= htmlspecialchars($partidaAtual['time_b']) ?>">
                                <?php foreach ($atletasTimeB as $bt): ?>
                                    <option value="<?= $bt['id'] ?>" data-equipe="<?= $partidaAtual['id_b'] ?>">
                                        #<?= $bt['numero_camisa'] ?? '-' ?> <?= htmlspecialchars($bt['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo_estatistica">Tipo do Evento</label>
                        <select id="tipo_estatistica" name="tipo_estatistica" class="form-control" required>
                            <option value="gol">⚽ Gol Marcado</option>
                            <option value="ponto">🏀 Ponto Marcado</option>
                            <option value="cartao_amarelo">🟨 Cartão Amarelo</option>
                            <option value="cartao_vermelho">🟥 Cartão Vermelho</option>
                            <option value="falta">⚠️ Falta Cometida</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="valor">Quantidade</label>
                        <input type="number" id="valor" name="valor" class="form-control" value="1" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width:100%;margin-top:10px;">
                        ➕ Registrar na Súmula
                    </button>
                </form>
            </div>

            <!-- TABELA DE EVENTOS DA SÚMULA -->
            <div>
                <h3 style="font-size:1.2rem;font-weight:800;color:var(--dark);margin-bottom:12px;">
                    📜 Registro de Eventos da Súmula (<?= count($eventosSúmula) ?>)
                </h3>

                <div class="table-responsive" style="margin-top:0;">
                    <table class="sports-table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Atleta</th>
                                <th>Equipe</th>
                                <th class="center">Qtd</th>
                                <th class="center">Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($eventosSúmula)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">
                                        Nenhum evento lançado para esta súmula.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($eventosSúmula as $ev): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-neutral" style="font-weight:700;">
                                                <?= ucfirst(str_replace('_', ' ', $ev['tipo_estatistica'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ev['atleta_nome']) ?></strong>
                                        </td>
                                        <td style="font-size:0.85rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($ev['equipe_nome']) ?>
                                        </td>
                                        <td class="center" style="font-weight:800;"><?= $ev['valor'] ?></td>
                                        <td class="center">
                                            <a href="sumula.php?partida_id=<?= $partidaAtual['id'] ?>&remover_evento=<?= $ev['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Deseja remover este evento da súmula?">
                                                ✕
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
