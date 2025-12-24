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
    $format = $_GET['format'] ?? 'word';
    $model = new CaisseModel();
    $items = $model->getAll();
    // build simple HTML table
    $html = '<h2>Livre de caisse</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Date</th><th>Type</th><th>Libellé</th><th>Recette</th><th>Dépense</th><th>Solde</th></tr></thead><tbody>';
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

    if ($format === 'word') {
      header('Content-Type: application/msword');
      header('Content-Disposition: attachment; filename="caisse.doc"');
      echo '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
      exit;
    }

    // PDF export: try mpdf if available
    if ($format === 'pdf' && class_exists('\\Mpdf\\Mpdf')) {
      $mpdf = new \Mpdf\Mpdf();
      $mpdf->WriteHTML($html);
      $mpdf->Output('caisse.pdf', 'D');
      exit;
    }

    // Fallback: serve HTML with .pdf filename (browser can print to PDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="caisse.pdf"');
    echo '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
    exit;
  }
}