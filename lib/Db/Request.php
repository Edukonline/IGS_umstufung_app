<?php
namespace OCA\UmstufungMNS\Db;

use OCP\AppFramework\Db\Entity;

class Request extends Entity implements \JsonSerializable {
    protected $userId;
    protected $studentName;
    protected $subject;
    protected $oldLevel;
    protected $newLevel;
    protected $reason;
    protected $status;
    protected $createdAt;

    public function __construct() {
        $this->addType('userId', 'string');
        $this->addType('studentName', 'string');
        $this->addType('subject', 'string');
        $this->addType('oldLevel', 'string');
        $this->addType('newLevel', 'string');
        $this->addType('reason', 'string');
        $this->addType('status', 'string');
        $this->addType('createdAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'studentName' => $this->studentName,
            'subject' => $this->subject,
            'oldLevel' => $this->oldLevel,
            'newLevel' => $this->newLevel,
            'reason' => $this->reason,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
        ];
    }
}
