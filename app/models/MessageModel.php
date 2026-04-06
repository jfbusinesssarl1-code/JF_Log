<?php
namespace App\Models;

use App\Core\Database;

class MessageModel
{
  protected $collection;

  public function __construct()
  {
    $db = (new Database())->getDb();
    $this->collection = $db->messages;
  }

  public function insert(array $item)
  {
    $item['created_at'] = new \MongoDB\BSON\UTCDateTime();
    $item['is_read'] = false; // Par défaut, le message n'est pas lu
    $this->collection->insertOne($item);
  }

  public function getAll()
  {
    $cursor = $this->collection->find([], ['sort' => ['created_at' => -1]]);
    return iterator_to_array($cursor);
  }

  /**
   * Get paginated messages with optional search query
   * returns ['items'=>[], 'total'=>int]
   */
  public function getPaginated($page = 1, $perPage = 20, $q = null)
  {
    $page = max(1, (int) $page);
    $perPage = max(1, (int) $perPage);

    $filter = [];
    if (!empty($q)) {
      $regex = new \MongoDB\BSON\Regex(preg_quote($q), 'i');
      $filter = [
        '$or' => [
          ['name' => ['$regex' => $regex]],
          ['email' => ['$regex' => $regex]],
          ['subject' => ['$regex' => $regex]],
          ['message' => ['$regex' => $regex]]
        ]
      ];
    }

    $total = $this->collection->countDocuments($filter);
    $options = [
      'sort' => ['created_at' => -1],
      'skip' => ($page - 1) * $perPage,
      'limit' => $perPage
    ];
    $cursor = $this->collection->find($filter, $options);
    return ['items' => iterator_to_array($cursor), 'total' => $total];
  }

  public function delete($id)
  {
    try {
      $oid = new \MongoDB\BSON\ObjectId($id);
    } catch (\Exception $e) {
      return false;
    }
    $this->collection->deleteOne(['_id' => $oid]);
    return true;
  }

  /**
   * Mark a message as read
   */
  public function markAsRead($id)
  {
    try {
      $oid = new \MongoDB\BSON\ObjectId($id);
    } catch (\Exception $e) {
      return false;
    }
    $this->collection->updateOne(
      ['_id' => $oid],
      ['$set' => ['is_read' => true]]
    );
    return true;
  }

  /**
   * Get count of unread messages
   */
  public function getUnreadCount()
  {
    return $this->collection->countDocuments(['is_read' => false]);
  }
}
