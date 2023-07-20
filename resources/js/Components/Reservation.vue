<script setup>
import DangerButton from '@/Components/DangerButton.vue';

</script>

<template>
    <div class="sm:flex sm:items-center sm:justify-between sm:space-x-5 bg-green-100 rounded-lg px-1 my-3" v-for="n in 3">
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
</template>
