<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\ImageConverterV2;

class HomeModel
{
    protected $collection;

    public function __construct()
    {
        $db = (new Database())->getDb();
        $this->collection = $db->home_items;
    }

    public function insert(array $item)
    {
        $item['created_at'] = new UTCDateTime();
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
            return $this->collection->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update($id, array $data)
    {
        try {
            $data['updated_at'] = new UTCDateTime();
            $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $data]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $oid = new ObjectId($id);
            
            // Récupérer le document avant suppression pour accéder aux images
            $item = $this->collection->findOne(['_id' => $oid]);
            
            // Supprimer l'image associée si elle existe
            if ($item && !empty($item['image'])) {
                ImageConverterV2::deleteImage($item['image']);
            }
            
            // Supprimer le document
            $this->collection->deleteOne(['_id' => $oid]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
