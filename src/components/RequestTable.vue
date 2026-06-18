<template>
    <div class="table-container">
        <table class="table-styled">
            <caption class="visually-hidden">{{ t('Übersicht der Umstufungsanträge') }}</caption>
            <thead>
                <tr>
                    <th v-if="isSchulleitung" scope="col">{{ t('Lehrkraft') }}</th>
                    <th scope="col">{{ t('Schüler:in') }}</th>
                    <th scope="col">{{ t('Klasse') }}</th>
                    <th scope="col">{{ t('Fach') }}</th>
                    <th scope="col">{{ t('Von') }}</th>
                    <th scope="col">{{ t('Nach') }}</th>
                    <th scope="col">{{ t('Datum') }}</th>
                    <th scope="col">{{ t('Status') }}</th>
                    <th class="right" scope="col">{{ t('Aktionen') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="request in requests" :key="request.id" class="table-row">
                    <td v-if="isSchulleitung" class="teacher">{{ request.userName || request.userId }}</td>
                    <td class="student">{{ request.studentName }}</td>
                    <td class="mono">{{ request.class || '-' }}</td>
                    <td>{{ request.subject }}</td>
                    <td><span class="level-tag">{{ request.oldLevel }}</span></td>
                    <td><span class="level-tag">{{ request.newLevel }}</span></td>
                    <td class="date">{{ formatDate(request.createdAt) }}</td>
                    <td>
                        <span :class="['status-badge', request.status]">{{ statusLabel(request.status) }}</span>
                        <div v-if="isDecided(request)" class="decision-info">
                            {{ request.decidedByName || request.decidedBy }}
                            <span v-if="request.decisionReason" role="img" :title="request.decisionReason" :aria-label="t('Begründung vorhanden')">💬</span>
                        </div>
                    </td>
                    <td class="right">
                        <div v-if="request.status === STATUS.DRAFT && !isSchulleitung" class="actions-group">
                            <button class="edit-btn" @click="$emit('edit', request)">{{ t('Bearbeiten') }}</button>
                            <button class="delete-btn" @click="$emit('delete', request.id)">{{ t('Löschen') }}</button>
                        </div>
                        <div v-else-if="request.status === STATUS.SUBMITTED && isSchulleitung" class="actions-group">
                            <button class="approve-btn" @click="$emit('decide', request, STATUS.APPROVED)">{{ t('Genehmigen') }}</button>
                            <button class="reject-btn" @click="$emit('decide', request, STATUS.REJECTED)">{{ t('Ablehnen') }}</button>
                        </div>
                    </td>
                </tr>
                <tr v-if="requests.length === 0">
                    <td :colspan="isSchulleitung ? 9 : 8" class="empty-state">
                        {{ emptyText }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { t } from '../l10n.js'
import { STATUS, STATUS_LABEL } from '../constants.js'

export default {
    name: 'RequestTable',
    props: {
        requests: { type: Array, default: () => [] },
        isSchulleitung: { type: Boolean, default: false },
        emptyText: { type: String, default: '' },
    },
    emits: ['edit', 'delete', 'decide'],
    data() {
        return { STATUS }
    },
    methods: {
        t,
        statusLabel(status) {
            return t(STATUS_LABEL[status] || status)
        },
        isDecided(request) {
            return request.status === STATUS.APPROVED || request.status === STATUS.REJECTED
        },
        formatDate(value) {
            if (!value) {
                return '-'
            }
            const date = new Date(value)
            if (Number.isNaN(date.getTime())) {
                return '-'
            }
            return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
        },
    },
}
</script>

<style scoped>
.table-container {
    background: var(--color-main-background);
    border-radius: var(--border-radius-large, 12px);
    overflow: hidden;
    border: 1px solid var(--color-border);
}
.table-styled {
    width: 100%;
    border-collapse: collapse;
}
.table-styled th {
    background: var(--color-background-dark);
    padding: 16px 18px;
    text-align: left;
    color: var(--color-text-maxcontrast);
    font-size: 0.8em;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.table-styled th.right,
td.right {
    text-align: right;
}
.table-row {
    border-bottom: 1px solid var(--color-border);
}
.table-row:hover {
    background: var(--color-background-hover);
}
.table-row td {
    padding: 16px 18px;
    color: var(--color-main-text);
}
.student {
    font-weight: 600;
}
.teacher {
    color: var(--color-primary-element, var(--color-primary));
    font-weight: 600;
}
.mono {
    font-family: monospace;
    font-weight: bold;
}
.date {
    font-size: 0.85em;
    color: var(--color-text-maxcontrast);
}
.level-tag {
    background: var(--color-background-dark);
    padding: 4px 10px;
    border-radius: var(--border-radius, 6px);
    font-family: monospace;
    font-weight: bold;
    color: var(--color-primary-element, var(--color-primary));
}
.status-badge {
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 0.78em;
    font-weight: 800;
    text-transform: uppercase;
}
.status-badge.draft {
    background: var(--color-warning-hover, rgba(243, 156, 18, 0.15));
    color: var(--color-warning-text, #c87f0a);
}
.status-badge.submitted {
    background: rgba(52, 152, 219, 0.15);
    color: var(--color-primary-element, #2980b9);
}
.status-badge.approved {
    background: rgba(39, 174, 96, 0.15);
    color: var(--color-success, #27ae60);
}
.status-badge.rejected {
    background: rgba(192, 57, 43, 0.15);
    color: var(--color-error, #c0392b);
}
.decision-info {
    margin-top: 4px;
    font-size: 0.75em;
    color: var(--color-text-maxcontrast);
}
.actions-group {
    display: inline-flex;
    gap: 8px;
}
.edit-btn,
.delete-btn,
.approve-btn,
.reject-btn {
    color: #fff;
    border: none;
    padding: 7px 14px;
    border-radius: var(--border-radius, 6px);
    cursor: pointer;
    font-weight: 600;
}
.edit-btn {
    background: var(--color-primary-element, #2980b9);
}
.delete-btn,
.reject-btn {
    background: var(--color-error, #c0392b);
}
.approve-btn {
    background: var(--color-success, #27ae60);
}
.empty-state {
    text-align: center;
    padding: 50px;
    color: var(--color-text-maxcontrast);
}
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
