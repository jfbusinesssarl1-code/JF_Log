<?php
namespace App\Models;
use App\Core\Model;
use MongoDB\BSON\ObjectId as ObjectIdBson;
use App\Helpers\PaginationHelper;

class CaisseModel extends Model
{
  private $collection;
  public function __construct()
  {
    parent::__construct();
    if (!class_exists(class: ObjectIdBson::class)) {
      throw new \RuntimeException(message: 'MongoDB PHP extension or mongodb/mongodb package not installed. Run "composer require mongodb/mongodb" and enable the php_mongodb extension in php.ini.');
    }
    $this->collection = $this->db->caisse;
    try {
      $this->collection->createIndex(['date' => 1]);
      $this->collection->createIndex(['type' => 1]);
      $this->collection->createIndex(['operateur' => 1]);
      $this->collection->createIndex(['numero_bon_manuscrit' => 1]);
      $this->collection->createIndex(['created_at' => -1]);
    } catch (\Throwable $e) {
    }
  }

  public function insert($data)
  {
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['montant'] = floatval($data['montant'] ?? 0);
    $res = $this->collection->insertOne($data);
    // Recompute solde for all entries to ensure each document has a stored solde
    try {
      $this->recomputeAllSoldes();
    } catch (\Throwable $e) {
      error_log('CaisseModel::insert recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function update($id, $data)
  {
    $res = $this->collection->updateOne(
      ['_id' => new ObjectIdBson($id)],
      ['$set' => $data]
    );
    try {
      $this->recomputeAllSoldes();
    } catch (\Throwable $e) {
      error_log('CaisseModel::update recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function delete($id)
  {
    $res = $this->collection->deleteOne(['_id' => new ObjectIdBson($id)]);
    try {
      $this->recomputeAllSoldes();
    } catch (\Throwable $e) {
      error_log('CaisseModel::delete recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function getAll($filters = [])
  {
    $query = [];
    if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
      $range = [];
      if (!empty($filters['date_debut']))
        $range['$gte'] = $filters['date_debut'];
      if (!empty($filters['date_fin']))
        $range['$lte'] = $filters['date_fin'];
      if ($range)
        $query['date'] = $range;
    }
    if (!empty($filters['type'])) {
      $query['type'] = $filters['type'];
    }
    if (!empty($filters['operateur'])) {
      $query['operateur'] = $filters['operateur'];
    }
    if (!empty($filters['numero_bon_manuscrit'])) {
      $query['numero_bon_manuscrit'] = $filters['numero_bon_manuscrit'];
    }
    $options = ['sort' => ['date' => 1, '_id' => 1]];
    $items = $this->collection->find($query, $options)->toArray();

    // If documents do not have stored 'solde', recompute global soldes and re-fetch once
    $needRecompute = false;
    foreach ($items as $it) {
      if (!isset($it['solde'])) {
        $needRecompute = true;
        break;
      }
    }
    if ($needRecompute) {
      try {
        $this->recomputeAllSoldes();
        $items = $this->collection->find($query, $options)->toArray();
      } catch (\Throwable $e) {
        error_log('CaisseModel::getAll recomputeAllSoldes failed: ' . $e->getMessage());
      }
    }

    // Ensure recette/depense are present for rendering
    foreach ($items as &$it) {
      $montant = floatval($it['montant'] ?? 0);
      if (($it['type'] ?? '') === 'entree') {
        $it['recette'] = $montant;
        $it['depense'] = 0.0;
      } else {
        $it['recette'] = 0.0;
        $it['depense'] = $montant;
      }
      $it['solde'] = isset($it['solde']) ? floatval($it['solde']) : 0.0;
    }
    return $items;
  }

  public function getAllWithPagination($filters = [], $page = 1, $itemsPerPage = 20)
  {
    $query = [];
    if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
      $range = [];
      if (!empty($filters['date_debut']))
        $range['$gte'] = $filters['date_debut'];
      if (!empty($filters['date_fin']))
        $range['$lte'] = $filters['date_fin'];
      if ($range)
        $query['date'] = $range;
    }
    if (!empty($filters['type'])) {
      $query['type'] = $filters['type'];
    }
    if (!empty($filters['operateur'])) {
      $query['operateur'] = $filters['operateur'];
    }
    if (!empty($filters['numero_bon_manuscrit'])) {
      $query['numero_bon_manuscrit'] = $filters['numero_bon_manuscrit'];
    }

    // Initialiser la pagination
    $pagination = new PaginationHelper($page, $itemsPerPage);

    // Compter le nombre total d'éléments
    $totalCount = $this->collection->countDocuments($query);
    $pagination->setTotalItems($totalCount);

    // Récupérer les éléments pour la page actuelle
    $options = [
      'sort' => ['date' => 1, '_id' => 1],
      'skip' => $pagination->getOffset(),
      'limit' => $pagination->getLimit()
    ];
    $items = $this->collection->find($query, $options)->toArray();

    // If any item lacks stored solde, recompute globally and re-fetch
    $needRecompute = false;
    foreach ($items as $it) {
      if (!isset($it['solde'])) {
        $needRecompute = true;
        break;
      }
    }
    if ($needRecompute) {
      try {
        $this->recomputeAllSoldes();
        $items = $this->collection->find($query, $options)->toArray();
      } catch (\Throwable $e) {
        error_log('CaisseModel::getAllWithPagination recomputeAllSoldes failed: ' . $e->getMessage());
      }
    }

    foreach ($items as &$it) {
      $montant = floatval($it['montant'] ?? 0);
      if (($it['type'] ?? '') === 'entree') {
        $it['recette'] = $montant;
        $it['depense'] = 0.0;
      } else {
        $it['recette'] = 0.0;
        $it['depense'] = $montant;
      }
      $it['solde'] = isset($it['solde']) ? floatval($it['solde']) : 0.0;
    }

    return [
      'items' => $items,
      'pagination' => $pagination
    ];
  }

  /**
   * Recompute and persist the cumulative 'solde' for all documents in the collection.
   * This writes the computed solde back to each document (only when it differs) so
   * that filtering does not change the stored balances.
   */
  private function recomputeAllSoldes()
  {
    $cursor = $this->collection->find([], ['sort' => ['date' => 1, '_id' => 1]]);
    $solde = 0.0;
    foreach ($cursor as $doc) {
      $montant = floatval($doc['montant'] ?? 0);
      if (($doc['type'] ?? '') === 'entree') {
        $solde += $montant;
      } else {
        $solde -= $montant;
      }
      $current = isset($doc['solde']) ? floatval($doc['solde']) : null;
      if ($current !== $solde) {
        $this->collection->updateOne(['_id' => $doc['_id']], ['$set' => ['solde' => $solde]]);
      }
    }
  }

  public function getById($id)
  {
    return $this->collection->findOne(['_id' => new ObjectIdBson($id)]);
  }

  public function countAll($query = [])
  {
    return $this->collection->countDocuments($query);
  }
}