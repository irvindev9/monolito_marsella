<script setup>
import { useAdminStore } from '@/Stores/adminStore';
import { ref, onMounted } from 'vue';

const store = useAdminStore();
const reservationsPendings = ref(0);

async function getReservationsPendings() {
    const { data } = await axios.get('/api/reservations/approval-requests/count')

    reservationsPendings.value = data;
}

onMounted(async () => {
    await getReservationsPendings();
});

</script>
<template>
    <div>
        <ul>
            <li class="list-item" @click="store.setActiveTab('users')">
                <a href="#">
                    <i class="bi bi-person-gear"></i> Usuarios
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('address')">
                <a href="#">
                    <i class="bi bi-house-gear"></i> Domicilios
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('club')">
                <a href="#">
                    <i class="bi bi-gear"></i> Terraza
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('clubRequests')">
                <a href="#" class="relative">
                    <i class="bi bi-calendar-plus"></i> Solicitudes terraza
                    <span v-if="reservationsPendings > 0"
                        class="animate-ping absolute inline-flex top-1 h-3 w-3 rounded-full bg-red-400 opacity-75"></span>
                    <span v-if="reservationsPendings > 0"
                        class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('events')">
                <a href="#">
                    <i class="bi bi-calendar-event"></i> Eventos
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('addEvent')">
                <a href="#">
                    <i class="bi bi-pen"></i> Crear evento
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('restrictions')">
                <a href="#">
                    <i class="bi bi-person-lock"></i> Bloqueos
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('restrictionsList')">
                <a href="#">
                    <i class="bi bi-house-lock"></i> Lista de bloqueos
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('directory')">
                <a href="#">
                    <i class="bi bi-journal-medical"></i> Directorio
                </a>
            </li>
        </ul>
    </div>
</template>