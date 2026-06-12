// Einzige Stelle, an der HTTP-Aufrufe ans Backend passieren.
// Trennt die Datenbeschaffung von der Präsentationslogik der Komponenten.
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const url = (path) => generateUrl('/apps/kursumstufung' + path)

export default {
    list(schoolYear) {
        const params = schoolYear ? { schoolYear } : {}
        return axios.get(url('/api/requests'), { params }).then((r) => r.data)
    },
    create(payload) {
        return axios.post(url('/api/requests'), payload).then((r) => r.data)
    },
    update(id, payload) {
        return axios.put(url(`/api/requests/${id}`), payload).then((r) => r.data)
    },
    remove(id) {
        return axios.delete(url(`/api/requests/${id}`)).then((r) => r.data)
    },
    submitAll() {
        return axios.post(url('/api/submit_all')).then((r) => r.data)
    },
    decide(id, decision, decisionReason) {
        return axios.post(url(`/api/requests/${id}/decide`), { decision, decisionReason }).then((r) => r.data)
    },
}
