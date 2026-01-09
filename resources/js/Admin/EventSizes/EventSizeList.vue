<script setup>
import { ref, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const emit = defineEmits(['edit', 'delete']);
const eventSizes = ref([]);

onMounted(() => {
    getEventSizes();
});

async function getEventSizes() {
    eventSizes.value = [];
    await axios.get('/api/event-sizes')
        .then(response => {
            eventSizes.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
        
}

function editEventSize(eventSize) {
    emit('edit', eventSize);
}

function deleteEventSize(eventSizeId) {
    emit('delete', eventSizeId);
}
</script>

<template>
    <div>
        <h1>Lista de tamaños de aforo</h1>
        <table>
            <thead>
                <tr>
                    <th>Tamaño</th>
                    <th>Precio</th>
                    <th>Capacidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="eventSize in eventSizes" :key="eventSize.id">
                    <td>{{ eventSize.size }}</td>
                    <td>{{ eventSize.price }}</td>
                    <td>{{ eventSize.capacity }}</td>
                    <td>
                        <PrimaryButton @click="editEventSize(eventSize)">Editar</PrimaryButton>
                        <DangerButton @click="deleteEventSize(eventSize.id)">Eliminar</DangerButton>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped lang="scss">
table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ddd;

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
}
</style>