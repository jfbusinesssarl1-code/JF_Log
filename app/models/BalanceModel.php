<?php
namespace App\Models;
use App\Core\Model;
class BalanceModel extends Model
{
    private $collection;
    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->journal;
    }
    public function getBalance($filters = [])
    {
        $match = [];
        if (!empty($filters['compte']))
            $match['compte'] = ['$regex' => $filters['compte'], '$options' => 'i'];
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut']))
                $dateQuery['$gte'] = $filters['date_debut'];
            if (!empty($filters['date_fin']))
                $dateQuery['$lte'] = $filters['date_fin'];
            if ($dateQuery)
                $match['date'] = $dateQuery;
        }
        $pipeline = [];
        if (!empty($match))
            $pipeline[] = ['$match' => $match];
        $pipeline[] = [
            '$group' => [
                '_id' => '$compte',
                'debit' => ['$sum' => ['$ifNull' => ['$debit', 0]]],
                'credit' => ['$sum' => ['$ifNull' => ['$credit', 0]]]
            ]
        ];
        return $this->collection->aggregate($pipeline)->toArray();
    }
}