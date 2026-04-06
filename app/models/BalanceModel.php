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

    /**
     * Retourne les résultats groupés paginés et l'objet PaginationHelper
     */
    public function getBalanceWithPagination($filters = [], $page = 1, $itemsPerPage = 20)
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

        // récupérer tous les groupes puis paginer côté PHP (plus simple et compatible)
        $all = $this->collection->aggregate($pipeline)->toArray();

        // compter
        $total = count($all);

        $pagination = new \App\Helpers\PaginationHelper($page, $itemsPerPage);
        $pagination->setTotalItems($total);

        $offset = $pagination->getOffset();
        $limit = $pagination->getLimit();

        $paged = array_slice($all, $offset, $limit);

        return [
            'balances' => $paged,
            'pagination' => $pagination
        ];
    }

    /**
     * Obtenir totaux (débit/credit/solde) pour les filtres fournis
     */
    public function getTotals($filters = [])
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
                '_id' => null,
                'debit' => ['$sum' => ['$ifNull' => ['$debit', 0]]],
                'credit' => ['$sum' => ['$ifNull' => ['$credit', 0]]]
            ]
        ];

        $res = $this->collection->aggregate($pipeline)->toArray();
        if (empty($res))
            return ['debit' => 0.0, 'credit' => 0.0, 'solde' => 0.0];
        $d = isset($res[0]['debit']) ? floatval($res[0]['debit']) : 0.0;
        $c = isset($res[0]['credit']) ? floatval($res[0]['credit']) : 0.0;
        return ['debit' => $d, 'credit' => $c, 'solde' => ($d - $c)];
    }
}