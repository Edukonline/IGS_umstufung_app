// Zentrale Status-/Niveau-Konstanten — gespiegelt zu lib/Constants/*.php,
// damit Magic-Strings nicht mehr über das Frontend verstreut sind.
export const STATUS = Object.freeze({
    DRAFT: 'draft',
    SUBMITTED: 'submitted',
    APPROVED: 'approved',
    REJECTED: 'rejected',
})

export const DEFAULT_LEVELS = ['G-Kurs', 'E-Kurs']

export const STATUS_LABEL = Object.freeze({
    [STATUS.DRAFT]: 'Entwurf',
    [STATUS.SUBMITTED]: 'Eingereicht',
    [STATUS.APPROVED]: 'Genehmigt',
    [STATUS.REJECTED]: 'Abgelehnt',
})
