import { describe, it, expect } from 'vitest'
import { buildCsv } from '../../src/utils/csv.js'

describe('buildCsv', () => {
    it('returns empty string for no rows', () => {
        expect(buildCsv([])).toBe('')
    })

    it('quotes header and values, joins with separator and CRLF', () => {
        const csv = buildCsv([{ Name: 'Max', Klasse: '5a' }])
        expect(csv).toBe('"Name";"Klasse"\r\n"Max";"5a"')
    })

    it('honours a custom separator', () => {
        const csv = buildCsv([{ a: '1', b: '2' }], ',')
        expect(csv).toBe('"a","b"\r\n"1","2"')
    })

    it('doubles embedded quotes (no broken file)', () => {
        const csv = buildCsv([{ x: 'he"llo' }])
        expect(csv).toBe('"x"\r\n"he""llo"')
    })

    it('keeps the separator safe inside a value', () => {
        const csv = buildCsv([{ x: 'a;b' }])
        expect(csv).toBe('"x"\r\n"a;b"')
    })

    it('renders null/undefined as empty quoted cell', () => {
        expect(buildCsv([{ x: null }])).toBe('"x"\r\n""')
        expect(buildCsv([{ x: undefined }])).toBe('"x"\r\n""')
    })

    describe('formula injection protection', () => {
        it.each([
            ['=HYPERLINK("http://evil")', '"\'=HYPERLINK(""http://evil"")"'],
            ['+1+1', '"\'+1+1"'],
            ['-2+3', '"\'-2+3"'],
            ['@cmd', '"\'@cmd"'],
        ])('neutralises a leading formula char: %s', (input, expectedCell) => {
            const csv = buildCsv([{ x: input }])
            expect(csv).toBe('"x"\r\n' + expectedCell)
        })

        it('does not prefix safe values', () => {
            const csv = buildCsv([{ x: 'Mathematik' }])
            expect(csv).toBe('"x"\r\n"Mathematik"')
        })
    })
})
