<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import Records from './Addresses/Records.vue';
import Form from './Addresses/Form.vue';

const store = useAdminStore();
const addresses = ref([]);

onMounted(async () => {
    store.setIsLoading(true);
    const { data } = await axios.get('/api/addresses')
    addresses.value = data;
    setTimeout(() => {
        store.setIsLoading(false);
    }, 1000);
});
</script>

<template>
    <Head title="Domicilios" />

    <div class="my-3 flex justify-between">
        <label class="font-bold my-3">
            Domicilios
        </label>
        <i class="bi bi-house-add text-2xl cursor-pointer hover:text-blue-600"></i>
    </div>

    <Records :addresses="addresses" />

    <Form />
</template>