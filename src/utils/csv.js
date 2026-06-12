// CSV-Export mit Schutz gegen Formel-Injection und korrektem Quoting.
// Frühere Version verdoppelte Anführungszeichen nicht (zerbrach die Datei)
// und ließ aktive Formeln (=, +, -, @) ungeschützt durch.
const FORMULA_PREFIX = /^[=+\-@\t\r]/

function escapeCell(value) {
    let str = value === null || value === undefined ? '' : String(value)
    // Aktive Formel-Präfixe neutralisieren (Tabellenkalkulation-Injection).
    if (FORMULA_PREFIX.test(str)) {
        str = "'" + str
    }
    // Anführungszeichen verdoppeln, Wert quoten.
    return '"' + str.replace(/"/g, '""') + '"'
}

export function buildCsv(rows, separator = ';') {
    if (!rows.length) {
        return ''
    }
    const keys = Object.keys(rows[0])
    const lines = [
        keys.map(escapeCell).join(separator),
        ...rows.map((row) => keys.map((k) => escapeCell(row[k])).join(separator)),
    ]
    return lines.join('\r\n')
}

export function downloadCsv(filename, content) {
    const blob = new Blob(['﻿' + content], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const objectUrl = URL.createObjectURL(blob)
    link.setAttribute('href', objectUrl)
    link.setAttribute('download', filename)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(objectUrl)
}
