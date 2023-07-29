<script setup>
import { useAdminStore } from '@/Stores/adminStore';

const store = useAdminStore();

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