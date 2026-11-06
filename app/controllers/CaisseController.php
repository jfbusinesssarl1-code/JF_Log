<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\CaisseModel;
use App\Models\UserModel;
use Mpdf\Mpdf;
class CaisseController extends Controller
{
  public function index()
  {
    $this->requireRole(['caissier', 'accountant', 'comptable', 'manager', 'admin']);
    $model = new CaisseModel();
    [$filters, $modelFilters, $cashiers, $selectedCashierName] = $this->getFilterContext();
    $this->prepareCreatorData($model, $modelFilters);

    $itemsPerPage = 20;
    $totalCount = $model->countFiltered($modelFilters);
    $totalPages = ceil($totalCount / $itemsPerPage);

    $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : max(1, $totalPages);

    $result = $model->getAllWithPagination($modelFilters, $page, $itemsPerPage);
    $items = $result['items'];
    $pagination = $result['pagination'];
    $operationTotals = $model->getOperationTotals($modelFilters);
    $today = date('Y-m-d');
    $summary = $model->getDashboardSummary($today, date('Y-m-01'), $modelFilters);

    $this->render('caisse', [
      'items' => $items,
      'filters' => $filters,
      'cashiers' => $cashiers,
      'selectedCashierName' => $selectedCashierName,
      'pagination' => $pagination,
      'operationTotals' => $operationTotals,
      'summary' => $summary,
      'dashboardDate' => $today
    ]);
  }

