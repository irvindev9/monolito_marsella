<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import { toast } from 'vue3-toastify';

const store = useAdminStore();
const configs = ref(null);

onMounted(async () => {
    store.setIsLoading(true)
    try {
        const { data } = await axios.get('/api/configs');
        configs.value = data;
        store.setIsLoading(false);
    } catch (error) {
        console.log(error);
        store.setIsLoading(false);
    }
});

async function saveConfig() {
    store.setIsLoading(true)
    try {
        const { data } = await axios.put('/api/configs', configs.value);
        toast.success(data.message);
        store.setIsLoading(false);
    } catch (error) {
        console.log(error);
        store.setIsLoading(false);
    }
}
</script>

<template>
    <Head title="Terraza" />
    <h3 for="title" class="my-3 font-bold">Terraza</h3>

    <div class="flex">
        <div class="w-1/3 px-3">
            <div class="flex flex-col" v-if="configs">
                <label class="font-bold my-3">
                    {{ configs.find(config => config.slug === 'sd').description }}
                </label>
                <select class="border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-1 focus:ring-blue-600"
                    v-model="configs.find(config => config.slug === 'sd').setting">
                    <option value="1">1 mes</option>
                    <option value="2">2 meses</option>
                    <option value="3">3 meses</option>
                    <option value="4">4 meses</option>
                    <option value="6">6 meses</option>
                    <option value="12">12 meses</option>
                </select>
            </div>
        </div>
        <div class="w-2/3 px-3">
            <div class="flex flex-col" v-if="configs">
                <label class="font-bold my-3">
                    {{ configs.find(config => config.slug === 'ttps').description }}
                </label>
                <input type="text"
                    class="border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-1 focus:ring-blue-600"
                    v-model="configs.find(config => config.slug === 'ttps').setting">
            </div>
        </div>
    </div>
    <div class="flex">
        <div class="w-full px-3">
            <div class="flex flex-col" v-if="configs">
                <label class="font-bold my-3">
                    {{ configs.find(config => config.slug === 'iro').description }}
                </label>
                <div class="flex my-1">
                    <input id="open" type="radio" class="appearance-none checked:bg-blue-500 mt-1 mr-1" value="1"
                        v-model="configs.find(config => config.slug === 'iro').setting">
                    <label for="open">Abierto</label><br>
                </div>
                <div class="flex my-1">
                    <input id="close" type="radio" class="appearance-none indeterminate:bg-gray-300 mt-1 mr-1" value="0"
                        v-model="configs.find(config => config.slug === 'iro').setting">
                    <label for="close">Cerrado</label><br>
                </div>
            </div>
        </div>
    </div>
    <div class="flex flex-row-reverse">
        <div class="w-full px-3 grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveConfig">
                Guardar ajustes
            </PrimaryButton>
        </div>
    </div>
</template>