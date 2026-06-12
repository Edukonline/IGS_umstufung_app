document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('kursumstufung-save-admin-group');
    const groupField = document.getElementById('kursumstufung-admin-group');
    const subjectsField = document.getElementById('kursumstufung-subjects');
    const classesField = document.getElementById('kursumstufung-classes');
    const msgSpan = document.getElementById('kursumstufung-save-msg');
    const errSpan = document.getElementById('kursumstufung-save-err');

    if (!saveButton) {
        return;
    }

    function toLines(textarea) {
        return textarea.value
            .split('\n')
            .map(function(s) { return s.trim(); })
            .filter(function(s) { return s.length > 0; });
    }

    function post(path, body) {
        return fetch(OC.generateUrl('apps/kursumstufung/api/settings/' + path), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'requesttoken': OC.requestToken,
            },
            body: JSON.stringify(body),
        }).then(function(response) {
            return response.json().then(function(data) {
                if (!response.ok || data.status !== 'success') {
                    throw new Error(data.error || 'Speichern fehlgeschlagen.');
                }
                return data;
            });
        });
    }

    function showError(message) {
        if (msgSpan) { msgSpan.style.display = 'none'; }
        if (errSpan) {
            errSpan.textContent = message;
            errSpan.style.display = 'inline';
        }
    }

    function showSuccess() {
        if (errSpan) { errSpan.style.display = 'none'; }
        if (msgSpan) {
            msgSpan.style.display = 'inline';
            setTimeout(function() { msgSpan.style.display = 'none'; }, 3000);
        }
    }

    saveButton.addEventListener('click', function() {
        saveButton.disabled = true;
        Promise.all([
            post('adminGroup', { groupName: groupField.value }),
            post('subjects', { subjects: toLines(subjectsField) }),
            post('classes', { classes: toLines(classesField) }),
        ])
            .then(function() { showSuccess(); })
            .catch(function(error) { showError(error.message); })
            .finally(function() { saveButton.disabled = false; });
    });
});
