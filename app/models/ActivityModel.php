<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\ImageConverterV2;

class ActivityModel
{
    protected $collection;

    public function __construct()
    {
        $db = (new Database())->getDb();
        $this->collection = $db->activities;
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
        try {
            $oid = new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            return false;
        }
        
        // Récupérer l'activité avant suppression pour accéder à l'image
        $activity = $this->collection->findOne(['_id' => $oid]);
        
        // Supprimer l'image associée si elle existe
        if ($activity && !empty($activity['image'])) {
            ImageConverterV2::deleteImage($activity['image']);
        }
        
        // Supprimer le document
        $this->collection->deleteOne(['_id' => $oid]);
        return true;
    }
}
