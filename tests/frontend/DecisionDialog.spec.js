import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import DecisionDialog from '../../src/components/DecisionDialog.vue'

const request = { studentName: 'Max Mustermann', subject: 'Mathematik' }

function mountDialog(value, props = {}) {
    return mount(DecisionDialog, {
        attachTo: document.body,
        props: { request, value, ...props },
    })
}

describe('DecisionDialog', () => {
    it('renders an accessible dialog with the student context', () => {
        const wrapper = mountDialog('approved')
        const modal = wrapper.find('[role="dialog"]')
        expect(modal.exists()).toBe(true)
        expect(modal.attributes('aria-modal')).toBe('true')
        expect(wrapper.text()).toContain('Max Mustermann')
        expect(wrapper.text()).toContain('Antrag genehmigen')
    })

    it('allows confirming an approval with an empty comment', async () => {
        const wrapper = mountDialog('approved')
        const confirm = wrapper.find('button.primary')
        expect(confirm.element.disabled).toBe(false)
        await confirm.trigger('click')
        expect(wrapper.emitted('confirm')).toEqual([['']])
    })

    it('blocks a rejection until a reason is given', async () => {
        const wrapper = mountDialog('rejected')
        const confirm = wrapper.find('button.primary')
        expect(confirm.element.disabled).toBe(true)

        await wrapper.find('textarea').setValue('Leistung reicht nicht')
        expect(confirm.element.disabled).toBe(false)
        await confirm.trigger('click')
        expect(wrapper.emitted('confirm')).toEqual([['Leistung reicht nicht']])
    })

    it('emits cancel on the cancel button and on Escape', async () => {
        const wrapper = mountDialog('approved')
        await wrapper.find('.cancel-btn').trigger('click')
        await wrapper.find('[role="dialog"]').trigger('keydown', { key: 'Escape' })
        expect(wrapper.emitted('cancel')).toHaveLength(2)
    })

    it('emits cancel when the overlay backdrop is clicked', async () => {
        const wrapper = mountDialog('approved')
        await wrapper.find('.modal-overlay').trigger('click')
        expect(wrapper.emitted('cancel')).toHaveLength(1)
    })
})
