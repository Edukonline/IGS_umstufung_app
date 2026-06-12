---
title: Technical Reference & Architecture
version: 1.0.4
last_updated: 2026-06-12
author: Edukonline
---

# Technical Reference

## Diagramme
- System-Architektur: `docs/diagrams/src/architecture.mmd` → `docs/diagrams/img/architecture.png`
- Authentifizierungs-Flow: `docs/diagrams/src/auth-flow.mmd` → `docs/diagrams/img/auth-flow.png`
- Datenbank-ERD: `docs/diagrams/src/schema.mmd` → `docs/diagrams/img/schema.png`

Rendern mit `sh docs/render_diagrams.sh` (benötigt `@mermaid-js/mermaid-cli`).

## Architecture Overview
Die `KursUmstufung` App folgt einer klaren Schichtenarchitektur:
1.  **Controller (`lib/Controller/`)** — dünn. Nehmen HTTP-Parameter entgegen, delegieren an Services, mappen Exceptions auf HTTP-Codes. Keine Geschäftslogik.
2.  **Services (`lib/Service/`)** — die Geschäftslogik:
    *   `RequestService` — Validierung, CRUD, Workflow (`submit`, `decide`), Schuljahres-Zuordnung.
    *   `AuthorizationService` — Rollenlogik (Lehrkraft vs. Schulleitung) per Dependency Injection (`IUserSession`, `IGroupManager`).
    *   `ConfigService` — App-Konfiguration (Schulleitungs-Gruppe, Fächer, Klassen) mit Defaults.
    *   `NotificationService` — erzeugt Nextcloud-Benachrichtigungen bei Workflow-Ereignissen.
3.  **Persistence (`lib/Db/`)** — `RequestMapper` (QBMapper) abstrahiert die DB; funktioniert mit MySQL, PostgreSQL und SQLite. Schema via Migrationen (`lib/Migration/`).
4.  **Frontend (`src/`)** — Vue-3-SPA, zerlegt in `App.vue` (Orchestrator), Komponenten (`RequestForm`, `RequestTable`, `NotificationBanner`), einen API-Service (`services/api.js`) und Utilities. Rolle, Fächer und Klassen kommen über `@nextcloud/initial-state` serverseitig ins Frontend.

## Authentication & Authorization
*   Alle API-Routen sind eingeloggten Nutzern vorbehalten (`@NoAdminRequired`, kein `@PublicPage`).
*   **Rollenmodell:** `AuthorizationService::isSchulleitung()` prüft, ob der Nutzer Nextcloud-Admin ist oder Mitglied der konfigurierten Gruppe (Default `schulleitung`). Die Gruppe ist über die Admin-Einstellungen konfigurierbar.
*   **Entscheidungen** (`/decide`) sind serverseitig auf Schulleitungs-Nutzer beschränkt — die UI-Sichtbarkeit ist nicht die Absicherung.

## API Endpoints (`lib/Controller/RequestController.php`)
*   `GET /api/requests[?schoolYear=YYYY/YYYY]` — Lehrkraft: eigene Anträge; Schulleitung: alle eingereichten/entschiedenen.
*   `POST /api/requests` — neuer Antrag (`draft`), validiert.
*   `PUT /api/requests/{id}` — Antrag aktualisieren (nur eigener Entwurf).
*   `DELETE /api/requests/{id}` — Antrag löschen (nur eigener Entwurf).
*   `POST /api/submit_all` — alle eigenen Entwürfe auf `submitted` setzen, Schulleitung benachrichtigen.
*   `POST /api/requests/{id}/decide` — `decision=approved|rejected` (+ optional `decisionReason`); nur Schulleitung; benachrichtigt die Lehrkraft.
*   `POST /api/settings/{adminGroup|subjects|classes}` — Admin-Konfiguration (`@AdminRequired`, CSRF aktiv).

## Validation & Error Handling
*   **Validierung** erfolgt serverseitig im `RequestService`: Pflichtfelder, Längen gegen die Schema-Limits, Whitelist für Niveau (`CourseLevel`), Fach und Klasse (gegen die konfigurierten Listen); `oldLevel != newLevel`. Verstöße werfen `ValidationException` → HTTP 400.
*   **Autorisierungsfehler** werfen `ForbiddenException` → HTTP 403; nicht gefundene Anträge → HTTP 404.
*   **Unerwartete Fehler** werden serverseitig über `Psr\Log\LoggerInterface` mit Kontext geloggt; der Client erhält eine generische Meldung (kein Leak interner Details).

## Security Practices
*   **CSRF Protection:** aktiv auf allen state-changing Endpunkten — inklusive der Admin-Einstellungen (kein `@NoCSRFRequired`). Das Frontend sendet den `requesttoken` mit.
*   **SQL Injection Prevention:** ausschließlich QueryBuilder mit `createNamedParameter` (PDO Prepared Statements).
*   **XSS:** Vue-Auto-Escaping im Frontend, `p()` in PHP-Templates; kein `v-html`.
*   **CSV-Injection:** Export neutralisiert Formel-Präfixe (`= + - @`) und quotet korrekt.

## Notifications (`lib/Notification/Notifier.php`)
Der `Notifier` ist über `registerNotifierService` registriert und rendert zwei Ereignistypen: „Anträge eingereicht" (an die Schulleitungs-Gruppe) und „Antrag genehmigt/abgelehnt" (an die Lehrkraft). Fehler beim Versand brechen den fachlichen Vorgang nie ab, werden aber geloggt.

## Tests (`tests/`)
PHPUnit-Unit-Tests decken die wertvollste Logik ab: Validierungsregeln, Eigentums-/Status-Checks, Entscheidungs-Workflow und Rollenauflösung. Ausführung: `composer install && composer test:unit`. Die GitHub-Actions-Pipeline (`.github/workflows/ci.yml`) führt PHP-Lint, Unit-Tests, Frontend-Build und einen Secret-Scan aus.
