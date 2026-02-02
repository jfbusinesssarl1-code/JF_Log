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
    // public function __construct($uri = 'mongodb://localhost:27017', $dbName = 'compta')
    public function __construct($uri = 'mongodb://192.168.0.107:27017', $dbName = 'compta')
    {
        $this->client = new Client($uri);
        $this->db = $this->client->$dbName;
    }
    public function getDb()
    {
        return $this->db;
    }
}