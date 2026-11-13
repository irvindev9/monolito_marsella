<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { format } from 'date-fns';
import { useAdminStore } from '@/Stores/adminStore';
import { toast } from 'vue3-toastify';

const BANKS = ['BBVA', 'Santander', 'Banorte', 'HSBC'];

const store = useAdminStore();
const configs = ref([]);
const logs = ref([]);

const bankConfig = computed(() => configs.value.find(c => c.slug === 'payment_bank'));
const clabeConfig = computed(() => configs.value.find(c => c.slug === 'payment_clabe'));
const beneficiaryConfig = computed(() => configs.value.find(c => c.slug === 'payment_beneficiary'));

async function fetchLogs() {
    const { data } = await axios.get('/api/configs/payment-logs');
    logs.value = data;
}

onMounted(async () => {
    store.setIsLoading(true);
    try {
        const [{ data }] = await Promise.all([
            axios.get('/api/configs'),
            fetchLogs(),
        ]);
        configs.value = data;
    } catch (error) {
        toast.error('No se pudieron cargar los datos de pago');
    } finally {
        store.setIsLoading(false);
    }
});

function sanitizeClabe(e) {
    if (!clabeConfig.value) return;
    clabeConfig.value.setting = e.target.value.replace(/[^\d\s]/g, '');
}

async function savePaymentData() {
    store.setIsLoading(true);
    try {
        const payload = [bankConfig.value, clabeConfig.value, beneficiaryConfig.value].filter(Boolean);
        const { data } = await axios.put('/api/configs', payload);
        await fetchLogs();
        toast.success(data.message);
    } catch (error) {
        toast.error(error?.response?.data?.message || 'Error al guardar los datos de pago');
    } finally {
        store.setIsLoading(false);
    }
}
</script>

<template>
    <Head title="Datos de pago" />
    <h3 class="my-3 font-bold">Datos de pago</h3>

    <div v-if="bankConfig && clabeConfig && beneficiaryConfig" class="space-y-4 pb-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-1/2 flex flex-col">
                <label class="font-bold mb-2">Banco</label>
                <select
                    v-model="bankConfig.setting"
                    class="border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-1 focus:ring-blue-600"
                >
                    <option v-for="bank in BANKS" :key="bank" :value="bank">{{ bank }}</option>
                </select>
                <img
                    v-if="bankConfig.setting === 'BBVA'"
                    src="/images/bbva.png"
                    alt="BBVA"
                    class="mt-3 h-7 w-auto self-start object-contain"
                />
            </div>

            <div class="w-full sm:w-1/2 flex flex-col">
                <label class="font-bold mb-2">CLABE interbancaria</label>
                <input
                    type="text"
                    inputmode="numeric"
                    :value="clabeConfig.setting"
                    @input="sanitizeClabe"
                    placeholder="Ej: 012 180 00123456789 0"
                    class="border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-1 focus:ring-blue-600"
                />
                <span class="text-xs text-gray-500 mt-1">Solo números y espacios.</span>
            </div>
        </div>

        <div class="flex flex-col">
            <label class="font-bold mb-2">Nombre de beneficiario</label>
            <input
                type="text"
                v-model="beneficiaryConfig.setting"
                placeholder="Nombre completo o razón social"
                class="border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-1 focus:ring-blue-600"
            />
        </div>

        <div class="flex justify-end">
            <PrimaryButton @click="savePaymentData">
                Guardar ajustes
            </PrimaryButton>
        </div>
    </div>

    <div class="mt-6">
        <h4 class="my-3 font-bold">Historial de cambios</h4>
        <div v-if="logs.length === 0" class="text-sm text-gray-500">
            Sin cambios registrados.
        </div>
        <table v-else class="w-full border table-records table-responsive-cards text-sm">
            <thead>
                <tr>
                    <th class="border p-2 text-left">Fecha y hora</th>
                    <th class="border p-2 text-left">Usuario</th>
                    <th class="border p-2 text-left">Cambios</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id">
                    <td class="border p-2 whitespace-nowrap" data-label="Fecha y hora">
                        {{ format(new Date(log.created_at), 'dd/MM/yyyy HH:mm') }}
                    </td>
                    <td class="border p-2" data-label="Usuario">
                        {{ log.user_name }}
                    </td>
                    <td class="border p-2" data-label="Cambios">
                        <ul class="space-y-1">
                            <li v-for="(change, idx) in log.changes" :key="idx">
                                <span class="font-semibold">{{ change.field }}:</span>
                                {{ change.from || '—' }} → {{ change.to || '—' }}
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
