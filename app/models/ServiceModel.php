<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\ImageConverterV2;

class ServiceModel
{
    protected $collection;

    public function __construct()
    {
        $db = (new Database())->getDb();
        $this->collection = $db->services;
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

    public function delete($id)
    {
        try {
            $oid = new \MongoDB\BSON\ObjectId($id);
            
            // Récupérer le service avant suppression pour accéder à l'icône
            $service = $this->collection->findOne(['_id' => $oid]);
            
            // Supprimer l'icône/image associée si elle existe
            if ($service && !empty($service['icon'])) {
                ImageConverterV2::deleteImage($service['icon']);
            }
            
            // Supprimer le document
            $this->collection->deleteOne(['_id' => $oid]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findById($id)
    {
        return $this->collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public function update($id, array $item)
    {
        $this->collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $item]
        );
    }
}
