<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue';
import { toast } from 'vue3-toastify';

const emit = defineEmits(['close']);

const title = ref('');
const password = ref('');
const accessId = ref(null);
const isActive = ref(1);

const props = defineProps({
    update: {
        type: Boolean,
        required: true,
    },
    access: {
        type: Object,
        required: false,
        default: null,
    },
});

onMounted(() => {
    if (props.update && props.access) {
        accessId.value = props.access.id;
        title.value = props.access.title;
        password.value = props.access.password;
        isActive.value = props.access.is_active;
    }
});

async function saveAccess() {
    if (!validateForm()) {
        return;
    }

    try {
        if (props.update) {
            await axios.put('/api/passwords/' + accessId.value, {
                id: accessId.value,
                title: title.value,
                password: password.value,
                is_active: isActive.value,
            });

            toast.success('Registro actualizado');
            emit('close');
        } else {
            await axios.post('/api/passwords', {
                title: title.value,
                password: password.value,
                is_active: isActive.value,
            });

            toast.success('Registro guardado');
            emit('close');
        }
    } catch (error) {
        console.log(error);
        toast.error('Error al guardar el registro');
    }
}

function validateForm() {
    if (title.value === '') {
        toast.error('El título es requerido');
        return false;
    }
    if (password.value === '') {
        toast.error('La contraseña es requerida');
        return false;
    }

    return true;
}
</script>

<template>
    <div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Título
                    </label>
                    <input type="text" class="border rounded p-2" v-model="title" />
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Contraseña
                    </label>
                    <input type="text" class="border rounded p-2" v-model="password" />
                </div>
            </div>
        </div>
        <div class="flex flex-col">
                <label class="font-bold mt-3">
                    Activo
                </label>
                <div class="flex my-1">
                    <input id="open" type="radio" class="appearance-none checked:bg-blue-500 mt-1 mr-1" value="1"
                        v-model="isActive">
                    <label for="open">Activo</label><br>
                </div>
                <div class="flex my-1">
                    <input id="close" type="radio" class="appearance-none indeterminate:bg-gray-300 mt-1 mr-1" value="0"
                        v-model="isActive">
                    <label for="close">Inactivo</label><br>
                </div>
            </div>
        <div class="grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveAccess">
                {{ props.update ? 'Actualizar' : 'Agregar' }}
            </PrimaryButton>
        </div>
    </div>
</template>