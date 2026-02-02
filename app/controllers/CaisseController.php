<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\CaisseModel;
use Mpdf\Mpdf;
class CaisseController extends Controller
{
  public function index()
  {
    $this->requireRole(['caissier', 'manager', 'admin']);
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

  public function add()
  {
    $this->requireRole(['caissier']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $token = $_POST['csrf_token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token))
        die('Erreur CSRF');
      $date = $_POST['date'] ?? '';
      $type = $_POST['type'] ?? '';
      $libelle = trim($_POST['libelle'] ?? '');
      $montant = floatval($_POST['montant'] ?? 0);
      $numero_bon_manuscrit = trim($_POST['numero_bon_manuscrit'] ?? '');
      $operateur = trim($_POST['operateur'] ?? '');
      $user = $_SESSION['user'] ?? null;
      $data = [
        'date' => $date,
        'type' => $type,
        'libelle' => $libelle,
        'montant' => $montant,
        'numero_bon_manuscrit' => $numero_bon_manuscrit,
        'operateur' => $operateur,
        'created_by' => $user['username'] ?? 'unknown'
      ];
      $model = new CaisseModel();
      $model->insert($data);
    }
    header('Location: ?page=caisse');
    exit;
  }

  public function edit()
  {
    $this->requireRole(['caissier']);
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
    $this->requireRole(['caissier']);
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
    $this->requireRole(['caissier', 'manager', 'admin']);
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