<?php
/**
 * JIF+ — Gestão de Competições Esportivas
 * Requisito: RF05
 */
define('BASE_PATH', '../');
$pageTitle = 'Gestão de Competições — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador']);

$pdo = Database::getConnection();
$erro = null;

// CADASTRO DE COMPETIÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome'] ?? '');
    $edicao = trim($_POST['edicao'] ?? '');
    $ano = (int)($_POST['ano'] ?? date('Y'));
    $instituicaoId = (int)($_POST['instituicao_organizadora_id'] ?? 0);
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $localSede = trim($_POST['local_sede'] ?? '');
    $status = $_POST['status'] ?? 'planejada';

    if (empty($nome) || empty($edicao) || empty($dataInicio) || empty($dataFim) || empty($localSede) || $instituicaoId <= 0) {
        $erro = 'Todos os campos são obrigatórios.';
    } else {
        $stmtInsert = $pdo->prepare("
            INSERT INTO competicoes (instituicao_organizadora_id, nome, edicao, ano, data_inicio, data_fim, local_sede, status)
            VALUES (:inst_id, :nome, :edicao, :ano, :data_ini, :data_fim, :local, :status)
        ");
        $stmtInsert->execute([
            ':inst_id' => $instituicaoId,
            ':nome' => $nome,
            ':edicao' => $edicao,
            ':ano' => $ano,
            ':data_ini' => $dataInicio,
            ':data_fim' => $dataFim,
            ':local' => $localSede,
            ':status' => $status
        ]);
        $_SESSION['mensagem_sucesso'] = "Competição '{$nome}' cadastrada com sucesso!";
        header('Location: competicoes.php');
        exit;
    }
}

// ATUALIZAÇÃO RÁPIDA DE STATUS
if (isset($_GET['status_id']) && isset($_GET['novo_status'])) {
    $cId = (int)$_GET['status_id'];
    $novoStatus = $_GET['novo_status'];
    if (in_array($novoStatus, ['planejada', 'em_andamento', 'encerrada'], true)) {
        $stmtUp = $pdo->prepare("UPDATE competicoes SET status = :status WHERE id = :id");
        $stmtUp->execute([':status' => $novoStatus, ':id' => $cId]);
        $_SESSION['mensagem_sucesso'] = "Status da competição atualizado!";
    }
    header('Location: competicoes.php');
    exit;
}

$instituicoes = $pdo->query("SELECT id, sigla, campus FROM instituicoes ORDER BY campus ASC")->fetchAll();
$competicoes = $pdo->query("
    SELECT c.*, inst.sigla AS inst_sigla, inst.campus AS inst_campus,
           (SELECT COUNT(*) FROM modalidades m WHERE m.competicao_id = c.id) AS total_mods,
           (SELECT COUNT(*) FROM partidas p WHERE p.competicao_id = c.id) AS total_jogos
    FROM competicoes c
    JOIN instituicoes inst ON c.instituicao_organizadora_id = inst.id
    ORDER BY c.ano DESC, c.data_inicio ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">🏆 Gerenciamento de Competições</h1>
            <p style="color:var(--text-muted);">Configuração de torneios, edições e períodos dos Jogos dos Institutos Federais</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 2fr;gap:30px;">
        
        <!-- FORMULÁRIO DE NOVA COMPETIÇÃO -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">Nova Competição</h3>
            <form method="POST" action="competicoes.php">
                <input type="hidden" name="acao" value="cadastrar">

                <div class="form-group">
                    <label class="form-label" for="nome">Título da Competição</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: JIFRO 2026" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edicao">Edição</label>
                    <input type="text" id="edicao" name="edicao" class="form-control" placeholder="ex.: Etapa Estadual Rondônia" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ano">Ano</label>
                    <input type="number" id="ano" name="ano" class="form-control" value="<?= date('Y') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="instituicao_organizadora_id">Instituição Organizadora / Anfitriã</label>
                    <select id="instituicao_organizadora_id" name="instituicao_organizadora_id" class="form-control" required>
                        <?php foreach ($instituicoes as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['sigla']) ?> - <?= htmlspecialchars($i['campus']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="local_sede">Cidade / Local Sede</label>
                    <input type="text" id="local_sede" name="local_sede" class="form-control" placeholder="ex.: Porto Velho - RO" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="form-group">
                        <label class="form-label" for="data_inicio">Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="data_fim">Término</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Situação Inicial</label>
                    <select id="status" name="status" class="form-control">
                        <option value="planejada" selected>Planejada</option>
                        <option value="em_andamento">Em Andamento</option>
                        <option value="encerrada">Encerrada</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Cadastrar Competição
                </button>
            </form>
        </div>

        <!-- LISTAGEM DE COMPETIÇÕES -->
        <div>
            <div class="table-responsive" style="margin-top:0;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Competição / Edição</th>
                            <th>Anfitrião / Sede</th>
                            <th>Período</th>
                            <th class="center">Modalidades</th>
                            <th class="center">Status</th>
                            <th class="center">Alterar Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competicoes as $c): ?>
                            <?php
                            $badgeSt = match($c['status']) {
                                'em_andamento' => 'badge-success',
                                'planejada' => 'badge-warning',
                                'encerrada' => 'badge-neutral',
                                default => 'badge-neutral'
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($c['nome']) ?></strong><br>
                                    <span style="font-size:0.8rem;color:var(--text-muted);"><?= htmlspecialchars($c['edicao']) ?> (<?= $c['ano'] ?>)</span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['inst_sigla']) ?> - <?= htmlspecialchars($c['inst_campus']) ?><br>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">📍 <?= htmlspecialchars($c['local_sede']) ?></span>
                                </td>
                                <td style="font-size:0.85rem;">
                                    <?= date('d/m/Y', strtotime($c['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($c['data_fim'])) ?>
                                </td>
                                <td class="center" style="font-weight:700;">
                                    <?= $c['total_mods'] ?>
                                </td>
                                <td class="center">
                                    <span class="badge <?= $badgeSt ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span>
                                </td>
                                <td class="center">
                                    <div style="display:flex;gap:4px;justify-content:center;">
                                        <a href="competicoes.php?status_id=<?= $c['id'] ?>&novo_status=em_andamento" class="btn btn-sm btn-outline" title="Iniciar">▶️</a>
                                        <a href="competicoes.php?status_id=<?= $c['id'] ?>&novo_status=encerrada" class="btn btn-sm btn-outline" title="Encerrar">🏁</a>
                                    </div>
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