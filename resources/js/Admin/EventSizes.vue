<script setup>
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormEventSize from './EventSizes/FormEventSize.vue';
import EventSizeList from './EventSizes/EventSizeList.vue';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const showForm = ref(false);
const isUpdate = ref(false);
const eventSizeValue = ref(null);

function editEventSize(eventSize) {
    isUpdate.value = true;
    eventSizeValue.value = eventSize;
    showForm.value = true;
}

async function deleteEventSize(eventSizeId) {
    await axios.delete('/api/event-sizes/' + eventSizeId)
        .then(async (response) => {
            toast.success(response.data.message);
            await getEventSizes();
        })
        .catch(async (error) => {
            console.log(error);
            toast.error(error.response.data.message);
            await getEventSizes();
        });
}

function openForm() {
    if (showForm.value) {
        showForm.value = false;
    } else {
        isUpdate.value = false;
        eventSizeValue.value = null;
        showForm.value = true;
    }
}

function closeForm() {
    showForm.value = false;
    eventSizeValue.value = null;
    isUpdate.value = false;
}
</script>

<template>
    <Head title="Aforo de los eventos" />

    <div class="add_div">
        <PrimaryButton @click="openForm">
            {{ showForm ? 'Cancelar' : 'Agregar nuevo tamaño' }}
        </PrimaryButton>
    </div>

    <h4 class="font-bold my-3">
        {{ showForm ? 'Agregar tamaño de aforo' : 'Aforo de los eventos' }}
    </h4>

    <div v-if="showForm">
        <FormEventSize @close="closeForm" :update="isUpdate" :eventSize="eventSizeValue" />
    </div>

    <div v-else>
        <EventSizeList @edit="editEventSize" @delete="deleteEventSize" />
    </div>
</template>