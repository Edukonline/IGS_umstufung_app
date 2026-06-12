# Changelog

All notable changes to this project will be documented in this file.

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
