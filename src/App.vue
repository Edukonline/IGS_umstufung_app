<template>
    <div id="content" class="app-kursumstufung" style="display: flex; height: 100%; min-height: 100vh; background: var(--color-main-background);">
        <!-- Seitennavigation -->
        <aside id="app-navigation" style="width: 300px; border-right: 1px solid var(--color-border); padding: 20px;">
            <ul class="with-icons">
                <li style="padding: 12px; background: var(--color-background-hover); border-radius: 8px; cursor: pointer; display: flex; align-items: center;">
                    <span class="icon-home" style="margin-right: 12px;"></span>
                    <span style="font-weight: 600;">Übersicht</span>
                </li>
            </ul>
        </aside>

        <!-- Hauptinhalt -->
        <main id="app-content" style="flex-grow: 1; padding: 0; overflow-y: auto; position: relative;">
            
            <!-- Benachrichtigungs-Banner -->
            <transition name="fade">
                <div v-if="notification.show" :class="['notification-banner', notification.type]">
                    <span class="icon">{{ notification.type === 'success' ? '✅' : '⚠️' }}</span>
                    <span class="message">{{ notification.message }}</span>
                    <button @click="notification.show = false" class="close-btn">&times;</button>
                </div>
            </transition>

            <div style="padding: 40px;">
                <div v-if="loading" style="text-align: center; padding: 100px;">
                    <div class="spinner" style="margin-bottom: 20px;"></div>
                    <p style="color: #888;">Lade Daten...</p>
                </div>

                <div v-else class="content-wrapper">
                    <!-- Kopfzeile -->
                    <header style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px;">
                        <div>
                            <h2 style="margin: 0; font-size: 2em; color: var(--color-main-text);">
                                {{ isSchulleitung ? 'Eingereichte Anträge' : 'Meine Umstufungs-Entwürfe' }}
                            </h2>
                            <p style="margin: 5px 0 0; color: #888;">{{ isSchulleitung ? 'Übersicht aller Anträge zur Prüfung' : 'Hier können Sie Entwürfe erstellen und bearbeiten' }}</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="export-btn" @click="exportToCSV" v-if="requests.length > 0">
                                📊 Export (.csv)
                            </button>
                            <button class="primary new-btn" @click="startNew" v-if="!showForm && !isSchulleitung">
                                + Neuer Entwurf
                            </button>
                        </div>
                    </header>

                    <!-- Suche -->
                    <div class="search-wrapper" v-if="requests.length > 0">
                        <input type="text" v-model="searchQuery" class="search-input" placeholder="🔍 Suche nach Schülername...">
                    </div>

                    <!-- Eingabeformular -->
                    <transition name="slide">
                        <div v-if="showForm" class="form-container">
                            <h3 style="margin-top: 0; color: var(--color-primary); font-size: 1.5em; margin-bottom: 25px;">
                                {{ form.id ? 'Entwurf bearbeiten' : 'Neuer Umstufungs-Entwurf' }}
                            </h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Schüler:in</label>
                                    <input type="text" v-model="form.studentName" class="form-input" placeholder="Vorname Nachname">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Klasse</label>
                                    <select v-model="form.class" class="form-input">
                                        <option value="">Bitte wählen...</option>
                                        <option v-for="c in availableClasses" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Fach</label>
                                    <select v-model="form.subject" class="form-input">
                                        <option value="">Bitte wählen...</option>
                                        <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Von Niveau</label>
                                    <select v-model="form.oldLevel" @change="syncLevels('from')" class="form-input">
                                        <option value="G-Kurs">G-Kurs</option>
                                        <option value="E-Kurs">E-Kurs</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nach Niveau</label>
                                    <select v-model="form.newLevel" @change="syncLevels('to')" class="form-input">
                                        <option value="G-Kurs">G-Kurs</option>
                                        <option value="E-Kurs">E-Kurs</option>
                                    </select>
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Begründung</label>
                                    <textarea v-model="form.reason" class="form-input" style="min-height: 120px;" placeholder="Warum soll die Umstufung erfolgen?"></textarea>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 15px; margin-top: 10px;">
                                <button class="primary action-btn" @click="saveRequest" :disabled="saving">
                                    {{ saving ? 'Speichert...' : (form.id ? 'Änderungen übernehmen' : 'Entwurf sichern') }}
                                </button>
                                <button @click="showForm = false" class="cancel-btn">Abbrechen</button>
                            </div>
                        </div>
                    </transition>

                    <!-- Sammel-Senden Button -->
                    <div v-if="!isSchulleitung && hasDrafts" style="margin-bottom: 40px;">
                        <button class="submit-all-btn" @click="submitAll" :disabled="submitting">
                            {{ submitting ? 'Wird gesendet...' : '🚀 Alle Entwürfe JETZT final einreichen' }}
                        </button>
                    </div>

                    <!-- Datentabelle -->
                    <div class="table-container">
                        <table class="table-styled">
                            <thead>
                                <tr>
                                    <th v-if="isSchulleitung">Lehrkraft</th>
                                    <th>Schüler:in</th>
                                    <th>Klasse</th>
                                    <th>Fach</th>
                                    <th>Von</th>
                                    <th>Nach</th>
                                    <th>Datum</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="request in filteredRequests" :key="request.id" class="table-row">
                                    <td v-if="isSchulleitung" style="color: var(--color-primary); font-weight: 600;">{{ request.userName || request.userId }}</td>
                                    <td style="font-weight: 600; color: #fff;">{{ request.studentName }}</td>
                                    <td style="font-family: monospace; font-weight: bold;">{{ request.class || '-' }}</td>
                                    <td>{{ request.subject }}</td>
                                    <td><span class="level-tag">{{ request.oldLevel }}</span></td>
                                    <td><span class="level-tag">{{ request.newLevel }}</span></td>
                                    <td style="font-size: 0.85em; color: #777;">{{ formatDate(request.createdAt) }}</td>
                                    <td>
                                        <span :class="['status-badge', request.status]">
                                            {{ request.status === 'draft' ? 'Entwurf' : 'Eingereicht' }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="actions-group" v-if="request.status === 'draft'">
                                            <button @click="editRequest(request)" class="edit-btn">Bearbeiten</button>
                                            <button @click="deleteRequest(request.id)" class="delete-btn">Löschen</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredRequests.length === 0">
                                    <td :colspan="isSchulleitung ? 9 : 8" class="empty-state">
                                        {{ searchQuery ? 'Keine Treffer für diese Suche.' : 'Keine Einträge vorhanden. Legen Sie einen neuen Entwurf an!' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
    name: 'App',
    data() {
        return {
            loading: true,
            saving: false,
            submitting: false,
            isSchulleitung: false,
            requests: [],
            showForm: false,
            searchQuery: '',
            subjects: ['Mathematik', 'Deutsch', 'Englisch', 'Chemie', 'Physik'],
            availableClasses: this.generateClasses(),
            notification: {
                show: false,
                message: '',
                type: 'success'
            },
            form: {
                id: null,
                studentName: '',
                class: '',
                subject: '',
                oldLevel: 'G-Kurs',
                newLevel: 'E-Kurs',
                reason: ''
            }
        }
    },
    computed: {
        hasDrafts() {
            return Array.isArray(this.requests) && this.requests.some(r => r.status === 'draft')
        },
        filteredRequests() {
            if (!this.searchQuery) return this.requests
            const query = this.searchQuery.toLowerCase()
            return this.requests.filter(r => 
                r.studentName.toLowerCase().includes(query) || 
                (r.class && r.class.toLowerCase().includes(query))
            )
        }
    },
    mounted() {
        this.loadData()
    },
    methods: {
        generateClasses() {
            const classes = []
            for (let i = 5; i <= 10; i++) {
                ['a', 'b', 'c'].forEach(zug => {
                    classes.push(`${i}${zug}`)
                })
            }
            return classes
        },
        formatDate(val) {
            if (!val) return '-'
            // Wenn es ein Objekt von Nextcloud ist {date: "...", ...}
            const dateStr = val.date || val
            const date = new Date(dateStr)
            if (isNaN(date.getTime())) return '-'
            return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
        },
        notify(message, type = 'success') {
            this.notification.message = message;
            this.notification.type = type;
            this.notification.show = true;
            if (type === 'success') {
                setTimeout(() => {
                    this.notification.show = false;
                }, 4000);
            }
        },
        async loadData() {
            this.loading = true
            try {
                const url = generateUrl('/apps/kursumstufung/api/requests')
                const response = await axios.get(url)
                this.isSchulleitung = response.data.isSchulleitung || false
                this.requests = response.data.requests || []
            } catch (error) {
                console.error('Fehler beim Laden', error)
            } finally {
                this.loading = false
            }
        },
        syncLevels(origin) {
            if (origin === 'from') {
                this.form.newLevel = this.form.oldLevel === 'G-Kurs' ? 'E-Kurs' : 'G-Kurs';
            } else {
                this.form.oldLevel = this.form.newLevel === 'G-Kurs' ? 'E-Kurs' : 'G-Kurs';
            }
        },
        startNew() {
            this.form = { id: null, studentName: '', class: '', subject: '', oldLevel: 'G-Kurs', newLevel: 'E-Kurs', reason: '' };
            this.showForm = true;
        },
        editRequest(request) {
            this.form = { ...request };
            this.showForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        async saveRequest() {
            if (!this.form.studentName || !this.form.class || !this.form.subject) {
                this.notify('Bitte Name, Klasse und Fach ausfüllen!', 'error');
                return;
            }
            this.saving = true;
            try {
                if (this.form.id) {
                    const url = generateUrl(`/apps/kursumstufung/api/requests/${this.form.id}`);
                    const response = await axios.put(url, this.form);
                    const index = this.requests.findIndex(r => r.id === this.form.id);
                    if (index !== -1) {
                        this.requests.splice(index, 1, response.data);
                    }
                    this.notify('Änderungen erfolgreich gespeichert');
                } else {
                    const url = generateUrl('/apps/kursumstufung/api/requests');
                    const response = await axios.post(url, this.form);
                    this.requests.push(response.data);
                    this.notify('Neuer Entwurf wurde angelegt');
                }
                this.showForm = false;
                this.form = { id: null, studentName: '', class: '', subject: '', oldLevel: 'G-Kurs', newLevel: 'E-Kurs', reason: '' };
            } catch (error) {
                const msg = error.response?.data?.error || error.message || 'Unbekannter Fehler';
                this.notify('Speichern fehlgeschlagen: ' + msg, 'error');
            } finally {
                this.saving = false;
            }
        },
        async deleteRequest(id) {
            if (!confirm('Wirklich löschen?')) return
            try {
                await axios.delete(generateUrl(`/apps/kursumstufung/api/requests/${id}`))
                this.requests = this.requests.filter(r => r.id !== id)
                this.notify('Eintrag wurde gelöscht');
            } catch (error) {
                this.notify('Löschen fehlgeschlagen', 'error');
            }
        },
        async submitAll() {
            if (!confirm('Alle Entwürfe jetzt final einreichen? Dies kann nicht rückgängig gemacht werden.')) return
            this.submitting = true
            try {
                await axios.post(generateUrl('/apps/kursumstufung/api/submit_all'))
                await this.loadData()
                this.notify('Alle Anträge wurden erfolgreich übermittelt');
            } catch (error) {
                this.notify('Fehler beim Übermitteln der Anträge', 'error');
            } finally {
                this.submitting = false
            }
        },
        exportToCSV() {
            const rows = this.filteredRequests.map(r => ({
                'ID': r.id,
                'Lehrkraft': r.userName || r.userId,
                'Schüler:in': r.studentName,
                'Klasse': r.class,
                'Fach': r.subject,
                'Von': r.oldLevel,
                'Nach': r.newLevel,
                'Status': r.status,
                'Erstellt am': this.formatDate(r.createdAt)
            }))

            if (rows.length === 0) return

            const separator = ';'
            const keys = Object.keys(rows[0])
            const csvContent = [
                keys.join(separator),
                ...rows.map(row => keys.map(k => `"${row[k]}"`).join(separator))
            ].join('\n')

            const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' })
            const link = document.createElement("a")
            const url = URL.createObjectURL(blob)
            link.setAttribute("href", url)
            link.setAttribute("download", `Umstufungen_Export_${new Date().toISOString().split('T')[0]}.csv`)
            link.style.visibility = 'hidden'
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        }
    }
}
</script>

<style scoped>
/* Suche */
.search-wrapper {
    margin-bottom: 25px;
}
.search-input {
    width: 100%;
    max-width: 400px;
    padding: 12px 15px;
    border-radius: 10px;
    background: #1a1a1a;
    border: 1px solid #333;
    color: white;
}

/* Banner */
.notification-banner {
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    animation: slideDown 0.4s ease;
}
.notification-banner.success {
    background: #27ae60;
    color: white;
}
.notification-banner.error {
    background: #c0392b;
    color: white;
}
.notification-banner .message {
    flex-grow: 1;
    font-weight: 600;
}
.close-btn {
    background: transparent;
    border: none;
    color: white;
    font-size: 1.5em;
    cursor: pointer;
    opacity: 0.7;
}

/* Form */
.form-container {
    background: #222;
    padding: 35px;
    border-radius: 20px;
    margin-bottom: 40px;
    border: 1px solid #333;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 25px;
}
.form-group.full-width {
    grid-column: 1 / -1;
}
.form-label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #aaa;
    font-size: 0.9em;
    text-transform: uppercase;
}
.form-input {
    width: 100%;
    padding: 14px 15px;
    border-radius: 8px;
    background: #111;
    color: white;
    border: 1px solid #444;
    font-size: 16px;
    line-height: 1.5;
    transition: border-color 0.3s, box-shadow 0.3s;
}
.form-input:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(0, 130, 201, 0.2);
    outline: none;
}

/* Buttons */
.new-btn {
    background: var(--color-primary);
    padding: 14px 28px;
    border-radius: 10px;
    font-size: 1.05em;
}
.export-btn {
    background: #333;
    color: #ccc;
    border: 1px solid #444;
    padding: 14px 25px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}
.export-btn:hover {
    background: #444;
    color: white;
}
.action-btn {
    padding: 14px 35px;
    border-radius: 8px;
    font-size: 1.1em;
}
.cancel-btn {
    background: transparent;
    border: 1px solid #444;
    color: #888;
    padding: 14px 25px;
    border-radius: 8px;
    cursor: pointer;
}
.submit-all-btn {
    width: 100%;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 20px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    border-radius: 12px;
    font-size: 1.2em;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
}

/* Table */
.table-container {
    background: #1a1a1a;
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid #333;
}
.table-styled {
    width: 100%;
    border-collapse: collapse;
}
.table-styled th {
    background: #252525;
    padding: 18px 20px;
    text-align: left;
    color: #777;
    font-size: 0.85em;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}
.table-row {
    border-bottom: 1px solid #2a2a2a;
    transition: background 0.2s;
}
.table-row:hover {
    background: #222;
}
.table-row td {
    padding: 20px;
    color: #ccc;
}
.level-tag {
    background: #333;
    padding: 5px 10px;
    border-radius: 6px;
    font-family: 'Courier New', Courier, monospace;
    font-weight: bold;
    color: #3498db;
}

/* Badges */
.status-badge {
    padding: 6px 15px;
    border-radius: 30px;
    font-size: 0.8em;
    font-weight: 800;
    text-transform: uppercase;
}
.status-badge.draft {
    background: rgba(243, 156, 18, 0.15);
    color: #f39c12;
    border: 1px solid rgba(243, 156, 18, 0.3);
}
.status-badge.submitted {
    background: rgba(39, 174, 96, 0.15);
    color: #27ae60;
    border: 1px solid rgba(39, 174, 96, 0.3);
}

/* Action Buttons */
.edit-btn {
    background: #2980b9;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}
.delete-btn {
    background: #c0392b;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active { transition: all 0.4s ease-out; }
.slide-enter-from { opacity: 0; transform: translateY(-20px); }

@keyframes slideDown {
    from { transform: translateY(-100%); }
    to { transform: translateY(0); }
}

select.form-input {
    height: 50px;
    cursor: pointer;
}
select.form-input option {
    background: #222;
    color: white;
}
</style>
