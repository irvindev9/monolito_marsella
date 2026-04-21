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

const isLoading = ref(false);
const reservations = ref([]);

const attrs = ref([]);
const pickDate = ref(new Date());

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

onMounted(async () => {
    isLoading.value = true;
    await getReservations();
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
