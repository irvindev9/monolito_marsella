<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { defineProps, defineEmits } from 'vue';
import { format } from 'date-fns';

const emit = defineEmits(['edit']);
const props = defineProps({
    events: {
        type: Array,
        required: true
    }
});

function editEvent(eventId) {
    emit('edit', eventId);
}
</script>

<template>
    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Domicilio</th>
                <th>Deposito</th>
                <th>Contrato</th>
                <th>Aprobado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="event in props.events" :key="event.id">
                <td class="border p-1">{{ format(new Date(event.reservation_date), "dd/MM/yyyy") }}</td>
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
                    <PrimaryButton @click="editEvent(event.id)">
                        <i class="bi bi-pencil"></i> Editar
                    </PrimaryButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>