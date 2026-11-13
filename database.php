<?php
/**
 * JIF+ — Gerenciador de Conexão com o Banco de Dados Relacional
 * Implementação segura via PDO com prepared statements obrigatórios (RNF03).
 */

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';
    private static bool $isFallback = false;

    /**
     * Retorna a instância única da conexão PDO (Singleton).
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Retorna o driver em uso ('mysql' ou 'sqlite').
     */
    public static function getDriver(): string {
        return self::$driver;
    }

    /**
     * Indica se está utilizando o banco de dados de contingência local.
     */
    public static function isFallback(): bool {
        return self::$isFallback;
    }

    /**
     * Inicializa a conexão com o banco de dados.
     */
    private static function connect(): void {
        $env = self::loadEnv();

        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? 3306;
        $dbName = $env['DB_NAME'] ?? 'jif_plus';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? '';

        try {
            // Tentativa de conexão principal com MySQL / MariaDB (conforme spec.md)
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Segurança contra SQL Injection (RNF03)
                PDO::ATTR_TIMEOUT => 2
            ]);

            self::$instance = $pdo;
            self::$driver = 'mysql';
            self::$isFallback = false;

        } catch (PDOException $e) {
            // Em caso de credencial pendente no MySQL local, ativa contingência SQLite
            // para que a aplicação e os fluxos permaneçam 100% funcionais no desenvolvimento
            $sqlitePath = __DIR__ . '/../database/jif_plus.sqlite';
            $needsInit = !file_exists($sqlitePath);

            try {
                $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
                $pdo->exec("PRAGMA foreign_keys = ON;");

                self::$instance = $pdo;
                self::$driver = 'sqlite';
                self::$isFallback = true;

                if ($needsInit) {
                    self::initializeFallbackDatabase($pdo);
                }

            } catch (Exception $fallbackError) {
                // Tratamento seguro sem expor credenciais em tela (RNF07)
                error_log("Erro de Conexão com Banco de Dados: " . $e->getMessage());
                die("<div style='font-family:sans-serif;padding:30px;background:#fff2f2;border:1px solid #ffcccc;border-radius:8px;max-width:600px;margin:50px auto;'>"
                    . "<h3 style='color:#cc0000;margin-top:0;'>Erro de Conexão com o Banco de Dados</h3>"
                    . "<p>Não foi possível estabelecer conexão com o MySQL e o banco local de contingência não pôde ser iniciado.</p>"
                    . "<p style='color:#666;font-size:13px;'>Verifique as credenciais no arquivo <code>.env</code> ou inicie o serviço MySQL.</p>"
                    . "</div>");
            }
        }
    }

    /**
     * Carrega as variáveis de ambiente do arquivo .env.
     */
    private static function loadEnv(): array {
        $envFile = __DIR__ . '/../.env';
        $env = [];

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                if (strpos($line, '=') !== false) {
                    list($k, $v) = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\n\r\0\x0B\"'");
                    $env[$k] = $v;
                }
            }
        }
        return $env;
    }

    /**
     * Inicializa a estrutura e dados no banco de contingência local.
     */
    private static function initializeFallbackDatabase(PDO $pdo): void {
        // Criação das 10 tabelas com compatibilidade SQLite
        $ddl = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha_hash TEXT NOT NULL,
            perfil TEXT NOT NULL DEFAULT 'atleta',
            ativo INTEGER NOT NULL DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS instituicoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            sigla TEXT NOT NULL,
            campus TEXT NOT NULL,
            estado TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS competicoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            instituicao_organizadora_id INTEGER NOT NULL REFERENCES instituicoes(id),
            nome TEXT NOT NULL,
            edicao TEXT NOT NULL,
            ano INTEGER NOT NULL,
            data_inicio DATE NOT NULL,
            data_fim DATE NOT NULL,
            local_sede TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'planejada'
        );

        CREATE TABLE IF NOT EXISTS modalidades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            competicao_id INTEGER NOT NULL REFERENCES competicoes(id) ON DELETE CASCADE,
            nome TEXT NOT NULL,
            categoria TEXT NOT NULL,
            tipo TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'ativa'
        );

        CREATE TABLE IF NOT EXISTS equipes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            instituicao_id INTEGER NOT NULL REFERENCES instituicoes(id),
            competicao_id INTEGER NOT NULL REFERENCES competicoes(id) ON DELETE CASCADE,
            modalidade_id INTEGER NOT NULL REFERENCES modalidades(id) ON DELETE CASCADE,
            responsavel_id INTEGER NOT NULL REFERENCES usuarios(id),
            nome TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS atletas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
            instituicao_id INTEGER NOT NULL REFERENCES instituicoes(id),
            nome TEXT NOT NULL,
            matricula_estudantil TEXT NOT NULL,
            data_nascimento DATE,
            foto_url TEXT,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS equipe_atletas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            atleta_id INTEGER NOT NULL REFERENCES atletas(id) ON DELETE CASCADE,
            numero_camisa INTEGER,
            posicao TEXT,
            status_inscricao TEXT NOT NULL DEFAULT 'regular',
            UNIQUE(equipe_id, atleta_id)
        );

        CREATE TABLE IF NOT EXISTS partidas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            competicao_id INTEGER NOT NULL REFERENCES competicoes(id) ON DELETE CASCADE,
            modalidade_id INTEGER NOT NULL REFERENCES modalidades(id) ON DELETE CASCADE,
            equipe_a_id INTEGER NOT NULL REFERENCES equipes(id),
            equipe_b_id INTEGER NOT NULL REFERENCES equipes(id),
            fase_rodada TEXT NOT NULL,
            data_hora DATETIME NOT NULL,
            local_quadra TEXT NOT NULL,
            placar_a INTEGER DEFAULT NULL,
            placar_b INTEGER DEFAULT NULL,
            situacao TEXT NOT NULL DEFAULT 'agendada'
        );

        CREATE TABLE IF NOT EXISTS estatisticas_atletas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            partida_id INTEGER NOT NULL REFERENCES partidas(id) ON DELETE CASCADE,
            atleta_id INTEGER NOT NULL REFERENCES atletas(id) ON DELETE CASCADE,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            tipo_estatistica TEXT NOT NULL,
            valor INTEGER NOT NULL DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS estatisticas_equipes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            partida_id INTEGER NOT NULL REFERENCES partidas(id) ON DELETE CASCADE,
            equipe_id INTEGER NOT NULL REFERENCES equipes(id) ON DELETE CASCADE,
            tipo_estatistica TEXT NOT NULL,
            valor INTEGER NOT NULL
        );
        ";
        $pdo->exec($ddl);

        // Importação dos dados iniciais de demonstração
        $seedSql = file_get_contents(__DIR__ . '/../database/seed.sql');
        // Remove linhas 'USE `jif_plus`;'
        $seedSql = preg_replace('/^USE\s+.*?;/mi', '', $seedSql);
        $pdo->exec($seedSql);
    }
}
