<?php
/**
 * JIF+ — Gestão de Equipes Esportivas
 * Requisito: RF07
 */
define('BASE_PATH', '../');
$pageTitle = 'Gestão de Equipes — JIF+';
require_once __DIR__ . '/../config/auth.php';

exigirAutenticacao(['admin', 'organizador', 'treinador']);

$usuario = obterUsuarioLogado();
$pdo = Database::getConnection();
$erro = null;

// CADASTRO DE EQUIPE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome'] ?? '');
    $instituicaoId = (int)($_POST['instituicao_id'] ?? 0);
    $competicaoId = (int)($_POST['competicao_id'] ?? 0);
    $modalidadeId = (int)($_POST['modalidade_id'] ?? 0);
    
    // Se for treinador, ele mesmo é o responsável; se for admin, pode selecionar
    $responsavelId = ($usuario['perfil'] === 'treinador') ? $usuario['id'] : (int)($_POST['responsavel_id'] ?? $usuario['id']);

    if (empty($nome) || $instituicaoId <= 0 || $competicaoId <= 0 || $modalidadeId <= 0) {
        $erro = 'Todos os campos são obrigatórios.';
    } else {
        // Validação de regra de negócio: previne duplicidade de equipe do mesmo campus na mesma modalidade/competição
        $stmtCheck = $pdo->prepare("
            SELECT id FROM equipes
            WHERE instituicao_id = :inst_id AND competicao_id = :comp_id AND modalidade_id = :mod_id
        ");
        $stmtCheck->execute([
            ':inst_id' => $instituicaoId,
            ':comp_id' => $competicaoId,
            ':mod_id' => $modalidadeId
        ]);

        if ($stmtCheck->fetch()) {
            $erro = 'Já existe uma equipe deste campus inscrita nesta mesma modalidade e competição.';
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO equipes (instituicao_id, competicao_id, modalidade_id, responsavel_id, nome)
                VALUES (:inst_id, :comp_id, :mod_id, :resp_id, :nome)
            ");
            $stmtInsert->execute([
                ':inst_id' => $instituicaoId,
                ':comp_id' => $competicaoId,
                ':mod_id' => $modalidadeId,
                ':resp_id' => $responsavelId,
                ':nome' => $nome
            ]);
            $_SESSION['mensagem_sucesso'] = "Equipe '{$nome}' cadastrada com sucesso!";
            header('Location: equipes.php');
            exit;
        }
    }
}

// Opções para selects
$instituicoes = $pdo->query("SELECT id, sigla, campus FROM instituicoes ORDER BY campus ASC")->fetchAll();
$competicoes = $pdo->query("SELECT id, nome, edicao FROM competicoes WHERE status != 'encerrada' ORDER BY ano DESC")->fetchAll();
$modalidades = $pdo->query("SELECT id, nome, categoria FROM modalidades WHERE status = 'ativa' ORDER BY nome ASC")->fetchAll();
$treinadores = $pdo->query("SELECT id, nome, email FROM usuarios WHERE perfil IN ('treinador', 'admin') AND ativo = 1 ORDER BY nome ASC")->fetchAll();

$equipes = $pdo->query("
    SELECT eq.*,
           inst.sigla, inst.campus,
           comp.nome AS competicao_nome,
           mod.nome AS modalidade_nome, mod.categoria AS modalidade_categoria,
           u.nome AS responsavel_nome,
           (SELECT COUNT(*) FROM equipe_atletas ea WHERE ea.equipe_id = eq.id) AS total_atletas
    FROM equipes eq
    JOIN instituicoes inst ON eq.instituicao_id = inst.id
    JOIN competicoes comp ON eq.competicao_id = comp.id
    JOIN modalidades mod ON eq.modalidade_id = mod.id
    JOIN usuarios u ON eq.responsavel_id = u.id
    ORDER BY inst.campus ASC, mod.nome ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">🛡️ Gestão de Equipes</h1>
            <p style="color:var(--text-muted);">Inscrição e associação de equipes por campus e modalidade esportiva</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 2fr;gap:30px;">
        
        <!-- FORMULÁRIO DE NOVA EQUIPE -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">Cadastrar Equipe</h3>
            <form method="POST" action="equipes.php">
                <input type="hidden" name="acao" value="cadastrar">

                <div class="form-group">
                    <label class="form-label" for="nome">Nome Oficial da Equipe</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: IFRO Calama — Futsal Masc" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="instituicao_id">Campus Representado</label>
                    <select id="instituicao_id" name="instituicao_id" class="form-control" required>
                        <?php foreach ($instituicoes as $inst): ?>
                            <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['sigla']) ?> - <?= htmlspecialchars($inst['campus']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="competicao_id">Competição</label>
                    <select id="competicao_id" name="competicao_id" class="form-control" required>
                        <?php foreach ($competicoes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?> (<?= htmlspecialchars($c['edicao']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="modalidade_id">Modalidade Esportiva</label>
                    <select id="modalidade_id" name="modalidade_id" class="form-control" required>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['categoria']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($usuario['perfil'] === 'admin'): ?>
                    <div class="form-group">
                        <label class="form-label" for="responsavel_id">Treinador / Responsável</label>
                        <select id="responsavel_id" name="responsavel_id" class="form-control" required>
                            <?php foreach ($treinadores as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?> (<?= htmlspecialchars($t['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Salvar Equipe
                </button>
            </form>
        </div>

        <!-- LISTAGEM DE EQUIPES -->
        <div>
            <div class="table-responsive" style="margin-top:0;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Equipe</th>
                            <th>Campus</th>
                            <th>Modalidade</th>
                            <th>Técnico</th>
                            <th class="center">Atletas</th>
                            <th class="center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipes as $eq): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($eq['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($eq['campus']) ?></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($eq['modalidade_nome']) ?></span></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($eq['responsavel_nome']) ?></td>
                                <td class="center" style="font-weight:700;"><?= $eq['total_atletas'] ?></td>
                                <td class="center">
                                    <a href="atletas.php?equipe_id=<?= $eq['id'] ?>" class="btn btn-sm btn-outline">
                                        Escalação
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