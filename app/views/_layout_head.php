<?php
// Usage: set $title before including this file when needed
$title = $title ?? 'CB.JF - Comptabilité';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity=""
  crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<?php
$cssPath = realpath(__DIR__ . '/../assets/css/custom.css');
$cssVersion = $cssPath && file_exists($cssPath) ? filemtime($cssPath) : time();
?>
<link rel="stylesheet" href="/asset.php?f=css/custom.css&amp;v=<?= $cssVersion ?>">
<style>
body {
  font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  background: #f6f9fc;
  color: #222;
  padding-top: 40px;
}
</style>