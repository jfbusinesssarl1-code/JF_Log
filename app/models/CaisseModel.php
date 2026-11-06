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
      $this->collection->createIndex(['created_by_id' => 1, 'date' => 1]);
      $this->collection->createIndex(['created_at' => -1]);
    } catch (\Throwable $e) {
    }
  }

  public function insert($data)
  {
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['montant'] = floatval($data['montant'] ?? 0);
    $res = $this->collection->insertOne($data);
    try {
      if (($data['created_by_id'] ?? null) instanceof ObjectIdBson) {
        $this->recomputeCreatorSoldes((string) $data['created_by_id'], $data['created_by'] ?? '');
      } else {
        $this->recomputeAllSoldes();
      }
    } catch (\Throwable $e) {
      error_log('CaisseModel::insert recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function update($id, $data, $creatorId = null, $creatorUsername = null)
  {
    $query = ['_id' => new ObjectIdBson($id)];
    $creatorQuery = $this->buildCreatorQuery([
      'creator_id' => $creatorId,
      'creator_username' => $creatorUsername
    ]);
    if ($creatorQuery) {
      $query = array_merge($query, $creatorQuery);
    }
    $res = $this->collection->updateOne(
      $query,
      ['$set' => $data]
    );
    try {
      if ($creatorId !== null || $creatorUsername !== null) {
        $this->recomputeCreatorSoldes($creatorId, $creatorUsername);
      } else {
        $this->recomputeAllSoldes();
      }
    } catch (\Throwable $e) {
      error_log('CaisseModel::update recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function delete($id, $creatorId = null, $creatorUsername = null)
  {
    $query = ['_id' => new ObjectIdBson($id)];
    $creatorQuery = $this->buildCreatorQuery([
      'creator_id' => $creatorId,
      'creator_username' => $creatorUsername
    ]);
    if ($creatorQuery) {
      $query = array_merge($query, $creatorQuery);
    }
    $res = $this->collection->deleteOne($query);
    try {
      if ($creatorId !== null || $creatorUsername !== null) {
        $this->recomputeCreatorSoldes($creatorId, $creatorUsername);
      } else {
        $this->recomputeAllSoldes();
      }
    } catch (\Throwable $e) {
      error_log('CaisseModel::delete recomputeAllSoldes failed: ' . $e->getMessage());
    }
    return $res;
  }

  public function getAll($filters = [])
  {
    $query = $this->buildQuery($filters);
    $options = ['sort' => ['date' => 1, '_id' => 1]];
    $items = $this->collection->find($query, $options)->toArray();

    // Recompute the applicable balance stream if an older operation has no stored balance.
    $needRecompute = false;
    foreach ($items as $it) {
      if (!isset($it['solde'])) {
        $needRecompute = true;
        break;
      }
    }
    if ($needRecompute) {
      try {
        $this->recomputeForFilters($filters);
        $items = $this->collection->find($query, $options)->toArray();
      } catch (\Throwable $e) {
        error_log('CaisseModel::getAll balance recomputation failed: ' . $e->getMessage());
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
    $query = $this->buildQuery($filters);

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

    // Recompute the applicable balance stream if an older operation has no stored balance.
    $needRecompute = false;
    foreach ($items as $it) {
      if (!isset($it['solde'])) {
        $needRecompute = true;
        break;
      }
    }
    if ($needRecompute) {
      try {
        $this->recomputeForFilters($filters);
        $items = $this->collection->find($query, $options)->toArray();
      } catch (\Throwable $e) {
        error_log('CaisseModel::getAllWithPagination balance recomputation failed: ' . $e->getMessage());
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
   * Recompute and persist cumulative balances independently for each creator.
   */
  private function recomputeAllSoldes()
  {
    $cursor = $this->collection->find([], ['sort' => ['date' => 1, '_id' => 1]]);
    $soldes = [];
    foreach ($cursor as $doc) {
      $creator = isset($doc['created_by_id'])
        ? (string) $doc['created_by_id']
        : (string) ($doc['created_by'] ?? 'legacy');
      if (!isset($soldes[$creator])) {
        $soldes[$creator] = 0.0;
      }
      $montant = floatval($doc['montant'] ?? 0);
      if (($doc['type'] ?? '') === 'entree') {
        $soldes[$creator] += $montant;
      } else {
        $soldes[$creator] -= $montant;
      }
      $current = isset($doc['solde']) ? floatval($doc['solde']) : null;
      if ($current !== $soldes[$creator]) {
        $this->collection->updateOne(
          ['_id' => $doc['_id']],
          ['$set' => ['solde' => $soldes[$creator]]]
        );
      }
    }
  }

  public function recomputeCreatorSoldes($creatorId, $creatorUsername): void
  {
    $creatorQuery = $this->buildCreatorQuery([
      'creator_id' => $creatorId,
      'creator_username' => $creatorUsername
    ]);
    if (!$creatorQuery) {
      return;
    }

    $cursor = $this->collection->find($creatorQuery, ['sort' => ['date' => 1, '_id' => 1]]);
    $balance = 0.0;
    foreach ($cursor as $doc) {
      $amount = (float) ($doc['montant'] ?? 0);
      $balance += (($doc['type'] ?? '') === 'entree') ? $amount : -$amount;
      if (!isset($doc['solde']) || (float) $doc['solde'] !== $balance) {
        $this->collection->updateOne(
          ['_id' => $doc['_id']],
          ['$set' => ['solde' => $balance]]
        );
      }
    }
  }

  private function recomputeForFilters($filters): void
  {
    if (!empty($filters['creator_id']) || !empty($filters['creator_username'])) {
      $this->recomputeCreatorSoldes(
        $filters['creator_id'] ?? '',
        $filters['creator_username'] ?? ''
      );
      return;
    }
    $this->recomputeAllSoldes();
  }

  public function getById($id, $creatorId = null, $creatorUsername = null)
  {
    $query = ['_id' => new ObjectIdBson($id)];
    $creatorQuery = $this->buildCreatorQuery([
      'creator_id' => $creatorId,
      'creator_username' => $creatorUsername
    ]);
    if ($creatorQuery) {
      $query = array_merge($query, $creatorQuery);
    }
    return $this->collection->findOne($query);
  }

  public function countAll($query = [])
  {
    return $this->collection->countDocuments($query);
  }

  public function countFiltered($filters = [])
  {
    return $this->collection->countDocuments($this->buildQuery($filters));
  }

  public function getOperationTotals($filters = []): array
  {
    $query = $this->buildQuery($filters);
    $pipeline = [];
    if ($query) {
      $pipeline[] = ['$match' => $query];
    }
    $pipeline[] =
      [
        '$group' => [
          '_id' => null,
          'recettes' => [
            '$sum' => [
              '$cond' => [
                ['$eq' => ['$type', 'entree']],
                ['$ifNull' => ['$montant', 0]],
                0
              ]
            ]
          ],
          'depenses' => [
            '$sum' => [
              '$cond' => [
                ['$eq' => ['$type', 'sortie']],
                ['$ifNull' => ['$montant', 0]],
                0
              ]
            ]
          ]
        ]
      ];
    $result = $this->collection->aggregate($pipeline)->toArray();
    $recettes = (float) ($result[0]['recettes'] ?? 0);
    $depenses = (float) ($result[0]['depenses'] ?? 0);

    return [
      'recettes' => $recettes,
      'depenses' => $depenses,
      'solde' => $recettes - $depenses
    ];
  }

  public function getDashboardSummary($today, $monthStart, $filters = [])
  {
    $amount = ['$ifNull' => ['$montant', 0]];
    $isToday = ['$eq' => ['$date', $today]];
    $isCurrentMonth = [
      '$and' => [
        ['$gte' => ['$date', $monthStart]],
        ['$lte' => ['$date', $today]]
      ]
    ];
    $isIncome = ['$eq' => ['$type', 'entree']];

    $pipeline = [];
    $creatorQuery = $this->buildCreatorQuery($filters);
    if ($creatorQuery) {
      $pipeline[] = ['$match' => $creatorQuery];
    }
    $pipeline[] = [
        '$group' => [
          '_id' => null,
          'todayIncome' => [
            '$sum' => [
              '$cond' => [
                ['$and' => [$isToday, $isIncome]],
                $amount,
                0
              ]
            ]
          ],
          'todayExpense' => [
            '$sum' => [
              '$cond' => [
                ['$and' => [$isToday, ['$eq' => ['$type', 'sortie']]]],
                $amount,
                0
              ]
            ]
          ],
          'monthIncome' => [
            '$sum' => [
              '$cond' => [
                ['$and' => [$isCurrentMonth, $isIncome]],
                $amount,
                0
              ]
            ]
          ],
          'monthExpense' => [
            '$sum' => [
              '$cond' => [
                ['$and' => [$isCurrentMonth, ['$eq' => ['$type', 'sortie']]]],
                $amount,
                0
              ]
            ]
          ],
          'monthOperations' => [
            '$sum' => ['$cond' => [$isCurrentMonth, 1, 0]]
          ]
        ]
    ];
    $result = $this->collection->aggregate($pipeline)->toArray();

    $balancePipeline = [];
    if ($creatorQuery) {
      $balancePipeline[] = ['$match' => $creatorQuery];
    }
    $balancePipeline[] = ['$sort' => ['date' => 1, '_id' => 1]];
    $balancePipeline[] = [
      '$group' => [
        '_id' => ['$ifNull' => ['$created_by_id', '$created_by']],
        'balance' => ['$last' => ['$ifNull' => ['$solde', 0]]]
      ]
    ];
    $balancePipeline[] = [
      '$group' => [
        '_id' => null,
        'balance' => ['$sum' => '$balance']
      ]
    ];
    $balanceResult = $this->collection->aggregate($balancePipeline)->toArray();

    $summary = [
      'balance' => 0.0,
      'todayIncome' => 0.0,
      'todayExpense' => 0.0,
      'monthIncome' => 0.0,
      'monthExpense' => 0.0,
      'monthOperations' => 0
    ];
    if (!empty($result)) {
      foreach ($summary as $key => $default) {
        if ($key !== 'balance') {
          $summary[$key] = $key === 'monthOperations'
            ? (int) ($result[0][$key] ?? $default)
            : (float) ($result[0][$key] ?? $default);
        }
      }
    }
    if (!empty($balanceResult)) {
      $summary['balance'] = (float) ($balanceResult[0]['balance'] ?? 0);
    }

    return $summary;
  }

  public function backfillCreatorIds()
  {
    $users = $this->db->users->find(
      ['username' => ['$type' => 'string']],
      ['projection' => ['username' => 1]]
    );
    $updated = false;
    foreach ($users as $user) {
      $result = $this->collection->updateMany(
        [
          'created_by' => $user['username'],
          'created_by_id' => ['$exists' => false]
        ],
        ['$set' => ['created_by_id' => $user['_id']]]
      );
      if ($result->getModifiedCount() > 0) {
        $updated = true;
      }
    }
    if ($updated) {
      $this->recomputeAllSoldes();
    }
  }

  private function buildQuery($filters = [])
  {
    $query = [];
    if (!empty($filters['date_debut']) || !empty($filters['date_fin'])) {
      $range = [];
      if (!empty($filters['date_debut'])) {
        $range['$gte'] = $filters['date_debut'];
      }
      if (!empty($filters['date_fin'])) {
        $range['$lte'] = $filters['date_fin'];
      }
      if ($range) {
        $query['date'] = $range;
      }
    }
    foreach (['type', 'operateur', 'numero_bon_manuscrit'] as $field) {
      if (!empty($filters[$field])) {
        $query[$field] = $filters[$field];
      }
    }

    $creatorQuery = $this->buildCreatorQuery($filters);
    return $creatorQuery ? array_merge($query, $creatorQuery) : $query;
  }

  private function buildCreatorQuery($filters = [])
  {
    $creatorId = $filters['creator_id'] ?? '';
    $creatorUsername = $filters['creator_username'] ?? '';
    if ($creatorId === '' && $creatorUsername === '') {
      return [];
    }

    $conditions = [];
    if (is_string($creatorId) && preg_match('/^[a-f0-9]{24}$/i', $creatorId)) {
      $conditions[] = ['created_by_id' => new ObjectIdBson($creatorId)];
    }
    if (is_string($creatorUsername) && $creatorUsername !== '') {
      $conditions[] = [
        'created_by' => $creatorUsername,
        'created_by_id' => ['$exists' => false]
      ];
    }
    if (count($conditions) === 1) {
      return $conditions[0];
    }
    return $conditions ? ['$or' => $conditions] : ['_id' => ['$exists' => false]];
  }
}