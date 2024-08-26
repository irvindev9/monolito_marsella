<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useAdminStore } from '@/Stores/adminStore';
import axios from 'axios';
import draggable from 'vuedraggable'
import { ref, onMounted } from 'vue';
import { toast } from 'vue3-toastify';

const userDirectory = ref([]);
const store = useAdminStore();

onMounted(async () => {
    await getUserDirectory();
});

async function getUserDirectory() {
    try {
        const { data } = await axios.get('/api/directory');
        userDirectory.value = data;
    } catch (error) {
        console.log(error);
    }
}

async function saveUserDirectory() {

    store.setIsLoading(true);
    const newOrder = userDirectory.value.map((user, index) => {
        return {
            ...user,
            order: index + 1
        };
    });

    for (const user of newOrder) {
        try {
            await axios.put(`/api/directory/${user.id}`, user);
        } catch (error) {
            console.log(error);
        }
    }

    await getUserDirectory();

    toast.success('Directorio actualizado');
    store.setIsLoading(false);
}

async function deleteUser(user) {
    try {
        await axios.delete(`/api/directory/${user.id}`);
        await getUserDirectory();
        toast.success('Usuario eliminado');
    } catch (error) {
        console.log(error);
    }
}
</script>

<template>
    <draggable v-model="userDirectory" tag="ol" class="list-group list-group-numbered mb-5">
        <template #item="{ element: user }">
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="container-grip">
                    <i class="bi bi-grip-vertical"></i>
                </div>
                <div class="ms-2 me-auto pr-5">
                    <div class="fw-bold">{{ user.name }}</div>
                    <small>
                        Posición: {{ user.position }} <br>
                        Descripción: {{ user.description ?? '' }} <br>
                        Tel: {{ user.phone }}
                    </small>
                    <br>
                    <SecondaryButton @click="deleteUser(user)">Eliminar registro</SecondaryButton>
                </div>
                <span class="badge text-bg-primary rounded-pill">{{ user.order }}</span>
            </li>
        </template>
    </draggable>
    <PrimaryButton v-if="userDirectory.length" @click="saveUserDirectory">Guardar cambios</PrimaryButton>
</template>

<style lang="scss" scoped>
.list-group-numbered {
    list-style-type: none;
    counter-reset: section;
}

.list-group-item:first-child {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.list-group-item:last-child {
    border-bottom-right-radius: 10px;
    border-bottom-left-radius: 10px;
}

.list-group-item {
    cursor: grab;
}

.list-group-item:active {
    cursor: grabbing;
}

.align-items-start {
    align-items: flex-start !important;
}

.justify-content-between {
    justify-content: space-between !important;
}

.d-flex {
    display: flex !important;
}

.list-group-item {
    position: relative;
    display: block;
    padding: 0.5rem 1rem;
    color: #212529;
    text-decoration: none;
    background-color: #fff;
    border: 1px solid #dee2e6;
}

.ms-2 {
    margin-inline-start: 0.5rem;
}

.me-auto {
    margin-inline-end: auto;
}

.ms-2 {
    margin-left: 0.5rem !important;
}

.me-auto {
    margin-right: auto !important;
}

.fw-bold {
    font-weight: 700 !important;
}

.container-grip {
    position: relative;
    min-height: 120px;
}

.bi-grip-vertical {
    cursor: grab;
    position: absolute;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
}
</style>