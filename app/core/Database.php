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
    public function __construct($uri = 'mongodb+srv://congobuildjfbusiness_db_user:onesime@freeclouster.pqw6eon.mongodb.net/?appName=FreeClouster', $dbName = 'compta')
    {
        $this->client = new Client($uri);
        $this->db = $this->client->$dbName;
    }
    public function getDb()
    {
        return $this->db;
    }
}