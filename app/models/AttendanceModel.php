<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

class AttendanceModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->attendances;
        try {
            $this->collection->createIndex(['site_id' => 1, 'week_of' => 1]);
            $this->collection->createIndex(['site_id' => 1, 'worker_id' => 1, 'week_of' => 1], ['unique' => true]);
            $this->collection->createIndex(['week_of' => 1]);
        } catch (\Throwable $e) {}
    }

    /**
     * Crée ou met à jour une présence
     */
    public function upsert($data)
    {
        try {
            $siteOid = new ObjectId((string) $data['site_id']);
            $workerOid = new ObjectId((string) $data['worker_id']);
        } catch (\Throwable $e) {
            return false;
        }

        $data['site_id'] = $siteOid;
        $data['worker_id'] = $workerOid;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // Calculer le nombre de jours de présence
        $daysWorked = $this->calculateDaysWorked($data);
        $data['days_worked'] = $daysWorked;

        $result = $this->collection->updateOne(
            ['site_id' => $siteOid, 'worker_id' => $workerOid, 'week_of' => $data['week_of']],
            ['$set' => $data],
            ['upsert' => true]
        );

        return true;
    }

    /**
     * Récupère les présences d'un chantier pour une semaine
     */
    public function getBySiteAndWeek($siteId, $weekOf)
    {
        // Si c'est une string, convertir en ObjectId
        if (is_string($siteId)) {
            try {
                $siteId = new ObjectId($siteId);
            } catch (\Throwable $e) {
                return [];
            }
        }
        
        return $this->collection->find(
            ['site_id' => $siteId, 'week_of' => $weekOf],
            ['sort' => ['_id' => 1]]
        )->toArray();
    }

    /**
     * Récupère les présences d'un chantier pour une période donnée (week_start et week_end)
     */
    public function getBySiteAndWeekRange($siteId, $weekStart, $weekEnd)
    {
        // Si c'est une string, convertir en ObjectId
        if (is_string($siteId)) {
            try {
                $siteId = new ObjectId($siteId);
            } catch (\Throwable $e) {
                return [];
            }
        }
        
        return $this->collection->find(
            [
                'site_id' => $siteId,
                'week_start' => $weekStart,
                'week_end' => $weekEnd
            ],
            ['sort' => ['_id' => 1]]
        )->toArray();
    }
    public function getByWorkerAndWeek($workerId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $workerId);
        } catch (\Throwable $e) {
            return null;
        }

        return $this->collection->findOne([
            'worker_id' => $oid,
            'week_of' => $weekOf
        ]);
    }

    /**
     * Récupère toutes les présences d'un ouvrier
     */
    public function getByWorker($workerId)
    {
        try {
            $oid = new ObjectId((string) $workerId);
        } catch (\Throwable $e) {
            return [];
        }

        return $this->collection->find(
            ['worker_id' => $oid],
            ['sort' => ['week_of' => -1]]
        )->toArray();
    }

    /**
     * Calcule le nombre de jours travaillés
     * Chaque jour peut valoir: 0 (absent), 0.5 (demi-journée), 1 (journée)
     */
    public function calculateDaysWorked($attendanceData)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $total = 0;

        foreach ($days as $day) {
            $value = $attendanceData[$day] ?? 0;
            if (in_array($value, [0, 0.5, 1])) {
                $total += $value;
            }
        }

        return $total;
    }

    /**
     * Supprime les présences d'un chantier pour une semaine
     */
    public function deleteBySiteAndWeek($siteId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return false;
        }

        $result = $this->collection->deleteMany(['site_id' => $oid, 'week_of' => $weekOf]);
        return ($result->getDeletedCount() > 0);
    }

    /**
     * Récupère les statistiques journalières pour un chantier et une semaine
     */
    public function getDailyStats($siteId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return [];
        }

        $attendances = $this->getBySiteAndWeek($siteId, $weekOf);
        $stats = [
            'monday' => ['T.T' => 0, 'M.C' => 0],
            'tuesday' => ['T.T' => 0, 'M.C' => 0],
            'wednesday' => ['T.T' => 0, 'M.C' => 0],
            'thursday' => ['T.T' => 0, 'M.C' => 0],
            'friday' => ['T.T' => 0, 'M.C' => 0],
            'saturday' => ['T.T' => 0, 'M.C' => 0],
        ];

        foreach ($attendances as $record) {
            $category = $record['category'] ?? 'T.T';
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day) {
                if (isset($record[$day])) {
                    $stats[$day][$category] += $record[$day];
                }
            }
        }

        return $stats;
    }
}