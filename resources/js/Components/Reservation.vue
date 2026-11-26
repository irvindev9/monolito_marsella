<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import {
    MONTHS_ES_SHORT, DOW_ES_SHORT,
    fmtShort, fmtLong, sameDay, daysBetween, relativeDate,
} from '@/Utils/dateFormat';
import { statusOf, contractOf, paymentInfo } from '@/Utils/reservation';

const emit = defineEmits(['refresh']);

const user = usePage().props.auth.user;

const myReservations = ref([]);
const paymentConfig = ref({ mdtpr: 7, bank: '', clabe: '', beneficiary: '', fee: '' });
const passwordsByReservation = ref({});
const filter = ref('upcoming');
const confirmTarget = ref(null);
const transferTarget = ref(null);
const now = ref(new Date());

let nowTimer = null;

const FILTERS = [
    { key: 'upcoming', label: 'Próximas' },
    { key: 'rejected', label: 'Rechazadas' },
    { key: 'history',  label: 'Historial' },
];

const daysToPay = computed(() => paymentConfig.value.mdtpr || 7);

const hasPaymentConfig = computed(() => {
    const { bank, clabe, beneficiary } = paymentConfig.value;
    return Boolean(bank?.trim() && clabe?.trim() && beneficiary?.trim());
});

function payment(r) {
    return paymentInfo(r, now.value, daysToPay.value);
}

function transferConcept(r) {
    const d = new Date(r.reservation_date);
    const mo = MONTHS_ES_SHORT[d.getMonth()];
    const moCap = mo.charAt(0).toUpperCase() + mo.slice(1);
    const street = r.house?.street?.name ?? '';
    const num = r.house?.house_number ?? '';
    return `${d.getDate()} ${moCap} ${street} ${num}`.trim().replace(/\s+/g, ' ');
}

async function getReservations() {
    const { data } = await axios.get(`/api/reservations/${user.house_id}`);
    myReservations.value = data;
    await fetchPasswordsForUpcoming();
}

async function fetchPasswordsForUpcoming() {
    const targets = myReservations.value.filter(r =>
        r.is_approved === 1 && daysBetween(r.reservation_date, now.value) >= 0
    );
    const next = {};
    await Promise.all(targets.map(async (r) => {
        try {
            const { data } = await axios.get(`/api/passwords/public/${r.id}`);
            next[r.id] = data;
        } catch (e) {
            next[r.id] = [];
        }
    }));
    passwordsByReservation.value = next;
}

async function getPaymentConfig() {
    try {
        const { data } = await axios.get('/api/configs/payment');
        paymentConfig.value = data;
    } catch (error) {
        // silent
    }
}

async function performDelete(id) {
    const { data } = await axios.delete(`/api/reservations/${id}`);
    await getReservations();
    emit('refresh');
    toast.success(data.message);
}

function onArchiveClick(r) {
    confirmTarget.value = r;
}

async function doConfirm() {
    const r = confirmTarget.value;
    if (!r) return;
    confirmTarget.value = null;
    await performDelete(r.id);
}

async function copyValue(v) {
    try {
        await navigator.clipboard.writeText(v);
        toast.success(`Copiado: ${v}`);
    } catch (e) {
        toast.error('No se pudo copiar');
    }
}

const counts = computed(() => {
    const c = { upcoming: 0, rejected: 0, history: myReservations.value.length };
    myReservations.value.forEach(r => {
        const future = daysBetween(r.reservation_date, now.value) >= 0;
        if (future && (r.is_approved === 0 || r.is_approved === 1)) c.upcoming++;
        if (r.is_approved === 2) c.rejected++;
    });
    return c;
});

const filtered = computed(() => {
    const list = myReservations.value.filter(r => {
        const future = daysBetween(r.reservation_date, now.value) >= 0;
        if (filter.value === 'upcoming') return future && (r.is_approved === 0 || r.is_approved === 1);
        if (filter.value === 'rejected') return r.is_approved === 2;
        if (filter.value === 'history')  return true;
        return true;
    });
    return [...list].sort((a, b) => new Date(b.reservation_date) - new Date(a.reservation_date));
});

function showAccessFor(r) {
    return r.is_approved === 1 && daysBetween(r.reservation_date, now.value) >= 0;
}
function isRevealed(r) {
    return sameDay(r.reservation_date, now.value);
}

onMounted(async () => {
    await getPaymentConfig();
    await getReservations();
    nowTimer = setInterval(() => { now.value = new Date(); }, 1000);
});

onBeforeUnmount(() => {
    if (nowTimer) clearInterval(nowTimer);
});
</script>

