<script setup>
import Records from '@/Admin/Events/Records.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import FormRecord from './Events/FormRecord.vue';

const events = ref([]);
const event = ref(null);
const showAll = ref(false);
const editForm = ref(false);
const minYear = ref(new Date().getFullYear());

const currentFilters = ref({ showAll: false, year: '', month: '' });

onMounted(async () => {
    await getEvents();
});

async function getEvents(filters = {}) {
    if (filters && typeof filters === 'object') {
        currentFilters.value = { ...currentFilters.value, ...filters };
    } else if (typeof filters === 'boolean') {
        currentFilters.value.showAll = filters;
    }

    showAll.value = currentFilters.value.showAll;
    events.value = [];
    await axios.get('/api/events', {
            params: {
                showAll: currentFilters.value.showAll,
                year: currentFilters.value.year,
                month: currentFilters.value.month,
            }
        })
        .then(response => {
            if (response.data && response.data.events) {
                events.value = response.data.events;
                minYear.value = response.data.minYear || new Date().getFullYear();
            } else {
                events.value = Array.isArray(response.data) ? response.data : [];
            }
        })
        .catch(error => {
            console.log(error);
        });
}

async function reloadEvents(payload) {
    await getEvents(payload);
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

    <Records :events="events" :minYear="minYear" v-if="!editForm" @edit="editEvent" @getEvents="reloadEvents" :showAll="showAll" />

    <FormRecord :event="event" v-if="editForm" @close="close" />
</template>