<template>
    <transition name="fade">
        <div v-if="notification.show" :class="['notification-banner', notification.type]" role="alert">
            <span class="icon" aria-hidden="true">{{ notification.type === 'success' ? '✅' : '⚠️' }}</span>
            <span class="message">{{ notification.message }}</span>
            <button v-if="notification.action" type="button" class="action-btn" @click="$emit('action')">{{ notification.action.label }}</button>
            <button type="button" class="close-btn" :aria-label="t('Schließen')" @click="$emit('close')">&times;</button>
        </div>
    </transition>
</template>

<script>
import { t } from '../l10n.js'

export default {
    name: 'NotificationBanner',
    props: {
        notification: {
            type: Object,
            required: true,
        },
    },
    emits: ['close', 'action'],
    methods: { t },
}
</script>

<style scoped>
.notification-banner {
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
.notification-banner.success {
    background: var(--color-success, #27ae60);
    color: var(--color-primary-text, #fff);
}
.notification-banner.error {
    background: var(--color-error, #c0392b);
    color: var(--color-primary-text, #fff);
}
.notification-banner .message {
    flex-grow: 1;
    font-weight: 600;
}
.action-btn {
    background: rgba(255, 255, 255, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.6);
    color: inherit;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: var(--border-radius, 6px);
    cursor: pointer;
}
.close-btn {
    background: transparent;
    border: none;
    color: inherit;
    font-size: 1.5em;
    cursor: pointer;
    opacity: 0.8;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
