<?php
namespace App\Models;
use App\Core\Model;
use App\Models\BalanceModel;
use App\Models\CompteModel;
use MongoDB\BSON\ObjectId;

class BilanModel extends Model
{
    private $collection_initial;
    private $collection_copies;

    public function __construct()
    {
        parent::__construct();
        $this->collection_initial = $this->db->bilan_initial;
        $this->collection_copies = $this->db->bilan_copies;
    }

    /**
     * Get the initial balance sheet
     */
    public function getInitialBilan()
    {
        $result = $this->collection_initial->findOne([], ['sort' => ['created_at' => -1]]);
        return $result ?: null;
    }

    /**
     * Save or update the initial balance sheet
     */
    public function saveInitialBilan($data)
    {
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();
        if (!isset($data['_id'])) {
            $data['created_at'] = $data['updated_at'];
        }
        $result = $this->collection_initial->replaceOne(
            ['_id' => $data['_id'] ?? null],
            $data,
            ['upsert' => true]
        );
        return $result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0;
    }

    /**
     * Add an account to the initial balance
     */
    public function addAccountToInitial($accountData)
    {
        $bilan = $this->getInitialBilan();
        if (!$bilan) {
            $bilan = [
                'title' => 'Bilan Initial',
                'date' => date('Y-m-d'),
                'accounts' => [],
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
                'updated_at' => new \MongoDB\BSON\UTCDateTime()
            ];
        }

        // Check if account already exists
        $exists = false;
        foreach ($bilan['accounts'] as &$acc) {
            if ($acc['code'] === $accountData['code']) {
                $acc = array_merge($acc, $accountData);
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $bilan['accounts'][] = $accountData;
        }

        return $this->saveInitialBilan($bilan);
    }

    /**
     * Remove an account from initial balance
     */
    public function removeAccountFromInitial($accountCode)
    {
        $bilan = $this->getInitialBilan();
        if (!$bilan) return false;

        $bilan['accounts'] = array_filter($bilan['accounts'], function($acc) use ($accountCode) {
            return $acc['code'] !== $accountCode;
        });

        return $this->saveInitialBilan($bilan);
    }

    /**
     * Get current balance sheet from journal balances
     */
    public function getCurrentBilan()
    {
        $balanceModel = new BalanceModel();
        $balances = $balanceModel->getBalance();

        $bilan = [
            'title' => 'Bilan en Cours',
            'date' => date('Y-m-d'),
            'accounts' => []
        ];

        $compteModel = new CompteModel();
        $allComptes = $compteModel->getAll();

        foreach ($balances as $balance) {
            $code = $balance['_id'];
            $solde = $balance['debit'] - $balance['credit'];

            // Find account name
            $accountName = '';
            foreach ($allComptes as $compte) {
                if ($compte['code'] === $code) {
                    $accountName = $compte['intitule'];
                    break;
                }
            }

            // Determine category based on account code
            $category = $this->getCategoryFromCode($code);

            $bilan['accounts'][] = [
                'code' => $code,
                'name' => $accountName,
                'debit' => $balance['debit'],
                'credit' => $balance['credit'],
                'solde' => $solde,
                'category' => $category,
                'type' => $solde >= 0 ? 'debit' : 'credit',
                'value' => abs($solde)
            ];
        }

        return $bilan;
    }

    /**
     * Save a periodic copy of the balance sheet
     */
    public function savePeriodicCopy($title = null, $date = null)
    {
        $currentBilan = $this->getCurrentBilan();
        $currentBilan['title'] = $title ?: 'Copie Périodique - ' . date('Y-m-d H:i:s');
        $currentBilan['date'] = $date ?: date('Y-m-d');
        $currentBilan['status'] = 'active'; // active, archived
        $currentBilan['created_at'] = new \MongoDB\BSON\UTCDateTime();

        $result = $this->collection_copies->insertOne($currentBilan);
        return $result->getInsertedId();
    }

    /**
     * Get all periodic copies
     */
    public function getPeriodicCopies($status = null)
    {
        $filter = [];
        if ($status) {
            $filter['status'] = $status;
        }
        return $this->collection_copies->find($filter, [
            'sort' => ['created_at' => -1]
        ])->toArray();
    }

    /**
     * Get a specific periodic copy
     */
    public function getPeriodicCopy($id)
    {
        return $this->collection_copies->findOne(['_id' => new ObjectId($id)]);
    }

    /**
     * Archive a periodic copy
     */
    public function archivePeriodicCopy($id)
    {
        return $this->collection_copies->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => ['status' => 'archived']]
        );
    }

    /**
     * Restore an archived periodic copy
     */
    public function restorePeriodicCopy($id)
    {
        return $this->collection_copies->updateOne(
            ['_id' => new ObjectId($id)],
            ['$unset' => ['status' => 1]]
        );
    }

    /**
     * Delete a periodic copy (only if not archived)
     */
    public function deletePeriodicCopy($id)
    {
        return $this->collection_copies->deleteOne([
            '_id' => new ObjectId($id),
            'status' => ['$ne' => 'archived']
        ]);
    }

    /**
     * Determine category from account code
     */
    private function getCategoryFromCode($code)
    {
        $firstDigit = substr($code, 0, 1);
        $secondDigit = substr($code, 1, 1);

        // Excluded accounts
        if (substr($code, 0, 2) === '18' || substr($code, 0, 2) === '46') {
            return 'excluded';
        }

        // Actif circulant special cases
        if (substr($code,0,3) === '472') {
            return 'creances';
        }

        switch ($firstDigit) {
            case '1':
                return 'capitaux_propres';
            case '2':
                return 'actif_immobilise';
            case '3':
                return 'stocks'; // Actif circulant
            case '4':
                // Handle passif circulant accounts (40-45, 471)
                $prefix2 = substr($code, 0, 2);
                if (in_array($prefix2, ['40','41','42','43','44','45']) || substr($code,0,3) === '471') {
                    return 'passif_circulant';
                }
                // 46 and others in class 4 are excluded or creances
                if (substr($code, 0, 2) === '46') {
                    return 'excluded';
                }
                return 'creances'; // fallback for other class 4 accounts
            case '5':
                if ($secondDigit == '0' || $secondDigit == '2' || $secondDigit == '7') {
                    return 'tresorerie_actif';
                }
                return 'autres';
            case '6':
                return 'charges';
            case '7':
                return 'produits';
            default:
                // For passif accounts in initial balance
                if (substr($code, 0, 2) === '16') {
                    return 'emprunts'; // Passif non courant
                } elseif (substr($code, 0, 2) === '55' || substr($code, 0, 2) === '56') {
                    return 'tresorerie_passif';
                }
                return 'autres';
        }
    }

    /**
     * Get balance sheet structure with totals
     */
    public function getBilanStructure($bilan)
    {
        $structure = [
            'actif' => [
                'immobilise' => ['total' => 0, 'accounts' => []],
                'circulant' => ['total' => 0, 'accounts' => []],
                'tresorerie' => ['total' => 0, 'accounts' => []]
            ],
            'passif' => [
                'capitaux_propres' => ['total' => 0, 'accounts' => []],
                'non_courant' => ['total' => 0, 'accounts' => []],
                'circulant' => ['total' => 0, 'accounts' => []],
                'tresorerie' => ['total' => 0, 'accounts' => []]
            ]
        ];

        $accounts = $bilan['accounts'] ?? [];
        foreach ($accounts as $account) {
            $category = $account['category'] ?? $this->getCategoryFromCode($account['code']);

            if ($category === 'excluded') {
                continue;
            }

            $value = isset($account['value']) ? $account['value'] : abs($account['solde'] ?? 0);

            // Actif
            if ($category === 'actif_immobilise') {
                $structure['actif']['immobilise']['accounts'][] = $account;
                $structure['actif']['immobilise']['total'] += $value;
            } elseif (in_array($category, ['stocks', 'creances'])) {
                $structure['actif']['circulant']['accounts'][] = $account;
                $structure['actif']['circulant']['total'] += $value;
            } elseif ($category === 'tresorerie_actif') {
                $structure['actif']['tresorerie']['accounts'][] = $account;
                $structure['actif']['tresorerie']['total'] += $value;
            }
            // Passif
            elseif ($category === 'capitaux_propres') {
                $structure['passif']['capitaux_propres']['accounts'][] = $account;
                $structure['passif']['capitaux_propres']['total'] += $value;
            } elseif ($category === 'emprunts') {
                $structure['passif']['non_courant']['accounts'][] = $account;
                $structure['passif']['non_courant']['total'] += $value;
            } elseif ($category === 'passif_circulant') {
                $structure['passif']['circulant']['accounts'][] = $account;
                $structure['passif']['circulant']['total'] += $value;
            } elseif ($category === 'tresorerie_passif') {
                $structure['passif']['tresorerie']['accounts'][] = $account;
                $structure['passif']['tresorerie']['total'] += $value;
            }
        }

        // Calculate totals
        $structure['actif']['total'] = $structure['actif']['immobilise']['total'] +
                                      $structure['actif']['circulant']['total'] +
                                      $structure['actif']['tresorerie']['total'];

        $structure['passif']['total'] = $structure['passif']['capitaux_propres']['total'] +
                                       $structure['passif']['non_courant']['total'] +
                                       $structure['passif']['circulant']['total'] +
                                       $structure['passif']['tresorerie']['total'];

        // Sort accounts by value descending in each section
        $this->sortAccountsByValueDescending($structure['actif']['immobilise']['accounts']);
        $this->sortAccountsByValueDescending($structure['actif']['circulant']['accounts']);
        $this->sortAccountsByValueDescending($structure['actif']['tresorerie']['accounts']);
        $this->sortAccountsByValueDescending($structure['passif']['capitaux_propres']['accounts']);
        $this->sortAccountsByValueDescending($structure['passif']['non_courant']['accounts']);
        $this->sortAccountsByValueDescending($structure['passif']['circulant']['accounts']);
        $this->sortAccountsByValueDescending($structure['passif']['tresorerie']['accounts']);

        return $structure;
    }

    /**
     * Sort accounts by account code in ascending order
     */
    private function sortAccountsByValueDescending(&$accounts)
    {
        usort($accounts, function($a, $b) {
            $valueA = isset($a['value']) ? $a['value'] : 0;
            $valueB = isset($b['value']) ? $b['value'] : 0;
            return $valueB <=> $valueA; // Descending by value
        });
    }
}