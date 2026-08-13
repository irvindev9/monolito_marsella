<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LoadingScreen from '@/Components/LoadingScreen.vue';
import { Head } from '@inertiajs/vue3';
import { DatePicker } from 'v-calendar';
import { ref, computed, onMounted } from 'vue';
import { format, isSameDay } from 'date-fns';
import { toast } from 'vue3-toastify';
import Reservation from '@/Components/Reservation.vue';
import { ModalsContainer, useModal } from 'vue-final-modal'
import ModalTerms from '@/Modals/Terms.vue';

import { fmtLong } from '@/Utils/dateFormat';

const isLoading = ref(false);
const reservations = ref([]);

const attrs = ref([]);
const pickDate = ref(new Date());

// Cleaning data
const cleaningData = ref({ host: null, reviewers: [] });
const reviewersList = ref([]);
const submittingReport = ref(false);

const isDateTaken = computed(() => {
    if (!pickDate.value) return false;
    return reservations.value.some((r) =>
        (r.is_approved === 1 || r.is_approved === 0) &&
        isSameDay(new Date(r.reservation_date), pickDate.value)
    );
});

async function reserveDate() {
    isLoading.value = true;
    try {
        const { data } = await axios.post('/api/reservations', {
            reservation_date: pickDate.value.toDateString(),
            acceptTerms: 2,
        })
        toast.success(`${data.message} - Fecha reservada: ${data.reservation_date}`);
        await getReservations();
        isLoading.value = false;
    } catch (error) {
        console.error(error.response.data);
        toast.error(`Error: ${error.response.data[0]}`)
        isLoading.value = false;
    }
};

const { open, close } = useModal({
    component: ModalTerms,
    attrs: {
        async onConfirm() {
            await reserveDate();
            close();
        },
        onClose() {
            close()
        },
        selectedDate: pickDate,
    },
})

async function getReservations() {
    try {
        const { data } = await axios.get('/api/reservations');
        reservations.value = data;
        updateCalendarAttrs();
    } catch (error) {
        console.log(error);
    }
}

async function getCleaningData() {
    try {
        const { data } = await axios.get('/api/cleaning/dashboard');
        cleaningData.value = data;
        if (data.reviewers && Array.isArray(data.reviewers)) {
            reviewersList.value = data.reviewers.map(r => ({
                ...r,
                isOpen: false,
                responses: (r.criteria || []).map(c => ({
                    cleaning_criteria_id: c.id,
                    name: c.name,
                    response_type: c.response_type,
                    response: c.response_type === 'boolean' ? '0' : '',
                })),
            }));
        }
    } catch (error) {
        console.log(error);
    }
}

function toggleAccordion(idx) {
    reviewersList.value.forEach((item, i) => {
        item.isOpen = i === idx ? !item.isOpen : false;
    });
}

async function submitCleaningReport(item) {
    if (!item || !item.event_passed || item.already_reported) return;
    submittingReport.value = true;
    try {
        const { data } = await axios.post('/api/cleaning/report', {
            reservation_id: item.previous_reservation_id,
            responses: item.responses.map(r => ({
                cleaning_criteria_id: r.cleaning_criteria_id,
                response: String(r.response),
            })),
        });
        toast.success(data.message);
        await getCleaningData();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Error al enviar reporte');
    }
    submittingReport.value = false;
}

function showPrivacyInfo() {
    toast.info('Los datos del vecino se mostrarán 7 días antes de tu evento');
}

onMounted(async () => {
    isLoading.value = true;
    await getReservations();
    await getCleaningData();
    isLoading.value = false;
});

function updateCalendarAttrs() {
    const newAttrs = [];
    reservations.value.forEach((reservation) => {
        if (reservation.is_approved === 1) {
            newAttrs.push({
                key: 'today',
                highlight: {
                    color: 'blue',
                    fillMode: 'light',
                    contentClass: 'italic',
                },
                dates: new Date(reservation.reservation_date),
                description: 'Reservado',
                popover: true,
            });
        }

        if (reservation.is_approved === 0) {
            newAttrs.push({
                key: 'holiday',
                dot: 'blue',
                dates: [
                    new Date(reservation.reservation_date),
                ],
                description: 'Reservas en espera',
                popover: true,
            });
        }

    });
    attrs.value = newAttrs;
}

