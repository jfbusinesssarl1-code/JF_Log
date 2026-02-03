<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\CaisseModel;
use Mpdf\Mpdf;
class CaisseController extends Controller
{
  public function index()
  {
    $this->requireRole(['caissier', 'accountant', 'manager', 'admin']);
    $model = new CaisseModel();
    $filters = [
      'date_debut' => $_GET['date_debut'] ?? '',
      'date_fin' => $_GET['date_fin'] ?? '',
      'type' => $_GET['type'] ?? '',
      'operateur' => $_GET['operateur'] ?? '',
      'numero_bon_manuscrit' => $_GET['numero_bon_manuscrit'] ?? ''
    ];
    $items = $model->getAll($filters);
    $this->render('caisse', ['items' => $items, 'filters' => $filters]);
  }

  // Retourne uniquement le <tbody> (utilisé pour refresh partiel via AJAX)
  public function list_partial()
  {
    $this->requireRole(['caissier', 'accountant', 'manager', 'admin']);
    $model = new CaisseModel();
    $filters = [
      'date_debut' => $_GET['date_debut'] ?? '',
      'date_fin' => $_GET['date_fin'] ?? '',
      'type' => $_GET['type'] ?? '',
      'operateur' => $_GET['operateur'] ?? '',
      'numero_bon_manuscrit' => $_GET['numero_bon_manuscrit'] ?? ''
    ];
    $items = $model->getAll($filters);
    header('Content-Type: text/html; charset=utf-8');
    // Provide the number of rows to help clients validate partial updates
    header('X-Row-Count: ' . count($items));
    if (!empty($items)) {
      foreach ($items as $it) {
        $id = isset($it['_id']) ? (string) $it['_id'] : '';
        $date = htmlspecialchars($it['date'] ?? '');
        $type = htmlspecialchars($it['type'] ?? '');
        $num = htmlspecialchars($it['numero_bon_manuscrit'] ?? '');
        $oper = htmlspecialchars($it['operateur'] ?? '');
        $lib = htmlspecialchars($it['libelle'] ?? '');
        $recette = number_format($it['recette'] ?? 0, 2);
        $depense = number_format($it['depense'] ?? 0, 2);
        $solde = number_format($it['solde'] ?? 0, 2);
        echo "<tr>\n";
        echo "<td>$date</td>\n";
        echo "<td>$type</td>\n";
        echo "<td>$num</td>\n";
        echo "<td>$oper</td>\n";
        echo "<td>$lib</td>\n";
        echo "<td class=\"text-end\">$recette</td>\n";
        echo "<td class=\"text-end\">$depense</td>\n";
        echo "<td class=\"text-end\">$solde</td>\n";
        if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['caissier', 'admin'])) {
          echo "<td><a class=\"btn btn-sm btn-primary\" href=\"?page=caisse&action=edit&id=$id\">Modifier</a> <a class=\"btn btn-sm btn-danger\" href=\"?page=caisse&action=delete&id=$id\" onclick=\"return confirm('Supprimer ?')\">Supprimer</a></td>\n";
        } else {
          echo "<td>—</td>\n";
        }
        echo "</tr>\n";
      }
    } else {
      echo '<tr><td colspan="9" class="text-center">Aucune opération</td></tr>';
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
      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors[] = 'Date invalide';
      if (!in_array($type, ['entree', 'sortie'])) $errors[] = 'Type invalide';
      if ($libelle === '' || strlen($libelle) > 255) $errors[] = 'Libellé invalide';
      if ($montant === '' || !is_numeric($montant) || floatval($montant) <= 0) $errors[] = 'Montant invalide';
      if ($operateur === '' || strlen($operateur) > 128) $errors[] = 'Opérateur invalide';

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
        $all = $model->getAll();
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
      $model->update($id, $data);
      header('Location: ?page=caisse');
      exit;
    }
    $entry = $id ? $model->getById($id) : null;
    $this->render('caisse_edit', ['entry' => $entry]);
  }

  public function delete()
  {
    $this->requireRole(['caissier', 'admin']);
    $id = $_GET['id'] ?? null;
    if ($id) {
      $model = new CaisseModel();
      $model->delete($id);
    }
    header('Location: ?page=caisse');
    exit;
  }

  public function export()
  {
    $this->requireRole(['caissier', 'accountant', 'manager', 'admin']);
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
    $items = $model->getAll();

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
    $header .= '<div style="flex:1 1 auto;text-align:right;font-size:12px;color:#333;">' . date('d/m/Y H:i') . '</div>';
    $header .= '</div>';
    $header .= '<div style="height:4px;background:#0d6efd;margin:8px 0 12px 0"></div>';
    $header .= '<h2 style="text-align:center;font-weight:700;margin:6px 0 12px 0">Livre de caisse</h2>';

    // build simple HTML table
    $html = $header;
    $html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Type</th><th>Libellé</th><th>Recette</th><th>Dépense</th><th>Solde</th></tr></thead><tbody>';

    foreach ($items as $it) {
      $html .= '<tr>';
      $html .= '<td>' . htmlspecialchars($it['date'] ?? '') . '</td>';
      $html .= '<td>' . htmlspecialchars($it['type'] ?? '') . '</td>';
      $html .= '<td>' . htmlspecialchars($it['libelle'] ?? '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['recette'] ?? 0, 2, '.', '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['depense'] ?? 0, 2, '.', '') . '</td>';
      $html .= '<td style="text-align:right">' . number_format($it['solde'] ?? 0, 2, '.', '') . '</td>';
      $html .= '</tr>';
    }
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
}