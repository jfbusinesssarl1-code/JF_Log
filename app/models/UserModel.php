<?php
namespace App\Models;
use App\Core\Model;
class UserModel extends Model
{
    private $collection;
    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->users;
        // Ensure unique index on username
        try {
            $this->collection->createIndex(['username' => 1], ['unique' => true]);
        } catch (\Throwable $e) {
            // ignore index errors
        }
    }
    public function findByUsername($username)
    {
        return $this->collection->findOne(['username' => $username]);
    }
    public function create($username, $password, $role = 'user')
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->collection->insertOne([
            'username' => $username,
            'password' => $hash,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    public function verify($username, $password)
    {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    public function findById($id)
    {
        return $this->collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
    public function getAll()
    {
        return $this->collection->find()->toArray();
    }
    public function updateRole($userId, $role)
    {
        return $this->collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($userId)],
            ['$set' => ['role' => $role]]
        );
    }
    public function updatePassword($userId, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($userId)],
            ['$set' => ['password' => $hash]]
        );
    }
    public function delete($userId)
    {
        return $this->collection->deleteOne(
            ['_id' => new \MongoDB\BSON\ObjectId($userId)]
        );
    }
}
