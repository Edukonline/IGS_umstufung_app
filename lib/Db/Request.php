<?php
namespace OCA\KursUmstufung\Db;

use OCP\AppFramework\Db\Entity;

class Request extends Entity implements \JsonSerializable {
    protected $userId;
    protected $userName;
    protected $studentName;
    protected $studentClass;
    protected $subject;
    protected $oldLevel;
    protected $newLevel;
    protected $reason;
    protected $status;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'studentName' => $this->studentName,
            'class' => $this->studentClass,
            'subject' => $this->subject,
            'oldLevel' => $this->oldLevel,
            'newLevel' => $this->newLevel,
            'reason' => $this->reason,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
