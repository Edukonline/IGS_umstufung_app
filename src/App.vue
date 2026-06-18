<template>
    <div id="content" class="app-kursumstufung">
        <aside id="app-navigation">
            <ul class="with-icons">
                <li class="nav-active">
                    <span class="icon-home"></span>
                    <span>{{ t('Übersicht') }}</span>
                </li>
            </ul>
        </aside>

        <main id="app-content">
            <NotificationBanner :notification="notification" @close="dismissNotification" @action="onNotificationAction" />

            <div class="page">
                <div v-if="loading" class="centered" role="status" aria-live="polite">
                    <div class="spinner"></div>
                    <p>{{ t('Lade Daten...') }}</p>
                </div>

                <div v-else-if="loadError" class="centered error-box">
                    <p>⚠️ {{ t('Die Anträge konnten nicht geladen werden.') }}</p>
                    <button class="primary" @click="loadData">{{ t('Erneut versuchen') }}</button>
                </div>

                <div v-else>
                    <header class="page-header">
                        <div>
                            <h2>{{ isSchulleitung ? t('Eingereichte Anträge') : t('Meine Umstufungs-Entwürfe') }}</h2>
                            <p class="subtitle">{{ isSchulleitung ? t('Übersicht aller Anträge zur Prüfung') : t('Hier können Sie Entwürfe erstellen und bearbeiten') }}</p>
                        </div>
                        <div class="header-actions">
                            <button v-if="requests.length > 0" class="export-btn" @click="exportCsv">📊 {{ t('Export (.csv)') }}</button>
                            <button v-if="!showForm && !isSchulleitung" class="primary new-btn" @click="openNew">+ {{ t('Neuer Entwurf') }}</button>
                        </div>
                    </header>

                    <div v-if="requests.length > 0" class="filters">
                        <input v-model="searchQuery" type="text" class="filter-input" :aria-label="t('Suche nach Schülername')" :placeholder="t('🔍 Suche nach Schülername...')">
                        <select v-if="availableYears.length > 1" v-model="yearFilter" class="filter-input year" :aria-label="t('Schuljahr filtern')">
                            <option value="">{{ t('Alle Schuljahre') }}</option>
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>

                    <RequestForm
                        v-if="showForm"
                        :request="editingRequest"
                        :subjects="subjects"
                        :classes="classes"
                        :levels="levels"
                        :saving="saving"
                        @save="saveRequest"
                        @cancel="showForm = false" />

                    <div v-if="!isSchulleitung && hasDrafts" class="submit-all-wrap">
                        <button class="submit-all-btn" :disabled="submitting" @click="submitAll">
                            {{ submitting ? t('Wird gesendet...') : '🚀 ' + t('Alle Entwürfe JETZT final einreichen') }}
                        </button>
                    </div>

                    <RequestTable
                        :requests="filteredRequests"
                        :is-schulleitung="isSchulleitung"
                        :empty-text="emptyText"
                        @edit="openEdit"
                        @delete="deleteRequest"
                        @decide="openDecision" />
                </div>
            </div>

            <!-- Entscheidungs-Dialog (Schulleitung) -->
            <DecisionDialog
                v-if="decision.show"
                :request="decision.request"
                :value="decision.value"
                :deciding="deciding"
                @confirm="confirmDecision"
                @cancel="closeDecision" />
        </main>
    </div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import DecisionDialog from './components/DecisionDialog.vue'
import NotificationBanner from './components/NotificationBanner.vue'
import RequestForm from './components/RequestForm.vue'
import RequestTable from './components/RequestTable.vue'
import api from './services/api.js'
import { t } from './l10n.js'
import { STATUS, DEFAULT_LEVELS } from './constants.js'
import { buildCsv, downloadCsv } from './utils/csv.js'
import { currentSchoolYear } from './utils/schoolYear.js'

function initialState(key, fallback) {
    try {
        return loadState('kursumstufung', key, fallback)
    } catch (e) {
        return fallback
    }
}

