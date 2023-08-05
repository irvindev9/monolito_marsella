<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const events = ref([]);

onMounted(async () => {
    await getEvents();
});

async function getEvents() {
    await axios.get('/api/events')
        .then(response => {
            events.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
}
</script>

<template>
    <Head title="Eventos" />
    Eventos

    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th>Domicilio</th>
                <th>Deposito</th>
                <th>Contrato</th>
                <th>Aprobado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="event in events" :key="event.id">
                <td class="border p-1">{{ event.house.street.name }} {{ event.house.house_number }}</td>
                <td class="border p-1">
                    <input type="checkbox" disabled class="appearance-none checked:bg-blue-500 m-auto block"
                        :checked="event.is_paid" />
                </td>
                <td class="border p-1">
                    <input type="checkbox" disabled class="appearance-none checked:bg-blue-500 m-auto block"
                        :checked="event.is_signed" />
                </td>
                <td class="border p-1">{{ event.approved_by ? event.approved_by.name : '' }}</td>
                <td class="border p-1 text-center">
                    <PrimaryButton @click="editEvent(event.id)">Editar</PrimaryButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>