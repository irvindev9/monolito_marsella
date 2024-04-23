<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue';
import { DatePicker } from 'v-calendar';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';

const streetId = ref(null);
const houseNumber = ref(null);
const pickDate = ref(new Date(new Date().setFullYear(new Date().getFullYear() + 1)));
const streets = ref([]);
const reason = ref(null);

onMounted(async () => {
    const { data } = await axios.get('/api/streets');
    streets.value = data;
});

function validateHouseNumber(e) {
    //only number max 4 digits
    houseNumber.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 4);
    if (e.target.value.length > 4) {
        e.target.value = houseNumber.value;
    }
}

async function saveDate() {
    try {
        const { data } = await axios.post('/api/restrictions/admin', {
            street_id: streetId.value,
            house_number: houseNumber.value,
            block_date_end: pickDate.value.toDateString(),
            reason: reason.value,
        });
        toast.success(data.message);
    } catch (error) {
        console.error(error.response);
        toast.error(`Error: ${error.response.data.message}`)
    }
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
                    <input type="number" class="border rounded p-2" @keyup="validateHouseNumber" />
                </div>
            </div>
        </div>
        <small class="small">
            Todos los usuarios del domicilio seleccionado serán bloqueados hasta la fecha seleccionada.
        </small>
        <div class="flex">
            <div class="w-full px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Motivo
                    </label>
                    <textarea class="border rounded p-2" rows="4" v-model="reason"></textarea>
                </div>
            </div>

        </div>
        <div class="flex">
            <div class="sm:w-full mb-5">
                <Label class="font-bold">Bloquear domicilio hasta:</Label>
                <DatePicker expanded v-model="pickDate" timezone="America/Denver" />
                <div class="block">
                    Fecha: {{ pickDate ? format(pickDate, 'dd/MM/yyyy') : '--/--/----' }} <br>
                </div>
            </div>
        </div>
        <div class="grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveDate">
                Agregar bloqueo
            </PrimaryButton>
        </div>
    </div>
</template>