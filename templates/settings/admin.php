<?php
\OCP\Util::addScript('kursumstufung', 'settings-admin');
?>
<div class="section" id="kursumstufung-admin">
    <h2>KursUmstufungen</h2>
    <p>Hier kannst du konfigurieren, welche Nextcloud-Gruppe Zugriff auf die Schulleitungs-Ansicht (alle Anträge) hat.</p>
    
    <p style="margin-top: 10px;">
        <label for="kursumstufung-admin-group"><strong>Gruppe für Schulleitung:</strong></label><br/>
        <input type="text" id="kursumstufung-admin-group" name="admin_group" value="<?php p($_['adminGroup']); ?>" placeholder="z.B. schulleitung" style="width: 300px; padding: 5px; margin-top: 5px;" />
    </p>
    
    <p style="margin-top: 10px;">
        <button id="kursumstufung-save-admin-group" class="button primary">Einstellungen speichern</button>
        <span id="kursumstufung-save-msg" style="margin-left: 10px; color: green; display: none;">Erfolgreich gespeichert!</span>
    </p>
</div>