</script>

<template>
    <Head title="Terraza" />

    <AuthenticatedLayout>
        <div class="mrs-root">
            <div class="mrs-container">
                <div class="mrs-page-head">
                    <h2>Reservar fecha</h2>
                </div>

                <!-- HOST BANNER: shown during event + 24hrs -->
                <div v-if="cleaningData.host" class="mrs-card mrs-cleaning-banner mrs-cleaning-host">
                    <div class="mrs-cleaning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div class="mrs-cleaning-content">
                        <div class="mrs-cleaning-title">Reporte de limpieza</div>
                        <p>Para disfrutar de una mejor experiencia de nuestras áreas comunes comunícate con el vecino próximo a usar el inmueble <strong>{{ cleaningData.host.next_user_name }}</strong> para coordinar la entrega y revisión de la limpieza.</p>
                        <p v-if="cleaningData.host.next_user_phone" class="mrs-cleaning-phone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            Teléfono/WhatsApp: <strong>{{ cleaningData.host.next_user_phone }}</strong>
                        </p>
                        <div class="mt-2">
                            <div class="mrs-cleaning-status" :class="cleaningData.host.report_completed ? 'completed' : 'pending'">
                                <span class="mrs-cleaning-status-dot"></span>
                                {{ cleaningData.host.report_completed ? 'Reporte completado' : 'Pendiente de reportar limpieza' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REVIEWER BANNERS (ACCORDION) -->
                <div v-if="reviewersList.length > 0" class="mrs-cleaning-accordion-container mb-4">
                    <div v-for="(item, idx) in reviewersList" :key="item.my_reservation_id" class="mrs-card mrs-cleaning-banner mrs-cleaning-reviewer mrs-accordion-item">
                        <!-- Accordion Header -->
                        <div class="mrs-accordion-header" @click="toggleAccordion(idx)">
                            <div class="mrs-accordion-header-title">
                                <div class="mrs-cleaning-icon reviewer">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                                    </svg>
                                </div>
                                <span class="font-bold text-sm">Supervisión de limpieza de tu evento ({{ fmtLong(item.my_reservation_date) }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="item.already_reported" class="mrs-cleaning-status completed">
                                    <span class="mrs-cleaning-status-dot"></span> Reportado
                                </span>
                                <span v-else-if="!item.event_passed" class="mrs-cleaning-status pending">
                                    <span class="mrs-cleaning-status-dot"></span> En espera del evento
                                </span>
                                <span v-else class="mrs-cleaning-status pending">
                                    <span class="mrs-cleaning-status-dot"></span> Pendiente
                                </span>
                                <svg class="mrs-accordion-arrow" :class="{ 'is-open': item.isOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Accordion Body -->
                        <div v-show="item.isOpen" class="mrs-accordion-body">
                            <div class="mrs-cleaning-content w-full">
                                <p>Tu próximo evento se acerca, para tener una mejor experiencia te pedimos de favor que nos ayudes a supervisar la limpieza del inmueble del evento anterior.</p>
                                <p class="mt-1 font-semibold text-gray-700">Fecha del evento anterior prevista: {{ fmtLong(item.previous_reservation_date) }}</p>
                                
                                <div class="my-2 p-3 bg-white border rounded">
                                    <p class="mrs-cleaning-host-info">
                                        Vecino anfitrión del evento anterior: 
                                        <strong v-if="item.is_near">{{ item.host_name }}</strong>
                                        <span v-else class="mrs-privacy-masked">
                                            *** *** 
                                            <span class="mrs-privacy-tooltip" @click.stop="showPrivacyInfo" title="Los datos del vecino se mostrarán 7 días antes de tu evento">
                                                <svg class="inline-block w-4 h-4 text-gray-400 hover:text-gray-600 cursor-pointer ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            </span>
                                        </span>
                                    </p>
                                    <p class="mrs-cleaning-phone mt-1">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                        Teléfono/WhatsApp: 
                                        <strong v-if="item.is_near && item.host_phone">{{ item.host_phone }}</strong>
                                        <span v-else-if="!item.is_near" class="mrs-privacy-masked">
                                            *** *** ****
                                            <span class="mrs-privacy-tooltip" @click.stop="showPrivacyInfo" title="Los datos del vecino se mostrarán 7 días antes de tu evento">
                                                <svg class="inline-block w-4 h-4 text-gray-400 hover:text-gray-600 cursor-pointer ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            </span>
                                        </span>
                                        <span v-else class="text-gray-400">Sin teléfono</span>
                                    </p>
                                </div>

                                <div v-if="item.already_reported" class="mrs-cleaning-completed-msg">
                                    <p class="text-sm font-semibold text-green-700 mt-2">✓ Reporte de limpieza completado para este evento.</p>
                                </div>
                                <div v-else class="mrs-cleaning-checklist">
                                    <div class="mrs-cleaning-checklist-title">Criterios a revisar:</div>
                                    <div v-for="resp in item.responses" :key="resp.cleaning_criteria_id" class="mrs-cleaning-criteria-item">
                                        <template v-if="resp.response_type === 'boolean'">
                                            <label class="mrs-cleaning-checkbox">
                                                <input type="checkbox" :disabled="!item.event_passed" :checked="resp.response === '1'" @change="resp.response = $event.target.checked ? '1' : '0'" />
                                                <span>{{ resp.name }}</span>
                                            </label>
                                        </template>
                                        <template v-else-if="resp.response_type === 'number'">
                                            <label class="mrs-cleaning-field">
                                                <span>{{ resp.name }}</span>
                                                <input type="number" :disabled="!item.event_passed" v-model="resp.response" class="mrs-cleaning-input" />
                                            </label>
                                        </template>
                                        <template v-else>
                                            <label class="mrs-cleaning-field">
                                                <span>{{ resp.name }}</span>
                                                <input type="text" :disabled="!item.event_passed" v-model="resp.response" class="mrs-cleaning-input" />
                                            </label>
                                        </template>
                                    </div>
                                    <button 
                                        class="mrs-btn mrs-btn-primary mrs-cleaning-submit" 
                                        :disabled="!item.event_passed || submittingReport" 
                                        @click="submitCleaningReport(item)"
                                    >
                                        {{ submittingReport ? 'Enviando...' : 'Enviar reporte' }}
                                    </button>
                                    <p v-if="!item.event_passed" class="mrs-cleaning-lock-msg mt-2 text-xs text-amber-700 flex items-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="inline mr-1 flex-shrink-0"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        El formulario se abrirá cuando el evento anterior haya pasado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mrs-card mrs-legend">
                    <div class="mrs-legend-title">Representación gráfica de las reservas</div>
                    <div class="mrs-legend-items">
                        <div class="mrs-legend-item">
                            <span class="mrs-legend-dot approved"></span> Reservado
                        </div>
                        <div class="mrs-legend-item">
                            <span class="mrs-legend-dot pending"></span> En espera de aprobación
                        </div>
                    </div>
                </div>

                <div class="mrs-card mrs-calendar-card">
                    <DatePicker expanded v-model="pickDate" :attributes="attrs" timezone="America/Denver" />
                </div>

                <div class="mrs-actions">
                    <div class="mrs-selected">
                        <span class="mrs-selected-label">Fecha seleccionada</span>
                        <span class="mrs-selected-value">{{ pickDate ? format(pickDate, 'dd/MM/yyyy') : '--/--/----' }}</span>
                    </div>
                    <button
                        class="mrs-btn mrs-btn-primary"
                        :class="{ 'is-disabled': isDateTaken || isLoading }"
                        :disabled="isDateTaken || isLoading"
                        @click="open"
                    >
                        <span v-if="isLoading">Guardando...</span>
                        <span v-else-if="isDateTaken">Fecha ocupada</span>
                        <span v-else>Reservar fecha</span>
                    </button>
                </div>
            </div>

            <div class="mrs-container mrs-reservations">
                <Reservation v-if="!isLoading" @refresh="getReservations" />
            </div>
        </div>
        <LoadingScreen :show="isLoading" />
        <ModalsContainer />
    </AuthenticatedLayout>
</template>

<style scoped src="./Dashboard.css"></style>