<template>
    <div class="mrs-root">
        <div class="mrs-page-head">
            <h2>Mis reservas</h2>
            <div class="mrs-summary" v-if="counts.upcoming > 0">
                <b>{{ counts.upcoming }}</b> {{ counts.upcoming === 1 ? 'próxima' : 'próximas' }}
            </div>
            <div class="mrs-summary" v-else>Sin reservas próximas</div>
        </div>

        <div class="mrs-toolbar">
            <div class="mrs-tabs">
                <button v-for="f in FILTERS" :key="f.key"
                    :aria-pressed="filter === f.key"
                    @click="filter = f.key">
                    {{ f.label }}
                    <span class="mrs-count">{{ counts[f.key] }}</span>
                </button>
            </div>
        </div>

        <div class="mrs-rv-list" v-if="filtered.length">
            <div v-for="r in filtered" :key="r.id" class="mrs-rv-row">
                <div class="mrs-rv-date" aria-hidden="true">
                    <div class="mo">{{ MONTHS_ES_SHORT[new Date(r.reservation_date).getMonth()] }}</div>
                    <div class="dd">{{ new Date(r.reservation_date).getDate() }}</div>
                    <div class="dow">{{ DOW_ES_SHORT[new Date(r.reservation_date).getDay()] }}</div>
                </div>

                <div class="mrs-rv-main">
                    <div class="mrs-rv-title">
                        <span>{{ fmtLong(r.reservation_date) }}</span>
                        <span class="mrs-rel">{{ relativeDate(r.reservation_date) }}</span>
                        <template v-if="r.is_approved === 0">
                            <span v-if="payment(r) && !payment(r).expired"
                                :class="['mrs-inline-warn', { hot: payment(r).hot }]">
                                <span class="dot"></span>
                                <span>{{ payment(r).shortText }}</span>
                            </span>
                            <span v-else-if="payment(r) && payment(r).expired" class="mrs-inline-warn expired">
                                <span class="dot"></span>pago expirado
                            </span>
                        </template>
                    </div>
                    <div class="mrs-rv-sub">
                        {{ r.house.street.name }} #{{ r.house.house_number }}
                        <span class="sep">·</span>{{ r.user ? r.user.name : 'Registrado por comité' }}
                        <span class="sep">·</span>Solicitado {{ fmtShort(r.created_at) }}
                    </div>

                    <div v-if="showAccessFor(r)" class="mrs-rv-accesos">
                        <div class="mrs-access-box">
                            <div class="title">
                                <svg v-if="isRevealed(r)" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="8" cy="15" r="4"/><path d="M10.5 12.5L20 3M16 7l3 3"/>
                                </svg>
                                <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/>
                                </svg>
                                <span>{{ isRevealed(r) ? 'Accesos disponibles hoy' : 'Accesos protegidos — disponibles el ' + fmtShort(r.reservation_date) }}</span>
                            </div>
                            <div class="mrs-access-grid" v-if="isRevealed(r) && (passwordsByReservation[r.id] || []).length">
                                <div v-for="p in passwordsByReservation[r.id]" :key="p.id" class="mrs-access-item">
                                    <div class="t">{{ p.title }}</div>
                                    <div class="p">
                                        <span>{{ p.password }}</span>
                                        <button class="mrs-copy" @click.stop="copyValue(p.password)" title="Copiar">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <span :class="['mrs-status-pill', statusOf(r).cls]">{{ statusOf(r).label }}</span>

                <span v-if="r.is_approved === 1" :class="['mrs-status-pill', r.cleaning_supervised ? 'mrs-status-approved' : 'mrs-status-pending']">
                    {{ r.cleaning_supervised ? 'Limpieza supervisada' : 'Pendiente supervisión' }}
                </span>

                <div class="mrs-rv-meta-col">
                    <span style="font-variant-numeric: tabular-nums;">#MRS-{{ String(r.id).padStart(5, '0') }}</span>
                    <span v-if="r.is_approved === 1 || r.is_approved === 0"
                        :class="['mrs-contract-line', { signed: contractOf(r).signed }]">
                        <svg v-if="contractOf(r).signed" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12l5 5L20 7"/>
                        </svg>
                        <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/>
                            <path d="M14 3v5h5"/>
                        </svg>
                        {{ contractOf(r).label }}
                    </span>
                </div>

                <div v-if="filter !== 'history' && r.is_approved === 0" class="mrs-rv-actions">
                    <button v-if="hasPaymentConfig && payment(r) && !payment(r).expired" class="mrs-btn mrs-btn-sm mrs-btn-primary" @click.stop="transferTarget = r">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 10h18M5 6l7-3 7 3M4 10v8M9 10v8M15 10v8M20 10v8M3 18h18"/>
                        </svg>
                        Transferencia
                    </button>
                    <button class="mrs-btn mrs-btn-sm mrs-btn-danger" @click.stop="onArchiveClick(r)">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13a1 1 0 001 1h8a1 1 0 001-1l1-13M9 7V4h6v3"/>
                        </svg>
                        Cancelar solicitud
                    </button>
                </div>
                <button v-else-if="filter !== 'history' && r.is_approved === 1" class="mrs-btn mrs-btn-sm mrs-btn-ghost" @click.stop="onArchiveClick(r)">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="4" rx="1"/>
                        <path d="M5 8v11a1 1 0 001 1h12a1 1 0 001-1V8"/>
                    </svg>
                    Archivar
                </button>
                <div v-else class="mrs-action-placeholder"></div>
            </div>
        </div>
        <div v-else class="mrs-empty">
            <h3>Sin reservas por aquí</h3>
            <p>No hay reservas en esta vista. Prueba con otro filtro.</p>
        </div>

        <transition name="mrs-fade">
            <div v-if="transferTarget" class="mrs-backdrop" @click="transferTarget = null">
                <div class="mrs-dialog" @click.stop>
                    <h3>Pago por transferencia</h3>
                    <p>Único método de pago permitido. Realiza tu transferencia con los siguientes datos:</p>

                    <img
                        v-if="paymentConfig.bank === 'BBVA'"
                        src="/images/bbva.png"
                        alt="BBVA"
                        class="mrs-bank-logo"
                    />

                    <div class="mrs-transfer-list">
                        <div class="mrs-transfer-item">
                            <span class="lbl">Banco</span>
                            <span class="val">{{ paymentConfig.bank || 'No configurado' }}</span>
                        </div>
                        <div class="mrs-transfer-item">
                            <span class="lbl">CLABE interbancaria</span>
                            <span class="val tabular">
                                {{ paymentConfig.clabe || 'No configurada' }}
                                <button v-if="paymentConfig.clabe" class="mrs-copy" @click="copyValue(paymentConfig.clabe)" title="Copiar">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/>
                                    </svg>
                                </button>
                            </span>
                        </div>
                        <div class="mrs-transfer-item">
                            <span class="lbl">Nombre de beneficiario</span>
                            <span class="val">
                                {{ paymentConfig.beneficiary || 'No configurado' }}
                                <button v-if="paymentConfig.beneficiary" class="mrs-copy" @click="copyValue(paymentConfig.beneficiary)" title="Copiar">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/>
                                    </svg>
                                </button>
                            </span>
                        </div>
                        <div class="mrs-transfer-item">
                            <span class="lbl">Cuota</span>
                            <span class="val">{{ paymentConfig.fee || 'No configurada' }}</span>
                        </div>
                        <div class="mrs-transfer-item highlight">
                            <span class="lbl">Concepto</span>
                            <span class="val">
                                {{ transferConcept(transferTarget) }}
                                <button class="mrs-copy" @click="copyValue(transferConcept(transferTarget))" title="Copiar">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/>
                                    </svg>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="actions">
                        <button class="mrs-btn mrs-btn-sm" @click="transferTarget = null">Cerrar</button>
                    </div>
                </div>
            </div>
        </transition>

        <transition name="mrs-fade">
            <div v-if="confirmTarget" class="mrs-backdrop" @click="confirmTarget = null">
                <div class="mrs-dialog" @click.stop>
                    <h3>{{ confirmTarget.is_approved === 0 ? 'Cancelar solicitud' : 'Archivar reservación' }}</h3>
                    <p v-if="confirmTarget.is_approved === 0">
                        Vas a cancelar tu solicitud del {{ fmtLong(confirmTarget.reservation_date) }}. Esta acción no se puede deshacer.
                    </p>
                    <p v-else>
                        Vas a archivar la reservación del {{ fmtLong(confirmTarget.reservation_date) }}. Dejará de aparecer en tu lista.
                    </p>
                    <div class="actions">
                        <button class="mrs-btn mrs-btn-ghost mrs-btn-sm" @click="confirmTarget = null">Cancelar</button>
                        <button class="mrs-btn mrs-btn-sm mrs-btn-danger" @click="doConfirm">
                            {{ confirmTarget.is_approved === 0 ? 'Sí, cancelar' : 'Sí, archivar' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped src="./Reservation.css"></style>
