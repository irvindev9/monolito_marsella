<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { defineProps, defineEmits, ref, onMounted } from 'vue';
import { format } from 'date-fns';
import { toast } from 'vue3-toastify';

const emit = defineEmits(['close']);
const props = defineProps({
    event: {
        type: Object,
        required: true
    }
});

const isPaid = ref(null);
const isSigned = ref(null);

onMounted(() => {
    isPaid.value = props.event.is_paid == 1;
    isSigned.value = props.event.is_signed == 1;
});

async function updateEvent() {
    try {
        const { data } = await axios.put('/api/events/' + props.event.id, {
            is_paid: isPaid.value,
            is_signed: isSigned.value,
            notes: props.event.notes
        })

        emit('close');
        toast.success(data.message);
    } catch (error) {
        console.log(error);
    }
}

function close() {
    emit('close');
}
</script>

<template>
    <div v-if="props.event">
        <div class="flex">
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Fecha de reserva
                    </label>
                    <input type="text"
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        readonly disabled :value="format(new Date(props.event.reservation_date), 'dd/MM/yyyy')" />
                </div>
            </div>
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Domicilio
                    </label>
                    <input type="text"
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        readonly disabled :value="props.event.house.street.name + ' ' + props.event.house.house_number" />
                </div>
            </div>
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Reservado por
                    </label>
                    <input type="text"
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        readonly disabled :value="props.event.user.name" />
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Reservado el
                    </label>
                    <input type="text"
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        readonly disabled
                        :value="format(new Date(props.event.approved_by.created_at), 'dd/MM/yyyy HH:mm:ss')" />
                </div>
            </div>
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Aprobado por
                    </label>
                    <input type="text"
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        readonly disabled :value="props.event.approved_by.name" />
                </div>
            </div>
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Notas
                    </label>
                    <textarea
                        class="border rounded p-2 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none"
                        v-model="props.event.notes"></textarea>
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Entregó deposito
                    </label>
                    <input type="checkbox" class="appearance-none checked:bg-blue-500" v-model="isPaid" />
                </div>
            </div>
            <div class="w-1/3 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Entregó contrato
                    </label>
                    <input type="checkbox" class="appearance-none checked:bg-blue-500" v-model="isSigned" />
                </div>
            </div>
        </div>
        <div class="flex flex-row-reverse">
            <PrimaryButton class="m-1" @click="updateEvent">
                Guardar
            </PrimaryButton>
            <DangerButton class="m-1" @click="close">
                Cancelar
            </DangerButton>
        </div>
    </div>
</template>