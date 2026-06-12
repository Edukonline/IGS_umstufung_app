// Leichtgewichtiger Übersetzungs-Helfer ohne zusätzliche Abhängigkeit.
// Nutzt zur Laufzeit die globale Nextcloud-Übersetzungsfunktion (window.t/n),
// damit alle Strings übersetzungsbereit sind, und fällt sonst sauber zurück.
const APP_ID = 'kursumstufung'

export function t(text, vars, count) {
    if (typeof window !== 'undefined' && typeof window.t === 'function') {
        return window.t(APP_ID, text, vars, count)
    }
    if (vars) {
        return Object.keys(vars).reduce(
            (acc, key) => acc.replace(new RegExp('\\{' + key + '\\}', 'g'), vars[key]),
            text,
        )
    }
    return text
}

export function n(singular, plural, count, vars) {
    if (typeof window !== 'undefined' && typeof window.n === 'function') {
        return window.n(APP_ID, singular, plural, count, vars)
    }
    return count === 1 ? t(singular, vars) : t(plural, vars)
}
