<?php
/**
 * Database.php — Singleton PDO Connection Manager para o Oyama Hub
 *
 * Uso:
 *   require_once __DIR__ . '/Database.php';
 *   $pdo = Database::getConnection();
 *   $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
 *   $stmt->execute([$userId]);
 *   $user = $stmt->fetch();
 */

class Database {
    private static ?PDO $instance = null;

    // Configurações padrão
    private static string $host     = 'localhost';
    private static string $dbname   = 'oyama_hub';
    private static string $username = 'root';
    private static string $password = 'Home@spSENAI2025!';
    private static string $charset  = 'utf8mb4';

    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {}

    /**
     * Impedir clonagem da instância
     */
    private function __clone() {}

    /**
     * Impedir desserialização
     */
    public function __wakeup() {
        throw new \Exception("Não é permitido desserializar um Singleton.");
    }

    /**
     * Configura credenciais personalizadas (se necessário)
     */
    public static function configure(string $host, string $dbname, string $username, string $password, string $charset = 'utf8mb4'): void {
        self::$host     = $host;
        self::$dbname   = $dbname;
        self::$username = $username;
        self::$password = $password;
        self::$charset  = $charset;
        self::$instance = null; // Reiniciar conexão se reconfigurado
    }

    /**
     * Retorna a conexão PDO compartilhada (Singleton)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-03:00'"
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // Registrar log sem expor credenciais na tela
                error_log("Erro de conexão PDO no Oyama Hub: " . $e->getMessage());
                die("Falha na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
            }
        }

        return self::$instance;
    }

    /**
     * Atalho para executar consulta preparada retornando todos os registros
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Atalho para executar consulta preparada retornando um único registro
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Atalho para executar comando INSERT/UPDATE/DELETE
     */
    public static function execute(string $sql, array $params = []): bool {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}
