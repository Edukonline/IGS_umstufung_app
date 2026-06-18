<?php
namespace OCA\KursUmstufung\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getStudentName()
 * @method void setStudentName(string $studentName)
 * @method string|null getStudentClass()
 * @method void setStudentClass(?string $studentClass)
 * @method string getSubject()
 * @method void setSubject(string $subject)
 * @method string getOldLevel()
 * @method void setOldLevel(string $oldLevel)
 * @method string getNewLevel()
 * @method void setNewLevel(string $newLevel)
 * @method string|null getReason()
 * @method void setReason(?string $reason)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string|null getSchoolYear()
 * @method void setSchoolYear(?string $schoolYear)
 * @method string|null getDecidedBy()
 * @method void setDecidedBy(?string $decidedBy)
 * @method string|null getDecisionReason()
 * @method void setDecisionReason(?string $decisionReason)
 * @method \DateTime|null getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime|null getUpdatedAt()
 * @method void setUpdatedAt(\DateTime $updatedAt)
 * @method \DateTime|null getDeletedAt()
 * @method void setDeletedAt(?\DateTime $deletedAt)
 */
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
    protected $schoolYear;
    protected $decidedBy;
    protected $decidedByName;
    protected $decisionReason;
    protected $createdAt;
    protected $updatedAt;
    protected $deletedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
        $this->addType('deletedAt', 'datetime');
    }

    /**
     * Serialisiert DateTime-Felder als ISO-8601-Strings, damit das Frontend
     * keinen rohen PHP-DateTime-Container (date/timezone_type) auspacken muss.
     */
    private function formatDate($value): ?string {
        return $value instanceof \DateTimeInterface
            ? $value->format(\DateTimeInterface::ATOM)
            : null;
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
            'schoolYear' => $this->schoolYear,
            'decidedBy' => $this->decidedBy,
            'decidedByName' => $this->decidedByName,
            'decisionReason' => $this->decisionReason,
            'createdAt' => $this->formatDate($this->createdAt),
            'updatedAt' => $this->formatDate($this->updatedAt),
        ];
    }
}
