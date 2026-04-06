<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

class SiteModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->sites;
        try {
            $this->collection->createIndex(['name' => 1]);
            $this->collection->createIndex(['created_at' => -1]);
        } catch (\Throwable $e) {}
    }

    /**
     * Crée un nouveau chantier
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';
        
        // Champs par défaut
        $data['engineer_name'] = $data['engineer_name'] ?? '';
        $data['engineer_phone'] = $data['engineer_phone'] ?? '';
        $data['warehouse_manager_name'] = $data['warehouse_manager_name'] ?? '';
        $data['location'] = $data['location'] ?? '';
        $data['description'] = $data['description'] ?? '';

        $result = $this->collection->insertOne($data);
        return $result->getInsertedId();
    }

    /**
     * Récupère tous les chantiers
     */
    public function getAll()
    {
        return $this->collection->find([], ['sort' => ['created_at' => -1]])->toArray();
    }

    /**
     * Récupère les chantiers actifs
     */
    public function getActive()
    {
        return $this->collection->find(['status' => 'active'], ['sort' => ['name' => 1]])->toArray();
    }

    /**
     * Récupère un chantier par ID
     */
    public function getById($id)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return null;
        }
        return $this->collection->findOne(['_id' => $oid]);
    }

    /**
     * Met à jour un chantier
     */
    public function update($id, $data)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->collection->updateOne(['_id' => $oid], ['$set' => $data]);
        return ($result->getModifiedCount() > 0);
    }

    /**
     * Supprime un chantier
     */
    public function delete($id)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return false;
        }
        $result = $this->collection->deleteOne(['_id' => $oid]);
        return ($result->getDeletedCount() > 0);
    }

    /**
     * Récupère les statistiques d'un chantier
     */
    public function getStats($siteId)
    {
        $workerModel = new WorkerModel();
        $workers = $workerModel->getBySite($siteId);
        
        return [
            'total_workers' => count($workers),
            'workers_tc' => count(array_filter($workers, fn($w) => $w['category'] === 'T.T')),
            'workers_mc' => count(array_filter($workers, fn($w) => $w['category'] === 'M.C'))
        ];
    }
}