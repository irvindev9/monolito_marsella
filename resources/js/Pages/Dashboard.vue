<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LoadingScreen from '@/Components/LoadingScreen.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, DatePicker } from 'v-calendar';
import { ref, onMounted } from 'vue';
import { format } from 'date-fns';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { toast } from 'vue3-toastify';
import Reservation from '@/Components/Reservation.vue';

const isLoading = ref(false);
const reservations = ref([]);

const attrs = ref([]);
const pickDate = ref(new Date());

async function reserveDate() {
    isLoading.value = true;
    try {
        const { data } = await axios.post('/api/reservations', {
            reservation_date: pickDate.value.toDateString(),
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
                    fillMode: 'solid',
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
        <div class="lg:flex p-3">
            <div class="sm:w-full lg:w-1/4 mx-auto sm:px-3 lg:px-12 mb-5">
                <Label class="font-bold">Reservar fecha</Label>
                <DatePicker expanded v-model="pickDate" timezone="America/Denver" />
                <div class="block">
                    Fecha seleccionada: {{ pickDate ? format(pickDate, 'dd/MM/yyyy') : '--/--/----' }} <br>
                    <PrimaryButton class="my-3" @click="reserveDate">
                        <div v-if="!isLoading">
                            Reservar fecha
                        </div>
                        <div v-else>
                            Guardando...
                        </div>
                    </PrimaryButton>
                </div>
            </div>
            <div class="sm:w-full lg:w-3/4 mx-auto sm:px-3 lg:px-12">
                <Label class="font-bold">Reservaciones</Label>
                <Calendar expanded :attributes="attrs" timezone="America/Denver" />
                <div class="w-full bg-white rounded border border-slate-300 mt-3 p-3">
                    <Label class="font-bold">Representación gráfica de las reservas</Label>
                    <div class="flex space-x-4">
                        <div>
                            <i class="bi bi-circle-fill text-blue"></i> Reservado
                        </div>
                        <div>
                            <i class="bi bi-circle-fill text-xs text-blue font-xs"></i> En espera de aprobación
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-3 lg:mx-12">
            <Label class="font-bold">Mis reservas</Label>
            <div class="border border-slate-300 rounded p-4 bg-white">
                <Reservation v-if="!isLoading" @refresh="getReservations" />
            </div>
        </div>
        <LoadingScreen :show="isLoading" />
    </AuthenticatedLayout>
</template>
