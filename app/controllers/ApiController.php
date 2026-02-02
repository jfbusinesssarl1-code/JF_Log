<?php
namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ApiController
{
  /**
   * Récupère la liste des comptes depuis PLAN.xlsx
   */
  public function getComptes()
  {
    header('Content-Type: application/json');

    try {
      $planFile = __DIR__ . '/../../public/PLAN.xlsx';

      if (!file_exists($planFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'Fichier PLAN.xlsx non trouvé']);
        exit;
      }

      $spreadsheet = IOFactory::load($planFile);
      $worksheet = $spreadsheet->getActiveSheet();

      $comptes = [];
      $highestRow = $worksheet->getHighestRow();

      // Parcourir toutes les lignes
      for ($row = 2; $row <= $highestRow; $row++) {
        $code = trim($worksheet->getCell('A' . $row)->getValue() ?? '');
        $intitule = trim($worksheet->getCell('B' . $row)->getValue() ?? '');

        if (!empty($code)) {
          $comptes[] = [
            'code' => $code,
            'intitule' => $intitule,
            'label' => $code . ' - ' . $intitule
          ];
        }
      }

      echo json_encode($comptes);

    } catch (\Exception $e) {
      http_response_code(500);
      echo json_encode(['error' => $e->getMessage()]);
    }
  }
}
