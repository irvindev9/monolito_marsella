<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, reactive } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import { toast } from 'vue3-toastify';

const store = useAdminStore();
const criteria = ref([]);
const editing = ref(null);
const form = reactive({
    name: '',
    response_type: 'boolean',
    is_active: true,
    order: 0,
});

function resetForm() {
    form.name = '';
    form.response_type = 'boolean';
    form.is_active = true;
    form.order = 0;
    editing.value = null;
}

function startEdit(item) {
    editing.value = item.id;
    form.name = item.name;
    form.response_type = item.response_type;
    form.is_active = item.is_active;
    form.order = item.order;
}

async function getCriteria() {
    store.setIsLoading(true);
    const { data } = await axios.get('/api/cleaning-criteria');
    criteria.value = data;
    setTimeout(() => store.setIsLoading(false), 300);
}

async function saveCriteria() {
    if (!form.name.trim()) {
        toast.error('El nombre es requerido');
        return;
    }

    try {
        if (editing.value) {
            await axios.put(`/api/cleaning-criteria/${editing.value}`, { ...form });
            toast.success('Criterio actualizado');
        } else {
            await axios.post('/api/cleaning-criteria', { ...form });
            toast.success('Criterio creado');
        }
        resetForm();
        await getCriteria();
    } catch (error) {
        toast.error('Error al guardar criterio');
    }
}

async function deleteCriteria(id) {
    try {
        await axios.delete(`/api/cleaning-criteria/${id}`);
        toast.success('Criterio eliminado');
        await getCriteria();
    } catch (error) {
        toast.error('Error al eliminar criterio');
    }
}

const RESPONSE_TYPES = [
    { value: 'boolean', label: 'Sí/No' },
    { value: 'number', label: 'Número' },
    { value: 'text', label: 'Texto' },
];

onMounted(() => getCriteria());
</script>

<template>
    <Head title="Criterios de limpieza" />

    <h4 class="font-bold my-3">Criterios de limpieza</h4>

    <div class="bg-gray-50 border rounded p-4 mb-4">
        <h5 class="font-bold mb-2">{{ editing ? 'Editar criterio' : 'Nuevo criterio' }}</h5>
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col flex-1 min-w-[200px]">
                <label class="text-xs font-bold mb-1">Nombre</label>
                <input type="text" class="border rounded p-2 text-sm" v-model="form.name" placeholder="Ej: Limpieza de pisos" />
            </div>
            <div class="flex flex-col">
                <label class="text-xs font-bold mb-1">Tipo de respuesta</label>
                <select class="border rounded p-2 text-sm" v-model="form.response_type">
                    <option v-for="t in RESPONSE_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-xs font-bold mb-1">Orden</label>
                <input type="number" class="border rounded p-2 text-sm w-20" v-model.number="form.order" />
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-1 text-sm cursor-pointer">
                    <input type="checkbox" v-model="form.is_active" />
                    Activo
                </label>
            </div>
            <div class="flex gap-2">
                <PrimaryButton @click="saveCriteria">
                    {{ editing ? 'Actualizar' : 'Crear' }}
                </PrimaryButton>
                <DangerButton v-if="editing" @click="resetForm">
                    Cancelar
                </DangerButton>
            </div>
        </div>
    </div>

    <table class="table-fixed border table-records table-responsive-cards w-full">
        <thead>
            <tr>
                <th class="px-4 py-2">Orden</th>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Tipo</th>
                <th class="px-4 py-2">Estado</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in criteria" :key="item.id">
                <td class="border px-4 py-2" data-label="Orden">{{ item.order }}</td>
                <td class="border px-4 py-2" data-label="Nombre">{{ item.name }}</td>
                <td class="border px-4 py-2" data-label="Tipo">
                    {{ item.response_type === 'boolean' ? 'Sí/No' : item.response_type === 'number' ? 'Número' : 'Texto' }}
                </td>
                <td class="border px-4 py-2" data-label="Estado">
                    <span :class="item.is_active ? 'text-green-600' : 'text-gray-400'">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="border px-4 py-2 text-center" data-label="Acciones">
                    <PrimaryButton class="m-1" @click="startEdit(item)">
                        <i class="bi bi-pencil"></i> Editar
                    </PrimaryButton>
                    <DangerButton class="m-1" @click="deleteCriteria(item.id)">
                        <i class="bi bi-trash"></i> Borrar
                    </DangerButton>
                </td>
            </tr>
            <tr v-if="criteria.length === 0">
                <td colspan="5" class="border px-4 py-4 text-center text-gray-400">No hay criterios registrados</td>
            </tr>
        </tbody>
    </table>
</template>
