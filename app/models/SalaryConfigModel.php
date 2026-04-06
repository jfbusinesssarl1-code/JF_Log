<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

class SalaryConfigModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->salary_configs;
        try {
            $this->collection->createIndex(['site_id' => 1, 'category' => 1], ['unique' => true]);
        } catch (\Throwable $e) {}
    }

    /**
     * Crée une nouvelle config de salaire
     */
    public function create($data)
    {
        // Convertir site_id en ObjectId
        try {
            $siteOid = new ObjectId((string) $data['site_id']);
        } catch (\Throwable $e) {
            return false;
        }

        $data['site_id'] = $siteOid;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Validation des données
        if (empty($data['site_id']) || empty($data['category'])) {
            return false;
        }

        // Vérifier si la config existe déjà
        $existing = $this->getBySiteAndCategory($data['site_id'], $data['category']);
        if ($existing) {
            return $this->updateBySiteAndCategory($data['site_id'], $data['category'], $data);
        }

        $result = $this->collection->insertOne($data);
        return $result->getInsertedId();
    }

    /**
     * Récupère la config de salaire par site et catégorie
     */
    public function getBySiteAndCategory($siteId, $category)
    {
        // Si c'est une string, convertir en ObjectId
        if (is_string($siteId)) {
            try {
                $siteId = new ObjectId($siteId);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return $this->collection->findOne([
            'site_id' => $siteId,
            'category' => $category
        ]);
    }

    /**
     * Récupère toutes les configs d'un chantier
     */
    public function getBySite($siteId)
    {
        // Si c'est une string, convertir en ObjectId
        if (is_string($siteId)) {
            try {
                $siteId = new ObjectId((string) $siteId);
            } catch (\Throwable $e) {
                return [];
            }
        }
        return $this->collection->find(['site_id' => $siteId], ['sort' => ['category' => 1]])->toArray();
    }

    /**
     * Met à jour la config de salaire par site et catégorie
     */
    public function updateBySiteAndCategory($siteId, $category, $data)
    {
        // Si c'est une string, convertir en ObjectId
        if (is_string($siteId)) {
            try {
                $siteId = new ObjectId($siteId);
            } catch (\Throwable $e) {
                return false;
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->collection->updateOne(
            ['site_id' => $siteId, 'category' => $category],
            ['$set' => $data]
        );
        return ($result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0);
    }

    /**
     * Récupère le taux journalier pour T.T (Tout Travaux)
     * T.T = 3$ par jour, 1.5$ par demi-journée
     */
    public function getTTDailyRate($siteId)
    {
        $config = $this->getBySiteAndCategory($siteId, 'T.T');
        if ($config && isset($config['daily_rate'])) {
            return $config['daily_rate'];
        }
        // Taux par défaut: 3$
        return 3.0;
    }

    /**
     * Récupère le taux journalier pour M.C (Maçon)
     * Varie selon le chantier (6$, 7$ ou autre convention)
     */
    public function getMCDailyRate($siteId)
    {
        $config = $this->getBySiteAndCategory($siteId, 'M.C');
        if ($config && isset($config['daily_rate'])) {
            return $config['daily_rate'];
        }
        // Taux par défaut: 6$
        return 6.0;
    }

    /**
     * Récupère tous les taux d'un chantier
     */
    public function getRatesBySite($siteId)
    {
        $configs = $this->getBySite($siteId);
        $rates = [];
        foreach ($configs as $config) {
            $rates[$config['category']] = $config['daily_rate'] ?? 0;
        }
        return $rates;
    }

    /**
     * Calcule le salaire d'un ouvrier pour une semaine
     */
    public function calculateWeeklySalary($siteId, $category, $days)
    {
        $dailyRate = ($category === 'M.C') ? $this->getMCDailyRate($siteId) : $this->getTTDailyRate($siteId);
        
        $totalSalary = 0;
        foreach ($days as $day => $value) {
            if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])) {
                if ($value === 1) {
                    $totalSalary += $dailyRate; // Journée complète
                } elseif ($value === 0.5) {
                    $totalSalary += $dailyRate / 2; // Demi-journée
                }
            }
        }
        
        return $totalSalary;
    }
}