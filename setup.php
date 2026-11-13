<?php
/**
 * JIF+ — Script de Instalação e Inicialização do Banco de Dados
 * Executável via linha de comando (CLI) ou navegador web.
 */

// Garante exibição adequada de caracteres UTF-8
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre style="background:#1a1a1a;color:#00ff66;padding:20px;font-family:monospace;font-size:14px;border-radius:8px;">';
}

function output($mensagem) {
    if (php_sapi_name() === 'cli') {
        echo $mensagem . PHP_EOL;
    } else {
        echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . "\n";
    }
}

output("======================================================================");
output("   JIF+ — INICIALIZAÇÃO AUTOMATIZADA DO BANCO DE DADOS (MySQL)        ");
output("======================================================================");

// Carregamento de variáveis do arquivo .env
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_to_comment($line)) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $env[$key] = $value;
        }
    }
}

function str_starts_to_comment($str) {
    return isset($str[0]) && $str[0] === '#';
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? 3306;
$dbName = $env['DB_NAME'] ?? 'jif_plus';
$dbUser = $env['DB_USER'] ?? 'root';
$dbPass = $env['DB_PASS'] ?? '';

output("[1/4] Conectando ao servidor MySQL em {$dbHost}:{$dbPort} com usuário '{$dbUser}'...");

try {
    // Conexão inicial sem selecionar banco
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    output(" -> Conexão estabelecida com sucesso!");

    // Criação do banco de dados
    output("[2/4] Criando e selecionando banco de dados `{$dbName}`...");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    output(" -> Banco de dados `{$dbName}` pronto.");

    // Execução do Schema DDL
    output("[3/4] Executando DDL das 10 tabelas oficiais (schema.sql)...");
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Arquivo schema.sql não encontrado em {$schemaFile}");
    }
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    output(" -> Estrutura relacional de 10 tabelas criada com sucesso!");

    // Execução do Seed
    output("[4/4] Inserindo dados realistas de demonstração (seed.sql)...");
    $seedFile = __DIR__ . '/seed.sql';
    if (!file_exists($seedFile)) {
        throw new Exception("Arquivo seed.sql não encontrado em {$seedFile}");
    }
    $seedSql = file_get_contents($seedFile);
    $pdo->exec($seedSql);
    output(" -> Carga de dados inicial inserida com sucesso!");

    output("----------------------------------------------------------------------");
    output("VERIFICAÇÃO DE REGISTROS CRIADOS:");
    
    $tabelas = [
        'usuarios', 'instituicoes', 'competicoes', 'modalidades',
        'equipes', 'atletas', 'equipe_atletas', 'partidas',
        'estatisticas_atletas', 'estatisticas_equipes'
    ];

    foreach ($tabelas as $tab) {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `{$tab}`");
        $qtd = $stmt->fetch()['total'];
        output(sprintf("  %-25s : %d registros", $tab, $qtd));
    }

    output("======================================================================");
    output("   SISTEMA DE BANCO DE DADOS DO JIF+ INSTALADO E 100% OPERACIONAL!    ");
    output("======================================================================");

} catch (PDOException $e) {
    output("ERRO DE BANCO DE DADOS: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    output("ERRO GERAL: " . $e->getMessage());
    exit(1);
}

if (php_sapi_name() !== 'cli') {
    echo '</pre>';
}
