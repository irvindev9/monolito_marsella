<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
    cooldownSeconds: {
        type: Number,
        default: 0,
    },
    email: {
        type: String,
        default: '',
    },
});

const digits = ref(['', '', '', '']);
const inputs = ref([]);
const cooldown = ref(props.cooldownSeconds);
let timer = null;

const verifyForm = useForm({
    code: '',
});

const resendForm = useForm({});

const codeComplete = computed(() => digits.value.every(d => /^\d$/.test(d)));

const formattedCooldown = computed(() => {
    const mins = Math.floor(cooldown.value / 60);
    const secs = cooldown.value % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
});

function startTimer(seconds) {
    if (timer) clearInterval(timer);
    cooldown.value = seconds;
    if (cooldown.value <= 0) return;

    timer = setInterval(() => {
        if (cooldown.value > 0) {
            cooldown.value--;
        } else {
            clearInterval(timer);
            timer = null;
        }
    }, 1000);
}

watch(() => props.cooldownSeconds, (val) => {
    if (val > 0) startTimer(val);
});

onMounted(() => {
    if (cooldown.value > 0) startTimer(cooldown.value);
    inputs.value[0]?.focus();
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

function onInput(idx, e) {
    const val = e.target.value.replace(/\D/g, '').slice(-1);
    digits.value[idx] = val;
    e.target.value = val;
    if (val && idx < 3) {
        inputs.value[idx + 1]?.focus();
    }
}

function onKeydown(idx, e) {
    if (e.key === 'Backspace' && !digits.value[idx] && idx > 0) {
        inputs.value[idx - 1]?.focus();
    }
}

function onPaste(e) {
    const pasted = (e.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 4);
    if (!pasted) return;
    e.preventDefault();
    pasted.split('').forEach((ch, i) => {
        digits.value[i] = ch;
    });
    const nextIdx = Math.min(pasted.length, 3);
    inputs.value[nextIdx]?.focus();
}

function submitCode() {
    verifyForm.code = digits.value.join('');
    verifyForm.post(route('verification.code'), {
        onError: () => {
            digits.value = ['', '', '', ''];
            inputs.value[0]?.focus();
        },
    });
}

function resendCode() {
    resendForm.post(route('verification.send'), {
        preserveScroll: true,
        onSuccess: () => {
            startTimer(600);
        },
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Verificar correo" />

        <h2 class="text-lg font-bold text-gray-900 mb-2">Verifica tu correo electrónico</h2>
        <p class="mb-4 text-sm text-gray-600">
            Enviamos un código de 4 dígitos a <span class="font-semibold text-gray-800">{{ email }}</span>.
            Ingrésalo a continuación para continuar.
        </p>

        <div v-if="status === 'verification-code-sent'" class="mb-4 font-medium text-sm text-green-600">
            Se envió un nuevo código de verificación a tu correo.
        </div>

        <form @submit.prevent="submitCode">
            <div class="flex justify-center gap-3 my-4" @paste="onPaste">
                <input
                    v-for="(_, idx) in 4"
                    :key="idx"
                    :ref="el => inputs[idx] = el"
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    :value="digits[idx]"
                    @input="onInput(idx, $event)"
                    @keydown="onKeydown(idx, $event)"
                    class="w-12 h-14 text-center text-2xl font-bold border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                    :aria-label="`Dígito ${idx + 1}`"
                />
            </div>

            <InputError class="mt-2 text-center" :message="verifyForm.errors.code" />
            <InputError class="mt-2 text-center" :message="resendForm.errors.resend" />

            <div class="mt-5 flex flex-col gap-3">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': verifyForm.processing || !codeComplete }"
                    :disabled="verifyForm.processing || !codeComplete"
                >
                    Verificar correo
                </PrimaryButton>

                <div class="flex items-center justify-between pt-2">
                    <button
                        type="button"
                        @click="resendCode"
                        :disabled="resendForm.processing || cooldown > 0"
                        class="underline text-sm text-gray-600 hover:text-gray-900 disabled:opacity-50 disabled:no-underline disabled:cursor-not-allowed"
                    >
                        {{ cooldown > 0 ? `Reenviar código en ${formattedCooldown}` : 'Enviar / Reenviar código' }}
                    </button>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cerrar sesión
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
