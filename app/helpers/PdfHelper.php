<?php
namespace App\Helpers;

class PdfHelper
{
  public static function formatPrintedAt(): string
  {
    $dayMap = [
      'Monday' => 'Lundi',
      'Tuesday' => 'Mardi',
      'Wednesday' => 'Mercredi',
      'Thursday' => 'Jeudi',
      'Friday' => 'Vendredi',
      'Saturday' => 'Samedi',
      'Sunday' => 'Dimanche'
    ];

    $englishDay = date('l');
    $frenchDay = $dayMap[$englishDay] ?? $englishDay;

    return $frenchDay . ', le ' . date('d/m/Y H:i');
  }

  public static function renderHeader(string $title): string
  {
    // logo
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
    $header .= '<div style="flex:1 1 auto;text-align:right;font-size:12px;color:#333;">' . self::formatPrintedAt() . '</div>';
    $header .= '</div>';
    $header .= '<div style="height:4px;background:#0d6efd;margin:8px 0 12px 0"></div>';
    $header .= '<h2 style="text-align:center;font-weight:700;margin:6px 0 12px 0">' . htmlspecialchars($title) . '</h2>';

    return $header;
  }

  public static function renderFooter(): string
  {
    return '<div style="margin-top:16px;font-size:11px;text-align:center;color:#555;">© 2024 JF BUSINESS SARL — Système comptable intégré. Tous droits réservés. Développé avec ❤️ pour une comptabilité précise et fiable.</div>';
  }
}
