<?php
/**
 * JIF+ — Inscrição e Gestão de Atletas nas Equipes
 * Requisitos: RF08, RF09
 */
define('BASE_PATH', '../');
$pageTitle = 'Gestão de Atletas — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador', 'treinador']);

$pdo = Database::getConnection();
$erro = null;

// 1. CADASTRO DE NOVO ATLETA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_atleta') {
    $nome = trim($_POST['nome'] ?? '');
    $matricula = trim($_POST['matricula_estudantil'] ?? '');
    $instituicaoId = (int)($_POST['instituicao_id'] ?? 0);
    $dataNascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;

    if (empty($nome) || empty($matricula) || $instituicaoId <= 0) {
        $erro = 'Nome, matrícula e campus são campos obrigatórios.';
    } else {
        // Verifica se a matrícula já existe
        $stmtCheck = $pdo->prepare("SELECT id FROM atletas WHERE matricula_estudantil = :mat");
        $stmtCheck->execute([':mat' => $matricula]);
        if ($stmtCheck->fetch()) {
            $erro = 'Esta matrícula estudantil já está cadastrada para outro atleta.';
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO atletas (instituicao_id, nome, matricula_estudantil, data_nascimento)
                VALUES (:inst_id, :nome, :mat, :nasc)
            ");
            $stmtInsert->execute([
                ':inst_id' => $instituicaoId,
                ':nome' => $nome,
                ':mat' => $matricula,
                ':nasc' => $dataNascimento
            ]);
            $_SESSION['mensagem_sucesso'] = "Atleta '{$nome}' cadastrado com sucesso!";
            header('Location: atletas.php');
            exit;
        }
    }
}

// 2. VINCULAR ATLETA A UMA EQUIPE COM NÚMERO E POSIÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'vincular_equipe') {
    $equipeId = (int)($_POST['equipe_id'] ?? 0);
    $atletaId = (int)($_POST['atleta_id'] ?? 0);
    $numeroCamisa = !empty($_POST['numero_camisa']) ? (int)$_POST['numero_camisa'] : null;
    $posicao = trim($_POST['posicao'] ?? '');

    if ($equipeId <= 0 || $atletaId <= 0) {
        $erro = 'Selecione a equipe e o atleta para vincular.';
    } else {
        // Verifica se já está vinculado
        $stmtCheckVinculo = $pdo->prepare("SELECT id FROM equipe_atletas WHERE equipe_id = :eq AND atleta_id = :at");
        $stmtCheckVinculo->execute([':eq' => $equipeId, ':at' => $atletaId]);
        if ($stmtCheckVinculo->fetch()) {
            $erro = 'Este atleta já está inscrito nesta equipe.';
        } else {
            $stmtVinculo = $pdo->prepare("
                INSERT INTO equipe_atletas (equipe_id, atleta_id, numero_camisa, posicao, status_inscricao)
                VALUES (:eq, :at, :num, :pos, 'regular')
            ");
            $stmtVinculo->execute([
                ':eq' => $equipeId,
                ':at' => $atletaId,
                ':num' => $numeroCamisa,
                ':pos' => $posicao
            ]);
            $_SESSION['mensagem_sucesso'] = "Atleta vinculado à equipe com sucesso!";
            header('Location: atletas.php');
            exit;
        }
    }
}

// Dados para os selects
$instituicoes = $pdo->query("SELECT id, sigla, campus FROM instituicoes ORDER BY campus ASC")->fetchAll();
$equipes = $pdo->query("SELECT eq.id, eq.nome, inst.campus FROM equipes eq JOIN instituicoes inst ON eq.instituicao_id = inst.id ORDER BY eq.nome ASC")->fetchAll();
$todosAtletas = $pdo->query("SELECT id, nome, matricula_estudantil FROM atletas ORDER BY nome ASC")->fetchAll();

