<script setup>
import Records from '@/Admin/Events/Records.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import FormRecord from './Events/FormRecord.vue';

const events = ref([]);
const event = ref(null);
const showAll = ref(false);
const editForm = ref(false);

onMounted(async () => {
    await getEvents();
});

async function getEvents() {
    events.value = [];
    await axios.get('/api/events', {
            params: {
                showAll: showAll.value
            }
        })
        .then(response => {
            events.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
}

async function reloadEvents(updated) {
    showAll.value = updated;
    await getEvents();
}

function editEvent(eventId) {
    event.value = events.value.find(event => event.id === eventId);
    editForm.value = true;
}

async function close() {
    editForm.value = false;
    event.value = null;
    await getEvents();
}
</script>

<template>
    <Head title="Eventos" />
    <h3 class="font-bold py-3">Eventos</h3>

    <Records :events="events" v-if="!editForm && events.length > 0" @edit="editEvent" @getEvents="reloadEvents" :showAll="showAll" />

    <FormRecord :event="event" v-if="editForm" @close="close" />
</template>