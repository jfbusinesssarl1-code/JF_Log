<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Ensure admin exists (init.php creates admin if missing)
@exec('php ' . escapeshellarg(__DIR__ . '/../init.php'), $out, $rc);

use App\Helpers\PdfHelper;

echo "=== Test exports (local) ===\n";

// Build sample HTML using helper
$html = PdfHelper::renderHeader('Test Export PDF');
$html .= '<p>Exemple de contenu pour vérifier la génération PDF côté serveur.</p>';
$html .= '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="5" cellspacing="0">';
$html .= '<tr><th>Col 1</th><th>Col 2</th></tr>';
for ($i = 0; $i < 5; $i++) {
  $html .= '<tr><td>' . ($i + 1) . '</td><td>Valeur ' . ($i + 1) . '</td></tr>';
}
$html .= '</table>';

// Try to instantiate mpdf and generate PDF
if (!class_exists('\Mpdf\Mpdf')) {
  echo "ERROR: mpdf not available (class \Mpdf\Mpdf not found).\n";
  exit(2);
}

try {
  $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
  $pdfString = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
  if (substr($pdfString, 0, 4) === '%PDF') {
    $outPath = __DIR__ . '/tmp_test_export.pdf';
    file_put_contents($outPath, $pdfString);
    echo "OK: PDF généré, écrit dans: $outPath\n";
    echo "Peek bytes: " . bin2hex(substr($pdfString, 0, 8)) . "\n";
  } else {
    echo "ERROR: Les octets initiaux ne sont pas '%PDF'. Première valeur: " . bin2hex(substr($pdfString, 0, 8)) . "\n";
    exit(3);
  }
} catch (\Throwable $e) {
  echo "ERROR: Exception lors de la génération mpdf: " . $e->getMessage() . "\n";
  exit(4);
}

// Optional: HTTP tests if base URL provided as first arg
$baseUrl = $argv[1] ?? '';
if ($baseUrl) {
  echo "\n=== Test exports via HTTP against: $baseUrl ===\n";
  $cookieFile = sys_get_temp_dir() . '/cbjf_test_cookies.txt';

  // 1) GET login page to obtain CSRF
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, rtrim($baseUrl, '/') . '/?page=login');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  $loginHtml = curl_exec($ch);
  curl_close($ch);

  if (!$loginHtml) {
    echo "ERROR: Impossible de récupérer la page de login.\n";
    exit(5);
  }

  if (!preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $m)) {
    echo "ERROR: Token CSRF introuvable dans la page de login.\n";
    exit(6);
  }
  $csrf = $m[1];
  echo "CSRF token trouvé.\n";

  // 2) POST login
  $post = [
    'csrf_token' => $csrf,
    'username' => 'admin',
    'password' => 'admin123'
  ];
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, rtrim($baseUrl, '/') . '/?page=login');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  $home = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code >= 400) {
    echo "ERROR: Login HTTP code $code.\n";
    exit(7);
  }
  echo "Login HTTP OK (code $code).\n";

  $endpoints = [
    '?page=caisse&action=export&format=pdf',
    '?page=journal&action=export&format=pdf',
    '?page=grandlivre&action=export&format=pdf',
    '?page=balance&action=export&format=pdf',
    '?page=bilan&action=export&format=pdf&type=current',
    '?page=bilan&action=export&format=pdf&type=initial',
    '?page=stock&action=export&format=pdf',
    '?page=releve&action=export&format=pdf',
    '?page=dashboard&action=export&format=pdf',
  ];

  foreach ($endpoints as $e) {
    $url = rtrim($baseUrl, '/') . '/' . ltrim($e, '/');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $ct = $info['content_type'] ?? 'unknown';
    $first = substr($resp, 0, 4);
    $firstHex = bin2hex(substr($resp, 0, 8));
    echo "Endpoint: $e -> Content-Type: $ct; First bytes: $first ($firstHex)\n";
    if ($first === '%PDF') {
      echo "  -> PDF OK\n";
    } else {
      echo "  -> NOT PDF (likely HTML login/page)\n";
    }
  }
}

echo "\nDone.\n";
