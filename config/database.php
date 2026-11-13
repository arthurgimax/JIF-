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
     * Em produção, exige MySQL/MariaDB configurado e nunca cai silenciosamente em SQLite.
     * O fallback SQLite existe somente para desenvolvimento local.
     */
    private static function connect(): void {
        $env = self::loadEnv();
        $appEnv = strtolower(trim((string)($env['APP_ENV'] ?? 'development')));
        $isProduction = in_array($appEnv, ['production', 'prod'], true);

        $host = trim((string)($env['DB_HOST'] ?? '127.0.0.1'));
        $port = (int)($env['DB_PORT'] ?? 3306);
        $dbName = trim((string)($env['DB_NAME'] ?? 'jif_plus'));
        $user = trim((string)($env['DB_USER'] ?? 'root'));
        $pass = (string)($env['DB_PASS'] ?? '');
        $charset = (string)($env['DB_CHARSET'] ?? 'utf8mb4');

        // Evita inserir parâmetros inesperados na DSN.
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $charset)) {
            $charset = 'utf8mb4';
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $host) || !preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
            error_log('JIF+: host ou nome de banco possui caracteres inválidos.');
            http_response_code(503);
            exit('A configuração do banco de dados é inválida.');
        }
        if ($port < 1 || $port > 65535) {
            $port = 3306;
        }

        if ($isProduction) {
            $requiredKeys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
            foreach ($requiredKeys as $requiredKey) {
                if (!isset($env[$requiredKey]) || trim((string)$env[$requiredKey]) === '') {
                    error_log('JIF+: variável obrigatória de banco ausente em produção: ' . $requiredKey);
                    http_response_code(503);
                    exit('O sistema está temporariamente indisponível. A configuração do banco de dados precisa ser verificada.');
                }
            }
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            self::$instance = $pdo;
            self::$driver = 'mysql';
            self::$isFallback = false;
            return;
        } catch (PDOException $e) {
            error_log('JIF+: falha na conexão MySQL: ' . $e->getMessage());

            // Não permite que uma falha de configuração em produção crie um banco vazio/local.
            if ($isProduction) {
                http_response_code(503);
                exit('Não foi possível conectar ao banco de dados. Verifique as variáveis de ambiente do servidor.');
            }
        }

        // Contingência SQLite restrita ao ambiente de desenvolvimento.
        $sqlitePath = __DIR__ . '/../database/jif_plus.sqlite';
        $needsInit = !file_exists($sqlitePath);

        try {
            $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            self::$instance = $pdo;
            self::$driver = 'sqlite';
            self::$isFallback = true;

            if ($needsInit) {
                self::initializeFallbackDatabase($pdo);
            }
        } catch (Throwable $fallbackError) {
            error_log('JIF+: falha no banco SQLite local: ' . $fallbackError->getMessage());
            http_response_code(500);
            exit('Não foi possível iniciar o banco de dados local. Verifique as extensões PDO instaladas no PHP.');
        }
    }

    /**
     * Carrega configurações de .env/config.local.php e, por último, variáveis do servidor.
     * Variáveis de ambiente do provedor sempre têm prioridade.
     */
    private static function loadEnv(): array {
        $env = [];

        $envFile = __DIR__ . '/../.env';
        if (is_file($envFile) && is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key !== '') {
                    $env[$key] = trim($value, " \t\n\r\0\x0B\"'");
                }
            }
        }

        $localConfigFile = __DIR__ . '/../config.local.php';
        if (is_file($localConfigFile)) {
            $localConfig = include $localConfigFile;
            if (is_array($localConfig)) {
                $env = array_merge($env, $localConfig);
            }
        }

        $keys = [
            'APP_ENV', 'APP_NAME', 'APP_URL', 'SESSION_SECRET',
            'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET',
        ];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value === false || $value === '') {
                $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
            }
            if ($value !== null && $value !== '') {
                $env[$key] = $value;
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