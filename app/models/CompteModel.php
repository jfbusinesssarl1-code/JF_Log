<?php
namespace App\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * CompteModel
 * - lit et écrit le fichier public/PLAN.xlsx
 * - fournit des méthodes idempotentes pour ajouter un compte s'il n'existe pas
 */
class CompteModel
{
  protected $planPath;

  public function __construct(?string $planPath = null)
  {
    $this->planPath = $planPath ?: realpath(__DIR__ . '/../../public/PLAN.xlsx');
  }

  /**
   * Retourne la liste complète des comptes du fichier Excel.
   * Format: [ ['code'=>'1010', 'intitule'=>'Cash', 'label'=>'1010 - Cash'], ... ]
   */
  public function getAll(): array
  {
    if (!$this->planPath || !is_file($this->planPath)) {
      return [];
    }

    $map = [];
    try {
      $ss = IOFactory::load($this->planPath);
      foreach ($ss->getAllSheets() as $sheet) {
        $highestRow = $sheet->getHighestRow();
        for ($r = 2; $r <= $highestRow; $r++) {
          $raw = $sheet->getCell('A' . $r)->getValue();
          $raw2 = $sheet->getCell('B' . $r)->getValue();
          $code = $this->normalizeCode($raw);
          $intitule = $this->normalizeIntitule($raw2);
          if ($code === '')
            continue;
          $key = mb_strtolower($code);
          if (!isset($map[$key])) {
            $map[$key] = [
              'code' => $code,
              'intitule' => $intitule,
              'label' => $code . ($intitule !== '' ? ' - ' . $intitule : '')
            ];
          }
        }
      }
    } catch (\Throwable $e) {
      error_log('CompteModel::getAll error: ' . $e->getMessage());
      return [];
    }

    // return sorted by code (natural sort)
    $list = array_values($map);
    usort($list, function ($a, $b) {
      return strnatcmp($a['code'], $b['code']); });
    return $list;
  }

  public function exists(string $code): bool
  {
    $code = trim((string) $code);
    if ($code === '')
      return false;
    $needle = mb_strtolower($code);
    foreach ($this->getAll() as $c) {
      if (mb_strtolower($c['code']) === $needle)
        return true;
    }
    return false;
  }

  /**
   * Ajoute un compte au fichier Excel s'il n'existe pas déjà.
   * Renvoie true si le compte a été ajouté ou existait déjà, false en cas d'échec.
   */
  public function addIfMissing(string $code, string $intitule = ''): bool
  {
    $code = trim((string) $code);
    $intitule = trim((string) $intitule);
    if ($code === '')
      return false;

    if ($this->exists($code))
      return true;

    if (!$this->planPath || !is_file($this->planPath) || !is_writable(dirname($this->planPath))) {
      error_log('CompteModel::addIfMissing cannot access PLAN.xlsx: ' . var_export($this->planPath, true));
      return false;
    }

    try {
      // backup
      $bak = $this->planPath . '.bak.' . date('Ymd_His');
      copy($this->planPath, $bak);

      $ss = IOFactory::load($this->planPath);

      // Try to find a sensible worksheet to append to.
      $sheet = null;
      // prefer a sheet named PLAN or Comptes
      foreach ($ss->getAllSheets() as $sh) {
        $name = trim((string) $sh->getTitle());
        if (in_array(mb_strtolower($name), ['plan', 'comptes', 'plan des comptes'])) {
          $sheet = $sh;
          break;
        }
      }
      if (!$sheet)
        $sheet = $ss->getActiveSheet();

      $row = max(2, $sheet->getHighestRow() + 1);

      // find next truly-empty row (in case highestRow is noisy)
      while ($row <= 100000) {
        $cell = $sheet->getCellByColumnAndRow(1, $row)->getValue();
        if ($this->normalizeCode($cell) === '')
          break;
        $row++;
      }

      $sheet->setCellValueExplicit('A' . $row, $code, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
      $sheet->setCellValue('B' . $row, $intitule);

      // save to temporary file then atomically replace
      $tmp = $this->planPath . '.tmp.' . bin2hex(random_bytes(6)) . '.xlsx';
      $writer = new Xlsx($ss);
      $writer->save($tmp);
      rename($tmp, $this->planPath);

      return true;
    } catch (\Throwable $e) {
      error_log('CompteModel::addIfMissing error: ' . $e->getMessage());
      return false;
    }
  }

  protected function normalizeCode($v): string
  {
    if ($v === null)
      return '';
    // Some codes may be numeric in Excel; cast and remove trailing .0
    $s = (string) $v;
    $s = trim($s);
    // Remove non-breaking spaces
    $s = str_replace(["\xc2\xa0", "\u00A0"], ' ', $s);
    // collapse spaces
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
  }

  protected function normalizeIntitule($v): string
  {
    if ($v === null)
      return '';
    $s = (string) $v;
    $s = trim($s);
    $s = str_replace(["\xc2\xa0", "\u00A0"], ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
  }
}
