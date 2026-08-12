<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref, watch, computed } from 'vue';
import { format } from 'date-fns';

const events = ref([]);
const filter = ref(null);
const emit = defineEmits(['edit', 'getEvents']);
const props = defineProps({
    events: {
        type: Array,
        required: true
    },
    showAll: {
        type: Boolean,
        required: false,
        default: false
    },
    minYear: {
        type: Number,
        required: false,
        default: () => new Date().getFullYear()
    }
});

const selectedReport = ref(null);
const selectedEvent = ref(null);
const showReportModal = ref(false);

function openReportModal(report, event) {
    selectedReport.value = report;
    selectedEvent.value = event;
    showReportModal.value = true;
}

function closeReportModal() {
    showReportModal.value = false;
    selectedReport.value = null;
    selectedEvent.value = null;
}

const selectedYear = ref('');
const selectedMonth = ref('');

const yearsList = computed(() => {
    const currentYear = new Date().getFullYear();
    const startYear = props.minYear || currentYear;
    const years = [];
    for (let y = startYear; y <= currentYear; y++) {
        years.push(y);
    }
    return years;
});
const monthsList = ref([
    { id: 1, name: 'Enero' },
    { id: 2, name: 'Febrero' },
    { id: 3, name: 'Marzo' },
    { id: 4, name: 'Abril' },
    { id: 5, name: 'Mayo' },
    { id: 6, name: 'Junio' },
    { id: 7, name: 'Julio' },
    { id: 8, name: 'Agosto' },
    { id: 9, name: 'Septiembre' },
    { id: 10, name: 'Octubre' },
    { id: 11, name: 'Noviembre' },
    { id: 12, name: 'Diciembre' },
]);

function onSelectFilterChange() {
    emit('getEvents', {
        showAll: props.showAll,
        year: selectedYear.value,
        month: selectedMonth.value,
    });
}

function toggleShowAll() {
    const nextShowAll = !props.showAll;
    if (!nextShowAll) {
        selectedYear.value = '';
        selectedMonth.value = '';
    }
    emit('getEvents', {
        showAll: nextShowAll,
        year: selectedYear.value,
        month: selectedMonth.value,
    });
}

function editEvent(eventId) {
    emit('edit', eventId);
}

function filterEvents(value) {
    if (value === '' || value === null) {
        events.value = props.events;
    } else {
        events.value = props.events.filter(event => {
            if (event.house && event.house.street && event.house.house_number) {
                return event.house.street?.name.toLowerCase().includes(value.toLowerCase()) ||
                    event.house.house_number?.toLowerCase().includes(value.toLowerCase()) ||
                    event.approved_by?.name.toLowerCase().includes(value.toLowerCase()) ||
                    format(new Date(event.reservation_date), "dd/MM/yyyy").includes(value.toLowerCase());
            } else {
                return event.approved_by?.name.toLowerCase().includes(value.toLowerCase()) ||
                    format(new Date(event.reservation_date), "dd/MM/yyyy").includes(value.toLowerCase());
            }
        });
    }
}

watch(filter, (value) => filterEvents(value));

watch(() => props.events, (newVal) => {
    filterEvents(filter.value);
});

