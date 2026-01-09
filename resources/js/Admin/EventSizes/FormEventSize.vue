<script setup>
import { ref, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    eventSize: {
        type: Object,
        default: null,
    },
    update: {
        type: Boolean,
        required: false,
        default: false,
    },
});

const eventSize = ref({
    size: '',
    price: '',
    capacity: '',
});

onMounted(() => {
    if (props.eventSize) {
        eventSize.value = { ...props.eventSize };
    }
});

async function saveEventSize() {
    if (props.update) {
        await axios.put('/api/event-sizes/' + props.eventSize.id, eventSize.value);
    } else {
        await axios.post('/api/event-sizes', eventSize.value);
    }
    emit('close');
}

function close() {
    emit('close');
}
</script>
<template>
    <div>
        <h1>Formulario de tamaño de aforo</h1>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Tamaño
                    </label>
                    <input type="text" class="border rounded p-2" v-model="eventSize.size" />
                </div>
            </div>
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Precio
                    </label>
                    <input type="number" class="border rounded p-2" v-model="eventSize.price" />
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Capacidad
                    </label>
                </div>
                <input type="number" class="border rounded p-2" v-model="eventSize.capacity" />
            </div>
        </div>
        <div class="grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveEventSize">
                {{ update ? 'Actualizar' : 'Agregar' }}
            </PrimaryButton>
        </div>
    </div>
</template>