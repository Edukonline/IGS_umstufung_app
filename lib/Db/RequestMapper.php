<?php
namespace OCA\UmstufungMNS\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RequestMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'umstufung_mns_requests', Request::class);
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
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

        return $this->findEntities($qb);
    }

    /**
     * Findet alle final eingereichten Requests (für Schulleitung)
     */
    public function findAllSubmitted(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('status', $qb->createNamedParameter('submitted')));

        return $this->findEntities($qb);
    }

    /**
     * Setzt alle Entwürfe eines Nutzers auf 'submitted'
     */
    public function submitAllDraftsForUser(string $userId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->tableName)
           ->set('status', $qb->createNamedParameter('submitted'))
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('draft')));
        
        $qb->executeStatement();
    }
}
