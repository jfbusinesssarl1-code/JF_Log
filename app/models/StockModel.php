<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;
use App\Helpers\PaginationHelper;

class StockModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        // utiliser la collection 'stocks' (adapte si nécessaire)
        $this->collection = $this->db->stocks;
        try {
            $this->collection->createIndex(['compte' => 1, 'date' => 1]);
            $this->collection->createIndex(['lieu' => 1]);
            $this->collection->createIndex(['created_at' => -1]);
        } catch (\Throwable $e) {}
    }

    public function addEntry($data)
    {
        // normaliser date en Y-m-d
        if (!empty($data['date'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $data['date']);
            if ($d)
                $data['date'] = $d->format('Y-m-d');
        } else {
            $data['date'] = (new \DateTime())->format('Y-m-d');
        }

        // ajouter le timestamp d'enregistrement
        $data['created_at'] = date('Y-m-d H:i:s');

        // calculer prix global si PU fourni
        if (isset($data['quantite']) && isset($data['pu'])) {
            $data['pg'] = floatval($data['quantite']) * floatval($data['pu']);
        } elseif (isset($data['quantite'])) {
            $data['pg'] = floatval($data['quantite']) * (floatval($data['pu'] ?? 0));
        } else {
            $data['pg'] = 0;
        }

        // garantir structure entree/sortie/stock
        $this->normalizeEntryArrays($data);

        $res = $this->collection->insertOne($data);
        // recalcul FIFO pour ce compte
        if (!empty($data['compte'])) {
            $this->recomputeCompte($data['compte']);
        }
        return $res->getInsertedId();
    }

    public function getAll()
    {
        return $this->collection->find([], ['sort' => ['date' => 1]])->toArray();
    }

    public function deleteEntry($id)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return false;
        }
        $doc = $this->collection->findOne(['_id' => $oid]);
        $res = $this->collection->deleteOne(['_id' => $oid]);
        $ok = ($res->getDeletedCount() > 0);
        if ($ok && !empty($doc['compte'])) {
            $this->recomputeCompte($doc['compte']);
        }
        return $ok;
    }

    public function getEntry($id)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return null;
        }
        return $this->collection->findOne(['_id' => $oid]);
    }

    public function updateEntry($id, $data)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return false;
        }

        if (!empty($data['date'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $data['date']);
            if ($d)
                $data['date'] = $d->format('Y-m-d');
        }

        if (isset($data['quantite']) && isset($data['pu'])) {
            $data['pg'] = floatval($data['quantite']) * floatval($data['pu']);
        }

        $this->normalizeEntryArrays($data);

        $res = $this->collection->updateOne(['_id' => $oid], ['$set' => $data]);
        if (!empty($data['compte'])) {
            $this->recomputeCompte($data['compte']);
        }
        return ($res->getModifiedCount() > 0);
    }

    public function getFiltered($filters = [])
    {
        $query = [];

        // Dates en format Y-m-d -> comparaison lexicographique fonctionne
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut'])) {
                $d = \DateTime::createFromFormat('Y-m-d', $filters['date_debut']);
                if ($d)
                    $dateQuery['$gte'] = $d->format('Y-m-d');
            }
            if (!empty($filters['date_fin'])) {
                $d2 = \DateTime::createFromFormat('Y-m-d', $filters['date_fin']);
                if ($d2)
                    $dateQuery['$lte'] = $d2->format('Y-m-d');
            }
            if (!empty($dateQuery))
                $query['date'] = $dateQuery;
        }

        if (!empty($filters['compte_filtre'])) {
            $val = $filters['compte_filtre'];
            $query['compte'] = ['$regex' => $val, '$options' => 'i'];
        }

        if (!empty($filters['lieu'])) {
            $query['lieu'] = ['$regex' => $filters['lieu'], '$options' => 'i'];
        }

        return $this->collection->find($query, ['sort' => ['date' => 1]])->toArray();
    }

    public function getFilteredWithPagination($filters = [], $page = 1, $itemsPerPage = 20)
    {
        $query = [];

        // Dates en format Y-m-d -> comparaison lexicographique fonctionne
        if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
            $dateQuery = [];
            if (!empty($filters['date_debut'])) {
                $d = \DateTime::createFromFormat('Y-m-d', $filters['date_debut']);
                if ($d)
                    $dateQuery['$gte'] = $d->format('Y-m-d');
            }
            if (!empty($filters['date_fin'])) {
                $d2 = \DateTime::createFromFormat('Y-m-d', $filters['date_fin']);
                if ($d2)
                    $dateQuery['$lte'] = $d2->format('Y-m-d');
            }
            if (!empty($dateQuery))
                $query['date'] = $dateQuery;
        }

        if (!empty($filters['compte_filtre'])) {
            $val = $filters['compte_filtre'];
            $query['compte'] = ['$regex' => $val, '$options' => 'i'];
        }

        if (!empty($filters['lieu'])) {
            $query['lieu'] = ['$regex' => $filters['lieu'], '$options' => 'i'];
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
        $items = $this->collection->find($query, $options)->toArray();

        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }

    public function getLastByCompte($compte)
    {
        $cursor = $this->collection->find(['compte' => $compte], ['sort' => ['date' => -1], 'limit' => 1])->toArray();
        return (!empty($cursor) ? $cursor[0] : null);
    }

    public function getComptesMap()
    {
        $cursor = $this->collection->find([], ['projection' => ['compte' => 1, 'intitule' => 1]]);
        $map = [];
        foreach ($cursor as $doc) {
            if (!empty($doc['compte'])) {
                $map[$doc['compte']] = $doc['intitule'] ?? '';
            }
        }
        return $map;
    }

    private function normalizeEntryArrays(array &$data)
    {
        // s'assurer que structure entree/sortie/stock existe
        if (!isset($data['entree']) || !is_array($data['entree'])) {
            $data['entree'] = [
                'qte' => $data['operation'] === 'entree' ? floatval($data['quantite'] ?? 0) : 0,
                'pu' => $data['operation'] === 'entree' ? floatval($data['pu'] ?? 0) : 0,
                'total' => $data['operation'] === 'entree' ? floatval($data['pg'] ?? 0) : 0,
            ];
        }
        if (!isset($data['sortie']) || !is_array($data['sortie'])) {
            $data['sortie'] = [
                'qte' => $data['operation'] === 'sortie' ? floatval($data['quantite'] ?? 0) : 0,
                'pu' => $data['operation'] === 'sortie' ? floatval($data['pu'] ?? 0) : 0,
                'total' => $data['operation'] === 'sortie' ? floatval($data['pg'] ?? 0) : 0,
            ];
        }
        if (!isset($data['stock']) || !is_array($data['stock'])) {
            $data['stock'] = [
                'qte' => intval($data['stock']['qte'] ?? ($data['entree']['qte'] ?? 0)),
                'pu' => floatval($data['stock']['pu'] ?? 0),
                'total' => floatval($data['stock']['total'] ?? 0),
            ];
        }
    }

    // Recalcule le stock d'un compte en appliquant FIFO et moyenne des PU
    public function recomputeCompte(string $compte): void
    {
        $cursor = $this->collection->find(['compte' => $compte], ['sort' => ['date' => 1, '_id' => 1]]);
        $lots = []; // chaque lot: ['qte' => float, 'pu' => float]
        $stockQty = 0.0;
        $stockAvgPu = 0.0;
        foreach ($cursor as $doc) {
            $id = $doc['_id'];
            $entQ = floatval($doc['entree']['qte'] ?? 0);
            $entPU = floatval($doc['entree']['pu'] ?? 0);
            $sorQ = floatval($doc['sortie']['qte'] ?? 0);

            if ($entQ > 0) {
                $lots[] = ['qte' => $entQ, 'pu' => $entPU];
                $stockTotalValue = ($stockQty * $stockAvgPu) + ($entQ * $entPU);
                $stockQty += $entQ;
                $stockAvgPu = $stockQty > 0 ? $stockTotalValue / $stockQty : 0;
            }

            $sortieTotalCost = 0.0;
            if ($sorQ > 0) {
                $qtyToConsume = $sorQ;
                $newLots = [];
                foreach ($lots as $lot) {
                    if ($qtyToConsume <= 0) {
                        $newLots[] = $lot;
                        continue;
                    }
                    $consume = min($lot['qte'], $qtyToConsume);
                    $sortieTotalCost += $consume * $lot['pu'];
                    $remaining = $lot['qte'] - $consume;
                    if ($remaining > 0) {
                        $newLots[] = ['qte' => $remaining, 'pu' => $lot['pu']];
                    }
                    $qtyToConsume -= $consume;
                }
                $lots = $newLots;
                $stockQty = max(0.0, $stockQty - $sorQ);
                // recalcul moyenne sur les lots restants
                $totalValue = 0.0;
                foreach ($lots as $lot) {
                    $totalValue += $lot['qte'] * $lot['pu'];
                }
                $stockAvgPu = $stockQty > 0 ? ($totalValue / $stockQty) : 0.0;
            }

            $update = [
                'stock.qte' => $stockQty,
                'stock.pu' => $stockAvgPu,
                'stock.total' => $stockQty * $stockAvgPu,
            ];
            if ($sorQ > 0) {
                $update['sortie.pu'] = $sorQ > 0 ? ($sortieTotalCost / $sorQ) : 0;
                $update['sortie.total'] = $sortieTotalCost;
            }
            $this->collection->updateOne(['_id' => $id], ['$set' => $update]);
        }
    }

    // Utilisée par JournalController pour compatibilité
    public function addOrUpdate($compte, $intitule, $debit, $credit): void
    {
        // Intentionnellement vide: le journal n'alimente pas directement la fiche de stock ici.
        // Cette méthode existe pour éviter une erreur si elle est appelée.
    }

    public function countDocuments($query = [])
    {
        return $this->collection->countDocuments($query);
    }
}