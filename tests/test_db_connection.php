<?php
/**
 * Teste Automatizado de Conexão com o Banco de Dados e Leitura das 10 Tabelas
 */
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getConnection();
    echo "============================================================\n";
    echo "  TESTE DE CONEXÃO COM BANCO DE DADOS — JIF+\n";
    echo "============================================================\n";
    echo "Driver ativo: " . Database::getDriver() . "\n";
    echo "Modo contingência: " . (Database::isFallback() ? "SIM (SQLite Local)" : "NÃO (MySQL Nativo)") . "\n";
    echo "------------------------------------------------------------\n";

    $tabelas = [
        'usuarios', 'instituicoes', 'competicoes', 'modalidades',
        'equipes', 'atletas', 'equipe_atletas', 'partidas',
        'estatisticas_atletas', 'estatisticas_equipes'
    ];

    $totalGeral = 0;
    foreach ($tabelas as $tab) {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM {$tab}");
        $qtd = $stmt->fetch()['total'];
        $totalGeral += $qtd;
        printf("  Tabela %-22s: %d registros OK\n", $tab, $qtd);
    }

    echo "------------------------------------------------------------\n";
    echo "Total de registros validados: {$totalGeral}\n";
    echo "STATUS: conexão e presença das tabelas verificadas; execute a suíte completa para validar os fluxos.\n";
    echo "============================================================\n";

} catch (Exception $e) {
    echo "ERRO NO TESTE: " . $e->getMessage() . "\n";
    exit(1);
}