export default {
    name: 'App',
    components: { DecisionDialog, NotificationBanner, RequestForm, RequestTable },
    data() {
        return {
            STATUS,
            loading: true,
            loadError: false,
            saving: false,
            submitting: false,
            deciding: false,
            isSchulleitung: initialState('isSchulleitung', false),
            subjects: initialState('subjects', []),
            classes: initialState('classes', []),
            levels: initialState('levels', DEFAULT_LEVELS),
            requests: [],
            showForm: false,
            editingRequest: null,
            searchQuery: '',
            yearFilter: '',
            yearDefaulted: false,
            notification: { show: false, message: '', type: 'success', action: null },
            undoHandler: null,
            notifyTimer: null,
            decision: { show: false, request: null, value: '' },
        }
    },
    computed: {
        hasDrafts() {
            return this.requests.some((r) => r.status === STATUS.DRAFT)
        },
        availableYears() {
            return [...new Set(this.requests.map((r) => r.schoolYear).filter(Boolean))].sort().reverse()
        },
        filteredRequests() {
            const query = this.searchQuery.trim().toLowerCase()
            return this.requests.filter((r) => {
                if (this.yearFilter && r.schoolYear !== this.yearFilter) {
                    return false
                }
                if (!query) {
                    return true
                }
                return (r.studentName || '').toLowerCase().includes(query)
                    || (r.class || '').toLowerCase().includes(query)
            })
        },
        emptyText() {
            return this.searchQuery || this.yearFilter
                ? t('Keine Treffer für diese Filter.')
                : t('Keine Einträge vorhanden. Legen Sie einen neuen Entwurf an!')
        },
    },
    mounted() {
        this.loadData()
    },
    methods: {
        t,
        async loadData() {
            this.loading = true
            this.loadError = false
            try {
                const data = await api.list()
                this.isSchulleitung = data.isSchulleitung ?? this.isSchulleitung
                this.requests = data.requests || []
                this.applyDefaultYear()
            } catch (error) {
                this.loadError = true
                this.notify(t('Die Anträge konnten nicht geladen werden.'), 'error')
            } finally {
                this.loading = false
            }
        },
        applyDefaultYear() {
            // Standardansicht aufs aktuelle Schuljahr beschränken, sofern dort
            // Anträge existieren — bändigt die Liste über Jahre. Nur einmalig,
            // damit eine spätere Nutzerauswahl nicht überschrieben wird.
            if (this.yearDefaulted) {
                return
            }
            this.yearDefaulted = true
            const current = currentSchoolYear()
            if (this.requests.some((r) => r.schoolYear === current)) {
                this.yearFilter = current
            }
        },
        openNew() {
            this.editingRequest = null
            this.showForm = true
        },
        openEdit(request) {
            this.editingRequest = request
            this.showForm = true
            window.scrollTo({ top: 0, behavior: 'smooth' })
        },
        async saveRequest(form) {
            this.saving = true
            try {
                if (form.id) {
                    const updated = await api.update(form.id, form)
                    const index = this.requests.findIndex((r) => r.id === form.id)
                    if (index !== -1) {
                        this.requests.splice(index, 1, updated)
                    }
                    this.notify(t('Änderungen erfolgreich gespeichert'))
                } else {
                    const created = await api.create(form)
                    this.requests.unshift(created)
                    this.notify(t('Neuer Entwurf wurde angelegt'))
                }
                this.showForm = false
            } catch (error) {
                this.notify(t('Speichern fehlgeschlagen: {msg}', { msg: this.errorMessage(error) }), 'error')
            } finally {
                this.saving = false
            }
        },
        async deleteRequest(id) {
            const request = this.requests.find((r) => r.id === id)
            if (!request) {
                return
            }
            // Optimistisch entfernen; der Server löscht soft (wiederherstellbar).
            this.requests = this.requests.filter((r) => r.id !== id)
            try {
                await api.remove(id)
            } catch (error) {
                await this.loadData()
                this.notify(t('Löschen fehlgeschlagen'), 'error')
                return
            }
            this.notifyWithUndo(t('Eintrag gelöscht'), async () => {
                try {
                    await api.restore(id)
                    await this.loadData()
                    this.notify(t('Wiederhergestellt'))
                } catch (error) {
                    this.notify(t('Wiederherstellen fehlgeschlagen'), 'error')
                }
            })
        },
        async submitAll() {
            if (!window.confirm(t('Alle Entwürfe jetzt final einreichen? Dies kann nicht rückgängig gemacht werden.'))) {
                return
            }
            this.submitting = true
            try {
                await api.submitAll()
                await this.loadData()
                this.notify(t('Alle Anträge wurden erfolgreich übermittelt'))
            } catch (error) {
                this.notify(t('Fehler beim Übermitteln der Anträge'), 'error')
            } finally {
                this.submitting = false
            }
        },
        openDecision(request, value) {
            this.decision = { show: true, request, value }
        },
        closeDecision() {
            this.decision.show = false
        },
        async confirmDecision(reason) {
            const { request, value } = this.decision
            this.deciding = true
            try {
                const updated = await api.decide(request.id, value, reason)
                const index = this.requests.findIndex((r) => r.id === request.id)
                if (index !== -1) {
                    this.requests.splice(index, 1, updated)
                }
                this.notify(value === STATUS.APPROVED ? t('Antrag genehmigt') : t('Antrag abgelehnt'))
                this.closeDecision()
            } catch (error) {
                this.notify(t('Entscheidung fehlgeschlagen: {msg}', { msg: this.errorMessage(error) }), 'error')
            } finally {
                this.deciding = false
            }
        },
        exportCsv() {
            const rows = this.filteredRequests.map((r) => ({
                ID: r.id,
                Lehrkraft: r.userName || r.userId,
                'Schüler:in': r.studentName,
                Klasse: r.class,
                Fach: r.subject,
                Von: r.oldLevel,
                Nach: r.newLevel,
                Status: r.status,
                Schuljahr: r.schoolYear,
                'Erstellt am': r.createdAt ? new Date(r.createdAt).toLocaleDateString('de-DE') : '',
            }))
            if (!rows.length) {
                return
            }
            const today = new Date().toISOString().split('T')[0]
            downloadCsv(`Umstufungen_Export_${today}.csv`, buildCsv(rows))
        },
        notify(message, type = 'success') {
            this.clearUndo()
            this.notification = { show: true, message, type, action: null }
            if (type === 'success') {
                this.notifyTimer = setTimeout(() => { this.notification.show = false }, 4000)
            }
        },
        notifyWithUndo(message, handler) {
            this.clearUndo()
            this.undoHandler = handler
            this.notification = { show: true, message, type: 'success', action: { label: t('Rückgängig') } }
            this.notifyTimer = setTimeout(() => {
                this.notification.show = false
                this.undoHandler = null
            }, 7000)
        },
        onNotificationAction() {
            const handler = this.undoHandler
            this.clearUndo()
            this.notification.show = false
            if (handler) {
                handler()
            }
        },
        dismissNotification() {
            this.clearUndo()
            this.notification.show = false
        },
        clearUndo() {
            if (this.notifyTimer) {
                clearTimeout(this.notifyTimer)
                this.notifyTimer = null
            }
            this.undoHandler = null
        },
        errorMessage(error) {
            return error?.response?.data?.error || error?.message || t('Unbekannter Fehler')
        },
    },
}
</script>

