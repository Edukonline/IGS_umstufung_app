<?php
namespace OCA\KursUmstufung\Service;

use OCP\IConfig;

/**
 * Kapselt den Zugriff auf die App-Konfiguration (appconfig).
 * Liefert Defaults und stellt sicher, dass Fächer/Klassen nicht mehr
 * im Frontend einbetoniert sind.
 */
class ConfigService {
    private const APP_ID = 'kursumstufung';

    private const KEY_ADMIN_GROUP = 'admin_group';
    private const KEY_SUBJECTS = 'subjects';
    private const KEY_CLASSES = 'classes';

    private const DEFAULT_ADMIN_GROUP = 'schulleitung';
    private const DEFAULT_SUBJECTS = ['Mathematik', 'Deutsch', 'Englisch', 'Chemie', 'Physik'];

    private IConfig $config;

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    public function getAdminGroup(): string {
        return $this->config->getAppValue(self::APP_ID, self::KEY_ADMIN_GROUP, self::DEFAULT_ADMIN_GROUP);
    }

    public function setAdminGroup(string $group): void {
        $this->config->setAppValue(self::APP_ID, self::KEY_ADMIN_GROUP, trim($group));
    }

    /** @return string[] */
    public function getSubjects(): array {
        return $this->readList(self::KEY_SUBJECTS, self::DEFAULT_SUBJECTS);
    }

    /** @param string[] $subjects */
    public function setSubjects(array $subjects): void {
        $this->writeList(self::KEY_SUBJECTS, $subjects);
    }

    /** @return string[] */
    public function getClasses(): array {
        return $this->readList(self::KEY_CLASSES, $this->defaultClasses());
    }

    /** @param string[] $classes */
    public function setClasses(array $classes): void {
        $this->writeList(self::KEY_CLASSES, $classes);
    }

    /**
     * @param string[] $default
     * @return string[]
     */
    private function readList(string $key, array $default): array {
        $raw = $this->config->getAppValue(self::APP_ID, $key, '');
        if ($raw === '') {
            return $default;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $default;
        }
        $clean = array_values(array_filter(array_map('strval', $decoded), static fn ($v) => trim($v) !== ''));
        return $clean === [] ? $default : $clean;
    }

    /** @param string[] $values */
    private function writeList(string $key, array $values): void {
        $clean = array_values(array_filter(array_map(static fn ($v) => trim((string)$v), $values), static fn ($v) => $v !== ''));
        $this->config->setAppValue(self::APP_ID, $key, json_encode($clean));
    }

    /**
     * Standard-Klassenliste (Stufen 5–10, Züge a/b/c).
     * @return string[]
     */
    private function defaultClasses(): array {
        $classes = [];
        foreach (range(5, 10) as $grade) {
            foreach (['a', 'b', 'c'] as $zug) {
                $classes[] = $grade . $zug;
            }
        }
        return $classes;
    }
}
