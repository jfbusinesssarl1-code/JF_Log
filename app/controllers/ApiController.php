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

      // diagnostic headers (helpful in dev or when empty)
      $planPath = (property_exists($model, 'planPath') ? $model->planPath : null);
      $exists = ($planPath && is_file($planPath)) ? 1 : 0;
      $readable = ($exists && is_readable($planPath)) ? 1 : 0;
      header('X-Plan-Exists: ' . $exists);
      header('X-Plan-Readable: ' . $readable);
      header('X-Plan-Count: ' . count($comptes));

      // Toujours renvoyer un tableau (évite les erreurs côté client)
      echo json_encode(array_values($comptes));
    } catch (\Throwable $e) {
      error_log('ApiController::getComptes error: ' . $e->getMessage());
      // Provide lightweight diagnostics to help the frontend debug (no stack trace)
      header('X-Plan-Exists: 0');
      header('X-Plan-Readable: 0');
      header('X-Plan-Count: 0');
      echo json_encode([]);
    }
  }
}