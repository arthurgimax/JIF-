<?php
/**
 * JIF+ — Gestão de Modalidades Esportivas
 * Requisito: RF06
 */
define('BASE_PATH', '../');
$pageTitle = 'Gestão de Modalidades — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador']);

$pdo = Database::getConnection();
$erro = null;

// CADASTRO DE MODALIDADE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $competicaoId = (int)($_POST['competicao_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $categoria = $_POST['categoria'] ?? 'masculino';
    $tipo = $_POST['tipo'] ?? 'coletiva';

    if (empty($nome) || $competicaoId <= 0) {
        $erro = 'Informe o nome da modalidade e a competição vinculada.';
    } else {
        $stmtInsert = $pdo->prepare("
            INSERT INTO modalidades (competicao_id, nome, categoria, tipo, status)
            VALUES (:comp_id, :nome, :cat, :tipo, 'ativa')
        ");
        $stmtInsert->execute([
            ':comp_id' => $competicaoId,
            ':nome' => $nome,
            ':cat' => $categoria,
            ':tipo' => $tipo
        ]);
        $_SESSION['mensagem_sucesso'] = "Modalidade '{$nome}' cadastrada com sucesso!";
        header('Location: modalidades.php');
        exit;
    }
}

$competicoes = $pdo->query("SELECT id, nome, edicao FROM competicoes WHERE status != 'encerrada' ORDER BY ano DESC")->fetchAll();
$modalidades = $pdo->query("
    SELECT m.*, c.nome AS competicao_nome,
           (SELECT COUNT(*) FROM equipes eq WHERE eq.modalidade_id = m.id) AS total_equipes
    FROM modalidades m
    JOIN competicoes c ON m.competicao_id = c.id
    ORDER BY m.nome ASC, m.categoria ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">🏅 Gestão de Modalidades Esportivas</h1>
            <p style="color:var(--text-muted);">Cadastro de modalidades, formatos (coletivo/individual) e categorias disputadas</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 2fr;gap:30px;">
        
        <!-- FORMULÁRIO DE NOVA MODALIDADE -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">Nova Modalidade</h3>
            <form method="POST" action="modalidades.php">
                <input type="hidden" name="acao" value="cadastrar">

                <div class="form-group">
                    <label class="form-label" for="competicao_id">Competição</label>
                    <select id="competicao_id" name="competicao_id" class="form-control" required>
                        <?php foreach ($competicoes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?> (<?= htmlspecialchars($c['edicao']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nome">Nome do Esporte</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: Futsal, Voleibol, Atletismo" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="misto">Misto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tipo">Formato</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="coletiva">Coletiva (Times)</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Salvar Modalidade
                </button>
            </form>
        </div>

        <!-- LISTAGEM DE MODALIDADES -->
        <div>
            <div class="table-responsive" style="margin-top:0;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Modalidade</th>
                            <th>Categoria</th>
                            <th>Formato</th>
                            <th>Competição</th>
                            <th class="center">Times Inscritos</th>
                            <th class="center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modalidades as $m): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['nome']) ?></strong></td>
                                <td><span class="badge badge-info"><?= ucfirst($m['categoria']) ?></span></td>
                                <td><?= ucfirst($m['tipo']) ?></td>
                                <td style="font-size:0.85rem;color:var(--text-muted);"><?= htmlspecialchars($m['competicao_nome']) ?></td>
                                <td class="center" style="font-weight:700;"><?= $m['total_equipes'] ?></td>
                                <td class="center"><span class="badge badge-success"><?= ucfirst($m['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>