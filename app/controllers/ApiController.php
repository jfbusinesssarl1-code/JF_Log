<?php
namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CompteModel;

class ApiController
{
  /**
   * Récupère la liste des comptes depuis PLAN.xlsx (robuste — toutes feuilles, sans doublons)
   */
  public function getComptes()
  {
    header('Content-Type: application/json');

    try {
      $model = new CompteModel();
      $comptes = $model->getAll();
      // Toujours renvoyer un tableau (évite les erreurs côté client)
      echo json_encode(array_values($comptes));
    } catch (\Throwable $e) {
      error_log('ApiController::getComptes error: ' . $e->getMessage());
      // Ne pas exposer la stack — renvoyer tableau vide pour ne pas bloquer l'UI
      echo json_encode([]);
    }
  }
}
