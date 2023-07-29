<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';

const store = useAdminStore();
const users = ref([]);

onMounted(async () => {
    store.setIsLoading(true);
    const { data } = await axios.get('/api/users')
    users.value = data;
    setTimeout(() => {
        store.setIsLoading(false);
    }, 1000);
});
</script>

<template>
    <Head title="Usuarios" />

    <h4 class="font-bold my-3">
        Usuarios
    </h4>
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
                    <PrimaryButton class="m-1">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </PrimaryButton>
                    <DangerButton>
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>