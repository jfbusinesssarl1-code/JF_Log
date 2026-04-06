<?php
namespace App\Models;
use App\Core\Model;
use MongoDB\BSON\ObjectId;
class GrandLivreModel extends Model
{
    public function getById($id)
    {
        return $this->collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
    public function delete($id)
    {
        return $this->collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
    public function update($id, $data)
    {
        return $this->collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }
    private $collection;
    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->journal;
    }
    public function getByCompte($compte, $filters = [])
    {
        $query = ['compte' => $compte];
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut']))
                $dateQuery['$gte'] = $filters['date_debut'];
            if (!empty($filters['date_fin']))
                $dateQuery['$lte'] = $filters['date_fin'];
            if ($dateQuery)
                $query['date'] = $dateQuery;
        }
        return $this->collection->find($query, ['sort' => ['date' => 1]])->toArray();
    }
    public function getComptes()
    {
        return $this->collection->distinct('compte');
    }
}