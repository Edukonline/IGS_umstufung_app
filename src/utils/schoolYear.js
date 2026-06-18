// Deutsches Schuljahr (1. August – 31. Juli) im Format "2025/2026".
// Spiegelt RequestService::schoolYearFor() im Backend.
export function currentSchoolYear(date = new Date()) {
    const year = date.getFullYear()
    // getMonth() ist 0-basiert: 7 = August.
    const start = date.getMonth() >= 7 ? year : year - 1
    return start + '/' + (start + 1)
}
