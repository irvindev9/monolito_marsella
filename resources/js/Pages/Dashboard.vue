<script setup>
import Loader from '@/assets/loaging.svg';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, DatePicker } from 'v-calendar';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { toast } from 'vue3-toastify';
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

        <div class="px-12">
            <Label class="font-bold">Mis reservas</Label>
            <div class="sm:flex sm:items-center sm:justify-between sm:space-x-5 bg-white rounded-lg px-1">
                <div class="flex items-center flex-1 min-w-0">
                    <img src="https://d34u8crftukxnk.cloudfront.net/slackpress/prod/sites/6/SlackLogo_CompanyNews_SecondaryAubergine_Hero.jpg?d=500x500&amp;f=fill"
                        class="flex-shrink-0 object-cover rounded-full btn- w-10 h-10" />
                    <div class="mt-0 mr-0 mb-0 ml-4 flex-1 min-w-0">
                        <p class="text-lg font-bold text-gray-800 truncate">Engineering Manager</p>
                        <p class="text-gray-600 text-md">Slack</p>
                    </div>
                </div>
                <div class="mt-4 mr-0 mb-0 ml-0 pt-0 pr-0 pb-0 pl-14 flex items-center sm:space-x-6 sm:pl-0 sm:mt-0">
                    <a href="" class="bg-gray-800 pt-2 pr-6 pb-2 pl-6 text-lg font-medium text-gray-100 transition-all
                    duration-200 hover:bg-gray-700 rounded-lg">Apply</a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
