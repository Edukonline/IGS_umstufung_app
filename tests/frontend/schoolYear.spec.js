import { describe, it, expect } from 'vitest'
import { currentSchoolYear } from '../../src/utils/schoolYear.js'

describe('currentSchoolYear', () => {
    it('maps autumn (Aug–Dec) to the year that just started', () => {
        expect(currentSchoolYear(new Date('2025-09-15'))).toBe('2025/2026')
        expect(currentSchoolYear(new Date('2025-08-01'))).toBe('2025/2026')
        expect(currentSchoolYear(new Date('2025-12-31'))).toBe('2025/2026')
    })

    it('maps spring/summer (Jan–Jul) to the year that started last August', () => {
        expect(currentSchoolYear(new Date('2026-03-10'))).toBe('2025/2026')
        expect(currentSchoolYear(new Date('2026-07-31'))).toBe('2025/2026')
    })

    it('rolls over on the 1st of August', () => {
        expect(currentSchoolYear(new Date('2026-07-31'))).toBe('2025/2026')
        expect(currentSchoolYear(new Date('2026-08-01'))).toBe('2026/2027')
    })
})
