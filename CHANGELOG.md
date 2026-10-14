# Changelog

All notable changes to this project will be documented in this file.

## [1.0.5] - 2026-10-14
### Fixed
- **Aktionen-Spalte war unerreichbar**: Die Antragstabelle wurde ohne Scrollmöglichkeit abgeschnitten (`overflow: hidden`). Unterhalb von rund 1600 px Fensterbreite ließen sich „Genehmigen"/„Ablehnen" und „Bearbeiten"/„Löschen" nicht bedienen — auf einem 1366-px-Laptop fehlten 205 px, auf dem Handy 900 px. Die Tabelle scrollt jetzt waagerecht.
- **Lesbarkeit im hellen Design**: Schaltflächen und Status-Kennzeichen nutzten `--color-success`/`--color-error` als Textfarbe bzw. ein festes `#fff`. Das sind in Nextcloud Flächenfarben; der Kontrast lag bei 1,18:1 statt der geforderten 4,5:1. Jetzt mit den zugehörigen `*-text`-Variablen gepaart (7,8–8,6:1).
- **Logflut durch den Notifier**: `Notifier::prepare()` warf `\InvalidArgumentException`, seit Nextcloud 30 veraltet. Je Benachrichtigung entstand ein Logeintrag, auf manchen Serverversionen als Warnung. Jetzt `UnknownNotificationException`, mit Rückfall für NC 25–29.

### Added
- **Rückgängig beim Löschen**: Löschen erfolgt jetzt als Soft-Delete; ein Undo-Banner stellt versehentlich gelöschte Entwürfe wieder her (neue Spalte `deleted_at`, Migration v1.0.5).
- **Schuljahres-Default**: Die Übersicht öffnet standardmäßig im aktuellen Schuljahr, sofern dort Anträge existieren.

### Changed
- **Atomare Einstellungen**: Gruppe, Fächer und Klassen werden in einem validierten Request gespeichert (keine Teilspeicherung mehr).
- **NC-Kompatibilität**: Controller nutzen jetzt PHP-Attribute (`#[NoAdminRequired]`/`#[NoCSRFRequired]`) zusätzlich zu den Annotationen (NC 25–35).
- **Wartbarkeit**: Entscheidungs-Dialog in eine eigene, barrierearme Komponente (`DecisionDialog`) ausgelagert; Frontend-Tests (vitest) und Accessibility-Verbesserungen ergänzt.

## [1.0.4] - 2026-06-12
### Added
- **Genehmigungs-Workflow**: Die Schulleitung kann eingereichte Anträge genehmigen oder mit Begründung ablehnen (Status `approved`/`rejected`).
- **Nextcloud-Benachrichtigungen**: Die einreichende Lehrkraft wird über die Entscheidung informiert; die Schulleitung über neu eingereichte Anträge.
- **Konfigurierbare Fächer und Klassen** über die Admin-Einstellungen (nicht mehr im Frontend einbetoniert).
- **Schuljahres-Filter** und Archivierung; Anträge werden automatisch dem Schuljahr (Aug–Jul) zugeordnet.
- **Tests & CI**: PHPUnit-Tests für Service-/Autorisierungslogik, GitHub-Actions-Pipeline (PHP-Lint, Unit-Tests, Frontend-Build, Secret-Scan).

### Changed
- **Sicherheit**: CSRF-Schutz auf dem Settings-Endpunkt aktiviert, serverseitige Eingabe-Validierung an allen API-Grenzen, generische Fehlermeldungen statt Leak interner Details (strukturiertes Logging).
- **Architektur**: Controller auf Dependency Injection umgestellt (kein `\OC::$server`), Geschäftslogik in dedizierte Services ausgelagert, Frontend-Monolith in Komponenten zerlegt.
- **Robustheit**: Lade-Fehler werden sichtbar gemeldet, CSV-Export gegen Formel-Injection abgesichert, N+1-Abfrage entschärft, UI nutzt Nextcloud-Theme-Variablen (Light/Dark).

### Removed
- Ungenutzte Abhängigkeit `@nextcloud/vue` und Debug-Endpunkte/Logs entfernt.

## [1.0.3] - 2026-04-29
### Added
- Anzeige des Lehrkraft-Namens (Display Name) in der Schulleitungs-Ansicht.
- Klassenauswahl für Schüler:innen.

### Changed
- Rebrand auf "KursUmstufung", Datenbank-Migrationen für Produktionsstabilität korrigiert.

## [1.0.2] - 2026-04-29
### Added
- **Klassenauswahl**: Dropdown-Menü für Klassenstufen 5 bis 10 (Züge A, B, C) hinzugefügt.
- **Suchfunktion**: Live-Suche nach Schülername und Klasse implementiert.
- **CSV-Export**: Funktion zum Exportieren der Antragsliste als .csv Datei für die Schulleitung.
- **Zeitstempel**: Speicherung von Erstellungs- und Änderungsdatum für alle Anträge.
- **Bestätigungsdialoge**: Sicherheitsabfragen vor dem Löschen oder finalen Einreichen.

## [1.0.1] - 2026-04-29
### Added
- Professional UI for managing course level upgrades (G-Kurs/E-Kurs).
- Draft system for teachers to save and edit requests before submission.
- Admin view for school leadership to review submitted requests.
- Course level synchronization (auto-toggle G/E levels).

### Changed
- Neutralized application branding to "KursUmstufung".
- Updated database schema for production stability.

## [1.0.0] - 2026-04-20
### Added
- Initial development and core functionality.
