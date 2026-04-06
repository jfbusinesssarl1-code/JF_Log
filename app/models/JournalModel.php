<?php
namespace App\Models;
use App\Core\Model;
use App\Helpers\PaginationHelper;

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
            $this->collection->createIndex(['created_at' => -1]);
        } catch (\Throwable $e) {
        }
    }
    public function insert($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->collection->insertOne($data);
    }
    public function getAll()
    {
        return $this->collection->find([], ['sort' => ['date' => 1]])->toArray();
    }

    public function getFiltered($filters = [])
    {
        $query = [];
        if (!empty($filters['only_debit'])) {
            $query['debit'] = ['$gt' => 0];
        }
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

    public function getFilteredLimited($filters = [], $limit = 0)
    {
        $query = [];
        if (!empty($filters['only_debit'])) {
            $query['debit'] = ['$gt' => 0];
        }
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
        $options = ['sort' => ['date' => 1]];
        if (is_int($limit) && $limit > 0) {
            $options['limit'] = $limit;
        }
        return $this->collection->find($query, $options)->toArray();
    }

    public function getFilteredWithPagination($filters = [], $page = 1, $itemsPerPage = 20)
    {
        $query = [];
        if (!empty($filters['only_debit'])) {
            $query['debit'] = ['$gt' => 0];
        }
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

        // Initialiser la pagination
        $pagination = new PaginationHelper($page, $itemsPerPage);

        // Compter le nombre total d'éléments
        $totalCount = $this->collection->countDocuments($query);
        $pagination->setTotalItems($totalCount);

        // Récupérer les éléments pour la page actuelle
        $options = [
            'sort' => ['date' => 1],
            'skip' => $pagination->getOffset(),
            'limit' => $pagination->getLimit()
        ];
        $entries = $this->collection->find($query, $options)->toArray();

        return [
            'entries' => $entries,
            'pagination' => $pagination
        ];
    }

    /**
     * Retourne les totaux (debit/credit) pour les filtres fournis
     */
    public function getTotals($filters = [])
    {
        $query = [];
        if (!empty($filters['only_debit'])) {
            $query['debit'] = ['$gt' => 0];
        }
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

        $pipeline = [];
        if (!empty($query)) {
            $pipeline[] = ['$match' => $query];
        }
        $pipeline[] = ['$group' => ['_id' => null, 'debit' => ['$sum' => ['$ifNull' => ['$debit', 0]]], 'credit' => ['$sum' => ['$ifNull' => ['$credit', 0]]]]];

        $res = $this->collection->aggregate($pipeline)->toArray();
        if (empty($res))
            return ['debit' => 0.0, 'credit' => 0.0];
        return ['debit' => isset($res[0]['debit']) ? floatval($res[0]['debit']) : 0.0, 'credit' => isset($res[0]['credit']) ? floatval($res[0]['credit']) : 0.0];
    }

    public function countDocuments($query = [])
    {
        return $this->collection->countDocuments($query);
    }
}
