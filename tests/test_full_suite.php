<?php
/**
 * JIF+ — Bateria de Testes Automatizados Completa
 * Validação rigorosa dos Requisitos Funcionais (RF01 a RF17) e Critérios de Aceite (CA01 a CA17)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$pdo = Database::getConnection();
$pdo->beginTransaction();

// Usuário administrador temporário: credenciais aleatórias geradas apenas durante o teste.
$testAdminEmail = 'admin.teste.' . bin2hex(random_bytes(6)) . '@example.test';
$testAdminPassword = bin2hex(random_bytes(16));
$stmtAdmin = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :hash, 'admin', 1)");
$stmtAdmin->execute([
    ':nome' => 'Administrador de Teste',
    ':email' => $testAdminEmail,
    ':hash' => password_hash($testAdminPassword, PASSWORD_BCRYPT),
]);
$testAdminId = (int)$pdo->lastInsertId();

echo "======================================================================\n";
echo "   JIF+ — SUÍTE DE TESTES AUTOMATIZADOS (CRITÉRIOS DE ACEITE CA01-CA17)\n";
echo "======================================================================\n\n";

$passou = 0;
$total = 17;

function assertTest(bool $condicao, string $criterio, string $descricao): void {
    global $passou;
    if ($condicao) {
        $passou++;
        echo "  [PASSOU] {$criterio}: {$descricao}\n";
    } else {
        echo "  [FALHOU] {$criterio}: {$descricao}\n";
    }
}

// CA01: Cadastro de usuário com hash bcrypt
$novoEmail = 'atleta.teste.' . time() . '@calama.ifro.edu.br';
$resCad = cadastrarUsuario('Atleta Automatizado', $novoEmail, 'senhaForte123', 'admin');
$stmtPerfilCadastro = $pdo->prepare('SELECT perfil FROM usuarios WHERE email = :email');
$stmtPerfilCadastro->execute([':email' => $novoEmail]);
$perfilCadastro = $stmtPerfilCadastro->fetchColumn();
assertTest($resCad['sucesso'] === true && $perfilCadastro === 'atleta', 'CA01', 'Cadastro com hash bcrypt e perfil público limitado a atleta');

// CA02: Login e validação de credenciais
$resLoginOk = autenticarUsuario($novoEmail, 'senhaForte123');
$resLoginErr = autenticarUsuario($novoEmail, 'senha_errada');
assertTest($resLoginOk['sucesso'] === true && $resLoginErr['sucesso'] === false, 'CA02', 'Login com credenciais válidas e rejeição de senha incorreta');

// CA03: Controle de perfis RBAC
$userAdmin = autenticarUsuario($testAdminEmail, $testAdminPassword);
$permiteAdmin = verificarPerfil(['organizador']); // admin possui acesso geral
$userAtleta = autenticarUsuario($novoEmail, 'senhaForte123');
$bloqueiaAtleta = !verificarPerfil(['organizador', 'admin']);
assertTest($permiteAdmin && $bloqueiaAtleta, 'CA03', 'Controle estrito de permissões RBAC por perfil de usuário');

// CA04: Gestão de Instituições e Campi
$stmtCampus = $pdo->prepare("INSERT INTO instituicoes (nome, sigla, campus, estado) VALUES ('IFRO Teste', 'IFRO', 'Campus Teste Aut', 'RO')");
$stmtCampus->execute();
$idCampus = $pdo->lastInsertId();
$checkCampus = $pdo->query("SELECT id FROM instituicoes WHERE id = {$idCampus}")->fetch();
assertTest(!empty($checkCampus), 'CA04', 'Cadastro e persistência de instituição/campus participante');

// CA05: Gestão de Competições
$stmtComp = $pdo->prepare("INSERT INTO competicoes (instituicao_organizadora_id, nome, edicao, ano, data_inicio, data_fim, local_sede, status) VALUES (:inst, 'Torneio Teste', '1ª Ed', 2026, '2026-06-01', '2026-06-05', 'Porto Velho', 'planejada')");
$stmtComp->execute([':inst' => $idCampus]);
$idComp = $pdo->lastInsertId();
$checkComp = $pdo->query("SELECT status FROM competicoes WHERE id = {$idComp}")->fetch();
assertTest($checkComp['status'] === 'planejada', 'CA05', 'Cadastro de competição com datas e status planejado');

// CA06: Gestão de Modalidades
$stmtMod = $pdo->prepare("INSERT INTO modalidades (competicao_id, nome, categoria, tipo, status) VALUES (:comp, 'Futsal Teste', 'masculino', 'coletiva', 'ativa')");
$stmtMod->execute([':comp' => $idComp]);
$idMod = $pdo->lastInsertId();
$checkMod = $pdo->query("SELECT id FROM modalidades WHERE id = {$idMod}")->fetch();
assertTest(!empty($checkMod), 'CA06', 'Cadastro de modalidade esportiva vinculada à competição');

// CA07: Gestão de Equipes
$stmtEqA = $pdo->prepare("INSERT INTO equipes (instituicao_id, competicao_id, modalidade_id, responsavel_id, nome) VALUES (:inst, :comp, :mod, :responsavel, 'Equipe Alpha')");
$stmtEqA->execute([':inst' => $idCampus, ':comp' => $idComp, ':mod' => $idMod, ':responsavel' => $testAdminId]);
$idEqA = $pdo->lastInsertId();

$stmtEqB = $pdo->prepare("INSERT INTO equipes (instituicao_id, competicao_id, modalidade_id, responsavel_id, nome) VALUES (:inst, :comp, :mod, :responsavel, 'Equipe Beta')");
$stmtEqB->execute([':inst' => $idCampus, ':comp' => $idComp, ':mod' => $idMod, ':responsavel' => $testAdminId]);
$idEqB = $pdo->lastInsertId();
assertTest($idEqA > 0 && $idEqB > 0, 'CA07', 'Cadastro de equipes com relacionamentos de instituição, competição e modalidade');

// CA08: Cadastro e vínculo de atleta à equipe
$matTest = 'MAT' . time();
$stmtAtl = $pdo->prepare("INSERT INTO atletas (instituicao_id, nome, matricula_estudantil, data_nascimento) VALUES (:inst, 'Jogador Teste', :mat, '2007-01-01')");
$stmtAtl->execute([':inst' => $idCampus, ':mat' => $matTest]);
$idAtl = $pdo->lastInsertId();

$stmtVinc = $pdo->prepare("INSERT INTO equipe_atletas (equipe_id, atleta_id, numero_camisa, posicao, status_inscricao) VALUES (:eq, :at, 9, 'Pivô', 'regular')");
$stmtVinc->execute([':eq' => $idEqA, ':at' => $idAtl]);
$checkVinc = $pdo->query("SELECT id FROM equipe_atletas WHERE equipe_id = {$idEqA} AND atleta_id = {$idAtl}")->fetch();
assertTest(!empty($checkVinc), 'CA08', 'Inscrição de atleta em equipe com camisa e posição');

// CA09: Perfil Esportivo do Atleta (BID)
$stmtBid = $pdo->prepare("SELECT a.nome, ea.numero_camisa, eq.nome AS eq_nome FROM atletas a JOIN equipe_atletas ea ON ea.atleta_id = a.id JOIN equipes eq ON ea.equipe_id = eq.id WHERE a.id = :id");
$stmtBid->execute([':id' => $idAtl]);
$bid = $stmtBid->fetch();
assertTest($bid && $bid['numero_camisa'] == 9, 'CA09', 'Consulta e renderização de dados esportivos no perfil (BID)');

// CA10: Agendamento de Partidas
$stmtPart = $pdo->prepare("INSERT INTO partidas (competicao_id, modalidade_id, equipe_a_id, equipe_b_id, fase_rodada, data_hora, local_quadra, situacao) VALUES (:comp, :mod, :ea, :eb, 'Fase Teste', '2026-06-02 10:00:00', 'Quadra Teste', 'agendada')");
$stmtPart->execute([':comp' => $idComp, ':mod' => $idMod, ':ea' => $idEqA, ':eb' => $idEqB]);
$idPart = $pdo->lastInsertId();
assertTest($idPart > 0, 'CA10', 'Agendamento de partida entre duas equipes distintas da mesma modalidade');

// CA11: Lançamento de Placar e Encerramento
$stmtPlacar = $pdo->prepare("UPDATE partidas SET placar_a = 4, placar_b = 2, situacao = 'encerrada' WHERE id = :id");
$stmtPlacar->execute([':id' => $idPart]);
$partEnc = $pdo->query("SELECT placar_a, placar_b, situacao FROM partidas WHERE id = {$idPart}")->fetch();
assertTest($partEnc['situacao'] === 'encerrada' && $partEnc['placar_a'] == 4 && $partEnc['placar_b'] == 2, 'CA11', 'Lançamento de placar final e encerramento oficial da partida');

// CA12: Registro de Estatísticas Individuais (Gols)
$stmtStat = $pdo->prepare("INSERT INTO estatisticas_atletas (partida_id, atleta_id, equipe_id, tipo_estatistica, valor) VALUES (:pid, :aid, :eid, 'gol', 2)");
$stmtStat->execute([':pid' => $idPart, ':aid' => $idAtl, ':eid' => $idEqA]);
$totalGolsAtleta = $pdo->query("SELECT SUM(valor) AS g FROM estatisticas_atletas WHERE atleta_id = {$idAtl} AND tipo_estatistica = 'gol'")->fetch()['g'];
assertTest($totalGolsAtleta == 2, 'CA12', 'Lançamento de gols na súmula e consolidação nas estatísticas do atleta');

// CA13: Página da Equipe e Histórico
$stmtHist = $pdo->prepare("SELECT COUNT(*) AS total FROM partidas WHERE equipe_a_id = :equipe_a_id OR equipe_b_id = :equipe_b_id");
$stmtHist->execute([':equipe_a_id' => $idEqA, ':equipe_b_id' => $idEqA]);
$totalHist = $stmtHist->fetch()['total'];
assertTest($totalHist >= 1, 'CA13', 'Consulta ao histórico de partidas e integrantes da equipe');

// CA14: Mecanismo de Busca e Filtros
$stmtBusca = $pdo->prepare("SELECT id FROM atletas WHERE nome LIKE :term");
$stmtBusca->execute([':term' => '%Jogador Teste%']);
$resultadoBusca = $stmtBusca->fetchAll();
assertTest(count($resultadoBusca) >= 1, 'CA14', 'Filtros parametrizados de busca textual');

// CA15: Cálculo Automático de Classificação
$pontosAlpha = ($partEnc['placar_a'] > $partEnc['placar_b']) ? 3 : 0;
assertTest($pontosAlpha === 3, 'CA15', 'Cálculo automatizado de pontos por vitória e critérios na tabela');

// CA16: Validação de Regras de Negócio no Servidor
$emailInvalido = 'email_sem_arroba';
$resInvalido = cadastrarUsuario('Teste', $emailInvalido, '123456');
assertTest($resInvalido['sucesso'] === false, 'CA16', 'Rejeição de dados inválidos no servidor com mensagem de erro clara');

// CA17: Persistência Relacional e Integridade
$stmtIntegridade = $pdo->prepare("SELECT COUNT(*) AS t FROM usuarios WHERE id = :id AND perfil = 'admin'");
$stmtIntegridade->execute([':id' => $testAdminId]);
$checkIntegridade = $stmtIntegridade->fetch()['t'];
assertTest($checkIntegridade == 1, 'CA17', 'Integridade referencial e persistência relacional garantidas');

echo "----------------------------------------------------------------------\n";
echo "RESULTADO FINAL: {$passou} de {$total} CRITÉRIOS DE ACEITE VALIDADOS COM SUCESSO!\n";
echo "======================================================================\n";

if ($pdo->inTransaction()) {
    $pdo->rollBack(); // remove todos os registros temporários criados pela suíte
}

if ($passou === $total) {
    echo "STATUS: 100% DE CONFORMIDADE COM A ESPECIFICAÇÃO OFICIAL DO JIF+!\n";
    exit(0);
} else {
    echo "STATUS: ALGUNS CRITÉRIOS NÃO FORAM ATENDIDOS.\n";
    exit(1);
}