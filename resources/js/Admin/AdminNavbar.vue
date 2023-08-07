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
                <a href="#">
                    <i class="bi bi-calendar-plus"></i> Solicitudes terraza
                    <span v-if="reservationsPendings > 0"
                        class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </a>
            </li>
            <li class="list-item" @click="store.setActiveTab('events')">
                <a href="#">
                    <i class="bi bi-calendar-event"></i> Eventos
                </a>
            </li>
        </ul>
    </div>
</template>