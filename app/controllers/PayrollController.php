<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SiteModel;
use App\Models\WorkerModel;
use App\Models\SalaryConfigModel;
use App\Models\AttendanceModel;
use App\Models\PayslipModel;

/**
 * Classe étendue de TCPDF pour le rapport synthèse hebdomadaire
 */
class WeeklyReportPDF extends \TCPDF {
    private $weekStart;
    private $weekEnd;
    private $logoPath;

    public function __construct($weekStart, $weekEnd, $logoPath) {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->weekStart = $weekStart;
        $this->weekEnd = $weekEnd;
        $this->logoPath = $logoPath;
    }

    // En-tête personnalisé
    public function Header() {
        // Logo
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 10, 25, 20, 'PNG');
        }

        // Informations entreprise
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(45, 10);
        $this->Cell(0, 6, 'JF BUSINESS SARL', 0, 1, 'L');

        $this->SetFont('helvetica', '', 8);
        $this->SetX(45);
        $this->Cell(0, 4, 'N° RCCM : CD/KNG/RCCM/24-B-D4138 | ID-NAT : 01-F4200-N 37015G', 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(0, 4, 'N° IMPOT : A2504347D | N° d\'affiliation INSS : 1022461300', 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(0, 4, 'N° d\'immatriculation A L\'INPP : A2504347D', 0, 1, 'L');

        // Période
        $this->SetX(140);
        $this->Cell(0, 4, 'Période : ' . $this->weekStart . ' au ' . $this->weekEnd, 0, 1, 'L');

        // Ligne de séparation
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(29, 112, 184); // Bleu
        $this->Line(15, 35, 195, 35);

        // Titre
        $this->SetFont('helvetica', 'B', 16);
        $this->SetY(40);
        $this->Cell(0, 10, 'RAPPORT SYNTHÈSE HEBDOMADAIRE', 0, 1, 'C');
        $this->Ln(5);
    }

    // Pied de page personnalisé
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(135, 206, 235);
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        $this->SetY(-12);
        $this->Cell(0, 5, '© 2024 JF BUSINESS SARL — Système comptable intégré. Tous droits réservés.', 0, 0, 'L');

        $days_fr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $day_en = date('l');
        $day_fr = $days_fr[$day_en] ?? $day_en;
        $this->Cell(0, 5, $day_fr . ', le ' . date('d/m/Y à H:i:s'), 0, 0, 'R');
    }
}

class PayrollController extends Controller
{
    // ============ SITES/CHANTIERS ============

    /**
     * Liste tous les chantiers (page d'accueil du module)
     */
    public function sites()
    {
        $this->requireAdmin();
        $siteModel = new SiteModel();
        $sites = $siteModel->getAll();
        $this->render('admin/payroll_sites', ['sites' => $sites]);
    }

    /**
     * Crée un nouveau chantier
     */
    public function createSite()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
                header('Location: ?page=payroll&action=sites');
                exit;
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'description' => $_POST['description'] ?? '',
                'engineer_name' => $_POST['engineer_name'] ?? '',
                'engineer_phone' => $_POST['engineer_phone'] ?? '',
                'warehouse_manager_name' => $_POST['warehouse_manager_name'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            if (!empty($data['name'])) {
                $siteModel = new SiteModel();
                $siteModel->create($data);
                session_start();
                $_SESSION['flash_success'] = 'Chantier créé avec succès';
            }

            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $this->render('admin/payroll_site_form', ['action' => 'create']);
    }

    /**
     * Édite un chantier
     */
    public function editSite()
    {
        $this->requireAdmin();
        $siteId = $_GET['id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=editSite&id=' . $siteId);
                exit;
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'location' => $_POST['location'] ?? '',
                'description' => $_POST['description'] ?? '',
                'engineer_name' => $_POST['engineer_name'] ?? '',
                'engineer_phone' => $_POST['engineer_phone'] ?? '',
                'warehouse_manager_name' => $_POST['warehouse_manager_name'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            $siteModel->update($siteId, $data);
            session_start();
            $_SESSION['flash_success'] = 'Chantier mis à jour';
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $this->render('admin/payroll_site_form', ['site' => $site, 'action' => 'edit']);
    }

    /**
     * Affiche le détail d'un chantier avec options d'accès aux modules
     */
    public function siteDetail()
    {
        $this->requireAdmin();
        $siteId = $_GET['id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $stats = $siteModel->getStats($siteId);
        $this->render('admin/payroll_site_detail', ['site' => $site, 'stats' => $stats]);
    }

    // ============ OUVRIERS ============

    /**
     * Liste les ouvriers d'un chantier
     */
    public function workers()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $workerModel = new WorkerModel();
        $workers = $workerModel->getBySite($siteId);

        $this->render('admin/payroll_workers', [
            'site' => $site,
            'workers' => $workers
        ]);
    }

    /**
     * Crée un nouvel ouvrier
     */
    public function createWorker()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            $data = [
                'site_id' => $siteId,
                'name' => $_POST['name'] ?? '',
                'category' => $_POST['category'] ?? 'T.T',
                'status' => 'active'
            ];

            if (!empty($data['name'])) {
                $workerModel = new WorkerModel();
                $workerModel->create($data);
                session_start();
                $_SESSION['flash_success'] = 'Ouvrier ajouté';
            }

            header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
            exit;
        }

