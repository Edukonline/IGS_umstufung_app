# KursUmstufung - Nextcloud App

A professional Nextcloud extension for the efficient management of course level change requests (G-Course/E-Course) at schools.

## Features

*   **Teacher Dashboard:** Create, edit, and delete reclassification drafts.
*   **Automated Logic:** Intelligent pre-selection of subjects and automatic synchronization of course levels (G -> E / E -> G).
*   **Bulk Submission:** Multiple drafts can be submitted collectively to the school administration with a single click.
*   **School Administration View:** Central overview of all submitted applications (only visible to administrators or members of the `schulleitung` group).
*   **Modern UI/UX:** Fully integrated into the Nextcloud design, supports Dark Mode, and offers fluid animations as well as real-time feedback banners.

## Technology Stack

*   **Backend:** PHP 8.x (Nextcloud App Framework)
*   **Frontend:** Vue.js 3, Axios, Nextcloud-Components
*   **Database:** MySQL/MariaDB (via Nextcloud DB Schema)
*   **Design:** Vanilla CSS with Nextcloud design tokens for maximum compatibility.

## Installation

1.  Clone this repository into the `apps/kursumstufung` folder of your Nextcloud instance.
2.  Run the following commands inside the app folder:
```bash
    npm install
    npm run build
    ```
3.  Enable the app via the Nextcloud app management menu.

## Data Protection (GDPR)

The app was developed in strict compliance with data protection guidelines:
*   **Role Concept:** Strict need-to-know principle regarding data access.
*   **Minimal Data Storage:** Only data strictly relevant to the process is recorded.
*   **No Tracking:** No analysis of user behavior takes place.

## License

This project is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**. 

Since this is a Nextcloud application, it inherits the AGPL-3.0 license requirements of the Nextcloud ecosystem to ensure that the software remains free and open-source. You are free to use, modify, and distribute this software, provided that any derivative works are also distributed under the same license and the source code is made available to users interacting with the app over a network.
