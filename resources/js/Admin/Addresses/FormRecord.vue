<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { defineEmits, ref, onMounted } from 'vue';

const emit = defineEmits(['close']);

const streets = ref([]);
const houseNumber = ref('');
const streetId = ref(1);

onMounted(async () => {
    const { data } = await axios.get('/api/streets');
    streets.value = data;
});

async function saveAddress() {
    await axios.post('/api/addresses', {
        street_id: streetId.value,
        house_number: houseNumber.value,
    });
    emit('close');
}
</script>
<template>
    <div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Calle
                    </label>
                    <select type="text" class="border rounded p-2" v-model="streetId">
                        <option v-for="street in streets" :key="street.id" :value="street.id">{{ street.name }}</option>
                    </select>
                </div>
            </div>
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Número
                    </label>
                    <input type="number" class="border rounded p-2" v-model.number="houseNumber" />
                </div>
            </div>
        </div>
        <div class="grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveAddress">
                Agregar
            </PrimaryButton>
        </div>
    </div>
</template>