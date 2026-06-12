<?php
namespace OCA\KursUmstufung\Db;

use OCA\KursUmstufung\Constants\RequestStatus;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Request>
 */
class RequestMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'kurs_umstufung_requests', Request::class);
    }

    public function findById(int $id): Request {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Anträge einer Lehrkraft (alle Status), optional nach Schuljahr gefiltert.
     */
    public function findAllByUser(string $userId, ?string $schoolYear = null, int $limit = 200, int $offset = 0): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));

        if ($schoolYear !== null && $schoolYear !== '') {
            $qb->andWhere($qb->expr()->eq('school_year', $qb->createNamedParameter($schoolYear)));
        }

        $qb->orderBy('created_at', 'DESC')
           ->setMaxResults($limit)
           ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Alle eingereichten/entschiedenen Anträge (für die Schulleitung).
     * Entwürfe anderer Lehrkräfte bleiben unsichtbar.
     */
    public function findAllSubmitted(?string $schoolYear = null, int $limit = 500, int $offset = 0): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->neq('status', $qb->createNamedParameter(RequestStatus::DRAFT)));

        if ($schoolYear !== null && $schoolYear !== '') {
            $qb->andWhere($qb->expr()->eq('school_year', $qb->createNamedParameter($schoolYear)));
        }

        $qb->orderBy('created_at', 'DESC')
           ->setMaxResults($limit)
           ->setFirstResult($offset);

        return $this->findEntities($qb);
    }

    /**
     * Setzt alle Entwürfe eines Nutzers auf 'submitted'.
     * @return int Anzahl der betroffenen Zeilen.
     */
    public function submitAllDraftsForUser(string $userId): int {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->tableName)
           ->set('status', $qb->createNamedParameter(RequestStatus::SUBMITTED))
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->andWhere($qb->expr()->eq('status', $qb->createNamedParameter(RequestStatus::DRAFT)));

        return $qb->executeStatement();
    }
}
