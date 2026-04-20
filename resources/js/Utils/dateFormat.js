export const MONTHS_ES_SHORT = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
export const MONTHS_ES_LONG  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
export const DOW_ES_SHORT    = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
export const DOW_ES_LONG     = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

export function fmtShort(d) {
    const x = new Date(d);
    return `${x.getDate()} ${MONTHS_ES_SHORT[x.getMonth()]} ${x.getFullYear()}`;
}

export function fmtLong(d) {
    const x = new Date(d);
    return `${DOW_ES_LONG[x.getDay()]} ${x.getDate()} de ${MONTHS_ES_LONG[x.getMonth()]}`;
}

export function sameDay(a, b) {
    const x = new Date(a), y = new Date(b);
    return x.getFullYear() === y.getFullYear() && x.getMonth() === y.getMonth() && x.getDate() === y.getDate();
}

export function daysBetween(a, b) {
    const x = new Date(a); x.setHours(0, 0, 0, 0);
    const y = new Date(b); y.setHours(0, 0, 0, 0);
    return Math.round((x - y) / 86400000);
}

export function relativeDate(d, now = new Date()) {
    const n = daysBetween(d, now);
    if (n === 0) return 'hoy';
    if (n === 1) return 'mañana';
    if (n === -1) return 'ayer';
    if (n > 1 && n < 7) return `en ${n} días`;
    if (n < -1 && n > -7) return `hace ${Math.abs(n)} días`;
    if (n >= 7 && n < 30) return `en ${Math.floor(n / 7)} sem`;
    if (n <= -7 && n > -30) return `hace ${Math.floor(Math.abs(n) / 7)} sem`;
    if (n >= 30) {
        const m = Math.floor(n / 30);
        return `en ${m} ${m === 1 ? 'mes' : 'meses'}`;
    }
    const m = Math.floor(Math.abs(n) / 30);
    return `hace ${m} ${m === 1 ? 'mes' : 'meses'}`;
}
