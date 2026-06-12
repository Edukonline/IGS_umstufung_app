<?php
\OCP\Util::addScript('kursumstufung', 'settings-admin');
?>
<div class="section" id="kursumstufung-admin">
    <h2>KursUmstufungen</h2>
    <p>Konfiguration der Umstufungs-App: Zugriffsgruppe der Schulleitung sowie die wählbaren Fächer und Klassen.</p>

    <p style="margin-top: 16px;">
        <label for="kursumstufung-admin-group"><strong>Gruppe für Schulleitung:</strong></label><br/>
        <span class="hint">Mitglieder dieser Gruppe sehen alle eingereichten Anträge und können entscheiden.</span><br/>
        <input type="text" id="kursumstufung-admin-group" name="admin_group"
               value="<?php p($_['adminGroup']); ?>" placeholder="z.B. schulleitung"
               style="width: 320px; padding: 6px; margin-top: 4px;" />
    </p>

    <p style="margin-top: 16px;">
        <label for="kursumstufung-subjects"><strong>Fächer</strong> (eines pro Zeile):</label><br/>
        <textarea id="kursumstufung-subjects" rows="6" style="width: 320px; padding: 6px;"><?php p($_['subjects']); ?></textarea>
    </p>

    <p style="margin-top: 16px;">
        <label for="kursumstufung-classes"><strong>Klassen</strong> (eine pro Zeile):</label><br/>
        <textarea id="kursumstufung-classes" rows="6" style="width: 320px; padding: 6px;"><?php p($_['classes']); ?></textarea>
    </p>

    <p style="margin-top: 16px;">
        <button id="kursumstufung-save-admin-group" class="button primary">Einstellungen speichern</button>
        <span id="kursumstufung-save-msg" style="margin-left: 10px; color: var(--color-success, green); display: none;">Erfolgreich gespeichert!</span>
        <span id="kursumstufung-save-err" style="margin-left: 10px; color: var(--color-error, #c0392b); display: none;"></span>
    </p>
</div>
