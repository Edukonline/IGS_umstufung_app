import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import NotificationBanner from '../../src/components/NotificationBanner.vue'

describe('NotificationBanner', () => {
    it('renders the message when shown', () => {
        const wrapper = mount(NotificationBanner, {
            props: { notification: { show: true, message: 'Gespeichert', type: 'success' } },
        })
        expect(wrapper.text()).toContain('Gespeichert')
        expect(wrapper.find('.notification-banner.success').exists()).toBe(true)
    })

    it('is empty when not shown', () => {
        const wrapper = mount(NotificationBanner, {
            props: { notification: { show: false, message: 'x', type: 'success' } },
        })
        expect(wrapper.find('.notification-banner').exists()).toBe(false)
    })

    it('emits close when the close button is clicked', async () => {
        const wrapper = mount(NotificationBanner, {
            props: { notification: { show: true, message: 'x', type: 'error' } },
        })
        await wrapper.find('.close-btn').trigger('click')
        expect(wrapper.emitted('close')).toHaveLength(1)
    })
})