<style scoped>
.app-kursumstufung {
    display: flex;
    height: 100%;
    min-height: 100vh;
    background: var(--color-main-background);
    color: var(--color-main-text);
}
#app-navigation {
    width: 280px;
    border-right: 1px solid var(--color-border);
    padding: 20px;
}
#app-navigation .nav-active {
    padding: 12px;
    background: var(--color-background-hover);
    border-radius: var(--border-radius, 8px);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
}
#app-content {
    flex-grow: 1;
    overflow-y: auto;
    position: relative;
}
.page {
    padding: 40px;
}
.centered {
    text-align: center;
    padding: 80px 20px;
    color: var(--color-text-maxcontrast);
}
.error-box button {
    margin-top: 16px;
}
.spinner {
    width: 36px;
    height: 36px;
    margin: 0 auto 16px;
    border: 3px solid var(--color-border);
    border-top-color: var(--color-primary-element, var(--color-primary));
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    gap: 16px;
    flex-wrap: wrap;
}
.page-header h2 {
    margin: 0;
    font-size: 1.8em;
}
.subtitle {
    margin: 4px 0 0;
    color: var(--color-text-maxcontrast);
}
.header-actions {
    display: flex;
    gap: 10px;
}
.filters {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.filter-input {
    padding: 10px 14px;
    border-radius: var(--border-radius, 8px);
    background: var(--color-main-background);
    border: 1px solid var(--color-border-dark, var(--color-border));
    color: var(--color-main-text);
    min-width: 240px;
}
.filter-input.year {
    min-width: 160px;
    cursor: pointer;
}
.new-btn,
.export-btn {
    padding: 12px 22px;
    border-radius: var(--border-radius, 8px);
    cursor: pointer;
    font-weight: 600;
}
.export-btn {
    background: var(--color-background-dark);
    color: var(--color-main-text);
    border: 1px solid var(--color-border);
}
.submit-all-wrap {
    margin-bottom: 32px;
}
.submit-all-btn {
    width: 100%;
    background: var(--color-success, #27ae60);
    color: #fff;
    padding: 18px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    border-radius: var(--border-radius-large, 12px);
    font-size: 1.1em;
}
.submit-all-btn:disabled {
    opacity: 0.6;
    cursor: default;
}
</style>
