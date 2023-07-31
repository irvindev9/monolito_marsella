<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['close']);

function editUser(id) {
    emit('close', id);
}
</script>

<template>
    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Domicilio</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users" :key="user.id">
                <td class="border px-4 py-2">{{ user.name }}</td>
                <td class="border px-4 py-2">{{ user.email }}</td>
                <td class="border px-4 py-2">{{ `${user.house.street.name} ${user.house.house_number}` }}</td>
                <td class="border px-4 py-2 text-center">
                    <PrimaryButton class="m-1" @click="editUser(user.id)">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </PrimaryButton>
                    <DangerButton v-if="!user.house">
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>