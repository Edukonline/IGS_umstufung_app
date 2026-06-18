---
title: Schema Dictionary & Data Models
version: 1.0.4
last_updated: 2026-06-12
author: Edukonline
---

# Data Models

Die App verwendet eine zentrale Tabelle, die über Nextcloud DBAL in PHP migriert wird (`lib/Migration/`).

**ER-Diagramm:** `docs/diagrams/src/schema.mmd` → `docs/diagrams/img/schema.png` (rendern via `sh docs/render_diagrams.sh`).

## Model: `kurs_umstufung_requests`

Speichert Entwürfe der Lehrkräfte sowie eingereichte und entschiedene Umstufungsanträge.

### Field Table

| Field Name | Type (DB) | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | Integer | Primary Key, Auto-Increment | Eindeutige ID des Antrags. |
| `user_id` | String (64) | Not Null, Indexed | Nextcloud-ID der einreichenden Lehrkraft. |
| `student_name` | String (255) | Not Null | Vor- und Nachname der Schülerin/des Schülers. |
| `student_class` | String (16) | Nullable | Klasse (z.B. "8b"). |
| `subject` | String (64) | Not Null | Fach (gegen die konfigurierte Liste validiert). |
| `old_level` | String (32) | Not Null | Aktuelles Niveau (`G-Kurs`/`E-Kurs`). |
| `new_level` | String (32) | Not Null | Gewünschtes Niveau (≠ `old_level`). |
| `reason` | Text | Nullable | Begründung der Lehrkraft. |
| `status` | String (32) | Not Null, Indexed, Default `draft` | Workflow-Status. |
| `school_year` | String (16) | Nullable | Schuljahr (z.B. "2025/2026"), automatisch beim Anlegen gesetzt. *(v1.0.4)* |
| `decided_by` | String (64) | Nullable | Nextcloud-ID des entscheidenden Schulleitungs-Mitglieds. *(v1.0.4)* |
| `decision_reason` | Text | Nullable | Begründung der Entscheidung (v.a. bei Ablehnung). *(v1.0.4)* |
| `created_at` | Datetime | Not Null | Erstellungszeitpunkt. |
| `updated_at` | Datetime | Nullable | Letzte Änderung/Entscheidung. |

### Workflow State Machine
`draft` → `submitted` → `approved` | `rejected`

*   `draft`: sichtbar und editierbar **nur** für die erstellende Lehrkraft.
*   `submitted`: sichtbar für die Schulleitung (und die Lehrkraft, schreibgeschützt); entscheidbar.
*   `approved` / `rejected`: Endzustand; die Lehrkraft wird per Benachrichtigung informiert.

Die zulässigen Werte sind in `lib/Constants/RequestStatus.php` und (gespiegelt) in `src/constants.js` zentralisiert — keine Magic-Strings mehr.

### Indizes
*   `kurs_umstufung_user_idx` auf `user_id` — Lehrkraft-Ansicht.
*   `kurs_umstufung_status_idx` auf `status` — Schulleitungs-Ansicht (`findAllSubmitted`). *(v1.0.4)*

### Validierung
Die Validierung ist serverseitig im `RequestService` verbindlich (Pflichtfelder, Längen, Whitelists für Niveau/Fach/Klasse). Die Frontend-Prüfung ist nur Komfort; die DB-Schemagrenzen sind die letzte Absicherung. Es gibt bewusst keinen Foreign-Key auf `oc_users` (Nextcloud-Konvention); die Zuordnung erfolgt über die gespeicherte `user_id`.