  // Retourne uniquement le <tbody> (utilisé pour refresh partiel via AJAX)
  public function list_partial()
  {
    $this->requireRole(['caissier', 'accountant', 'comptable', 'manager', 'admin']);
    $model = new CaisseModel();
    [, $modelFilters] = $this->getFilterContext();
    $this->prepareCreatorData($model, $modelFilters);
    $items = $model->getAll($modelFilters);
    $operationTotals = $model->getOperationTotals($modelFilters);
    header('Content-Type: text/html; charset=utf-8');
    // Provide the number of rows to help clients validate partial updates
    header('X-Row-Count: ' . (count($items) + 1));
    if (!empty($items)) {
      foreach ($items as $it) {
        $id = isset($it['_id']) ? (string) $it['_id'] : '';
        $date = htmlspecialchars($it['date'] ?? '');
        $num = htmlspecialchars($it['numero_bon_manuscrit'] ?? '');
        $oper = htmlspecialchars($it['operateur'] ?? '');
        $lib = htmlspecialchars($it['libelle'] ?? '');
        $recette = number_format($it['recette'] ?? 0, 2);
        $depense = number_format($it['depense'] ?? 0, 2);
        $solde = number_format($it['solde'] ?? 0, 2);
        echo "<tr>\n";
        echo "<td>$date</td>\n";
        echo "<td>$num</td>\n";
        echo "<td>$oper</td>\n";
        echo "<td>$lib</td>\n";
        if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['caissier', 'admin'])) {
          $csrfToken = \App\Core\Csrf::getToken();
          $actions = "<a class=\"btn btn-sm btn-primary\" href=\"?page=caisse&action=edit&id=$id\">Modifier</a> <a class=\"btn btn-sm btn-danger\" href=\"?page=caisse&action=delete&id=$id&token=" . urlencode($csrfToken) . "\" onclick=\"return confirm('Supprimer ?')\">Supprimer</a>";
        } else {
          $actions = '—';
        }
        echo "<td class=\"text-end\">$recette</td>\n";
        echo "<td class=\"text-end\">$depense</td>\n";
        echo "<td class=\"text-end\">$solde</td>\n";
        echo "<td class=\"d-flex justify-content-center gap-2\">$actions</td>\n";
        echo "</tr>\n";
      }
      echo '<tr class="table-secondary fw-bold">';
      echo '<td colspan="4">Totaux des opérations</td>';
      echo '<td class="text-end">' . number_format($operationTotals['recettes'], 2) . '</td>';
      echo '<td class="text-end">' . number_format($operationTotals['depenses'], 2) . '</td>';
      echo '<td class="text-end">' . number_format($operationTotals['solde'], 2) . '</td>';
      echo '<td></td></tr>';
    } else {
      echo '<tr><td colspan="8" class="text-center">Aucune opération</td></tr>';
    }
    exit;
  }

  public function add()
  {
    $this->requireRole(['caissier', 'admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $token = $_POST['csrf_token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        // If AJAX, return JSON error instead of die/redirect
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
        if ($isAjax) {
          header('Content-Type: application/json; charset=utf-8');
          echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
          exit;
        }
        die('Erreur CSRF');
      }
      $date = $_POST['date'] ?? '';
      $type = $_POST['type'] ?? '';
      $libelle = trim($_POST['libelle'] ?? '');
      $montant = isset($_POST['montant']) ? $_POST['montant'] : '';
      $numero_bon_manuscrit = trim($_POST['numero_bon_manuscrit'] ?? '');
      $operateur = trim($_POST['operateur'] ?? '');
      $user = $_SESSION['user'] ?? null;

      $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

      // Server-side validation (return JSON on XHR)
      $errors = [];
      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
        $errors[] = 'Date invalide';
      if (!in_array($type, ['entree', 'sortie']))
        $errors[] = 'Type invalide';
      if ($libelle === '' || strlen($libelle) > 255)
        $errors[] = 'Libellé invalide';
      if ($montant === '' || !is_numeric($montant) || floatval($montant) <= 0)
        $errors[] = 'Montant invalide';
      if ($operateur === '' || strlen($operateur) > 128)
        $errors[] = 'Opérateur invalide';

      if (!empty($errors)) {
        if ($isAjax) {
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(400);
          echo json_encode(['success' => false, 'errors' => $errors]);
          exit;
        }
        // legacy behaviour for non-AJAX
        die(implode('<br>', $errors));
      }

      $data = [
        'date' => $date,
        'type' => $type,
        'libelle' => $libelle,
        'montant' => floatval($montant),
        'numero_bon_manuscrit' => $numero_bon_manuscrit,
        'operateur' => $operateur,
        'created_by' => $user['username'] ?? 'unknown'
      ];
      $creatorId = $user['_id'] ?? '';
      if (!is_string($creatorId) || !preg_match('/^[a-f0-9]{24}$/i', $creatorId)) {
        http_response_code(400);
        if ($isAjax) {
          header('Content-Type: application/json; charset=utf-8');
        }
        echo $isAjax
          ? json_encode(['success' => false, 'error' => 'Identifiant utilisateur invalide'])
          : 'Identifiant utilisateur invalide';
        exit;
      }
      $data['created_by_id'] = new \MongoDB\BSON\ObjectId($creatorId);

      $model = new CaisseModel();
      try {
        $insertResult = $model->insert($data);
      } catch (\Throwable $e) {
        error_log('CaisseController::add insert failed: ' . $e->getMessage());
        if ($isAjax) {
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(500);
          echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement']);
          exit;
        }
        throw $e;
      }

      // If request expects JSON (AJAX), return the created item (with computed recette/depense/solde)
      if ($isAjax) {
        $insertedId = (string) ($insertResult->getInsertedId() ?? '');
        if (!$insertedId) {
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(500);
          echo json_encode(['success' => false, 'error' => 'Insertion échouée']);
          exit;
        }
        // Recalculate solde by fetching items (getAll computes cumulative solde)
        $all = $model->getAll([
          'creator_id' => $creatorId,
          'creator_username' => $user['username'] ?? ''
        ]);
        $found = null;
        foreach ($all as $it) {
          if ((string) ($it['_id'] ?? '') === $insertedId) {
            $found = $it;
            break;
          }
        }
        if ($found) {
          $found['_id'] = (string) $found['_id'];
          $found['montant'] = floatval($found['montant'] ?? 0);
          $found['recette'] = floatval($found['recette'] ?? 0);
          $found['depense'] = floatval($found['depense'] ?? 0);
          $found['solde'] = floatval($found['solde'] ?? 0);
        } else {
          // best-effort fallback
          $found = array_merge($data, ['_id' => $insertedId, 'recette' => $data['type'] === 'entree' ? $data['montant'] : 0.0, 'depense' => $data['type'] === 'sortie' ? $data['montant'] : 0.0, 'solde' => null]);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'item' => $found]);
        exit;
      }
    }
    header('Location: ?page=caisse');
    exit;
  }

  public function edit()
  {
    $this->requireRole(['caissier', 'admin']);
    $id = $_GET['id'] ?? null;
    $model = new CaisseModel();
    [$ownerId, $ownerUsername] = $this->getOwnerScope();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
      $token = $_POST['csrf_token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token))
        die('Erreur CSRF');
      $data = [
        'date' => $_POST['date'] ?? '',
        'type' => $_POST['type'] ?? '',
        'libelle' => trim($_POST['libelle'] ?? ''),
        'montant' => floatval($_POST['montant'] ?? 0),
        'numero_bon_manuscrit' => trim($_POST['numero_bon_manuscrit'] ?? ''),
        'operateur' => trim($_POST['operateur'] ?? '')
      ];
      $model->update($id, $data, $ownerId, $ownerUsername);
      header('Location: ?page=caisse');
      exit;
    }
    $entry = $id ? $model->getById($id, $ownerId, $ownerUsername) : null;
    $this->render('caisse_edit', ['entry' => $entry]);
  }

  public function delete()
  {
    $this->requireRole(['caissier', 'admin']);
    $id = $_GET['id'] ?? null;
    $token = $_GET['token'] ?? '';
    if (!\App\Core\Csrf::checkToken($token)) {
      if (session_status() === PHP_SESSION_NONE) session_start();
      $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
    } elseif ($id) {
      $model = new CaisseModel();
      [$ownerId, $ownerUsername] = $this->getOwnerScope();
      $model->delete($id, $ownerId, $ownerUsername);
    }
    header('Location: ?page=caisse');
    exit;
  }

  public function export()
  {
    $this->requireRole(['caissier', 'accountant', 'comptable', 'manager', 'admin']);
    $format = $_GET['format'] ?? 'pdf';
    // Normalize: only PDF exports supported
    if ($format !== 'pdf')
      $format = 'pdf';
    // Debug helper: ?page=caisse&action=export&format=pdf&debug=1
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
      header('Content-Type: text/plain; charset=utf-8');
      echo "DEBUG export: class_exists Mpdf? " . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no') . "\n";
      echo "PHP SAPI: " . PHP_SAPI . "\n";
      echo "User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'n/a') . "\n";
      exit;
    }

    $model = new CaisseModel();
    [$filters, $modelFilters, , $selectedCashierName] = $this->getFilterContext();
    $this->prepareCreatorData($model, $modelFilters);
    $items = $model->getAll($modelFilters);

    // Build header with logo and company info
    $logoPath = realpath(__DIR__ . '/../../assets/images/logo.png');
    $logoSrc = '';
    if ($logoPath && is_file($logoPath)) {
      $logoData = base64_encode(file_get_contents($logoPath));
      $logoSrc = 'data:image/png;base64,' . $logoData;
    }

    $header = '<div style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;">';
    $header .= '<div style="flex:0 0 auto;text-align:left;">';
    if ($logoSrc) {
      $header .= '<img src="' . $logoSrc . '" style="height:70px;" alt="Logo">';
    }
    $header .= '<div style="font-size:12px;margin-top:6px;line-height:1.2;">N° RCCM : CD/KNG/RCCM/24-B-D4138<br>ID-NAT : 01-F4200-N 37015G<br>N° IMPOT : A2504347D<br>N° d’affiliation INSS : 1022461300<br>N° d’immatriculation A L’INPP : A2504347D</div>';
    $header .= '</div>';
    $header .= '<div style="flex:1 1 auto;text-align:right;font-size:12px;color:#333;">' . \App\Helpers\PdfHelper::formatPrintedAt() . '</div>';
    $header .= '</div>';
    $header .= '<div style="height:4px;background:#0d6efd;margin:8px 0 12px 0"></div>';
    $header .= '<h2 style="text-align:center;font-weight:700;margin:6px 0 12px 0">Livre de caisse</h2>';

    // Afficher les filtres actifs si présents
    $activeFilters = [];
    if (!empty($filters['date_debut']))
      $activeFilters[] = 'Depuis: ' . htmlspecialchars($filters['date_debut']);
    if (!empty($filters['date_fin']))
      $activeFilters[] = 'Jusqu\'au: ' . htmlspecialchars($filters['date_fin']);
    if (!empty($filters['type']))
      $activeFilters[] = 'Type: ' . htmlspecialchars($filters['type']);
    if (!empty($filters['operateur']))
      $activeFilters[] = 'Opérateur: ' . htmlspecialchars($filters['operateur']);
    if (!empty($filters['numero_bon_manuscrit']))
      $activeFilters[] = 'N° Bon: ' . htmlspecialchars($filters['numero_bon_manuscrit']);
    if ($selectedCashierName !== '') {
      $activeFilters[] = 'Caissier: ' . htmlspecialchars($selectedCashierName);
    }

    if (!empty($activeFilters)) {
      $header .= '<div style="background:#f8f9fa;padding:8px;margin-bottom:12px;border:1px solid #dee2e6;border-radius:4px;">';
      $header .= '<strong>Filtres appliqués:</strong> ' . implode(' | ', $activeFilters);
      $header .= '</div>';
    }

    // build simple HTML table
    $html = $header;
    $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Type</th><th>Libellé</th><th>Recette</th><th>Dépense</th><th>Solde</th></tr></thead><tbody>';

    $totalRecettes = 0.0;
    $totalDepenses = 0.0;
    $soldesFinauxParCaissier = [];
    foreach ($items as $it) {
      $totalRecettes += (float) ($it['recette'] ?? 0);
      $totalDepenses += (float) ($it['depense'] ?? 0);
      $creatorKey = isset($it['created_by_id'])
        ? (string) $it['created_by_id']
        : (string) ($it['created_by'] ?? 'legacy');
      $soldesFinauxParCaissier[$creatorKey] = (float) ($it['solde'] ?? 0);
      $html .= '<tr>';
      $html .= '<td>' . htmlspecialchars($it['date'] ?? '') . '</td>';
      $html .= '<td>' . htmlspecialchars($it['type'] ?? '') . '</td>';
      $html .= '<td>' . htmlspecialchars($it['libelle'] ?? '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['recette'] ?? 0, 2, '.', '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['depense'] ?? 0, 2, '.', '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['solde'] ?? 0, 2, '.', '') . '</td>';
      $html .= '</tr>';
    }
    $soldeFinal = array_sum($soldesFinauxParCaissier);
    $html .= '<tr style="font-weight:bold;background:#f1f3f5;">';
    $html .= '<td colspan="3">Totaux</td>';
    $html .= '<td style="text-align:right">' . number_format($totalRecettes, 2, '.', '') . '</td>';
    $html .= '<td style="text-align:right">' . number_format($totalDepenses, 2, '.', '') . '</td>';
    $html .= '<td style="text-align:right">' . number_format($soldeFinal, 2, '.', '') . '</td>';
    $html .= '</tr>';
    $html .= '</tbody></table>';



    // PDF export: try mpdf if available
    if ($format === 'pdf') {
      error_log('DEBUG: Caisse export PDF requested. class_exists Mpdf=' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no'));
      if (class_exists('\\Mpdf\\Mpdf')) {
        try {
          $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
          error_log('DEBUG: mpdf starting WriteHTML...');
          $mpdf->WriteHTML($html);
          error_log('DEBUG: mpdf outputting PDF to browser');
          $mpdf->Output('caisse.pdf', \Mpdf\Output\Destination::DOWNLOAD);
          exit;
        } catch (\Throwable $e) {
          error_log('ERROR: mpdf generation failed: ' . $e->getMessage());
          // Fall through to fallback
        }
      } else {
        error_log('DEBUG: mpdf not available, using HTML fallback');
      }
    }

    // Fallback: mpdf not installed. Serve HTML (user can print to PDF in browser).
    // Return a useful HTML file instead of sending invalid PDF bytes.
    $notice = '<div style="background:#fff3cd;padding:10px;border:1px solid #ffeeba;margin-bottom:10px;">' .
      '<strong>Remarque :</strong> Le générateur PDF côté serveur (mpdf) n\'est pas installé. Pour obtenir un vrai PDF, installez <code>mpdf/mpdf</code> via Composer (ex: <code>composer require mpdf/mpdf</code>).' .
      '</div>';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="caisse.html"');
    echo '<html><head><meta charset="utf-8"></head><body>' . $notice . $html . '</body></html>';
    exit;
  }

  public function exportToday()
  {
    $this->requireRole(['caissier', 'accountant', 'comptable', 'manager', 'admin']);
    $today = date('Y-m-d');
    $model = new CaisseModel();
    [, $modelFilters, , $selectedCashierName] = $this->getFilterContext();
    $this->prepareCreatorData($model, $modelFilters);
    $modelFilters['date_debut'] = $today;
    $modelFilters['date_fin'] = $today;
    $items = $model->getAll($modelFilters);
    $summary = $model->getDashboardSummary($today, date('Y-m-01'), $modelFilters);

    $title = 'Opérations de caisse du ' . $today;
    if ($selectedCashierName !== '') {
      $title .= ' — ' . $selectedCashierName;
    }
    $header = \App\Helpers\PdfHelper::renderHeader($title);
    $footer = \App\Helpers\PdfHelper::renderFooter();
    $html = '<div style="font-family: sans-serif;">' . $header;
    $html .= '<table style="width:100%;margin:16px 0;border-collapse:collapse;" cellpadding="8"><tbody>';
    $html .= '<tr><th style="text-align:left;background:#f1f3f5;">Total des recettes du jour</th><td style="text-align:right;">' . number_format($summary['todayIncome'], 2, '.', ' ') . '</td></tr>';
    $html .= '<tr><th style="text-align:left;background:#f1f3f5;">Total des dépenses du jour</th><td style="text-align:right;">' . number_format($summary['todayExpense'], 2, '.', ' ') . '</td></tr>';
    $html .= '<tr><th style="text-align:left;background:#e7f1ff;">Solde actuel de la caisse</th><td style="text-align:right;font-weight:bold;">' . number_format($summary['balance'], 2, '.', ' ') . '</td></tr>';
    $html .= '</tbody></table>';
    $html .= '<table style="width:100%;border-collapse:collapse;" border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr><th>Date</th><th>N° Bon</th><th>Opérateur</th><th>Libellé</th><th>Recettes</th><th>Dépenses</th><th>Solde</th></tr></thead><tbody>';

    if (empty($items)) {
      $html .= '<tr><td colspan="7" style="text-align:center;">Aucune opération enregistrée pour cette journée.</td></tr>';
    } else {
      foreach ($items as $item) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($item['date'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($item['numero_bon_manuscrit'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($item['operateur'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($item['libelle'] ?? '') . '</td>';
        $html .= '<td style="text-align:right;">' . number_format($item['recette'] ?? 0, 2, '.', ' ') . '</td>';
        $html .= '<td style="text-align:right;">' . number_format($item['depense'] ?? 0, 2, '.', ' ') . '</td>';
        $html .= '<td style="text-align:right;">' . number_format($item['solde'] ?? 0, 2, '.', ' ') . '</td>';
        $html .= '</tr>';
      }
    }

    $html .= '<tr style="font-weight:bold;background:#f1f3f5;"><td colspan="4">Totaux du jour</td>';
    $html .= '<td style="text-align:right;">' . number_format($summary['todayIncome'], 2, '.', ' ') . '</td>';
    $html .= '<td style="text-align:right;">' . number_format($summary['todayExpense'], 2, '.', ' ') . '</td>';
    $html .= '<td style="text-align:right;">' . number_format($summary['balance'], 2, '.', ' ') . '</td></tr>';
    $html .= '</tbody></table></div>';

    if (class_exists('\\Mpdf\\Mpdf')) {
      try {
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'P']);
        $mpdf->SetHTMLFooter($footer);
        $mpdf->WriteHTML($html);
        $mpdf->Output('caisse-' . $today . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        exit;
      } catch (\Throwable $e) {
        error_log('CaisseController::exportToday PDF generation failed: ' . $e->getMessage());
      }
    }

    $notice = '<div style="background:#fff3cd;padding:10px;border:1px solid #ffeeba;margin-bottom:10px;">' .
      '<strong>Remarque :</strong> Le générateur PDF côté serveur (<code>mpdf/mpdf</code>) est indisponible. Imprimez cette page en PDF depuis votre navigateur.' .
      '</div>';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="caisse-' . $today . '.html"');
    echo '<html><head><meta charset="utf-8"><style>'
      . 'html,body{min-height:100%;}body{min-height:100vh;display:flex;flex-direction:column;margin:16px;}'
      . '.caisse-export-content{flex:1 0 auto;}.caisse-export-footer{margin-top:auto;flex:0 0 auto;}'
      . '@media print{body{min-height:100vh;}}'
      . '</style></head><body>' . $notice
      . '<main class="caisse-export-content">' . $html . '</main>'
      . '<footer class="caisse-export-footer">' . $footer . '</footer></body></html>';
    exit;
  }

  private function getFilterContext()
  {
    $filters = [
      'date_debut' => $_GET['date_debut'] ?? '',
      'date_fin' => $_GET['date_fin'] ?? '',
      'type' => $_GET['type'] ?? '',
      'operateur' => $_GET['operateur'] ?? '',
      'numero_bon_manuscrit' => $_GET['numero_bon_manuscrit'] ?? '',
      'caissier_id' => ''
    ];
    $modelFilters = $filters;
    $cashiers = [];
    $selectedCashierName = '';
    $role = $_SESSION['user']['role'] ?? 'user';
    $canFilterCashier = in_array($role, ['admin', 'manager', 'accountant', 'comptable'], true);

    if ($role === 'caissier') {
      $modelFilters['creator_id'] = $_SESSION['user']['_id'] ?? '';
      $modelFilters['creator_username'] = $_SESSION['user']['username'] ?? '';
    } elseif ($canFilterCashier) {
      $userModel = new UserModel();
      $cashiers = $userModel->getCashiers();
      $selectedId = trim((string) ($_GET['caissier_id'] ?? ''));
      if ($selectedId !== '') {
        $filters['caissier_id'] = $selectedId;
        $modelFilters['caissier_id'] = $selectedId;
        $cashier = $userModel->findCashierById($selectedId);
        if ($cashier) {
          $modelFilters['creator_id'] = $cashier['id'];
          $modelFilters['creator_username'] = $cashier['username'];
          $selectedCashierName = $cashier['username'];
        } else {
          $modelFilters['creator_id'] = $selectedId;
        }
      }
    }

    return [$filters, $modelFilters, $cashiers, $selectedCashierName];
  }

  private function getOwnerScope()
  {
    if (($_SESSION['user']['role'] ?? '') !== 'caissier') {
      return [null, null];
    }
    return [
      $_SESSION['user']['_id'] ?? '',
      $_SESSION['user']['username'] ?? ''
    ];
  }

  private function prepareCreatorData(CaisseModel $model, array $filters): void
  {
    $role = $_SESSION['user']['role'] ?? 'user';
    if (in_array($role, ['admin', 'manager', 'accountant', 'comptable'], true)) {
      $model->backfillCreatorIds();
    }

    if (!empty($filters['creator_id']) || !empty($filters['creator_username'])) {
      $model->recomputeCreatorSoldes(
        $filters['creator_id'] ?? '',
        $filters['creator_username'] ?? ''
      );
    }
  }
}