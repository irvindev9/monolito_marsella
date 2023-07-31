<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref, onMounted, defineEmits, computed, reactive, defineProps } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import { toast } from 'vue3-toastify';

const props = defineProps({
    editUser: {
        type: Object,
        default: null,
    },
});

const store = useAdminStore();

const address = ref('');
const addresses = ref([]);
const selectAddress = ref(0);
const emit = defineEmits(['close']);

onMounted(async () => {
    store.setIsLoading(true);
    const { data } = await axios.get('/api/addresses');
    store.setIsLoading(false);
    addresses.value = data.map(address => {
        return {
            id: address.id,
            address: `${address.street.name} ${address.house_number}`
        }
    });

    if (props.editUser.house_id) {
        setAddress(props.editUser.house_id);
    }
});

const filterAddress = computed(() => {
    return address.value.length > 0 ? addresses.value.filter(item => item.address.toLowerCase().includes(address.value.toLowerCase())) : [];
});

function setAddress(id) {
    selectAddress.value = id;
    if (id > 0) {
        address.value = addresses.value.find(item => item.id === id).address;
    }
}

function validateAddress() {
    selectAddress.value = 0;

    if (filterAddress.value.length === 1 && address.value.toLowerCase() === filterAddress.value[0].address.toLowerCase()) {
        selectAddress.value = filterAddress.value[0].id;
        address.value = filterAddress.value[0].address;
    }
}

async function saveAddress() {
    if (selectAddress.value === 0) {
        toast.error('Debes seleccionar una dirección');
        return;
    }

    await axios.put(`/api/users/${props.editUser.id}`, {
        house_id: selectAddress.value,
    });

    toast.success('Domicilio asignado');

    emit('close');
}
</script>

<template>
    <div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Nombre
                    </label>
                    <input type="text" class="border rounded p-2 read-only:bg-gray-100" readonly disabled
                        :value="editUser.name" />
                </div>
            </div>
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Correo
                    </label>
                    <input type="text" class="border rounded p-2 read-only:bg-gray-100" readonly disabled
                        :value="editUser.email" />
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Asignar domicilio
                    </label>
                    <input type="text" class="border rounded p-2 read-only:bg-gray-100" v-model="address"
                        @keyup="validateAddress" />
                    <div class="autocomplete" v-if="filterAddress.length > 0 && !selectAddress">
                        <ul class="autocomplete-items">
                            <li class="autocomplete-item" v-for="(item, index) in filterAddress" :key="index"
                                @click="setAddress(item.id)">
                                {{ item.address }}
                            </li>
                        </ul>
                    </div>
                    <div v-if="selectAddress === 0 && filterAddress.length === 0 && address.length > 0">
                        <span class="text-xs">No hay resultados, agregue el domicilio</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-row-reverse">
            <PrimaryButton class="mt-1" @click="saveAddress">
                Agregar
            </PrimaryButton>
            <DangerButton class="mt-1 mx-1" @click="emit('close')">
                Cancelar
            </DangerButton>
        </div>
    </div>
</template>