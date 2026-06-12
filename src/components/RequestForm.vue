<template>
    <div class="form-container">
        <h3 class="form-title">{{ form.id ? t('Entwurf bearbeiten') : t('Neuer Umstufungs-Entwurf') }}</h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ t('Schüler:in') }}</label>
                <input v-model="form.studentName" type="text" class="form-input" :placeholder="t('Vorname Nachname')">
            </div>
            <div class="form-group">
                <label class="form-label">{{ t('Klasse') }}</label>
                <select v-model="form.class" class="form-input">
                    <option value="">{{ t('Bitte wählen...') }}</option>
                    <option v-for="c in classes" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ t('Fach') }}</label>
                <select v-model="form.subject" class="form-input">
                    <option value="">{{ t('Bitte wählen...') }}</option>
                    <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ t('Von Niveau') }}</label>
                <select v-model="form.oldLevel" class="form-input" @change="syncLevels('from')">
                    <option v-for="l in levels" :key="l" :value="l">{{ l }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ t('Nach Niveau') }}</label>
                <select v-model="form.newLevel" class="form-input" @change="syncLevels('to')">
                    <option v-for="l in levels" :key="l" :value="l">{{ l }}</option>
                </select>
            </div>
            <div class="form-group full-width">
                <label class="form-label">{{ t('Begründung') }}</label>
                <textarea v-model="form.reason" class="form-input reason" :placeholder="t('Warum soll die Umstufung erfolgen?')"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="primary action-btn" :disabled="saving" @click="submit">
                {{ saving ? t('Speichert...') : (form.id ? t('Änderungen übernehmen') : t('Entwurf sichern')) }}
            </button>
            <button class="cancel-btn" @click="$emit('cancel')">{{ t('Abbrechen') }}</button>
        </div>
    </div>
</template>

<script>
import { t } from '../l10n.js'
import { DEFAULT_LEVELS } from '../constants.js'

function emptyForm(levels) {
    return {
        id: null,
        studentName: '',
        class: '',
        subject: '',
        oldLevel: levels[0],
        newLevel: levels[1] || levels[0],
        reason: '',
    }
}

export default {
    name: 'RequestForm',
    props: {
        request: { type: Object, default: null },
        subjects: { type: Array, default: () => [] },
        classes: { type: Array, default: () => [] },
        levels: { type: Array, default: () => DEFAULT_LEVELS },
        saving: { type: Boolean, default: false },
    },
    emits: ['save', 'cancel'],
    data() {
        return {
            form: this.buildForm(this.request),
        }
    },
    watch: {
        request(value) {
            this.form = this.buildForm(value)
        },
    },
    methods: {
        t,
        buildForm(request) {
            if (request) {
                return {
                    id: request.id,
                    studentName: request.studentName || '',
                    class: request.class || '',
                    subject: request.subject || '',
                    oldLevel: request.oldLevel || this.levels[0],
                    newLevel: request.newLevel || this.levels[1] || this.levels[0],
                    reason: request.reason || '',
                }
            }
            return emptyForm(this.levels)
        },
        syncLevels(origin) {
            if (this.levels.length !== 2) {
                return
            }
            const [a, b] = this.levels
            if (origin === 'from') {
                this.form.newLevel = this.form.oldLevel === a ? b : a
            } else {
                this.form.oldLevel = this.form.newLevel === a ? b : a
            }
        },
        submit() {
            this.$emit('save', { ...this.form })
        },
    },
}
</script>

<style scoped>
.form-container {
    background: var(--color-background-hover);
    padding: 30px;
    border-radius: var(--border-radius-large, 16px);
    margin-bottom: 40px;
    border: 1px solid var(--color-border);
}
.form-title {
    margin-top: 0;
    color: var(--color-primary-element, var(--color-primary));
    font-size: 1.4em;
    margin-bottom: 24px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 22px;
}
.form-group.full-width {
    grid-column: 1 / -1;
}
.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    font-size: 0.85em;
    text-transform: uppercase;
}
.form-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: var(--border-radius, 8px);
    background: var(--color-main-background);
    color: var(--color-main-text);
    border: 1px solid var(--color-border-dark, var(--color-border));
    font-size: 15px;
    line-height: 1.5;
}
.form-input:focus {
    border-color: var(--color-primary-element, var(--color-primary));
    outline: none;
}
.reason {
    min-height: 120px;
    resize: vertical;
}
select.form-input {
    height: 46px;
    cursor: pointer;
}
.form-actions {
    display: flex;
    gap: 14px;
    margin-top: 24px;
}
.action-btn {
    padding: 12px 30px;
    border-radius: var(--border-radius, 8px);
    font-size: 1.05em;
}
.cancel-btn {
    background: transparent;
    border: 1px solid var(--color-border-dark, var(--color-border));
    color: var(--color-text-maxcontrast);
    padding: 12px 22px;
    border-radius: var(--border-radius, 8px);
    cursor: pointer;
}
</style>
