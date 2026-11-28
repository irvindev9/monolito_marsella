<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePasskey } from '@/Composables/usePasskey';

defineProps({
    passkeys: {
        type: Array,
        default: () => [],
    },
});

const alias = ref('');
const successMessage = ref('');
const deletingId = ref(null);
const { processing, error, isSupported, registerPasskey } = usePasskey();

const handleRegister = async () => {
    successMessage.value = '';
    const result = await registerPasskey(alias.value.trim());
    if (result) {
        alias.value = '';
        successMessage.value = 'Passkey registrada exitosamente.';
        router.reload({ only: ['passkeys'] });
    }
};

const handleDelete = (id) => {
    deletingId.value = id;
    successMessage.value = '';
    router.delete(route('webauthn.destroy', id), {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Acceso con Passkey (Huella / Face ID)</h2>

            <p class="mt-1 text-sm text-gray-600">
                Vincula este dispositivo para iniciar sesión de forma rápida y segura sin necesidad de escribir tu contraseña.
            </p>
        </header>

        <div v-if="passkeys.length" class="mt-4 divide-y divide-gray-200 rounded-md border border-gray-200">
            <div
                v-for="item in passkeys"
                :key="item.id"
                class="flex items-center justify-between px-4 py-3 text-sm"
            >
                <div class="flex items-center gap-3">
                    <i class="bi bi-fingerprint text-lg text-indigo-600"></i>
                    <div>
                        <p class="font-medium text-gray-900">{{ item.alias }}</p>
                        <p v-if="item.created_at" class="text-xs text-gray-500">
                            Registrado el {{ item.created_at }}
                        </p>
                    </div>
                </div>

                <SecondaryButton
                    type="button"
                    :disabled="deletingId === item.id"
                    @click="handleDelete(item.id)"
                >
                    Eliminar
                </SecondaryButton>
            </div>
        </div>

        <p v-else class="mt-4 text-sm text-gray-500">
            Aún no tienes ninguna Passkey configurada en tu cuenta.
        </p>

        <form v-if="isSupported()" @submit.prevent="handleRegister" class="mt-6 space-y-4">
            <div>
                <InputLabel for="passkey_alias" value="Nombre del dispositivo (opcional)" />

                <TextInput
                    id="passkey_alias"
                    v-model="alias"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Ej. Mi iPhone, Teléfono personal"
                />

                <InputError :message="error" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="processing">
                    <i class="bi bi-fingerprint mr-2"></i>
                    Registrar este dispositivo
                </PrimaryButton>

                <p v-if="successMessage" class="text-sm text-green-600">
                    {{ successMessage }}
                </p>
            </div>
        </form>

        <p v-else class="mt-4 text-sm text-amber-700">
            Este navegador o dispositivo no soporta Passkeys.
        </p>
    </section>
</template>
