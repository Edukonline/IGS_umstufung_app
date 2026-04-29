# UmstufungMNS - Nextcloud App

Eine professionelle Nextcloud-Erweiterung für die Main-Taunus-Schule (MNS) zur effizienten Verwaltung von Umstufungsanträgen (G-Kurs/E-Kurs).

## 🌟 Features

- **Lehrkraft-Dashboard**: Erstellen, Bearbeiten und Löschen von Umstufungs-Entwürfen.
- **Automatisierte Logik**: Intelligente Vorauswahl von Fächern und automatische Synchronisation der Kurs-Niveaus (G -> E / E -> G).
- **Sammel-Einreichung**: Mehrere Entwürfe können gesammelt mit einem Klick an die Schulleitung übermittelt werden.
- **Schulleitungs-Ansicht**: Zentrale Übersicht aller eingereichten Anträge (nur für Administratoren oder Mitglieder der Gruppe `schulleitung` sichtbar).
- **Modernes UI/UX**: Vollständig integriert in das Nextcloud-Design, unterstützt Dark Mode und bietet flüssige Animationen sowie Echtzeit-Feedback-Banner.

## 🛠 Technologie-Stack

- **Backend**: PHP 8.x (Nextcloud App Framework)
- **Frontend**: Vue.js 3, Axios, Nextcloud-Components
- **Datenbank**: MySQL/MariaDB (via Nextcloud DB Schema)
- **Design**: Vanilla CSS mit Nextcloud Design-Tokens für maximale Kompatibilität.

## 🚀 Installation

1. Klonen Sie dieses Repository in den Ordner `apps/umstufungmns` Ihrer Nextcloud-Instanz.
2. Führen Sie im App-Ordner folgende Befehle aus:
   ```bash
   npm install
   npm run build
   ```
3. Aktivieren Sie die App über das Nextcloud App-Menü.

## 🔒 Datenschutz (DSGVO)

Die App wurde unter Berücksichtigung strenger Datenschutzrichtlinien entwickelt:
- **Rollenkonzept**: Striktes Need-to-know-Prinzip bei der Dateneinsicht.
- **Minimale Datenspeicherung**: Nur prozessrelevante Daten werden erfasst.
- **Kein Tracking**: Es findet keine Analyse von Nutzerverhalten statt.

## 📄 Lizenz

Proprietär - Entwickelt für die Main-Taunus-Schule.

---
*Entwickelt mit ❤️ für die MNS.*
