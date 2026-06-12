---
title: KursUmstufung App (Nextcloud)
version: 1.0.4
last_updated: 2026-06-12
author: Edukonline
---

# KursUmstufung (Nextcloud App)

![License](https://img.shields.io/badge/License-AGPLv3-blue.svg)
![Status](https://img.shields.io/badge/Status-Active-success.svg)

## Project Overview
Eine Nextcloud-Erweiterung zur effizienten Verwaltung von Umstufungsanträgen zwischen verschiedenen Kurs-Niveaus (z.B. G-Kurs und E-Kurs).
Lehrkräfte legen Anträge als Entwürfe an, speichern und reichen sie gesammelt ein. Die Schulleitung sieht alle eingereichten Anträge zentral, kann sie **genehmigen oder ablehnen** (mit Begründung), und die einreichende Lehrkraft wird per Nextcloud-Benachrichtigung über die Entscheidung informiert.

## Tech Stack
*   **Backend:** Nextcloud App Framework (PHP 8), Schichtenarchitektur (Controller → Service → QBMapper)
*   **Frontend:** Vue 3 (eigene Komponenten, `@nextcloud/axios`/`-router`/`-initial-state`)
*   **Database:** Nextcloud DBAL (MySQL, PostgreSQL, SQLite)
*   **Build-System:** Node.js, Webpack, npm
*   **Tests/CI:** PHPUnit (Service-/Autorisierungslogik), GitHub Actions (PHP-Lint, Unit-Tests, Frontend-Build, Secret-Scan)

## Workflow
`draft` (Entwurf, nur Ersteller) → `submitted` (eingereicht, Schulleitung sichtbar) → `approved` / `rejected` (Entscheidung der Schulleitung). Fächer und Klassen sind in den Admin-Einstellungen konfigurierbar; ein Schuljahres-Filter erlaubt das Archivieren über Jahre.

## Tests
```bash
composer install
composer test:unit
```

## Getting Started

### Voraussetzungen
*   Laufende Nextcloud-Instanz (Version 25 bis 35)
*   Node.js (v18+) und npm

### Entwicklung & Build
Um die App für die Entwicklung oder Produktion zu bauen:

```bash
# 1. Abhängigkeiten installieren
npm install

# 2. Vue.js Frontend kompilieren
npm run build
```

### Installation in Nextcloud
1. Kopiere diesen Ordner in dein Nextcloud `apps/` oder `custom_apps/` Verzeichnis unter dem Namen `kursumstufung`.
2. Aktiviere die App über die Kommandozeile:
```bash
sudo -u www-data php occ app:enable kursumstufung
```

## Project Structure
```text
kursumstufung/
├── appinfo/            # App Metadaten (info.xml, routes.php)
├── css/                # Stylesheets
├── docs/               # Single Source of Truth Dokumentation
├── img/                # App-Icons und Screenshots
├── js/                 # Webpack-generierte Frontend-Scripte
├── lib/                # PHP Backend-Code
│   ├── Constants/      # Status- und Niveau-Konstanten
│   ├── Controller/     # API Endpunkte (dünn, delegieren an Service)
│   ├── Db/             # Entity + QBMapper
│   ├── Exception/      # Fachliche Exceptions (Validation/Forbidden)
│   ├── Migration/      # Datenbank-Schemas
│   ├── Notification/   # Nextcloud-Benachrichtigungen (Notifier)
│   ├── Service/        # Geschäftslogik, Validierung, Autorisierung
│   └── Settings/       # Admin-Einstellungen
├── src/                # Vue.js Quellcode (Komponenten, Services, Utils)
├── templates/          # PHP Templates für den Initialeinstieg
└── tests/              # PHPUnit-Tests
```
