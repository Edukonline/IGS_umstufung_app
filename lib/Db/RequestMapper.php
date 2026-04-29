<?php
namespace OCA\KursUmstufung\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RequestMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'kurs_umstufung_requests', Request::class);
    }

    /**
     * Findet einen spezifischen Request per ID
     */
    public function findById(int $id): Request {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Findet alle Requests für eine spezifische Lehrkraft (user_id)
     */
    public function findAllByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->orderBy('created_at', 'DESC');
        
        return $this->findEntities($qb);
    }

    /**
     * Findet alle eingereichten Requests (für Schulleitung)
     */
    public function findAllSubmitted(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('status', $qb->createNamedParameter('submitted')))
           ->orderBy('created_at', 'DESC');
        
        return $this->findEntities($qb);
    }

    /**
     * Setzt alle Entwürfe eines Nutzers auf 'submitted'
     */
    public function submitAllDraftsForUser(string $userId) {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->tableName)
           ->set('status', $qb->createNamedParameter('submitted'))
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('draft')));
        
        $qb->executeStatement();
    }
}
