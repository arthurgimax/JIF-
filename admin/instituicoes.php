<?php
/**
 * JIF+ — Gestão de Instituições e Campi
 * Requisito: RF04
 */
define('BASE_PATH', '../');
$pageTitle = 'Instituições e Campi — JIF+';
require_once __DIR__ . '/../config/auth.php';

// Apenas Administrador e Organizador podem gerenciar instituições
exigirAutenticacao(['admin', 'organizador']);

$pdo = Database::getConnection();
$erro = null;

// PROCESSAMENTO DO CADASTRO DE NOVO CAMPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome'] ?? '');
    $sigla = trim($_POST['sigla'] ?? '');
    $campus = trim($_POST['campus'] ?? '');
    $estado = strtoupper(trim($_POST['estado'] ?? ''));

    if (empty($nome) || empty($sigla) || empty($campus) || strlen($estado) !== 2) {
        $erro = 'Todos os campos são obrigatórios e a UF deve conter 2 letras.';
    } else {
        $stmtInsert = $pdo->prepare("
            INSERT INTO instituicoes (nome, sigla, campus, estado)
            VALUES (:nome, :sigla, :campus, :estado)
        ");
        $stmtInsert->execute([
            ':nome' => $nome,
            ':sigla' => $sigla,
            ':campus' => $campus,
            ':estado' => $estado
        ]);
        $_SESSION['mensagem_sucesso'] = "Campus '{$campus}' cadastrado com sucesso!";
        header('Location: instituicoes.php');
        exit;
    }
}

// PROCESSAMENTO DE EXCLUSÃO
if (isset($_GET['excluir'])) {
    $idExcluir = (int)$_GET['excluir'];
    // Verifica integridade referencial
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS total FROM equipes WHERE instituicao_id = :id");
    $stmtCheck->execute([':id' => $idExcluir]);
    $totalVinculos = $stmtCheck->fetch()['total'];

    if ($totalVinculos > 0) {
        $_SESSION['mensagem_erro'] = "Não é possível excluir este campus pois existem {$totalVinculos} equipe(s) vinculada(s) a ele.";
    } else {
        $stmtDel = $pdo->prepare("DELETE FROM instituicoes WHERE id = :id");
        $stmtDel->execute([':id' => $idExcluir]);
        $_SESSION['mensagem_sucesso'] = "Campus excluído com sucesso!";
    }
    header('Location: instituicoes.php');
    exit;
}

// Listagem de todas as instituições
$instituicoes = $pdo->query("
    SELECT inst.*,
           (SELECT COUNT(*) FROM equipes eq WHERE eq.instituicao_id = inst.id) AS total_equipes,
           (SELECT COUNT(*) FROM atletas a WHERE a.instituicao_id = inst.id) AS total_atletas
    FROM instituicoes inst
    ORDER BY inst.nome ASC, inst.campus ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom:20px;">
        <a href="index.php" style="font-weight:700;">&larr; Voltar ao Painel</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
        <div>
            <h1 style="font-size:1.8rem;font-weight:900;color:var(--dark);">🏛️ Gestão de Instituições e Campi</h1>
            <p style="color:var(--text-muted);">Cadastro dos Institutos Federais participantes dos jogos esportivos</p>
        </div>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns: 1fr 2fr;gap:30px;">
        
        <!-- FORMULÁRIO DE NOVO CAMPUS -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom:15px;">Cadastrar Novo Campus</h3>
            <form method="POST" action="instituicoes.php">
                <input type="hidden" name="acao" value="cadastrar">
                
                <div class="form-group">
                    <label class="form-label" for="nome">Nome da Instituição</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="ex.: Instituto Federal de Rondônia" value="Instituto Federal de Rondônia" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sigla">Sigla Oficial</label>
                    <input type="text" id="sigla" name="sigla" class="form-control" placeholder="ex.: IFRO" value="IFRO" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="campus">Nome do Campus</label>
                    <input type="text" id="campus" name="campus" class="form-control" placeholder="ex.: Porto Velho Calama" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estado">Estado (UF)</label>
                    <input type="text" id="estado" name="estado" class="form-control" placeholder="ex.: RO" maxlength="2" value="RO" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">
                    Salvar Campus
                </button>
            </form>
        </div>

        <!-- LISTAGEM DE CAMPI CADASTRADOS -->
        <div>
            <div class="table-responsive" style="margin-top:0;">
                <table class="sports-table">
                    <thead>
                        <tr>
                            <th>Instituição / Sigla</th>
                            <th>Campus</th>
                            <th class="center">UF</th>
                            <th class="center">Equipes</th>
                            <th class="center">Atletas</th>
                            <th class="center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instituicoes as $inst): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($inst['sigla']) ?></strong><br>
                                    <span style="font-size:0.8rem;color:var(--text-muted);"><?= htmlspecialchars($inst['nome']) ?></span>
                                </td>
                                <td><strong><?= htmlspecialchars($inst['campus']) ?></strong></td>
                                <td class="center"><span class="badge badge-neutral"><?= htmlspecialchars($inst['estado']) ?></span></td>
                                <td class="center"><?= $inst['total_equipes'] ?></td>
                                <td class="center"><?= $inst['total_atletas'] ?></td>
                                <td class="center">
                                    <a href="instituicoes.php?excluir=<?= $inst['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Deseja realmente remover este campus?">
                                        Excluir
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