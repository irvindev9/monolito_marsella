<script setup>
import Records from './Users/Records.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import FormRecord from './Users/FormRecord.vue';

const store = useAdminStore();
const users = ref([]);
const editForm = ref(false);
const editUser = ref(null);

onMounted(async () => {
    await getUsers();
});

function openEditor(id) {
    editForm.value = true;
    editUser.value = users.value.find(user => user.id === id);
}

async function getUsers() {
    store.setIsLoading(true);
    const { data } = await axios.get('/api/users')
    users.value = data;
    setTimeout(() => {
        store.setIsLoading(false);
    }, 500);
}

async function updateUser() {
    await getUsers();
    editForm.value = false;
}
</script>

<template>
    <Head title="Usuarios" />

    <h4 class="font-bold my-3">
        Usuarios
    </h4>
    <Records :users="users" v-if="!editForm" @close="openEditor" @refresh="getUsers" />

    <FormRecord :editUser="editUser" v-if="editForm" @close="updateUser" />
</template>