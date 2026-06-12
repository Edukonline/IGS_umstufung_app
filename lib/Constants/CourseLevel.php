<?php
namespace OCA\KursUmstufung\Constants;

/**
 * Erlaubte Kurs-Niveaus für die Umstufung.
 */
final class CourseLevel {
    public const G_KURS = 'G-Kurs';
    public const E_KURS = 'E-Kurs';

    public const ALL = [self::G_KURS, self::E_KURS];

    private function __construct() {
    }

    public static function isValid(string $level): bool {
        return in_array($level, self::ALL, true);
    }
}
