<?php
namespace App\Core;
use App\Core\Database;
class Model
{
    protected $db;
    public function __construct()
    {
        // Store the actual MongoDB database instance, not the wrapper
        $this->db = (new Database())->getDb();
    }
}