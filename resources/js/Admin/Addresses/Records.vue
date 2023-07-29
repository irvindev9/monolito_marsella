<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import { defineProps } from 'vue';

const props = defineProps({
    addresses: {
        type: Array,
        required: true,
    },
});
</script>
<template>
    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th class="px-4 py-2">Calle</th>
                <th class="px-4 py-2">Número</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="address in addresses" :key="address.id">
                <td class="border px-4 py-2">{{ address.street.name }}</td>
                <td class="border px-4 py-2">{{ address.house_number }}</td>
                <td class="border px-4 py-2 text-center">
                    <DangerButton>
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>