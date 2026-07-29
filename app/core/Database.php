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
        // Allow environment override (useful for CI / developer machines)
        $envUri = trim((string) ($_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: ''));
        if ($envUri !== '') {
            $uri = $envUri;
        }

        // Default to localhost when nothing provided
        if (empty($uri)) {
            $uri = 'mongodb://127.0.0.1:27017';
        }

        // Defensive: ensure there is no accidental leading/trailing whitespace
        $uri = trim($uri);
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
            self::$connections[$cacheKey] = [
                'client' => $this->client,
                'db' => $this->db,
            ];
        } catch (\Throwable $e) {
            // Provide a clearer, actionable error message for common dev mistakes
            $msg = "MongoDB connection failed (" . $uri . ") - " . $e->getMessage();
            error_log('Database::__construct - ' . $msg);
            throw new \RuntimeException($msg, 0, $e);
        }
    }
    public function getDb()
    {
        return $this->db;
    }
}
