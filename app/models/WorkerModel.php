<?php
namespace App\Models;

use App\Core\Model;
use MongoDB\BSON\ObjectId;

class WorkerModel extends Model
{
    private $collection;

    public function __construct()
    {
        parent::__construct();
        $this->collection = $this->db->workers;
        try {
            $this->collection->createIndex(['site_id' => 1, 'name' => 1]);
            $this->collection->createIndex(['site_id' => 1, 'category' => 1]);
            $this->collection->createIndex(['created_at' => -1]);
        } catch (\Throwable $e) {}
    }

    /**
     * Crée un nouvel ouvrier
     */
    public function create($data)
    {
        // Convertir site_id en ObjectId
        try {
            $siteOid = new ObjectId((string) $data['site_id']);
        } catch (\Throwable $e) {
            return false;
        }

        $data['site_id'] = $siteOid;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';
        
        // Validation des données
        if (empty($data['site_id']) || empty($data['name']) || empty($data['category'])) {
            return false;
        }

        $result = $this->collection->insertOne($data);
        return $result->getInsertedId();
    }

    /**
     * Récupère tous les ouvriers d'un chantier
     */
    public function getBySite($siteId)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return [];
        }
        return $this->collection->find(
            ['site_id' => $oid, 'status' => 'active'],
            ['sort' => ['category' => -1, 'name' => 1]]
        )->toArray();
    }

    /**
     * Récupère un ouvrier par ID
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
     * Met à jour un ouvrier
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
     * Supprime un ouvrier
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
     * Archive un ouvrier (soft delete)
     */
    public function archive($id)
    {
        try {
            $oid = new ObjectId((string) $id);
        } catch (\Throwable $e) {
            return false;
        }

        $result = $this->collection->updateOne(
            ['_id' => $oid],
            ['$set' => ['status' => 'archived', 'updated_at' => date('Y-m-d H:i:s')]]
        );
        return ($result->getModifiedCount() > 0);
    }

    /**
     * Récupère les ouvriers T.T d'un chantier
     */
    public function getTTBySite($siteId)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return [];
        }
        return $this->collection->find(
            ['site_id' => $oid, 'category' => 'T.T', 'status' => 'active'],
            ['sort' => ['name' => 1]]
        )->toArray();
    }

    /**
     * Récupère les ouvriers M.C d'un chantier
     */
    public function getMCBySite($siteId)
    {
        try {
            $oid = new ObjectId((string) $siteId);
        } catch (\Throwable $e) {
            return [];
        }
        return $this->collection->find(
            ['site_id' => $oid, 'category' => 'M.C', 'status' => 'active'],
            ['sort' => ['name' => 1]]
        )->toArray();
    }
}