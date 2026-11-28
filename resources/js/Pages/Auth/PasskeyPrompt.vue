<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { usePasskey } from '@/Composables/usePasskey';

const { processing, error, isSupported, registerPasskey } = usePasskey();
const dismissForm = useForm({});

const configureNow = async () => {
    const result = await registerPasskey();
    if (result) {
        router.visit(result.redirect || route('dashboard'));
    }
};

const configureLater = () => {
    dismissForm.post(route('passkey.dismiss'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Configurar acceso rápido" />

        <div class="text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                <i class="bi bi-fingerprint text-2xl"></i>
            </div>

            <h2 class="text-lg font-bold text-gray-900">
                Inicia sesión más rápido con Passkey
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                Activa el acceso con tu huella digital, Face ID o bloqueo de pantalla para entrar en un solo toque sin escribir tu contraseña.
            </p>
        </div>

        <div v-if="!isSupported()" class="mt-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">
            Este navegador o dispositivo no es compatible con Passkeys. Puedes continuar al panel e intentarlo más tarde desde otro dispositivo.
        </div>

        <InputError class="mt-3 text-center" :message="error" />

        <div class="mt-6 flex flex-col gap-3">
            <PrimaryButton
                v-if="isSupported()"
                type="button"
                class="w-full justify-center"
                :class="{ 'opacity-25': processing || dismissForm.processing }"
                :disabled="processing || dismissForm.processing"
                @click="configureNow"
            >
                <i class="bi bi-fingerprint mr-2"></i>
                Configurar ahora
            </PrimaryButton>

            <SecondaryButton
                type="button"
                class="w-full justify-center"
                :class="{ 'opacity-25': processing || dismissForm.processing }"
                :disabled="processing || dismissForm.processing"
                @click="configureLater"
            >
                Configurar después desde mi perfil
            </SecondaryButton>
        </div>
    </GuestLayout>
</template>
