<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref } from 'vue';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';

const restrictions = ref([]);

onMounted(async () => {
    await getRestrictions();
});

async function removeRestriction(restrictionId, isActive) {
    console.log(`/api/restrictions/admin/${restrictionId}`);
    await axios.put(`/api/restrictions/admin/${restrictionId}`, {
        is_active: isActive ? 1 : 0
    })
        .then(async (response) => {
            getRestrictions();
            toast.success(response.data.message);
        })
        .catch(error => {
            console.log(error);
            toast.error(error.response.data.message);
        });
}

async function getRestrictions() {
    restrictions.value = [];

    await axios.get('/api/restrictions/admin')
        .then(response => {
            restrictions.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
}

</script>

<template>
    <div class="block">
        <label>Restricciones</label>
    </div>
    <table class="table-fixed border table-records">
        <thead>
            <tr>
                <th>Domicilio</th>
                <th>Bloqueo hasta</th>
                <th>Bloqueado por</th>
                <th>Razón</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="restriction in restrictions" :key="restriction.id">
                <td class="border p-1">
                    <div v-if="restriction.house">{{ restriction.house.street.name ?? '' }} {{
                        restriction.house.house_number ?? '' }}</div>
                </td>
                <td class="border p-1">{{ format(new Date(restriction.block_date_end), "dd/MM/yyyy") }}</td>
                <td class="border p-1">
                    {{ restriction.block_by.name }}
                </td>
                <td class="border p-1">
                    {{ restriction.reason }}
                </td>
                <td class="border p-1">
                    <input type="checkbox" disabled class="appearance-none checked:bg-blue-500 m-auto block"
                        :checked="restriction.is_active" />
                </td>
                <td class="border p-1 text-center">
                    <PrimaryButton @click="removeRestriction(restriction.id, !restriction.is_active)">
                        {{ restriction.is_active ? 'Desactivar' : 'Activar' }}
                    </PrimaryButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>