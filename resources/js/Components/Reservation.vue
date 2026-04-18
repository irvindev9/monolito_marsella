<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Access from '@/Components/Access.vue';
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';
import CountDown from './CountDown.vue';

const emit = defineEmits(['refresh']);
const myReservations = ref([]);
const user = usePage().props.auth.user;
const configs = ref([]);

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

async function getConfig() {
    try {
        const { data } = await axios.get('/api/configs');
        configs.value = data;
    } catch (error) {
        console.log(error);
    }
}

onMounted(async () => {
    await getReservations();
    await getConfig();
});

function validateAccess(reservationDate) {
    // Show only for actual or future, remove time
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const reservationDateFormatted = new Date(reservationDate);
    reservationDateFormatted.setHours(0, 0, 0, 0);
    return reservationDateFormatted >= today;
}

</script>

<template>
    <div class="sm:space-x-5 rounded-lg px-1 my-3 border border-slate-200 reservation-box"
        :class="{ 'bg-gray-100': reservation.is_approved === 0, 'bg-green-100': reservation.is_approved === 1, 'bg-red-100': reservation.is_approved === 2 || reservation.is_approved === 3 }"
        v-for="reservation in myReservations" :key="reservation.id">
        <div class="sm:flex sm:items-center sm:justify-between ">
            <div class="flex items-center flex-1 min-w-0">
                <i class="bi bi-calendar4-week flex-shrink-0 object-cover rounded-full m-3"></i>
                <div class="mt-0 mr-0 mb-0 ml-4 flex-1 min-w-0">
                    <p class="text-lg truncate">
                        {{ reservation.user ? reservation.user.name : 'Registrado por comité' }} - {{
                            `${reservation.house.street.name} ${reservation.house.house_number}` }}
                    </p>
                    <p class="text-md">Fecha reserva: <span class="font-bold">{{ format(new Date(reservation.reservation_date),
                        'MM/dd/yyyy') }}</span></p>
                    <p class="text-sm">
                        <small>
                            Solicitud hecha el <span class="font-bold">{{ format(new Date(reservation.created_at),
                                'MM/dd/yyyy') }}</span>
                        </small>
                    </p>
                    <small class="text-sm font-bold">{{ reservation.is_approved === 0 ? 'En espera de aprobación' :
                        reservation.is_approved === 1 ? 'Aprobado' : reservation.is_approved === 2 ? 'Rechazado' : 'Cancelado por tiempo de pago expirado' }}</small> <br>
                    <small class="text-sm" v-if="reservation.notes">Mensaje: {{ reservation.notes }}</small>
                    <p>
                        <small>
                            <i v-if="!reservation.is_signed" class="bi bi-exclamation-circle"></i>
                            {{
                                (reservation.is_signed == 1) ? 'Contrato entregado' :
                                (reservation.is_signed == 2) ? 'Contrato firmado digitalmente' :
                                    'Pendiente de entregar '
                            }}
                            <a v-if="!reservation.is_signed" href="/documents/Contrato.pdf"
                                class="text-blue-500 hover:text-blue-700">
                                contrato
                            </a></small>
                        <br>
                    </p>
                </div>
            </div>
            <div>
                <DangerButton class="m-1 reservation-btn-archive" @click="deleteReservation(reservation.id)">
                    <div v-if="reservation.is_approved != 0">
                        <i class="bi bi-archive"></i>
                        Archivar
                    </div>
                    <div v-else>
                        <i class="bi bi-trash"></i>
                        Eliminar/Cancelar
                    </div>
                </DangerButton>
                <!-- countdown timer -->
                <CountDown :configs="configs" :reservation="reservation" v-if="reservation.is_approved === 0" />
            </div>
        </div>
        <div v-if="validateAccess(reservation.reservation_date)">
            <Access :passwords="passwords" title="Accesos" :reservation-id="reservation.id" :reservation-date="reservation.reservation_date" />
        </div>
    </div>
</template>

<style scoped>
    .reservation-box {
        position: relative;
    }

    @media screen and (max-width: 576px) {
        
    }
    .reservation-btn-archive {
        margin-bottom: 35px;
    }
</style>
