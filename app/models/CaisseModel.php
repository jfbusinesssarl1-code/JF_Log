<?php
namespace App\Models;
use App\Core\Model;
use MongoDB\BSON\ObjectId as ObjectIdBson;

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
    } catch (\Throwable $e) {
    }
  }

  public function insert($data)
  {
    $data['created_at'] = date('Y-m-d H:i:s');
    return $this->collection->insertOne($data);
  }

  public function update($id, $data)
  {
    return $this->collection->updateOne(
      ['_id' => new ObjectIdBson($id)],
      ['$set' => $data]
    );
  }

  public function delete($id)
  {
    return $this->collection->deleteOne(['_id' => new ObjectIdBson($id)]);
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

    // Calculer solde cumulatif
    $solde = 0.0;
    foreach ($items as &$it) {
      $montant = floatval($it['montant'] ?? 0);
      if (($it['type'] ?? '') === 'entree') {
        $solde += $montant;
        $it['recette'] = $montant;
        $it['depense'] = 0.0;
      } else {
        $solde -= $montant;
        $it['recette'] = 0.0;
        $it['depense'] = $montant;
      }
      $it['solde'] = $solde;
    }
    return $items;
  }

  public function getById($id)
  {
    return $this->collection->findOne(['_id' => new ObjectIdBson($id)]);
  }
}