onMounted(() => {
    events.value = props.events;
});
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-2 my-2">
        <div class="flex items-center gap-2">
            <label class="font-bold text-sm">Buscar: </label>
            <input class="border rounded p-2 h-8 text-sm" type="text" v-model="filter" placeholder="Domicilio o persona...">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <template v-if="props.showAll">
                <div class="flex items-center gap-1">
                    <label class="text-xs font-bold text-gray-600">Año:</label>
                    <select class="border rounded px-2 py-1 h-8 text-sm bg-white" v-model="selectedYear" @change="onSelectFilterChange">
                        <option value="">Todos los años</option>
                        <option v-for="y in yearsList" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <label class="text-xs font-bold text-gray-600">Mes:</label>
                    <select class="border rounded px-2 py-1 h-8 text-sm bg-white" v-model="selectedMonth" @change="onSelectFilterChange">
                        <option value="">Todos los meses</option>
                        <option v-for="m in monthsList" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
            </template>

            <button class="btn rounded bg-slate-50 border p-1 px-3 hover:bg-slate-100 h-8 text-xs font-semibold"
                @click="toggleShowAll">{{ props.showAll ? 'Ver proximos eventos' : 'Ver todos' }}</button>
        </div>
    </div>
    <table class="table-fixed border table-records table-responsive-cards">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Domicilio</th>
                <th>Contrato</th>
                <th>Aprobado por</th>
                <th>Limpieza</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="event in events" :key="event.id">
                <td class="border p-1" data-label="Fecha">{{ format(new Date(event.reservation_date), "dd/MM/yyyy") }}</td>
                <td class="border p-1" data-label="Domicilio">
                    <div v-if="event.house">{{ event.house.street.name ?? '' }} {{ event.house.house_number ?? '' }}</div>
                </td>
                <td class="border p-1" data-label="Contrato">
                    <input type="checkbox" disabled class="appearance-none checked:bg-blue-500 m-auto block"
                        :checked="event.is_signed" />
                </td>
                <td class="border p-1" data-label="Aprobado por">{{ event.approved_by ? event.approved_by.name : '' }}</td>
                <td class="border p-1 text-center" data-label="Limpieza">
                    <button v-if="event.cleaning_report && event.cleaning_report.completed_at"
                        class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-300 font-semibold px-2 py-1 rounded text-xs inline-flex items-center gap-1 transition"
                        @click="openReportModal(event.cleaning_report, event)">
                        <i class="bi bi-clipboard-check"></i> Reporte limpieza
                    </button>
                </td>
                <td class="border p-1 text-center" data-label="Acciones">
                    <PrimaryButton @click="editEvent(event.id)">
                        <i class="bi bi-pencil"></i> Editar
                    </PrimaryButton>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- MODAL REPORTE DE LIMPIEZA -->
    <Teleport to="body">
        <div v-if="showReportModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="closeReportModal">
            <div class="bg-white rounded-lg max-w-lg w-full max-h-[90vh] flex flex-col shadow-xl overflow-hidden" @click.stop>
                <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Reporte de Limpieza</h3>
                        <p class="text-xs text-gray-500" v-if="selectedEvent">
                            Evento del {{ format(new Date(selectedEvent.reservation_date), "dd/MM/yyyy") }} - 
                            {{ selectedEvent.house ? `${selectedEvent.house.street.name} ${selectedEvent.house.house_number}` : '' }}
                        </p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 p-1 text-xl leading-none" @click="closeReportModal">&times;</button>
                </div>
                
                <div class="p-4 overflow-y-auto space-y-4">
                    <div class="bg-blue-50/60 p-3 rounded-md border border-blue-100 text-sm space-y-1">
                        <div>
                            <span class="text-gray-500 font-medium">Revisado por (Vecino):</span> 
                            <strong class="text-gray-800 ml-1">{{ selectedReport?.reviewer ? selectedReport.reviewer.name : 'N/A' }}</strong>
                        </div>
                        <div v-if="selectedReport?.reviewer?.phone">
                            <span class="text-gray-500 font-medium">Teléfono:</span> 
                            <span class="text-gray-800 ml-1">{{ selectedReport.reviewer.phone }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-medium">Fecha de revisión:</span> 
                            <span class="text-gray-800 ml-1">{{ selectedReport?.completed_at ? format(new Date(selectedReport.completed_at), "dd/MM/yyyy HH:mm") : 'N/A' }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold text-sm text-gray-700 mb-2">Resultados de criterios:</h4>
                        <table class="w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="p-2 border">Criterio</th>
                                    <th class="p-2 border text-center">Respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in selectedReport?.criteria || []" :key="item.id">
                                    <td class="p-2 border font-medium">
                                        {{ item.criteria ? item.criteria.name : 'Criterio' }}
                                    </td>
                                    <td class="p-2 border text-center">
                                        <template v-if="item.criteria && item.criteria.response_type === 'boolean'">
                                            <span v-if="item.response === '1'" class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2 py-0.5 rounded text-xs font-semibold">
                                                <i class="bi bi-check-circle-fill"></i> Cumple
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1 text-red-700 bg-red-100 px-2 py-0.5 rounded text-xs font-semibold">
                                                <i class="bi bi-x-circle-fill"></i> No cumple
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span class="font-semibold text-gray-800">{{ item.response || '-' }}</span>
                                        </template>
                                    </td>
                                </tr>
                                <tr v-if="(!selectedReport?.criteria || selectedReport.criteria.length === 0)">
                                    <td colspan="2" class="p-3 text-center text-gray-400 text-xs">Sin detalles de criterios</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-3 border-t bg-gray-50 flex justify-end">
                    <button class="bg-gray-700 text-white hover:bg-gray-800 px-4 py-1.5 rounded text-sm font-semibold transition" @click="closeReportModal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>