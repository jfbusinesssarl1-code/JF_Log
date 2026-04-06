<?php
namespace App\Models;

use App\Core\Database;

class PartnerModel
{
    protected $collection;

    public function __construct()
    {
        $db = (new Database())->getDb();
        $this->collection = $db->partners;
    }

    public function insert(array $item)
    {
        $item['created_at'] = new \MongoDB\BSON\UTCDateTime();
        $this->collection->insertOne($item);
    }

    public function getAll()
    {
        $cursor = $this->collection->find([], ['sort' => ['created_at' => -1]]);
        return iterator_to_array($cursor);
    }

    public function findById($id)
    {
        try {
            $oid = new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            return null;
        }
        return $this->collection->findOne(['_id' => $oid]);
    }

    public function update($id, array $data)
    {
        try {
            $oid = new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            return false;
        }
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        $this->collection->updateOne(['_id' => $oid], ['$set' => $data]);
        return true;
    }

    public function delete($id)
    {
        $this->collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
}
