<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

class PayslipModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->payslips;
        try {
            $this->collection->createIndex(['site_id' => 1, 'week_start' => 1, 'week_end' => 1], ['unique' => true]);
            $this->collection->createIndex(['site_id' => 1, 'week_of' => 1]);
            $this->collection->createIndex(['week_of' => 1]);
        } catch (\Throwable $e) {}
    }

    /**
     * Crée ou met à jour une fiche de paie
     */
    public function upsert($data)
    {
        try {
            $siteOid = new ObjectId((string) $data['site_id']);
        } catch (\Throwable $e) {
            return false;
        }

        $data['site_id'] = $siteOid;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->collection->updateOne(
            [
                'site_id' => $siteOid,
                'week_start' => $data['week_start'],
                'week_end' => $data['week_end']
            ],
            ['$set' => $data],
            ['upsert' => true]
        );

        return true;
    }

    /**
     * Récupère une fiche de paie par site et semaine
     */
    public function getBySiteAndWeek($siteId, $weekOf)
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
            'week_of' => $weekOf
        ]);
    }

    /**
     * Récupère une fiche de paie par site et période (week_start et week_end)
     */
    public function getBySiteAndWeekRange($siteId, $weekStart, $weekEnd)
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
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ]);
    }
    public function getBySite($siteId)
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
                'site_id' => $siteId
            ],
            ['sort' => ['week_of' => -1]]
        )->toArray();
    }

    /**
     * Récupère toutes les fiches de paie archivées d'un chantier
     */
    public function getArchivedBySite($siteId)
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
                'archived' => true
            ],
            ['sort' => ['week_of' => -1]]
        )->toArray();
    }

    /**
     * Génère une fiche de paie pour un chantier et une semaine
     */
    public function generatePayslip($siteId, $weekOf)
    {
        try {
            $siteOid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return false;
        }

        $attendanceModel = new AttendanceModel();
        $salaryConfigModel = new SalaryConfigModel();
        $workerModel = new WorkerModel();

        // Récupérer tous les enregistrements de présence pour cette semaine
        $attendances = $attendanceModel->getBySiteAndWeek($siteId, $weekOf);

        $payrollData = [];
        $dailyStats = ['T.T' => [], 'M.C' => []];

        // Initialiser les stats journalières
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        foreach (['T.T', 'M.C'] as $category) {
            foreach ($days as $day) {
                $dailyStats[$category][$day] = 0;
            }
        }

        // Traiter chaque enregistrement de présence
        foreach ($attendances as $attendance) {
            $worker = $workerModel->getById((string) $attendance['worker_id']);
            if (!$worker) continue;

            $category = $worker['category'];
            $daysCount = $attendanceModel->calculateDaysWorked($attendance);

            // Calculer le salaire hebdomadaire
            $dailyRate = ($category === 'M.C') ? $salaryConfigModel->getMCDailyRate($siteId) : $salaryConfigModel->getTTDailyRate($siteId);
            
            $weeklySalary = 0;
            foreach ($days as $day) {
                $dayValue = $attendance[$day] ?? 0;
                if (in_array($dayValue, [0, 0.5, 1])) {
                    $weeklySalary += $dayValue * $dailyRate;
                    $dailyStats[$category][$day] += $dayValue;
                }
            }

            $payrollData[] = [
                'worker_id' => $attendance['worker_id'],
                'worker_name' => $worker['name'],
                'category' => $category,
                'monday' => $attendance['monday'] ?? 0,
                'tuesday' => $attendance['tuesday'] ?? 0,
                'wednesday' => $attendance['wednesday'] ?? 0,
                'thursday' => $attendance['thursday'] ?? 0,
                'friday' => $attendance['friday'] ?? 0,
                'saturday' => $attendance['saturday'] ?? 0,
                'days_worked' => $daysCount,
                'daily_rate' => $dailyRate,
                'weekly_salary' => round($weeklySalary, 2)
            ];
        }

        // Préparer les données de synthèse avec équivalence = volume * tarif journalier
        $dailyEquivalenceTC = [];
        $dailyEquivalenceMC = [];
        $totalEquivalenceTC = 0;
        $totalEquivalenceMC = 0;

        // Récupérer les tarifs journaliers
        $configModel = new SalaryConfigModel();
        $ttDailyRate = $configModel->getTTDailyRate($siteId);
        $mcDailyRate = $configModel->getMCDailyRate($siteId);

        foreach ($days as $day) {
            if (isset($dailyStats['T.T'][$day]) && $dailyStats['T.T'][$day] > 0) {
                $equivalenceValue = round($dailyStats['T.T'][$day] * $ttDailyRate, 2);
                $dailyEquivalenceTC[] = [
                    'day' => $this->getDayName($day),
                    'daily_volume' => $dailyStats['T.T'][$day],
                    'equivalence' => $equivalenceValue
                ];
                $totalEquivalenceTC += $equivalenceValue;
            }
            if (isset($dailyStats['M.C'][$day]) && $dailyStats['M.C'][$day] > 0) {
                $equivalenceValue = round($dailyStats['M.C'][$day] * $mcDailyRate, 2);
                $dailyEquivalenceMC[] = [
                    'day' => $this->getDayName($day),
                    'daily_volume' => $dailyStats['M.C'][$day],
                    'equivalence' => $equivalenceValue
                ];
                $totalEquivalenceMC += $equivalenceValue;
            }
        }

        $weekStart = $weekOf;
        $weekEnd = $weekOf;

        // Chercher une date de début/fin à partir des présences si disponibles
        if (!empty($attendances)) {
            $att = reset($attendances);
            $weekStart = $att['week_start'] ?? $weekOf;
            $weekEnd = $att['week_end'] ?? $weekOf;
        }

        $payslip = [
            'site_id' => $siteOid,
            'week_of' => $weekOf,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'payroll' => $payrollData,
            'daily_synthesis_tc' => $dailyEquivalenceTC,
            'daily_synthesis_mc' => $dailyEquivalenceMC,
            'total_synthesis_tc' => round($totalEquivalenceTC, 2),
            'total_synthesis_mc' => round($totalEquivalenceMC, 2),
            'total_salary' => array_sum(array_map(fn($p) => $p['weekly_salary'], $payrollData)),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $payslip;
    }

    /**
     * Obtient le nom du jour en français
     */
    private function getDayName($dayKey)
    {
        $dayNames = [
            'monday' => 'Lundi',
            'tuesday' => 'Mardi',
            'wednesday' => 'Mercredi',
            'thursday' => 'Jeudi',
            'friday' => 'Vendredi',
            'saturday' => 'Samedi'
        ];
        return $dayNames[$dayKey] ?? $dayKey;
    }

    /**
     * Sauvegarde une fiche de paie complète
     */
    public function savePayslip($payslip)
    {
        return $this->upsert($payslip);
    }

    /**
     * Archive une fiche de paie (marque comme archivée sans la supprimer)
     */
    public function archive($siteId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return false;
        }

        $result = $this->collection->updateOne(
            [
                'site_id' => $oid,
                'week_of' => $weekOf
            ],
            ['$set' => [
                'archived' => true,
                'archived_at' => date('Y-m-d H:i:s')
            ]]
        );
        return ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0);
    }

    /**
     * Désarchive une fiche de paie
     */
    public function unarchive($siteId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return false;
        }

        $result = $this->collection->updateOne(
            [
                'site_id' => $oid,
                'week_of' => $weekOf
            ],
            ['$set' => [
                'archived' => false,
                'unarchived_at' => date('Y-m-d H:i:s')
            ]]
        );
        return ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0);
    }

    /**
     * Supprime physiquement une fiche de paie
     */
    public function delete($siteId, $weekOf)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return false;
        }

        $result = $this->collection->deleteOne([
            'site_id' => $oid,
            'week_of' => $weekOf
        ]);
        return ($result->getDeletedCount() > 0);
    }
}