        $this->render('admin/payroll_worker_form', [
            'site' => $site,
            'action' => 'create'
        ]);
    }

    /**
     * Importe les ouvriers depuis un fichier Excel
     */
    public function importWorkers()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur lors du téléchargement du fichier';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            $filePath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            
            // Vérifier que c'est un fichier Excel
            if (!preg_match('/\.(xls|xlsx)$/i', $fileName)) {
                session_start();
                $_SESSION['flash_error'] = 'Le fichier doit être un fichier Excel (.xls ou .xlsx)';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            try {
                // Choisir le bon reader selon l'extension
                if (preg_match('/\.xlsx$/i', $fileName)) {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }
                
                $spreadsheet = $reader->load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                
                $workerModel = new WorkerModel();
                $importedCount = 0;
                $errors = [];
                
                // Parcourir les lignes (en commençant par la 2ème pour ignorer l'header)
                $maxRow = $worksheet->getHighestRow();
                for ($row = 2; $row <= $maxRow; $row++) {
                    $name = trim((string) $worksheet->getCell('A' . $row)->getValue());
                    $category = trim((string) $worksheet->getCell('B' . $row)->getValue());
                    
                    // Valider la catégorie
                    if (!in_array($category, ['T.T', 'M.C'])) {
                        $category = 'T.T';
                    }
                    
                    if (!empty($name)) {
                        $data = [
                            'site_id' => $siteId,
                            'name' => $name,
                            'category' => $category,
                            'status' => 'active'
                        ];
                        
                        if ($workerModel->create($data)) {
                            $importedCount++;
                        } else {
                            $errors[] = "Erreur lors de l'import de : $name";
                        }
                    }
                }
                
                session_start();
                $_SESSION['flash_success'] = "$importedCount ouvrier(s) importé(s) avec succès";
                if (!empty($errors)) {
                    $_SESSION['flash_warning'] = 'Quelques erreurs : ' . implode(', ', $errors);
                }
            } catch (\Throwable $e) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur lors de la lecture du fichier : ' . $e->getMessage();
            }

            header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
            exit;
        }
    }

    /**
     * Édite un ouvrier
     */
    public function editWorker()
    {
        $this->requireAdmin();
        $workerId = $_GET['id'] ?? '';
        $siteId = $_GET['site_id'] ?? '';

        $workerModel = new WorkerModel();
        $worker = $workerModel->getById($workerId);

        if (!$worker) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'category' => $_POST['category'] ?? 'T.T'
            ];

            $workerModel->update($workerId, $data);
            session_start();
            $_SESSION['flash_success'] = 'Ouvrier mis à jour';
            header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
            exit;
        }

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        $this->render('admin/payroll_worker_form', [
            'site' => $site,
            'worker' => $worker,
            'action' => 'edit'
        ]);
    }

    /**
     * Supprime un ouvrier
     */
    public function deleteWorker()
    {
        $this->requireAdmin();
        $workerId = $_GET['id'] ?? '';
        $siteId = $_GET['site_id'] ?? '';

        $workerModel = new WorkerModel();
        $worker = $workerModel->getById($workerId);

        if (!$worker) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
                exit;
            }

            // Utiliser archive au lieu de delete pour préserver l'intégrité des données
            if ($workerModel->archive($workerId)) {
                session_start();
                $_SESSION['flash_success'] = 'Ouvrier supprimé avec succès';
            } else {
                session_start();
                $_SESSION['flash_error'] = 'Erreur lors de la suppression de l\'ouvrier';
            }

            header('Location: ?page=payroll&action=workers&site_id=' . $siteId);
            exit;
        }

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        $this->render('admin/payroll_worker_delete', [
            'site' => $site,
            'worker' => $worker
        ]);
    }

    // ============ CONFIGURATION DES SALAIRES ============

    /**
     * Configure les salaires pour un chantier
     */
    public function salaryConfig()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $configModel = new SalaryConfigModel();
        $configs = $configModel->getBySite($siteId);
        $configsByCategory = [];
        foreach ($configs as $config) {
            $configsByCategory[$config['category']] = $config;
        }

        // Garantir les deux catégories
        if (!isset($configsByCategory['T.T'])) {
            $configsByCategory['T.T'] = [
                'site_id' => $siteId,
                'category' => 'T.T',
                'daily_rate' => 3.0,
                'half_day_rate' => 1.5
            ];
        }
        if (!isset($configsByCategory['M.C'])) {
            $configsByCategory['M.C'] = [
                'site_id' => $siteId,
                'category' => 'M.C',
                'daily_rate' => 6.0,
                'half_day_rate' => 3.0
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=salaryConfig&site_id=' . $siteId);
                exit;
            }

            // Traiter les configurations
            $categoryFields = [
                'T.T' => 'TT_daily_rate',
                'M.C' => 'MC_daily_rate'
            ];
            foreach ($categoryFields as $category => $fieldName) {
                $dailyRate = floatval($_POST[$fieldName] ?? 0);
                if ($dailyRate > 0) {
                    $data = [
                        'site_id' => $siteId,
                        'category' => $category,
                        'daily_rate' => $dailyRate,
                        'half_day_rate' => $dailyRate / 2
                    ];
                    $configModel->create($data);
                }
            }

            session_start();
            $_SESSION['flash_success'] = 'Configuration des salaires mise à jour';
            header('Location: ?page=payroll&action=salaryConfig&site_id=' . $siteId);
            exit;
        }

        $this->render('admin/payroll_salary_config', [
            'site' => $site,
            'configs' => $configsByCategory
        ]);
    }

    // ============ PRÉSENCES ============

    /**
     * Saisit les présences pour une semaine
     */
    public function attendance()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? date('Y-m-d');

        $weekTime = strtotime($weekOf);
        if (!$weekTime) {
            $weekTime = time();
        }

        $defaultWeekStart = date('Y-m-d', strtotime('monday this week', $weekTime));
        $defaultWeekEnd = date('Y-m-d', strtotime('saturday this week', $weekTime));

        $weekStart = $_GET['week_start'] ?? $defaultWeekStart;
        $weekEnd = $_GET['week_end'] ?? $defaultWeekEnd;

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        $existingPayslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);
        if ($existingPayslip && !empty($existingPayslip['archived'])) {
            session_start();
            $_SESSION['flash_warning'] = 'Cette semaine est déjà archivée et ne peut être modifiée. Vous pouvez consulter la fiche de paie.';
            header('Location: ?page=payroll&action=payslip&site_id=' . $siteId . '&week_of=' . $weekOf);
            exit;
        }

        $workerModel = new WorkerModel();
        $workers = $workerModel->getBySite($siteId);

        $attendanceModel = new AttendanceModel();
        $attendances = $attendanceModel->getBySiteAndWeek($siteId, $weekOf);

        $storedWeekStart = $attendances[0]['week_start'] ?? null;
        $storedWeekEnd = $attendances[0]['week_end'] ?? null;
        $weekStart = $_GET['week_start'] ?? $storedWeekStart ?? $weekStart;
        $weekEnd = $_GET['week_end'] ?? $storedWeekEnd ?? $weekEnd;

        $attendancesByWorker = [];
        foreach ($attendances as $att) {
            $attendancesByWorker[(string) $att['worker_id']] = $att;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $weekStart = $_POST['start_date'] ?? $weekStart;
            $weekEnd = $_POST['end_date'] ?? $weekEnd;
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
                header('Location: ?page=payroll&action=attendance&site_id=' . $siteId . '&week_of=' . $weekOf);
                exit;
            }

            // Traiter les présences pour chaque ouvrier
            $newAttendances = $_POST['attendance'] ?? [];
            foreach ($newAttendances as $workerId => $dayData) {
                $data = [
                    'site_id' => $siteId,
                    'worker_id' => $workerId,
                    'week_of' => $weekOf,
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'category' => $_POST['category'][$workerId] ?? 'T.T'
                ];

                // Traiter chaque jour
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                foreach ($days as $day) {
                    $value = $dayData[$day] ?? 0;
                    $data[$day] = floatval($value);
                }

                $attendanceModel->upsert($data);
            }

            session_start();
            $_SESSION['flash_success'] = 'Présences enregistrées';
            // Rediriger vers la fiche de paie générée
            header('Location: ?page=payroll&action=payslip&site_id=' . $siteId . '&week_of=' . $weekOf);
            exit;
        }

        $this->render('admin/payroll_attendance', [
            'site' => $site,
            'week_of' => $weekOf,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'workers' => $workers,
            'attendances' => $attendancesByWorker
        ]);
    }

    // ============ FICHES DE PAIE ============

    /**
     * Génère et affiche la fiche de paie d'une semaine
     */
    public function payslip()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? date('Y-m-d');

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        
        // Vérifier si une fiche existe déjà en base de données
        $payslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);

        // Si pas de fiche, la générer à partir des présences
        if (!$payslip) {
            $payslip = $payslipModel->generatePayslip($siteId, $weekOf);
            
            // Si aucune donnée de paie n'a été générée
            if (!$payslip || empty($payslip['payroll'])) {
                session_start();
                $_SESSION['flash_warning'] = 'Aucune présence enregistrée pour cette semaine. Veuillez saisir les présences d\'abord.';
                header('Location: ?page=payroll&action=attendance&site_id=' . $siteId . '&week_of=' . $weekOf);
                exit;
            }
        }

        $this->render('admin/payroll_payslip', [
            'site' => $site,
            'week_of' => $weekOf,
            'payslip' => $payslip
        ]);
    }

    /**
     * Sauvegarde la fiche de paie en base de données
     */
    public function savePayslip()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                session_start();
                $_SESSION['flash_error'] = 'Erreur CSRF';
            } else {
                $payslipModel = new PayslipModel();
                $existingPayslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);
                if ($existingPayslip && !empty($existingPayslip['archived'])) {
                    session_start();
                    $_SESSION['flash_error'] = 'Impossible de modifier une fiche de paie archivée.';
                } else {
                    $payslip = $payslipModel->generatePayslip($siteId, $weekOf);
                    $payslipModel->savePayslip($payslip);
                    session_start();
                    $_SESSION['flash_success'] = 'Fiche de paie sauvegardée';
                }
            }

            header('Location: ?page=payroll&action=payslip&site_id=' . $siteId . '&week_of=' . $weekOf);
            exit;
        }
    }

    /**
     * Exporte la fiche de paie en PDF
     */
    public function exportPayslipPDF()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);
        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        $payslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);

        if (!$payslip || empty($payslip['payroll'])) {
            session_start();
            $_SESSION['flash_error'] = 'Aucune fiche de paie disponible pour export.';
            header('Location: ?page=payroll&action=payslip&site_id=' . $siteId . '&week_of=' . $weekOf);
            exit;
        }

        // Trier les ouvriers : T.T en premier, puis M.C
        // Convertir BSONArray en tableau PHP si nécessaire
        $payrollArray = is_array($payslip['payroll']) ? $payslip['payroll'] : iterator_to_array($payslip['payroll']);
        $ttWorkers = array_filter($payrollArray, fn($w) => $w['category'] === 'T.T');
        $mcWorkers = array_filter($payrollArray, fn($w) => $w['category'] === 'M.C');
        $sortedPayroll = array_merge($ttWorkers, $mcWorkers);

        // Générer le HTML de la fiche de paie
        $html = '<style>
            body { font-family: "Segoe UI", "Roboto", Arial, sans-serif; font-size: 11px; margin: 5px 6px; padding: 0; color: #2a2a2a; }
            .header { margin-bottom: 5px; }
            .logo-container { text-align: center; margin-bottom: 8px; }
            .row { font-size: 0; margin-bottom: 5px; }
            .col-5 { display: inline-block; width: 45%; min-height: 115px; border: 1px solid #d0d7e1; background: #f7f9fc; padding: 10px 12px; border-radius: 8px; box-sizing: border-box; box-shadow: 0 2px 6px rgba(0,0,0,0.08); margin-right: 2%; vertical-align: top; font-size: 11px; }
            .col-5:last-child { margin-right: 0; }
            .company-info p, .site-info p { margin: 3px 0; font-size: 11px; text-align: left; }
            .company-info p strong, .site-info p strong { min-width: 110px; display: inline-block; }
            .blue-line { border-bottom: 3px solid #1d70b8; margin: 6px 0; }
            .title { text-align: center; font-size: 18px; font-weight: bold; color: #001025; margin: 10px 0 5px; }
            table { width: 100%; border-collapse: collapse; margin: 5px 0; font-size: 11px; background: #ffffff; }
            th, td { border: 1px solid #b9c6d6; padding: 2px 3px; text-align: center; }
            th { background: #1583e8; color: #ffffff; font-weight: bold; font-size: 13px; letter-spacing: 0.03em; }
            tbody tr:nth-child(odd) { background: #f8fbff; }
            .tt-row { background-color: #ffffff; }
            .mc-row { background-color: #fff8e8; color: #3f2a13; }
            .mc-row td { border-color: #e1c18a; }
            .mc-row strong, .mc-row th { color: #a56e00; }
            .total-row { background-color: #dbe9f6; font-weight: bold; font-size: 13px; }
            .signature-cell { width: 80px; }
            .synthesis-tables { display: flex; justify-content: space-between; margin-top: 8px; }
            .synthesis-table { width: 48%; font-size: 11px; }
            .synthesis-table th { background: #1757a6; color: white; font-size: 12px; font-weight: bold; }
            .synthesis-table .mc-summary th { background: #c98a2f; }
            .synthesis-table .tt-summary th { background: #1b5fbd; }
            .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; padding: 4px 6px; border-top: 1px solid #ccd5e2; }
            .footer-left { float: left; }
            .footer-right { float: right; }
            .zero-value { color: #d13438; font-weight: bold; }
        </style>';

        // En-tête avec logo et informations
        $html .= '<div class="header">';
        $html .= '<div class="logo-container">';
        $html .= '<img src="C:/dev/log_project/assets/images/logo.png" style="width: 80px; height: 70px; display: block; margin: 0 auto;" alt="Logo JF BUSINESS SARL">';
        $html .= '</div>';

        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; border: none;">';
        $html .= '<tr style="border: none;">';
        
        $html .= '<td style="width: 48%; text-align: left; min-height: 115px; border: 1px solid #ccc; background: #f4f7fb; padding: 8px 10px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); vertical-align: top; font-size: 11px;">';
        $html .= '<p style="margin: 2px 0;"><strong>N° RCCM :</strong> CD/KNG/RCCM/24-B-D4138</p>';
        $html .= '<p style="margin: 2px 0;"><strong>ID-NAT :</strong> 01-F4200-N 37015G</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° IMPOT :</strong> A2504347D</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° d\'affiliation INSS :</strong> 1022461300</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° d\'immatriculation A L\'INPP :</strong> A2504347D</p>';
        $html .= '</td>';
        
        $html .= '<td style="width: 4%; border: none;"></td>';
        
        $html .= '<td style="width: 48%; text-align: left; min-height: 115px; border: 1px solid #ccc; background: #f4f7fb; padding: 8px 10px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); vertical-align: top; font-size: 11px;">';
        $html .= '<p style="margin: 2px 0;"><strong>Superviseur :</strong> ........................................................................</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Ingénieur :</strong> ' . htmlspecialchars($site['engineer_name'] ?? '') . '</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Contacts Ir. :</strong> ' . htmlspecialchars($site['engineer_phone'] ?? '') . '</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Semaine du ' . htmlspecialchars($payslip['week_start'] ?? $weekOf) . ' au ' . htmlspecialchars($payslip['week_end'] ?? $weekOf) . '</strong></p>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<div class="blue-line"></div>';
        $html .= '<div class="title">Fiche de paie hebdomadaire des ouvriers : ' . htmlspecialchars($site['name']) . '</div>';

        // Tableau principal
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>No</th>';
        $html .= '<th style="width: 18%;">Nom</th>';
        $html .= '<th>Catégorie</th>';
        $html .= '<th>Lun</th>';
        $html .= '<th>Mar</th>';
        $html .= '<th>Mer</th>';
        $html .= '<th>Jeu</th>';
        $html .= '<th>Ven</th>';
        $html .= '<th>Sam</th>';
        $html .= '<th style="width: 10%;">Jrs Prest</th>';
        $html .= '<th style="width: 15%;">Sal Hebdo</th>';
        $html .= '<th class="signature-cell">Signature</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $count = 1;
        $totalGeneral = 0;

        foreach ($sortedPayroll as $row) {
            $rowClass = ($row['category'] === 'M.C') ? 'mc-row' : 'tt-row';
            $html .= '<tr class="' . $rowClass . '">';
            $html .= '<td>' . $count++ . '</td>';
            $html .= '<td style="text-align: left; width: 18%;">' . htmlspecialchars($row['worker_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['category'] ?? '') . '</td>';

            foreach ($days as $day) {
                $value = $row[$day] ?? 0;
                $displayValue = ($value == 0) ? '<span class="zero-value">—</span>' : $value;
                $html .= '<td>' . $displayValue . '</td>';
            }

            $html .= '<td style="width: 10%; text-align: right;">' . htmlspecialchars((string)($row['days_worked'] ?? '0')) . ' Jrs</td>';
            $html .= '<td style="width: 15%; text-align: right;">' . htmlspecialchars(number_format($row['weekly_salary'] ?? 0, 2)) . ' $</td>';
            $html .= '<td class="signature-cell"></td>';
            $html .= '</tr>';

            $totalGeneral += $row['weekly_salary'] ?? 0;
        }

        // Ligne Total Général
        $html .= '<tr class="total-row">';
        $html .= '<td colspan="10" style="text-align: center; font-weight: bold;">Total Général :</td>';
        $html .= '<td style="font-weight: bold; text-align: right;">' . number_format($totalGeneral, 2) . ' $</td>';
        $html .= '<td></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';

        // Tableaux de synthèse - Utiliser une table pour l'alignement
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: none;">';
        $html .= '<tr style="border: none;">';
        
        // Synthèse M.C (Maçons) - Gauche
        $html .= '<td style="width: 45%; vertical-align: top; border: none; padding-right: 2%;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 12px;">Synthèse Journalière M.C (Maçons)</h4>';
        $html .= '<table class="synthesis-table mc-summary" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr><th>Jour</th><th>Volume</th><th>Équivalence ($)</th></tr></thead>';
        $html .= '<tbody>';

        $dailySynthesisMc = is_array($payslip['daily_synthesis_mc'] ?? []) ? $payslip['daily_synthesis_mc'] : iterator_to_array($payslip['daily_synthesis_mc'] ?? []);
        if (!empty($dailySynthesisMc)) {
            foreach ($dailySynthesisMc as $day) {
                $html .= '<tr>';
                $html .= '<td style="text-align: left;">' . htmlspecialchars($day['day'] ?? '') . '</td>';
                $html .= '<td style="text-align: right;">' . htmlspecialchars($day['daily_volume'] ?? 0) . ' Jrs</td>';
                $html .= '<td style="text-align: right;">' . htmlspecialchars(number_format($day['equivalence'] ?? 0, 2)) . ' $</td>';
                $html .= '</tr>';
            }
        }

        // Total M.C
        $html .= '<tr style="font-weight: bold; background: #e9ecef;">';
        $html .= '<td colspan="2" style="text-align: center; font-weight: bold;">Total Général M.C :</td>';
        $html .= '<td style="text-align: right;"><strong>$' . number_format($payslip['total_synthesis_mc'] ?? 0, 2) . ' $</strong></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</td>';

        // Synthèse T.T (Tout Travaux) - Droite
        $html .= '<td style="width: 45%; vertical-align: top; border: none;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 12px;">Synthèse Journalière T.T (Tout Travaux)</h4>';
        $html .= '<table class="synthesis-table tt-summary" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr><th>Jour</th><th>Volume</th><th>Équivalence ($)</th></tr></thead>';
        $html .= '<tbody>';

        $dailySynthesisTc = is_array($payslip['daily_synthesis_tc'] ?? []) ? $payslip['daily_synthesis_tc'] : iterator_to_array($payslip['daily_synthesis_tc'] ?? []);
        if (!empty($dailySynthesisTc)) {
            foreach ($dailySynthesisTc as $day) {
                $html .= '<tr>';
                $html .= '<td style="text-align: left;">' . htmlspecialchars($day['day'] ?? '') . '</td>';
                $html .= '<td style="text-align: right;">' . htmlspecialchars($day['daily_volume'] ?? 0) . ' Jrs</td>';
                $html .= '<td style="text-align: right;">' . htmlspecialchars(number_format($day['equivalence'] ?? 0, 2)) . ' $</td>';
                $html .= '</tr>';
            }
        }

        // Total T.T
        $html .= '<tr style="font-weight: bold; background: #e9ecef;">';
        $html .= '<td colspan="2" style="text-align: center; font-weight: bold;">Total Général T.T :</td>';
        $html .= '<td style="text-align: right;"><strong>$' . number_format($payslip['total_synthesis_tc'] ?? 0, 2) . ' $</strong></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';

        // Section des signatures
        $html .= '<div style="margin-top: 10px; page-break-inside: avoid;">';
        $html .= '<table style="width: 100%; margin-top: 7px; border: none;">';
        $html .= '<tr>';
        $html .= '<td style="width: 33%; text-align: center; vertical-align: top; padding: 10px 5px; border: none;">';
        $html .= '<p style="margin: 25px 0; font-size: 10px; font-weight: bold;">' . htmlspecialchars($site['engineer_name'] ?? '') . '</p>';
        $html .= '<div style="color: #ffffff;">Ir. Patrick WASUKUNDI</div>';
        $html .= '<div style="border-bottom: 1px solid #333; color: #ffffff;">Ir. Patrick WASUKUNDI</div>';
        $html .= '<p style="margin: 15px 0 5px 0; font-size: 10px;">Ir. du chantier</p>';
        $html .= '</td>';
        $html .= '<td style="width: 33%; text-align: center; vertical-align: top; padding: 10px 5px; border: none;">';
        $html .= '<p style="margin: 25px 0; font-size: 10px; font-weight: bold;">John MUTAMU</p>';
        $html .= '<div style="color: #ffffff;">Directeur General</div>';
        $html .= '<div style="border-bottom: 1px solid #333; color: #ffffff;">Directeur General</div>';
        $html .= '<p style="margin: 15px 0 5px 0; font-size: 10px;">Directeur General</p>';
        $html .= '</td>';
        $html .= '<td style="width: 33%; text-align: center; vertical-align: top; padding: 10px 5px; border: none;">';
        $html .= '<p style="margin: 25px 0; font-size: 10px; font-weight: bold;">Louise MUSAVI</p>';
        $html .= '<div style="color: #ffffff;">Chargée de la finance</div>';
        $html .= '<div style="border-bottom: 1px solid #333; color: #ffffff;">Chargée de la finance</div>';
        $html .= '<p style="margin: 15px 0 5px 0; font-size: 10px;">Chargée de la finance</p>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';

        // Pied de page - Utiliser une table pour l'alignement
        $footer = '<table style="width: 100%; position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; padding: 5px; border-top: 2px solid #87CEEB;">';
        $footer .= '<tr>';
        $footer .= '<td style="text-align: left; width: 50%; border: none;">© 2024 JF BUSINESS SARL</td>';
        $days_fr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $day_en = date('l');
        $day_fr = $days_fr[$day_en] ?? $day_en;
        $footer .= '<td style="text-align: right; width: 50%; border: none;"> ' . $day_fr . ', le ' . date('d/m/Y à H:i:s') . '</td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        // Export PDF avec mPDF si disponible
        if (class_exists('\Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_top' => 10,
                    'margin_bottom' => 25,
                    'margin_left' => 10,
                    'margin_right' => 10
                ]);
                $mpdf->SetTitle('Fiche de Paie - ' . $site['name'] . ' - ' . $weekOf);
                $mpdf->SetHTMLFooter($footer);
                $mpdf->WriteHTML($html);
                $filename = 'payslip_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $site['name']) . '_' . $weekOf . '.pdf';
                $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
                exit;
            } catch (\Throwable $e) {
                // En cas d'erreur mpdf, fallback
                error_log('Erreur mpdf exportPayslipPDF: ' . $e->getMessage());
            }
        }

        // Fallback HTML si mPDF absent
        $notice = '<div style="background:#ffefc6;padding:8px;border:1px solid #f0c36d;margin-bottom:10px;">' .
            '<strong>Note :</strong> mpdf non installé. Téléchargement HTML en remplacement.</div>';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="payslip_' . $weekOf . '.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . $footer . '</body></html>';
        exit;
    }

    /**
     * Exporte la fiche de présence hebdomadaire en PDF
     */
    public function exportPresencePDF()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);
        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        $payslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);

        // Récupérer les données des ouvriers (même si la fiche de paie n'existe pas encore)
        $payrollArray = [];
        if ($payslip && !empty($payslip['payroll'])) {
            $payrollArray = is_array($payslip['payroll']) ? $payslip['payroll'] : iterator_to_array($payslip['payroll']);
        } else {
            // Si pas de fiche de paie, récupérer depuis les présences de la semaine
            $attendanceModel = new AttendanceModel();
            $attendances = $attendanceModel->getBySiteAndWeek($siteId, $weekOf);
            if (!empty($attendances)) {
                $workers = [];
                foreach ($attendances as $attendance) {
                    // Convertir BSONDocument en array
                    $attendanceArray = is_array($attendance) ? $attendance : iterator_to_array($attendance);
                    $workerId = (string)$attendanceArray['worker_id'];
                    if (!isset($workers[$workerId])) {
                        $workers[$workerId] = [
                            'worker_id' => $workerId,
                            'worker_name' => $attendanceArray['worker_name'] ?? '',
                            'category' => $attendanceArray['category'] ?? '',
                            'monday' => 0,
                            'tuesday' => 0,
                            'wednesday' => 0,
                            'thursday' => 0,
                            'friday' => 0,
                            'saturday' => 0
                        ];
                    }
                    // Les présences sont déjà dans les données d'attendance
                    $workers[$workerId] = array_merge($workers[$workerId], $attendanceArray);
                }
                $payrollArray = array_values($workers);
            }
        }

        // Trier les ouvriers : T.T en premier, puis M.C
        $ttWorkers = array_filter($payrollArray, fn($w) => $w['category'] === 'T.T');
        $mcWorkers = array_filter($payrollArray, fn($w) => $w['category'] === 'M.C');
        $sortedPayroll = array_merge($ttWorkers, $mcWorkers);

        // Générer le HTML de la fiche de présence
        $html = '<style>
            body { font-family: "Segoe UI", "Roboto", Arial, sans-serif; font-size: 11px; margin: 4px 5px; padding: 0; color: #2a2a2a; }
            .header { margin-bottom: 5px; }
            .logo-container { text-align: center; margin-bottom: 5px; }
            .row { font-size: 0; margin-bottom: 5px; }
            .col-5 { display: inline-block; width: 45%; min-height: 115px; border: 1px solid #d0d7e1; background: #f7f9fc; padding: 5px 6px; border-radius: 8px; box-sizing: border-box; box-shadow: 0 2px 6px rgba(0,0,0,0.08); margin-right: 2%; vertical-align: top; font-size: 11px; }
            .col-5:last-child { margin-right: 0; }
            .company-info p, .site-info p { margin: 3px 0; font-size: 11px; text-align: left; }
            .company-info p strong, .site-info p strong { min-width: 110px; display: inline-block; }
            .blue-line { border-bottom: 3px solid #1d70b8; margin: 3px 0; }
            .title { text-align: center; font-size: 18px; font-weight: bold; color: #0e3c78; margin: 5px 0 5px; }
            table { width: 100%; border-collapse: collapse; margin: 5px 0; font-size: 10px; background: #ffffff; }
            th, td { border: 1px solid #b9c6d6; padding: 3px 4px; text-align: center; }
            th { background: #1583e8; color: #ffffff; font-weight: bold; letter-spacing: 0.03em; font-size: 12px; }
            tbody tr:nth-child(odd) { background: #f8fbff; }
            .tt-row { background-color: #ffffff; }
            .mc-row { background-color: #fff8e8; color: #3f2a13; }
            .mc-row td { border-color: #e1c18a; }
            .mc-row strong, .mc-row th { color: #a56e00; }
            .total-row { background-color: #dbe9f6; font-weight: bold; }
            .signature-cell { width: 80px; }
            .synthesis-tables { display: flex; justify-content: space-between; margin-top: 10px; }
            .synthesis-table { width: 48%; }
            .synthesis-table th { background: #1757a6; color: white; }
            .synthesis-table .mc-summary th { background: #c98a2f; }
            .synthesis-table .tt-summary th { background: #1b5fbd; }
            .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; padding: 4px 6px; border-top: 2px solid #87CEEB; }
            .footer-left { float: left; }
            .footer-right { float: right; }
            .zero-value { color: #d13438; font-weight: bold; }
            .presence-note { text-align: center; font-style: italic; color: #666; margin: 20px 0; font-size: 12px; }
        </style>';

        // En-tête avec logo et informations
        $html .= '<div class="header">';
        $html .= '<div class="logo-container">';
        $html .= '<img src="C:/dev/log_project/assets/images/logo.png" style="width: 80px; height: 70px; display: block; margin: 0 auto;" alt="Logo JF BUSINESS SARL">';
        $html .= '</div>';

        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; border: none;">';
        $html .= '<tr style="border: none;">';
        
        $html .= '<td style="width: 48%; min-height: 115px; border: 1px solid #ccc; background: #f4f7fb; padding: 4px 5px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); vertical-align: top; font-size: 11px; text-align: left;">';
        $html .= '<p style="margin: 2px 0;"><strong>N° RCCM :</strong> CD/KNG/RCCM/24-B-D4138</p>';
        $html .= '<p style="margin: 2px 0;"><strong>ID-NAT :</strong> 01-F4200-N 37015G</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° IMPOT :</strong> A2504347D</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° d\'affiliation INSS :</strong> 1022461300</p>';
        $html .= '<p style="margin: 2px 0;"><strong>N° d\'immatriculation A L\'INPP :</strong> A2504347D</p>';
        $html .= '</td>';
        
        $html .= '<td style="width: 4%; border: none;"></td>';
        
        $html .= '<td style="width: 48%; min-height: 115px; border: 1px solid #ccc; background: #f4f7fb; padding: 4px 5px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); vertical-align: top; font-size: 11px; text-align: left;">';
        $html .= '<p style="margin: 2px 0;"><strong>Superviseur :</strong> ...............................................................................</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Ingénieur :</strong> ' . htmlspecialchars($site['engineer_name'] ?? '') . '</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Contacts Ir. :</strong> ' . htmlspecialchars($site['engineer_phone'] ?? '') . '</p>';
        $html .= '<p style="margin: 2px 0;"><strong>Semaine du ' . htmlspecialchars($payslip['week_start'] ?? $weekOf) . ' au ' . htmlspecialchars($payslip['week_end'] ?? $weekOf) . '</strong></p>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<div class="blue-line"></div>';
        $html .= '<div class="title">Fiche de présence hebdomadaire des ouvriers : ' . htmlspecialchars($site['name']) . '</div>';


        // Tableau de présence
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>No</th>';
        $html .= '<th style="width: 18%;">Nom</th>';
        $html .= '<th>Catégorie</th>';
        $html .= '<th>Lun</th>';
        $html .= '<th>Mar</th>';
        $html .= '<th>Mer</th>';
        $html .= '<th>Jeu</th>';
        $html .= '<th>Ven</th>';
        $html .= '<th>Sam</th>';
        $html .= '<th class="signature-cell">Signature</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $count = 1;

        foreach ($sortedPayroll as $row) {
            $rowClass = ($row['category'] === 'M.C') ? 'mc-row' : 'tt-row';
            $html .= '<tr class="' . $rowClass . '">';
            $html .= '<td>' . $count++ . '</td>';
            $html .= '<td style="text-align: left; width: 18%;">' . htmlspecialchars($row['worker_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['category'] ?? '') . '</td>';

            foreach ($days as $day) {
                $value = $row[$day] ?? 0;
                $displayValue = ($value == 0) ? '<span class="zero-value">—</span>' : $value;
                $html .= '<td></td>';
            }

            $html .= '<td class="signature-cell"></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        // Tableaux de synthèse - Utiliser une table pour l'alignement
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: none;">';
        $html .= '<tr style="border: none;">';
        
        // Synthèse M.C (Maçons) - Gauche
        $html .= '<td style="width: 45%; vertical-align: top; border: none; padding-right: 2%;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 12px;">Synthèse Journalière M.C (Maçons)</h4>';
        $html .= '<table class="synthesis-table mc-summary" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr><th>Jour</th><th>Volume</th><th>Équivalence ($)</th></tr></thead>';
        $html .= '<tbody>';

        // Lignes vides pour synthèse M.C (6 jours)
        $days_fr = ['monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi', 'thursday' => 'Jeudi', 'friday' => 'Vendredi', 'saturday' => 'Samedi'];
        foreach ($days_fr as $day_en => $day_fr) {
            $html .= '<tr>';
            $html .= '<td style="text-align: left;">' . $day_fr . '</td>';
            $html .= '<td style="text-align: right;"></td>';
            $html .= '<td style="text-align: right;"></td>';
            $html .= '</tr>';
        }

        // Total M.C
        $html .= '<tr style="font-weight: bold; background: #e9ecef;">';
        $html .= '<td colspan="2" style="text-align: center; font-weight: bold;">Total Général M.C :</td>';
        $html .= '<td style="text-align: right;"><strong></strong></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</td>';

        // Synthèse T.T (Tout Travaux) - Droite
        $html .= '<td style="width: 45%; vertical-align: top; border: none;">';
        $html .= '<h4 style="margin: 0 0 10px 0; font-size: 12px;">Synthèse Journalière T.T (Tout Travaux)</h4>';
        $html .= '<table class="synthesis-table tt-summary" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr><th>Jour</th><th>Volume</th><th>Équivalence ($)</th></tr></thead>';
        $html .= '<tbody>';

        // Lignes vides pour synthèse T.T (6 jours)
        foreach ($days_fr as $day_en => $day_fr) {
            $html .= '<tr>';
            $html .= '<td style="text-align: left;">' . $day_fr . '</td>';
            $html .= '<td style="text-align: right;"></td>';
            $html .= '<td style="text-align: right;"></td>';
            $html .= '</tr>';
        }

        // Total T.T
        $html .= '<tr style="font-weight: bold; background: #e9ecef;">';
        $html .= '<td colspan="2" style="text-align: center; font-weight: bold;">Total Général T.T :</td>';
        $html .= '<td style="text-align: right;"><strong></strong></td>';
        $html .= '</tr>';

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';

        // Pied de page - Utiliser une table pour l'alignement
        $footer = '<table style="width: 100%; position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; padding: 5px; border-top: 2px solid #87CEEB;">';
        $footer .= '<tr>';
        $footer .= '<td style="text-align: left; width: 50%; border: none;">© 2024 JF BUSINESS SARL</td>';
        $days_fr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $day_en = date('l');
        $day_fr = $days_fr[$day_en] ?? $day_en;
        $footer .= '<td style="text-align: right; width: 50%; border: none;"> ' . $day_fr . ', le ' . date('d/m/Y') . '</td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        // Export PDF avec mPDF si disponible
        if (class_exists('\Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_top' => 10,
                    'margin_bottom' => 25,
                    'margin_left' => 10,
                    'margin_right' => 10
                ]);
                $mpdf->SetTitle('Fiche de Présence - ' . $site['name'] . ' - ' . $weekOf);
                $mpdf->SetHTMLFooter($footer);
                $mpdf->WriteHTML($html);
                $filename = 'presence_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $site['name']) . '_' . $weekOf . '.pdf';
                $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
                exit;
            } catch (\Throwable $e) {
                // En cas d'erreur mpdf, fallback
                error_log('Erreur mpdf exportPresencePDF: ' . $e->getMessage());
            }
        }

        // Fallback HTML si mPDF absent
        $notice = '<div style="background:#ffefc6;padding:8px;border:1px solid #f0c36d;margin-bottom:10px;">' .
            '<strong>Note :</strong> mpdf non installé. Téléchargement HTML en remplacement.</div>';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="presence_' . $weekOf . '.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . $footer . '</body></html>';
        exit;
    }

    /**
     * Archive une fiche de paie
     */
    public function archivePayslip()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        if (!$siteId || !$weekOf) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        if ($payslipModel->archive($siteId, $weekOf)) {
            session_start();
            $_SESSION['flash_success'] = 'Fiche de paie archivée avec succès';
        } else {
            session_start();
            $_SESSION['flash_error'] = 'Erreur lors de l\'archivage de la fiche';
        }

        header('Location: ?page=payroll&action=payslips&site_id=' . $siteId);
        exit;
    }

    /**
     * Désarchive une fiche de paie
     */
    public function unarchivePayslip()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        if (!$siteId || !$weekOf) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        if ($payslipModel->unarchive($siteId, $weekOf)) {
            session_start();
            $_SESSION['flash_success'] = 'Fiche de paie restaurée avec succès';
        } else {
            session_start();
            $_SESSION['flash_error'] = 'Erreur lors de la restauration de la fiche';
        }

        header('Location: ?page=payroll&action=payslips&site_id=' . $siteId);
        exit;
    }

    /**
     * Supprime une fiche de paie
     */
    public function deletePayslip()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $weekOf = $_GET['week_of'] ?? '';

        if (!$siteId || !$weekOf) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        if ($payslipModel->delete($siteId, $weekOf)) {
            session_start();
            $_SESSION['flash_success'] = 'Fiche de paie supprimée avec succès';
        } else {
            session_start();
            $_SESSION['flash_error'] = 'Erreur lors de la suppression de la fiche';
        }

        header('Location: ?page=payroll&action=payslips&site_id=' . $siteId);
        exit;
    }

    /**
     * Liste les fiches de paie d'un chantier
     */
    public function payslips()
    {
        $this->requireAdmin();
        $siteId = $_GET['site_id'] ?? '';
        $siteModel = new SiteModel();
        $site = $siteModel->getById($siteId);

        if (!$site) {
            header('Location: ?page=payroll&action=sites');
            exit;
        }

        $payslipModel = new PayslipModel();
        $payslips = $payslipModel->getBySite($siteId);

        $this->render('admin/payroll_payslips_list', [
            'site' => $site,
            'payslips' => $payslips
        ]);
    }

    // ============ RAPPORTS SYNTHÈSE HEBDOMADAIRES ============

    /**
     * Affiche le rapport synthèse hebdomadaire de tous les chantiers
     */
    public function weeklyReportSynthesis()
    {
        $this->requireAdmin();
        
        // Déterminer la semaine en cours
        $weekOf = $_GET['week_of'] ?? date('Y-m-d');
        $weekTime = strtotime($weekOf);
        if (!$weekTime) {
            $weekTime = time();
        }
        
        $weekStart = date('Y-m-d', strtotime('monday this week', $weekTime));
        $weekEnd = date('Y-m-d', strtotime('saturday this week', $weekTime));
        
        // Récupérer tous les chantiers
        $siteModel = new SiteModel();
        $allSites = $siteModel->getAll();
        
        // Récupérer les payslips pour cette semaine
        $payslipModel = new PayslipModel();
        $payslipsData = [];
        $sitesWithActivity = [];
        $weeklyTotals = [
            'total_salary' => 0,
            'total_workers_tt' => 0,
            'total_workers_mc' => 0,
            'total_jobs_worked_tt' => 0,
            'total_jobs_worked_mc' => 0,
            'daily_synthesis_tc' => [
                'monday' => 0, 'tuesday' => 0, 'wednesday' => 0, 
                'thursday' => 0, 'friday' => 0, 'saturday' => 0
            ],
            'daily_synthesis_mc' => [
                'monday' => 0, 'tuesday' => 0, 'wednesday' => 0,
                'thursday' => 0, 'friday' => 0, 'saturday' => 0
            ],
            'summary_by_category' => [
                'T.T' => ['workers' => 0, 'days' => 0, 'salary' => 0],
                'M.C' => ['workers' => 0, 'days' => 0, 'salary' => 0]
            ]
        ];
        
        // Récupérer les présences pour calculer les données de synthèse
        $attendanceModel = new AttendanceModel();
        
        foreach ($allSites as $site) {
            $siteId = (string)$site['_id'];
            
            // Vérifier s'il y a des présences pour ce chantier cette semaine
            $attendances = $attendanceModel->getBySiteAndWeek($siteId, $weekOf);
            
            if (!empty($attendances)) {
                $payslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);
                
                if (!$payslip) {
                    // Créer une structure de payslip temporaire à partir des attendances
                    $payslip = $this->generatePayslipFromAttendances($site, $siteId, $attendances, $weekOf, $weekStart, $weekEnd);
                }
                
                $sitesWithActivity[$siteId] = [
                    'site' => $site,
                    'payslip' => $payslip,
                    'attendances' => $attendances
                ];
                
                $payslipsData[$siteId] = $payslip;
                
                // Accumuler les totaux
                $payslip = is_array($payslip) ? $payslip : iterator_to_array($payslip);
                
                $weeklyTotals['total_salary'] += $payslip['total_salary'] ?? 0;
                
                // Traiter le payroll
                $payroll = $payslip['payroll'] ?? [];
                $payroll = is_array($payroll) ? $payroll : iterator_to_array($payroll);
                
                foreach ($payroll as $worker) {
                    $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                    $category = $worker['category'] ?? 'T.T';
                    $salary = $worker['weekly_salary'] ?? 0;
                    $daysWorked = $worker['days_worked'] ?? 0;
                    
                    if ($category === 'M.C') {
                        $weeklyTotals['total_workers_mc']++;
                        $weeklyTotals['total_jobs_worked_mc'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['M.C']['workers']++;
                        $weeklyTotals['summary_by_category']['M.C']['days'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['M.C']['salary'] += $salary;
                    } else {
                        $weeklyTotals['total_workers_tt']++;
                        $weeklyTotals['total_jobs_worked_tt'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['T.T']['workers']++;
                        $weeklyTotals['summary_by_category']['T.T']['days'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['T.T']['salary'] += $salary;
                    }
                }
            }
        }
        
        $this->render('admin/payroll_weekly_synthesis', [
            'week_of' => $weekOf,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'sites_with_activity' => $sitesWithActivity,
            'weekly_totals' => $weeklyTotals
        ]);
    }

    /**
     * Génère une structure de payslip à partir des attendances
     */
    private function generatePayslipFromAttendances($site, $siteId, $attendances, $weekOf, $weekStart, $weekEnd)
    {
        $configModel = new SalaryConfigModel();
        $workerModel = new WorkerModel();
        
        $payroll = [];
        $dailySynthesisTc = [
            'monday' => ['day' => 'Lundi', 'daily_volume' => 0, 'equivalence' => 0],
            'tuesday' => ['day' => 'Mardi', 'daily_volume' => 0, 'equivalence' => 0],
            'wednesday' => ['day' => 'Mercredi', 'daily_volume' => 0, 'equivalence' => 0],
            'thursday' => ['day' => 'Jeudi', 'daily_volume' => 0, 'equivalence' => 0],
            'friday' => ['day' => 'Vendredi', 'daily_volume' => 0, 'equivalence' => 0],
            'saturday' => ['day' => 'Samedi', 'daily_volume' => 0, 'equivalence' => 0]
        ];
        
        $dailySynthesisMc = [
            'monday' => ['day' => 'Lundi', 'daily_volume' => 0, 'equivalence' => 0],
            'tuesday' => ['day' => 'Mardi', 'daily_volume' => 0, 'equivalence' => 0],
            'wednesday' => ['day' => 'Mercredi', 'daily_volume' => 0, 'equivalence' => 0],
            'thursday' => ['day' => 'Jeudi', 'daily_volume' => 0, 'equivalence' => 0],
            'friday' => ['day' => 'Vendredi', 'daily_volume' => 0, 'equivalence' => 0],
            'saturday' => ['day' => 'Samedi', 'daily_volume' => 0, 'equivalence' => 0]
        ];
        
        $totalSalaryTc = 0;
        $totalSalaryMc = 0;
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        foreach ($attendances as $attendance) {
            $attendance = is_array($attendance) ? $attendance : iterator_to_array($attendance);
            
            $category = $attendance['category'] ?? 'T.T';
            $config = $configModel->getBySiteAndCategory($siteId, $category);
            $dailyRate = $config['daily_rate'] ?? 3.0;
            
            // Récupérer l'ouvrier pour avoir son nom
            $workerId = $attendance['worker_id'] ?? '';
            $worker = null;
            if (!empty($workerId)) {
                $worker = $workerModel->getById($workerId);
            }
            $workerName = ($worker && isset($worker['name'])) ? $worker['name'] : 'N/A';
            
            $daysWorked = 0;
            
            foreach ($days as $day) {
                $value = $attendance[$day] ?? 0;
                $daysWorked += $value;
            }
            
            $weeklySalary = $daysWorked * $dailyRate;
            
            // Ajouter à la synthèse journalière
            foreach ($days as $day) {
                $value = $attendance[$day] ?? 0;
                $dayKey = $day;
                
                if ($category === 'M.C') {
                    $dailySynthesisMc[$dayKey]['daily_volume'] += $value;
                    $dailySynthesisMc[$dayKey]['equivalence'] += $value * $dailyRate;
                } else {
                    $dailySynthesisTc[$dayKey]['daily_volume'] += $value;
                    $dailySynthesisTc[$dayKey]['equivalence'] += $value * $dailyRate;
                }
            }
            
            $payroll[] = [
                'worker_id' => $workerId,
                'worker_name' => $workerName,
                'category' => $category,
                'monday' => $attendance['monday'] ?? 0,
                'tuesday' => $attendance['tuesday'] ?? 0,
                'wednesday' => $attendance['wednesday'] ?? 0,
                'thursday' => $attendance['thursday'] ?? 0,
                'friday' => $attendance['friday'] ?? 0,
                'saturday' => $attendance['saturday'] ?? 0,
                'days_worked' => $daysWorked,
                'daily_rate' => $dailyRate,
                'weekly_salary' => $weeklySalary
            ];
            
            if ($category === 'M.C') {
                $totalSalaryMc += $weeklySalary;
            } else {
                $totalSalaryTc += $weeklySalary;
            }
        }
        
        return [
            'site_id' => $siteId,
            'week_of' => $weekOf,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'payroll' => $payroll,
            'daily_synthesis_tc' => array_values($dailySynthesisTc),
            'daily_synthesis_mc' => array_values($dailySynthesisMc),
            'total_synthesis_tc' => $totalSalaryTc,
            'total_synthesis_mc' => $totalSalaryMc,
            'total_salary' => $totalSalaryTc + $totalSalaryMc
        ];
    }

    /**
     * Exporte le rapport synthèse hebdomadaire en PDF
     */
    public function exportWeeklySynthesisPDF()
    {
        $this->requireAdmin();
        
        // Même logique que weeklyReportSynthesis()
        $weekOf = $_GET['week_of'] ?? date('Y-m-d');
        $weekTime = strtotime($weekOf);
        if (!$weekTime) {
            $weekTime = time();
        }
        
        $weekStart = date('Y-m-d', strtotime('monday this week', $weekTime));
        $weekEnd = date('Y-m-d', strtotime('saturday this week', $weekTime));
        
        $siteModel = new SiteModel();
        $allSites = $siteModel->getAll();
        
        $payslipModel = new PayslipModel();
        $attendanceModel = new AttendanceModel();
        $sitesWithActivity = [];
        
        $weeklyTotals = [
            'total_salary' => 0,
            'total_workers_tt' => 0,
            'total_workers_mc' => 0,
            'summary_by_category' => [
                'T.T' => ['workers' => 0, 'days' => 0, 'salary' => 0],
                'M.C' => ['workers' => 0, 'days' => 0, 'salary' => 0]
            ]
        ];
        
        foreach ($allSites as $site) {
            $siteId = (string)$site['_id'];
            $attendances = $attendanceModel->getBySiteAndWeek($siteId, $weekOf);
            
            if (!empty($attendances)) {
                $payslip = $payslipModel->getBySiteAndWeek($siteId, $weekOf);
                
                if (!$payslip) {
                    $payslip = $this->generatePayslipFromAttendances($site, $siteId, $attendances, $weekOf, $weekStart, $weekEnd);
                }
                
                $sitesWithActivity[$siteId] = [
                    'site' => $site,
                    'payslip' => $payslip
                ];
                
                $payslip = is_array($payslip) ? $payslip : iterator_to_array($payslip);
                $weeklyTotals['total_salary'] += $payslip['total_salary'] ?? 0;
                
                $payroll = $payslip['payroll'] ?? [];
                $payroll = is_array($payroll) ? $payroll : iterator_to_array($payroll);
                
                foreach ($payroll as $worker) {
                    $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                    $category = $worker['category'] ?? 'T.T';
                    $salary = $worker['weekly_salary'] ?? 0;
                    $daysWorked = $worker['days_worked'] ?? 0;
                    
                    if ($category === 'M.C') {
                        $weeklyTotals['total_workers_mc']++;
                        $weeklyTotals['summary_by_category']['M.C']['workers']++;
                        $weeklyTotals['summary_by_category']['M.C']['days'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['M.C']['salary'] += $salary;
                    } else {
                        $weeklyTotals['total_workers_tt']++;
                        $weeklyTotals['summary_by_category']['T.T']['workers']++;
                        $weeklyTotals['summary_by_category']['T.T']['days'] += $daysWorked;
                        $weeklyTotals['summary_by_category']['T.T']['salary'] += $salary;
                    }
                }
            }
        }
        
        // Vérifier s'il y a des données à afficher
        if (empty($sitesWithActivity)) {
            // Générer un PDF avec un message d'information
            $html = '<style>';
            $html .= 'body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }';
            $html .= '.no-data { font-size: 18px; color: #666; margin: 20px 0; }';
            $html .= '.info { font-size: 14px; color: #999; margin: 10px 0; }';
            $html .= '</style>';
            $html .= '<h1>Rapport Synthèse Hebdomadaire</h1>';
            $html .= '<p class="no-data">Aucune activité enregistrée pour la période</p>';
            $html .= '<p class="info">Semaine du ' . htmlspecialchars($weekStart) . ' au ' . htmlspecialchars($weekEnd) . '</p>';
            $html .= '<p class="info">Vérifiez que les présences ont été saisies pour cette semaine.</p>';
        } else {
            // Générer le HTML pour le PDF avec les données
            $html = $this->generateWeeklySynthesisHtml($sitesWithActivity, $weeklyTotals, $weekStart, $weekEnd);
        }
        
        // Export PDF avec TCPDF (plus robuste pour les rapports)
        if (class_exists('\TCPDF')) {
            try {
                $this->generateWeeklySynthesisPDFWithTCPDF($sitesWithActivity, $weeklyTotals, $weekStart, $weekEnd);
                exit;
            } catch (\Throwable $e) {
                error_log('Erreur TCPDF exportWeeklySynthesisPDF: ' . $e->getMessage());
            }
        }

        // Fallback avec mPDF
        if (class_exists('\Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_top' => 15,
                    'margin_bottom' => 20,
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_header' => 5,
                    'margin_footer' => 10,
                    'orientation' => 'P',
                    'setAutoTopMargin' => 'stretch',
                    'setAutoBottomMargin' => 'stretch'
                ]);

                $mpdf->SetDisplayMode('fullpage');
                $mpdf->use_kwt = true;
                $mpdf->shrink_tables_to_fit = 1;

                $footer = '<table style="width: 100%; font-size: 9px; border-top: 1px solid #87CEEB; padding-top: 5px;">';
                $footer .= '<tr>';
                $footer .= '<td style="text-align: left; width: 50%;">© 2024 JF BUSINESS SARL</td>';
                $days_fr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
                $day_en = date('l');
                $day_fr = $days_fr[$day_en] ?? $day_en;
                $footer .= '<td style="text-align: right; width: 50%;"> ' . $day_fr . ', le ' . date('d/m/Y à H:i:s') . '</td>';
                $footer .= '</tr>';
                $footer .= '</table>';

                $mpdf->SetTitle('Rapport Synthèse Hebdomadaire - ' . $weekStart . ' au ' . $weekEnd);
                $mpdf->SetHTMLFooter($footer);
                $mpdf->WriteHTML($html);

                $filename = 'rapport_synthese_hebdomadaire_' . $weekStart . '.pdf';
                $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
                exit;
            } catch (\Throwable $e) {
                error_log('Erreur mPDF exportWeeklySynthesisPDF: ' . $e->getMessage());
            }
        }
        
        // Fallback HTML si mPDF absent
        $notice = '<div style="background:#ffefc6;padding:8px;border:1px solid #f0c36d;margin-bottom:10px;">' .
            '<strong>Note :</strong> mPDF non installé. Téléchargement HTML en remplacement.</div>';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="rapport_synthese_hebdomadaire_' . $weekStart . '.html"');
        echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
        exit;
    }

    /**
     * Génère le HTML du rapport synthèse hebdomadaire pour PDF
     */
    private function generateWeeklySynthesisHtml($sitesWithActivity, $weeklyTotals, $weekStart, $weekEnd)
    {
        // Logo
        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
        $logoSrc = '';
        if ($logoPath && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $html = '<style>';
        $html .= 'body { font-family: "Segoe UI", "Roboto", Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #2a2a2a; line-height: 1.4; }';
        $html .= '.header { margin-bottom: 20px; }';
        $html .= '.logo-container { text-align: center; margin-bottom: 15px; }';
        $html .= '.company-info { display: table; width: 100%; margin-bottom: 20px; border: 1px solid #d0d7e1; background: #f7f9fc; }';
        $html .= '.company-info-left, .company-info-right { display: table-cell; vertical-align: top; padding: 10px; width: 48%; }';
        $html .= '.company-info p { margin: 3px 0; font-size: 10px; }';
        $html .= '.company-info strong { display: inline-block; min-width: 120px; }';
        $html .= '.blue-line { border-bottom: 3px solid #1d70b8; margin: 10px 0 15px 0; }';
        $html .= '.title { text-align: center; font-size: 20px; font-weight: bold; color: #0e3c78; margin: 15px 0; }';
        $html .= '.section-title { text-align: center; font-size: 16px; font-weight: bold; color: #495057; margin: 20px 0 10px 0; background: #e9ecef; padding: 8px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }';
        $html .= 'th, td { border: 1px solid #b9c6d6; padding: 6px 8px; }';
        $html .= 'th { background: #1583e8; color: #ffffff; font-weight: bold; text-align: center; font-size: 11px; }';
        $html .= 'tbody tr:nth-child(odd) { background: #f8fbff; }';
        $html .= '.summary-section { background: #f8f9fa; padding: 15px; margin: 20px 0; border: 2px solid #dee2e6; }';
        $html .= '.summary-section h2 { margin: 0 0 15px 0; color: #495057; font-size: 16px; text-align: center; }';
        $html .= '.summary-table { width: 100%; border-collapse: collapse; }';
        $html .= '.summary-table td { border: 1px solid #dee2e6; padding: 8px; }';
        $html .= '.summary-label { font-weight: bold; width: 70%; }';
        $html .= '.summary-value { text-align: right; font-weight: bold; color: #495057; width: 30%; }';
        $html .= '.total-row { background: #e9ecef; }';
        $html .= '.total-row .summary-value { font-size: 14px; color: #dc3545; }';
        $html .= '.footer { text-align: center; font-size: 9px; padding: 10px; border-top: 1px solid #333; margin-top: 30px; }';
        $html .= '@page { size: A4; margin: 15mm; }';
        $html .= '</style>';

        // En-tête avec logo et informations entreprise
        $html .= '<div class="header">';
        $html .= '<div class="logo-container">';
        if ($logoSrc) {
            $html .= '<img src="' . $logoSrc . '" style="height: 70px;" alt="Logo">';
        }
        $html .= '</div>';

        $html .= '<div class="company-info">';
        $html .= '<div class="company-info-left">';
        $html .= '<p><strong>N° RCCM :</strong> CD/KNG/RCCM/24-B-D4138</p>';
        $html .= '<p><strong>ID-NAT :</strong> 01-F4200-N 37015G</p>';
        $html .= '<p><strong>N° IMPOT :</strong> A2504347D</p>';
        $html .= '<p><strong>N° d\'affiliation INSS :</strong> 1022461300</p>';
        $html .= '<p><strong>N° d\'immatriculation A L\'INPP :</strong> A2504347D</p>';
        $html .= '</div>';
        $html .= '<div class="company-info-right">';
        $html .= '<p><strong>Période :</strong> ' . htmlspecialchars($weekStart) . ' au ' . htmlspecialchars($weekEnd) . '</p>';
        $html .= '<p><strong>Type :</strong> Rapport Synthèse Hebdomadaire</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="blue-line"></div>';
        $html .= '<div class="title">RAPPORT SYNTHÈSE HEBDOMADAIRE</div>';

        // Section Chantiers Ayant Travaillé
        $html .= '<div class="section-title">CHANTIERS AYANT TRAVAILLÉ</div>';

        // Tableau synthèse des chantiers
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: 25%;">Nom Chantier</th>';
        $html .= '<th style="width: 25%;">Adresse Chantier</th>';
        $html .= '<th style="width: 12%; text-align: center;">Nbre T.T</th>';
        $html .= '<th style="width: 12%; text-align: center;">Nbre M.C</th>';
        $html .= '<th style="width: 15%; text-align: center;">Salaire total</th>';
        $html .= '<th style="width: 11%;">Observation</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Vérifier que sitesWithActivity n'est pas vide
        if (!empty($sitesWithActivity)) {
            foreach ($sitesWithActivity as $siteWithActivity) {
                $site = $siteWithActivity['site'];
                $payslip = $siteWithActivity['payslip'];

                $payslip = is_array($payslip) ? $payslip : iterator_to_array($payslip);
                $payroll = $payslip['payroll'] ?? [];
                $payroll = is_array($payroll) ? $payroll : iterator_to_array($payroll);

                $workersTt = 0;
                $workersMc = 0;

                foreach ($payroll as $worker) {
                    $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                    if (($worker['category'] ?? 'T.T') === 'M.C') {
                        $workersMc++;
                    } else {
                        $workersTt++;
                    }
                }

                $html .= '<tr>';
                $html .= '<td><strong>' . htmlspecialchars($site['name'] ?? 'N/A') . '</strong></td>';
                $html .= '<td>' . htmlspecialchars($site['location'] ?? 'N/A') . '</td>';
                $html .= '<td style="text-align: center;">' . $workersTt . '</td>';
                $html .= '<td style="text-align: center;">' . $workersMc . '</td>';
                $html .= '<td style="text-align: center; font-weight: bold;">' . number_format($payslip['total_salary'] ?? 0, 2) . ' $</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="6" style="text-align: center; padding: 20px;">Aucun chantier actif trouvé pour cette période.</td></tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        // Section Résumé Consolidé de la Semaine
        $html .= '<div class="summary-section">';
        $html .= '<h2>RÉSUMÉ CONSOLIDÉ DE LA SEMAINE</h2>';

        $html .= '<table class="summary-table">';
        $html .= '<tr>';
        $html .= '<td class="summary-label">Nombre de chantiers actifs :</td>';
        $html .= '<td class="summary-value">' . count($sitesWithActivity) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Total ouvriers T.T :</td>';
        $html .= '<td class="summary-value">' . ($weeklyTotals['summary_by_category']['T.T']['workers'] ?? 0) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Jours travaillés T.T :</td>';
        $html .= '<td class="summary-value">' . number_format(($weeklyTotals['summary_by_category']['T.T']['days'] ?? 0), 1) . ' j</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Salaire total T.T :</td>';
        $html .= '<td class="summary-value">' . number_format(($weeklyTotals['summary_by_category']['T.T']['salary'] ?? 0), 2) . ' $</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Total ouvriers M.C :</td>';
        $html .= '<td class="summary-value">' . ($weeklyTotals['summary_by_category']['M.C']['workers'] ?? 0) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Jours travaillés M.C :</td>';
        $html .= '<td class="summary-value">' . number_format(($weeklyTotals['summary_by_category']['M.C']['days'] ?? 0), 1) . ' j</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td class="summary-label">Salaire total M.C :</td>';
        $html .= '<td class="summary-value">' . number_format(($weeklyTotals['summary_by_category']['M.C']['salary'] ?? 0), 2) . ' $</td>';
        $html .= '</tr>';

        $html .= '<tr class="total-row">';
        $html .= '<td class="summary-label"><strong>TOTAL GÉNÉRAL SEMAINE :</strong></td>';
        $html .= '<td class="summary-value">' . number_format(($weeklyTotals['total_salary'] ?? 0), 2) . ' $</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';

        // Signatures
        $html .= '<div style="display:flex; justify-content:space-between; margin-top:30px;">';
        $html .= '<div style="width:48%; text-align:center;">';
        $html .= '<p style="margin:0; font-weight:bold;">John MUTAMU</p>';
        $html .= '<p style="margin:0;">Directeur Général</p>';
        $html .= '</div>';
        $html .= '<div style="width:48%; text-align:center;">';
        $html .= '<p style="margin:0; font-weight:bold;">Louise MUSAVI</p>';
        $html .= '<p style="margin:0;">Chargée de la finance</p>';
        $html .= '</div>';
        $html .= '</div>';

        // Footer
        $html .= '<div class="footer">';
        $html .= '<p>© 2024 JF BUSINESS SARL — Système comptable intégré. Tous droits réservés. Développé avec ❤️ pour une comptabilité précise et fiable.</p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Génère le rapport synthèse hebdomadaire en PDF avec TCPDF (plus robuste)
     */
    private function generateWeeklySynthesisPDFWithTCPDF($sitesWithActivity, $weeklyTotals, $weekStart, $weekEnd)
    {
        // Chemin du logo
        $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');

        // Créer le PDF
        $pdf = new WeeklyReportPDF($weekStart, $weekEnd, $logoPath);

        // Configuration du PDF
        $pdf->SetCreator('JF BUSINESS SARL');
        $pdf->SetAuthor('Système Comptable');
        $pdf->SetTitle('Rapport Synthèse Hebdomadaire - ' . $weekStart . ' au ' . $weekEnd);
        $pdf->SetSubject('Rapport de synthèse des travaux hebdomadaires');

        // Marges
        $pdf->SetMargins(15, 55, 15); // gauche, haut, droite
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(15);

        // Sauts de page automatiques
        $pdf->SetAutoPageBreak(TRUE, 20);

        // Ajouter une page
        $pdf->AddPage();

        // Police
        $pdf->SetFont('helvetica', '', 10);

        // Vérifier s'il y a des données
        if (empty($sitesWithActivity)) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 20, 'Aucune activité enregistrée pour cette période', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 10, 'Vérifiez que les présences ont été saisies pour la semaine du ' . $weekStart . ' au ' . $weekEnd, 0, 1, 'C');
        } else {
            // Section "CHANTIERS AYANT TRAVAILLÉ"
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(0, 8, 'CHANTIERS AYANT TRAVAILLÉ', 0, 1, 'C', true);
            $pdf->Ln(3);

            // Tableau des chantiers
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(24, 131, 232);
            $pdf->SetTextColor(255, 255, 255);

            // En-têtes du tableau
            $header = ['Nom Chantier', 'Adresse Chantier', 'Nbre T.T', 'Nbre M.C', 'Salaire total', 'Observation'];
            $widths = [45, 45, 20, 20, 25, 25];

            foreach ($header as $i => $col) {
                $pdf->Cell($widths[$i], 7, $col, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Contenu du tableau
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(248, 248, 248);

            $fill = false;
            foreach ($sitesWithActivity as $siteWithActivity) {
                $site = $siteWithActivity['site'];
                $payslip = $siteWithActivity['payslip'];

                $payslip = is_array($payslip) ? $payslip : iterator_to_array($payslip);
                $payroll = $payslip['payroll'] ?? [];
                $payroll = is_array($payroll) ? $payroll : iterator_to_array($payroll);

                $workersTt = 0;
                $workersMc = 0;

                foreach ($payroll as $worker) {
                    $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                    if (($worker['category'] ?? 'T.T') === 'M.C') {
                        $workersMc++;
                    } else {
                        $workersTt++;
                    }
                }

                $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
                $fill = !$fill;

                $pdf->Cell($widths[0], 6, $this->truncateText($site['name'] ?? 'N/A', 20), 1, 0, 'L', true);
                $pdf->Cell($widths[1], 6, $this->truncateText($site['location'] ?? 'N/A', 20), 1, 0, 'L', true);
                $pdf->Cell($widths[2], 6, $workersTt, 1, 0, 'C', true);
                $pdf->Cell($widths[3], 6, $workersMc, 1, 0, 'C', true);
                $pdf->Cell($widths[4], 6, number_format($payslip['total_salary'] ?? 0, 2) . ' $', 1, 0, 'R', true);
                $pdf->Cell($widths[5], 6, '', 1, 0, 'L', true);
                $pdf->Ln();
            }

            $pdf->Ln(5);

            // Section "RÉSUMÉ CONSOLIDÉ DE LA SEMAINE"
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(0, 8, 'RÉSUMÉ CONSOLIDÉ DE LA SEMAINE', 0, 1, 'C', true);
            $pdf->Ln(3);

            // Tableau de résumé
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(240, 240, 240);

            $summaryData = [
                ['Nombre de chantiers actifs :', count($sitesWithActivity)],
                ['Total ouvriers T.T :', $weeklyTotals['summary_by_category']['T.T']['workers'] ?? 0],
                ['Jours travaillés T.T :', number_format($weeklyTotals['summary_by_category']['T.T']['days'] ?? 0, 1) . ' j'],
                ['Salaire total T.T :', number_format($weeklyTotals['summary_by_category']['T.T']['salary'] ?? 0, 2) . ' $'],
                ['Total ouvriers M.C :', $weeklyTotals['summary_by_category']['M.C']['workers'] ?? 0],
                ['Jours travaillés M.C :', number_format($weeklyTotals['summary_by_category']['M.C']['days'] ?? 0, 1) . ' j'],
                ['Salaire total M.C :', number_format($weeklyTotals['summary_by_category']['M.C']['salary'] ?? 0, 2) . ' $'],
            ];

            foreach ($summaryData as $row) {
                $pdf->Cell(120, 6, $row[0], 1, 0, 'L', true);
                $pdf->Cell(30, 6, $row[1], 1, 0, 'R', true);
                $pdf->Ln();
            }

            // Total général
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(220, 235, 246);
            $pdf->Cell(120, 7, 'TOTAL GÉNÉRAL SEMAINE :', 1, 0, 'L', true);
            $pdf->Cell(30, 7, number_format($weeklyTotals['total_salary'] ?? 0, 2) . ' $', 1, 0, 'R', true);
            $pdf->Ln(12);

            // Signatures
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(50, 5, 'John MUTAMU', 0, 0, 'C');
            $pdf->Cell(50, 0, '', 0, 0);
            $pdf->Cell(100, 0, 'Louise MUSAVI', 0, 0, 'C');
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetLineWidth(0.2);
            $pdf->Cell(50, 0, '', 'T', 0, 'C');
            $pdf->Cell(75, 0, '', 0, 0);
            $pdf->Cell(50, 0, '', 'T', 1, 'C');
            $pdf->Ln(5);
            $pdf->Cell(50, 0, 'Directeur Général', 0, 0, 'C');
            $pdf->Cell(50, 0, '', 0, 0);
            $pdf->Cell(100, 0, 'Chargée de la finance', 0, 1, 'C');
            $pdf->Ln(5);
        }

        // Générer et télécharger le PDF
        $filename = 'rapport_synthese_hebdomadaire_' . $weekStart . '.pdf';
        $pdf->Output($filename, 'D');
    }

    /**
     * Tronque le texte s'il est trop long
     */
    private function truncateText($text, $maxLength) {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }
}