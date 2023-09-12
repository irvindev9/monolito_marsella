<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import { ref, onMounted, defineEmits } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';

const emit = defineEmits(['refresh']);
const myReservations = ref([]);
const user = usePage().props.auth.user;

async function getReservations() {
    const { data } = await axios.get(`/api/reservations/${user.house_id}`);
    myReservations.value = data;
}

async function deleteReservation(id) {
    const { data } = await axios.delete(`/api/reservations/${id}`);
    console.log(data.message);
    await getReservations();
    emit('refresh');
    toast.success(data.message);
}

onMounted(async () => {
    await getReservations();
});

</script>

<template>
    <div class="sm:flex sm:items-center sm:justify-between sm:space-x-5 rounded-lg px-1 my-3 border border-slate-200"
        :class="{ 'bg-gray-100': reservation.is_approved === 0, 'bg-green-100': reservation.is_approved === 1, 'bg-red-100': reservation.is_approved === 2 }"
        v-for="reservation in myReservations" :key="reservation.id">
        <div class="flex items-center flex-1 min-w-0">
            <img src="../../assets/calendar.png" alt="calendar"
                class="flex-shrink-0 object-cover rounded-full btn- w-10 h-10" />
            <div class="mt-0 mr-0 mb-0 ml-4 flex-1 min-w-0">
                <p class="text-lg truncate">
                    {{ reservation.user ? reservation.user.name : 'Registrado por comité' }} - {{
                        `${reservation.house.street.name} ${reservation.house.house_number}` }}
                </p>
                <p class="text-md">Fecha reserva: <span class="font-bold">{{ format(new Date(reservation.reservation_date),
                    'MM/dd/yyyy') }}</span></p>
                <small class="text-sm font-bold">{{ reservation.is_approved === 0 ? 'En espera de aprobación' :
                    reservation.is_approved === 1 ? 'Aprobado' : 'Rechazado' }}</small> <br>
                <small class="text-sm" v-if="reservation.notes">Mensaje: {{ reservation.notes }}</small>
                <p>
                    <small>
                        <i v-if="!reservation.is_signed" class="bi bi-exclamation-circle"></i>
                        {{ reservation.is_signed ? 'Contrato entregado' : 'Pendiente de entregar ' }}
                        <a v-if="!reservation.is_signed" href="/documents/Contrato.pdf"
                            class="text-blue-500 hover:text-blue-700">
                            contrato
                        </a></small>
                    <br>
                    <small>
                        <i v-if="!reservation.is_paid" class="bi bi-exclamation-circle"></i>
                        {{ reservation.is_paid ? 'Pago realizado' : 'Pago pendiente' }}
                    </small>
                </p>
            </div>
        </div>
        <DangerButton class="m-1" @click="deleteReservation(reservation.id)">
            <div v-if="reservation.is_approved != 0">
                <i class="bi bi-archive"></i>
                Archivar
            </div>
            <div v-else>
                <i class="bi bi-trash"></i>
                Eliminar/Cancelar
            </div>
        </DangerButton>
    </div>
</template>
