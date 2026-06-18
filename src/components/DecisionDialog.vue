<template>
    <div class="modal-overlay" @click.self="$emit('cancel')">
        <div ref="modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="decision-title" @keydown="onKeydown">
            <h3 id="decision-title">{{ isApprove ? t('Antrag genehmigen') : t('Antrag ablehnen') }}</h3>
            <p class="modal-sub">{{ request.studentName }} · {{ request.subject }}</p>
            <label class="form-label" for="decision-reason">{{ isReject ? t('Begründung (erforderlich)') : t('Kommentar (optional)') }}</label>
            <textarea id="decision-reason" ref="input" v-model="reason" class="modal-input" rows="4"></textarea>
            <div class="modal-actions">
                <button
                    :class="['primary', isReject ? 'danger' : '']"
                    :disabled="deciding || (isReject && !reason.trim())"
                    @click="$emit('confirm', reason)">
                    {{ deciding ? t('Speichert...') : (isApprove ? t('Genehmigen') : t('Ablehnen')) }}
                </button>
                <button class="cancel-btn" @click="$emit('cancel')">{{ t('Abbrechen') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { t } from '../l10n.js'
import { STATUS } from '../constants.js'

export default {
    name: 'DecisionDialog',
    props: {
        request: { type: Object, required: true },
        value: { type: String, required: true },
        deciding: { type: Boolean, default: false },
    },
    emits: ['confirm', 'cancel'],
    data() {
        return { reason: '', trigger: null }
    },
    computed: {
        isApprove() {
            return this.value === STATUS.APPROVED
        },
        isReject() {
            return this.value === STATUS.REJECTED
        },
    },
    mounted() {
        // Fokus in den Dialog holen und beim Schließen zum Auslöser zurückgeben.
        this.trigger = document.activeElement
        this.$nextTick(() => {
            if (this.$refs.input) {
                this.$refs.input.focus()
            }
        })
    },
    beforeUnmount() {
        if (this.trigger && typeof this.trigger.focus === 'function') {
            this.trigger.focus()
        }
    },
    methods: {
        t,
        onKeydown(e) {
            if (e.key === 'Escape') {
                this.$emit('cancel')
                return
            }
            if (e.key !== 'Tab') {
                return
            }
            const focusables = this.$refs.modal.querySelectorAll(
                'button, textarea, input, select, [href], [tabindex]:not([tabindex="-1"])',
            )
            if (!focusables.length) {
                return
            }
            const first = focusables[0]
            const last = focusables[focusables.length - 1]
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault()
                last.focus()
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault()
                first.focus()
            }
        },
    },
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}
.modal {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large, 12px);
    padding: 28px;
    width: min(480px, 90vw);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}
.modal h3 {
    margin: 0 0 4px;
}
.modal-sub {
    margin: 0 0 16px;
    color: var(--color-text-maxcontrast);
}
.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 0.85em;
    color: var(--color-text-maxcontrast);
}
.modal-input {
    width: 100%;
    padding: 10px;
    border-radius: var(--border-radius, 8px);
    background: var(--color-main-background);
    border: 1px solid var(--color-border-dark, var(--color-border));
    color: var(--color-main-text);
    resize: vertical;
}
.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}
.primary.danger {
    background: var(--color-error, #c0392b);
}
.cancel-btn {
    background: transparent;
    border: 1px solid var(--color-border-dark, var(--color-border));
    color: var(--color-text-maxcontrast);
    padding: 10px 20px;
    border-radius: var(--border-radius, 8px);
    cursor: pointer;
}
</style>
