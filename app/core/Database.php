<?php
namespace App\Core;
use MongoDB\Client;
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
class Database
{
    private $client;
    private $db;
    private static $connections = [];
    // public function __construct($uri = 'mongodb://localhost:27017', $dbName = 'compta')
    public function __construct($uri = null, $dbName = 'compta')
    {
        self::loadEnv();

        // Allow environment override (useful for CI / developer machines)
        $envUri = trim((string) ($_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: ''));
        if ($envUri !== '') {
            $uri = $envUri;
        }

        $envDbName = trim((string) ($_SERVER['MONGODB_DB'] ?? getenv('MONGODB_DB') ?: ''));
        if ($envDbName !== '') {
            $dbName = $envDbName;
        }

        // Default to localhost when nothing provided
        if (empty($uri)) {
            $uri = 'mongodb://127.0.0.1:27017';
        }

        // Defensive: ensure there is no accidental leading/trailing whitespace
        $uri = trim($uri);
        if (strpos($uri, 'YOUR_PASSWORD_HERE') !== false) {
            error_log('Database::__construct - MONGODB_URI contains YOUR_PASSWORD_HERE; falling back to local MongoDB.');
            $uri = 'mongodb://127.0.0.1:27017';
        }

        $cacheKey = $uri . '|' . $dbName;

        if (isset(self::$connections[$cacheKey])) {
            $this->client = self::$connections[$cacheKey]['client'];
            $this->db = self::$connections[$cacheKey]['db'];
            return;
        }

        // Try to create the client with a short serverSelectionTimeout for fast failure on misconfiguration
        try {
            $this->client = new Client($uri, ['serverSelectionTimeoutMS' => 2000]);
            $this->db = $this->client->$dbName;
            $this->db->command(['ping' => 1]);
            self::$connections[$cacheKey] = [
                'client' => $this->client,
                'db' => $this->db,
            ];
        } catch (\Throwable $e) {
            // Provide a clearer, actionable error message for common dev mistakes
            $msg = "MongoDB connection failed (" . self::sanitizeUri($uri) . ") - " . $e->getMessage();
            error_log('Database::__construct - ' . $msg);
            throw new \RuntimeException($msg, 0, $e);
        }
    }
    public function getDb()
    {
        return $this->db;
    }

    private static function loadEnv()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $envFile = __DIR__ . '/../../.env';
        if (!is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '' || getenv($key) !== false || isset($_SERVER[$key])) {
                continue;
            }

            $value = trim($value, "\"'");
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function sanitizeUri($uri)
    {
        return preg_replace('#(mongodb(?:\+srv)?://[^:/@]+:)[^@]+(@)#', '$1****$2', $uri);
    }
}
