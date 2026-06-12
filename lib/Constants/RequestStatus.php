<?php
namespace OCA\KursUmstufung\Constants;

/**
 * Zentrale Definition der Workflow-Status eines Umstufungsantrags.
 * Ersetzt die zuvor über PHP und Vue verstreuten Magic-Strings.
 */
final class RequestStatus {
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const ALL = [
        self::DRAFT,
        self::SUBMITTED,
        self::APPROVED,
        self::REJECTED,
    ];

    /** Status, über die die Schulleitung entscheiden darf. */
    public const DECIDABLE = [self::SUBMITTED];

    /** Endgültige Status, die nicht mehr geändert werden. */
    public const FINAL = [self::APPROVED, self::REJECTED];

    private function __construct() {
    }

    public static function isValid(string $status): bool {
        return in_array($status, self::ALL, true);
    }
}
