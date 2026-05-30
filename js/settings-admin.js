document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('kursumstufung-save-admin-group');
    const inputField = document.getElementById('kursumstufung-admin-group');
    const msgSpan = document.getElementById('kursumstufung-save-msg');

    if (saveButton && inputField) {
        saveButton.addEventListener('click', function() {
            const groupName = inputField.value;

            // OC.generateUrl is the standard way to build API URLs in Nextcloud apps
            const url = OC.generateUrl('apps/kursumstufung/api/settings/adminGroup');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'requesttoken': OC.requestToken // Essential for Nextcloud API calls
                },
                body: JSON.stringify({
                    groupName: groupName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    msgSpan.style.display = 'inline';
                    setTimeout(() => {
                        msgSpan.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Fehler beim Speichern der Einstellungen.');
                }
            })
            .catch(error => {
                console.error('Error saving settings:', error);
                alert('Netzwerkfehler beim Speichern.');
            });
        });
    }
});
