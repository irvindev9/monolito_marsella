<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const reservationRequests = ref([]);

onMounted(async () => {
    await getReservationRequests();
});

async function getReservationRequests() {
    await axios.get('/api/reservations/approval-requests')
        .then(response => {
            reservationRequests.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
}

async function updateReservation(reservationId, status) {
    try {
        const { data } = await axios.put(`/api/reservations/approval-requests`, {
            id: reservationId,
            is_approved: status
        })
        await getReservationRequests();
        toast.success(data.message);
    } catch (error) {
        console.log(error);
    }
}

async function cancelExpiredReservations() {
    try {
        const { data } = await axios.post('/api/reservations/cancel-expired');
        await getReservationRequests();
        toast.success(data.message);
    } catch (error) {
        console.log(error);
    }
}
</script>

<template>
    <Head title="Solicitudes" />
    <div class="header-titles flex justify-between items-center mb-4">
        <h3 class="font-bold py-1">Solicitudes</h3>
        <DangerButton class="expired-btn mt-1" @click="cancelExpiredReservations">
            <i class="bi bi-x-circle"></i> Cancelar reservas expiradas
        </DangerButton>
    </div>

    <div v-if="reservationRequests.length > 0">
        <ul>
            <li class="shadow-sm shadow-slate-200 border rounded px-3 mb-2" v-for="reservation in reservationRequests"
                :key="reservation.id">
                <div class="flex flex-col md:flex-row gap-2 py-2">
                    <div class="w-full md:w-1/2">
                        <label class="font-bold" for="title">{{ reservation.user ? reservation.user.name : 'Sin usuario' }}
                            -
                            {{ reservation.house.street.name
                            }} {{ reservation.house.house_number }}</label>
                        <p>
                            Fecha de reserva: {{ format(new Date(reservation.reservation_date), "dd/MM/yyyy") }} <br>
                            Fecha de solicitud: {{ format(new Date(reservation.created_at), "dd/MM/yyyy HH:mm:ss") }} <br>
                        </p>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="flex flex-col sm:flex-row md:flex-row-reverse gap-2 md:py-3">
                            <PrimaryButton class="w-full sm:w-auto justify-center" @click="updateReservation(reservation.id, 1)">
                                <i class="bi bi-hand-thumbs-up"></i> Aprobar
                            </PrimaryButton>
                            <DangerButton class="w-full sm:w-auto justify-center md:mx-1" @click="updateReservation(reservation.id, 2)">
                                <i class="bi bi-hand-thumbs-down"></i> Rechazar
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div v-else>
        <p>No hay solicitudes pendientes</p>
    </div>
</template>