// Listagem de atletas cadastrados com suas equipes
$atletasCadastrados = $pdo->query("
    SELECT a.*, inst.campus, inst.sigla,
           (SELECT COUNT(*) FROM equipe_atletas ea WHERE ea.atleta_id = a.id) AS total_equipes
    FROM atletas a
    JOIN instituicoes inst ON a.instituicao_id = inst.id
    ORDER BY a.nome ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">🏃‍♂️ Cadastro e Inscrição de Atletas</h1>
            <p style="color:var(--text-muted);">Registro de estudantes-atletas e escalação em equipes dos jogos</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 1fr;gap:25px;margin-bottom:40px;">
        
        <!-- FORMULÁRIO 1: NOVO ATLETA -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">1. Cadastrar Novo Atleta</h3>
            <form method="POST" action="atletas.php">
                <input type="hidden" name="acao" value="cadastrar_atleta">

                <div class="form-group">
                    <label class="form-label" for="nome">Nome Completo do Estudante</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: Arthur Henrique Gimax" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="matricula_estudantil">Matrícula Estudantil (BID)</label>
                    <input type="text" id="matricula_estudantil" name="matricula_estudantil" class="form-control" placeholder="ex.: 2024101001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="instituicao_id">Campus de Origem</label>
                    <select id="instituicao_id" name="instituicao_id" class="form-control" required>
                        <?php foreach ($instituicoes as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['sigla']) ?> - <?= htmlspecialchars($i['campus']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Salvar Dados do Atleta
                </button>
            </form>
        </div>

        <!-- FORMULÁRIO 2: VINCULAR ATLETA A UMA EQUIPE -->
        <div class="card" style="border-left:4px solid var(--secondary);">
            <h3 class="card-title" style="margin-bottom:15px;">2. Inscrever Atleta na Equipe</h3>
            <form method="POST" action="atletas.php">
                <input type="hidden" name="acao" value="vincular_equipe">

                <div class="form-group">
                    <label class="form-label" for="atleta_id">Selecione o Atleta</label>
                    <select id="atleta_id" name="atleta_id" class="form-control" required>
                        <option value="">-- Escolha um atleta --</option>
                        <?php foreach ($todosAtletas as $at): ?>
                            <option value="<?= $at['id'] ?>"><?= htmlspecialchars($at['nome']) ?> (<?= htmlspecialchars($at['matricula_estudantil']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="equipe_id">Selecione a Equipe</label>
                    <select id="equipe_id" name="equipe_id" class="form-control" required>
                        <option value="">-- Escolha a equipe --</option>
                        <?php foreach ($equipes as $eq): ?>
                            <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nome']) ?> (<?= htmlspecialchars($eq['campus']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;">
                    <div class="form-group">
                        <label class="form-label" for="numero_camisa">Nº Camisa</label>
                        <input type="number" id="numero_camisa" name="numero_camisa" class="form-control" placeholder="ex.: 10">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="posicao">Posição</label>
                        <input type="text" id="posicao" name="posicao" class="form-control" placeholder="ex.: Pivô, Ala, Goleiro">
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary" style="width:100%;margin-top:10px;">
                    Efetivar Inscrição na Equipe
                </button>
            </form>
        </div>

    </div>

    <!-- LISTAGEM DE ATLETAS CADASTRADOS -->
    <h2 style="font-size:1.3rem;font-weight:800;color:var(--dark);margin-bottom:15px;">
        📋 Relação Geral de Atletas Cadastrados (<?= count($atletasCadastrados) ?>)
    </h2>

    <div class="table-responsive">
        <table class="sports-table">
            <thead>
                <tr>
                    <th>Nome do Estudante-Atleta</th>
                    <th>Matrícula / BID</th>
                    <th>Campus</th>
                    <th>Data Nascimento</th>
                    <th class="center">Times Inscritos</th>
                    <th class="center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($atletasCadastrados as $a): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($a['nome']) ?></strong></td>
                        <td><code><?= htmlspecialchars($a['matricula_estudantil']) ?></code></td>
                        <td><?= htmlspecialchars($a['campus']) ?></td>
                        <td><?= $a['data_nascimento'] ? date('d/m/Y', strtotime($a['data_nascimento'])) : '-' ?></td>
                        <td class="center" style="font-weight:700;"><?= $a['total_equipes'] ?></td>
                        <td class="center">
                            <a href="../atletas.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline">
                                Ver BID &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>