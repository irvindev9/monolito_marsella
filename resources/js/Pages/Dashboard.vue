<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, DatePicker } from 'v-calendar';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { toast } from 'vue3-toastify';
import Reservation from '@/Components/Reservation.vue';
import axios from 'axios';

const date = new Date();
const year = date.getFullYear();
const month = date.getMonth();
const isLoading = ref(false);

const attrs = ref([
    {
        key: 'today',
        highlight: {
            color: 'purple',
            fillMode: 'solid',
            contentClass: 'italic',
        },
        dates: new Date(year, month, 10),
        description: 'Reservado',
        popover: true,
    },
    {
        key: 'event',
        highlight: {
            color: 'green',
            fillMode: 'light',
            contentStyle: {
                color: 'white',
            },
        },
        dates: [
            new Date(year, month, 12),
            new Date(year, month, 13),
            new Date(year, month, 14),
        ],
        description: 'Reservado',
        popover: true,
    },
    {
        key: 'holiday',
        dot: 'blue',
        dates: [
            new Date(year, month, 17),
            new Date(year, month, 18),
            new Date(year, month, 19),
        ],
        description: 'Reservas en espera',
        popover: true,
    },
]);
const pickDate = ref(new Date());

const reserveDate = async () => {
    isLoading.value = true;
    await axios.post('/api/reservations', {
        reservation_date: pickDate.value,
    }).then((response) => {
        console.log(response);
        isLoading.value = false;
        toast.success('Fecha solicitada, en espera de confirmación');
    }).catch((error) => {
        console.log(error);
        isLoading.value = false;
        toast.error('Error al solicitar fecha');
    });

};

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="lg:flex p-3">
            <div class="sm:w-full lg:w-1/4 mx-auto sm:px-3 lg:px-12 mb-5">
                <Label class="font-bold">Reservar fecha</Label>
                <DatePicker expanded v-model="pickDate" />
                <!-- <p>{{ pickDate }}</p> -->
                <PrimaryButton class="my-3" @click="reserveDate">
                    <div v-if="!isLoading">
                        Reservar fecha
                    </div>
                    <div v-else>
                        Guardando...
                    </div>
                </PrimaryButton>
            </div>
            <div class="sm:w-full lg:w-3/4 mx-auto sm:px-3 lg:px-12">
                <Label class="font-bold">Reservaciones</Label>
                <Calendar expanded :attributes="attrs" />
            </div>
        </div>

        <div class="px-3">
            <Label class="px-12 font-bold">Mis reservas</Label>
            <div class="px-12 rounded mx-12 py-4 bg-white">
                <Reservation />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
