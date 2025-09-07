<script setup>
import { ref, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const emit = defineEmits(['edit']);

function editAccess(access) {
    emit('edit', access);
}

async function deleteAccess(accessId) {
   await axios.delete('/api/passwords/' + accessId).then(async (response) => {
        toast.success(response.data.message);
        await getAccesses();
    }).catch(error => {
        console.log(error);
        toast.error(error.response.data.message);
    });
}

const accesses = ref([]);

onMounted(async () => {
    await getAccesses();
});

async function getAccesses() {
    await axios.get('/api/passwords').then(response => {
        accesses.value = response.data;
    }).catch(error => {
        console.log(error);
    });
}
</script>

<template>
    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th>Título</th>
                <th>Contraseña</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="access in accesses" :key="access.id">
                <td class="border px-4 py-2">
                    <i class="bi bi-check-circle-fill text-green-500" title="Visible para el público" v-if="access.is_active"></i>
                    <i class="bi bi-x-circle-fill text-red-500" title="Oculto para el público" v-else></i>
                    {{ access.title }}
                </td>
                <td class="border px-4 py-2">{{ access.password }}</td>
                <td class="border px-4 py-2 text-center">
                    <PrimaryButton class="m-1" @click="editAccess(access)">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </PrimaryButton>
                    <DangerButton class="m-1" @click="deleteAccess(access.id)">
                        <i class="bi bi-trash"></i>
                        Borrar
                    </DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>