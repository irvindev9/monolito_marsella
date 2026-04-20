export function statusOf(reservation) {
    if (reservation.is_approved === 0) return { key: 'pending',   label: 'En espera',  cls: 'mrs-status-pending' };
    if (reservation.is_approved === 1) return { key: 'approved',  label: 'Confirmada', cls: 'mrs-status-approved' };
    if (reservation.is_approved === 2) return { key: 'rejected',  label: 'Rechazada',  cls: 'mrs-status-rejected' };
    return                                    { key: 'cancelled', label: 'Cancelada',  cls: 'mrs-status-cancelled' };
}

export function contractOf(reservation) {
    if (reservation.is_signed == 1) return { label: 'Contrato entregado',   signed: true };
    if (reservation.is_signed == 2) return { label: 'Firmado digitalmente', signed: true };
    return                                 { label: 'Contrato pendiente',   signed: false };
}

export function paymentInfo(reservation, now, daysToPay) {
    if (reservation.is_approved !== 0) return null;
    const created = new Date(reservation.created_at);
    const deadline = new Date(created);
    deadline.setDate(deadline.getDate() + daysToPay);
    const total = deadline - created;
    const diff = deadline - now;
    if (diff <= 0) {
        return { expired: true, hot: false, shortText: 'pago expirado', progress: 100 };
    }
    const elapsed = now - created;
    const progress = Math.max(0, Math.min(100, (elapsed / total) * 100));
    const s = Math.floor(diff / 1000);
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor((s % 3600) / 60);
    const shortText = d >= 1 ? `${d}d ${h}h para pagar` : `${h}h ${m}m para pagar`;
    const hot = (diff / total) <= 0.4;
    return { expired: false, hot, shortText, progress };
}
