<?php
namespace App\Models;
use App\Core\Model;
class JournalModel extends Model
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
        try {
            $this->collection->createIndex(['compte' => 1]);
            $this->collection->createIndex(['date' => 1]);
            $this->collection->createIndex(['lieu' => 1]);
        } catch (\Throwable $e) {
        }
    }
    public function insert($data)
    {
        return $this->collection->insertOne($data);
    }
    public function getAll()
    {
        return $this->collection->find()->toArray();
    }

    public function getFiltered($filters = [])
    {
        $query = [];
        if (!empty($filters['compte'])) {
            $query['compte'] = ['$regex' => $filters['compte'], '$options' => 'i'];
        }
        if (!empty($filters['lieu'])) {
            $query['lieu'] = ['$regex' => $filters['lieu'], '$options' => 'i'];
        }
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut'])) {
                $dateQuery['$gte'] = $filters['date_debut'];
            }
            if (!empty($filters['date_fin'])) {
                $dateQuery['$lte'] = $filters['date_fin'];
            }
            if (!empty($dateQuery)) {
                $query['date'] = $dateQuery;
            }
        }
        return $this->collection->find($query, ['sort' => ['date' => 1]])->toArray();
    }
}