<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const reservationRequests = ref([]);
const rejectTarget = ref(null);
const rejectionReason = ref('');

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

function openRejectModal(reservation) {
    rejectTarget.value = reservation;
    rejectionReason.value = '';
}

function closeRejectModal() {
    rejectTarget.value = null;
    rejectionReason.value = '';
}

async function confirmReject() {
    if (!rejectTarget.value) return;
    const id = rejectTarget.value.id;
    const reason = rejectionReason.value;
    closeRejectModal();
    await updateReservation(id, 2, reason);
}

async function updateReservation(reservationId, status, reason = null) {
    try {
        const { data } = await axios.put(`/api/reservations/approval-requests`, {
            id: reservationId,
            is_approved: status,
            rejection_reason: reason,
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
                            <DangerButton class="w-full sm:w-auto justify-center md:mx-1" @click="openRejectModal(reservation)">
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

    <Modal :show="Boolean(rejectTarget)" maxWidth="md" @close="closeRejectModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Rechazar solicitud
            </h2>
            <p v-if="rejectTarget" class="mt-1 text-sm text-gray-600">
                Solicitud de <strong>{{ rejectTarget.user ? rejectTarget.user.name : 'Sin usuario' }}</strong>
                ({{ rejectTarget.house.street.name }} {{ rejectTarget.house.house_number }}) para el
                <strong>{{ format(new Date(rejectTarget.reservation_date), "dd/MM/yyyy") }}</strong>.
            </p>

            <div class="mt-4">
                <InputLabel for="rejection_reason" value="Motivo del rechazo (opcional)" />
                <TextInput
                    id="rejection_reason"
                    v-model="rejectionReason"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Ej. Adeudo pendiente, mantenimiento, etc."
                    @keyup.enter="confirmReject"
                />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <SecondaryButton @click="closeRejectModal">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="confirmReject">
                    <i class="bi bi-hand-thumbs-down mr-1"></i> Rechazar
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>