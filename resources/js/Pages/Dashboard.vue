<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, DatePicker } from 'v-calendar';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { toast } from 'vue3-toastify';
import axios from 'axios';
import DangerButton from '@/Components/DangerButton.vue';

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
])

const pickDate = ref(new Date());

const reserveDate = async () => {
    isLoading.value = true;
    // setTimeout(() => {
    //     isLoading.value = false;
    //     toast.success('Fecha solicitada, en espera de confirmación');
    // }, 2000);
    await axios.post('/api/reservations', {
        reservationDate: pickDate.value,
    }, {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
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

const renderLoader = () => h('div', { innerHtml: Loader });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6 flex">
            <div class="w-1/4 mx-auto sm:px-3 lg:px-12">
                <Label class="font-bold">Reservar fecha</Label>
                <DatePicker expanded v-model="pickDate" />
                <!-- <p>{{ pickDate }}</p> -->
                <PrimaryButton class="my-3" :onclick="reserveDate">
                    <div v-if="!isLoading">
                        Reservar fecha
                    </div>
                    <div v-else>
                        Guardando...
                    </div>
                </PrimaryButton>
            </div>
            <div class="w-3/4 mx-auto sm:px-3 lg:px-12">
                <Label class="font-bold">Reservaciones</Label>
                <Calendar expanded :attributes="attrs" />

            </div>
        </div>

        <div>
            <Label class="px-12 font-bold">Mis reservas</Label>
            <div class="px-12 rounded mx-12 py-4 bg-white">
                <div class="sm:flex sm:items-center sm:justify-between sm:space-x-5 bg-green-100 rounded-lg px-1 my-3"
                    v-for="n in 3">
                    <div class="flex items-center flex-1 min-w-0">
                        <img src="../../assets/calendar.png" alt="calendar"
                            class="flex-shrink-0 object-cover rounded-full btn- w-10 h-10" />
                        <div class="mt-0 mr-0 mb-0 ml-4 flex-1 min-w-0">
                            <p class="text-lg truncate">Irvin Lopez - De Ader 2262</p>
                            <p class="text-md">Fecha reserva: <span class="font-bold">02/06/2023</span></p>
                            <small class="text-sm">Aprovado</small>
                        </div>
                    </div>
                    <DangerButton>
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
