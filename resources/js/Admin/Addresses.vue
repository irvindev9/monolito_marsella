<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import Records from './Addresses/Records.vue';
import FormRecord from './Addresses/FormRecord.vue';

const store = useAdminStore();
const addresses = ref([]);
const createForm = ref(false);

onMounted(async () => {
    await getAddresses();
});

async function getAddresses() {
    store.setIsLoading(true);
    try {
        const { data } = await axios.get('/api/addresses')
        addresses.value = data;
        createForm.value = false;
    } catch (error) {
        console.log(error);
    }
    setTimeout(() => {
        store.setIsLoading(false);
    }, 500);
}
</script>

<template>
    <Head title="Domicilios" />

    <div class="my-3 flex justify-between">
        <label class="font-bold my-3">
            Domicilios
        </label>
        <i class="bi bi-house-add text-2xl cursor-pointer hover:text-blue-600" v-if="!createForm"
            @click="createForm = !createForm"></i>
        <i class="bi bi-arrow-return-left text-2xl cursor-pointer hover:text-blue-600" v-if="createForm"
            @click="createForm = !createForm"></i>
    </div>

    <Records :addresses="addresses" v-if="!createForm" @refresh="getAddresses" />

    <FormRecord v-if="createForm" @close="getAddresses" />
</template>