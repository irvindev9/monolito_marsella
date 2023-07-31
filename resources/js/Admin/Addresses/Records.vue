<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import { defineProps, defineEmits } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps({
    addresses: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['refresh']);

async function deleteAddress(house_id) {
    const address = props.addresses.find(item => item.id === house_id);
    if (!confirm('¿Estás seguro de borrar esta dirección?')) {
        return;
    }

    if (address.users.length > 0) {
        toast.error('No puedes borrar una dirección que tenga usuarios asignados');
        return;
    }

    await axios.delete(route('house.destroy'), {
        params: {
            id: house_id,
        },
    });

    toast.success('Dirección borrada correctamente');

    emit('refresh');
}
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
                    <DangerButton @click="deleteAddress(address.id)">
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>