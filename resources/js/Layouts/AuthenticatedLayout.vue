<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Modal from '@/Components/Modal.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import axios from 'axios';

const showingNavigationDropdown = ref(false);
const page = usePage();
const pushSupported = ref(false);
const pushSubscribed = ref(false);
const pushLoading = ref(false);
const showIosInstallHint = ref(false);

const showInstallBanner = ref(false);
const showInstallModal = ref(false);
const deferredInstallPrompt = ref(null);
const installPlatform = ref('ios');

const INSTALL_COOKIE = 'marsella_pwa_install_dismissed';

const hasDismissCookie = () =>
    document.cookie.split('; ').some((row) => row.startsWith(`${INSTALL_COOKIE}=`));

const setDismissCookie = () => {
    const maxAge = 14 * 24 * 60 * 60; // 14 days
    document.cookie = `${INSTALL_COOKIE}=1; max-age=${maxAge}; path=/; SameSite=Lax`;
};

const dismissInstallBanner = () => {
    setDismissCookie();
    showInstallBanner.value = false;
};

const openInstallGuide = async () => {
    setDismissCookie();
    showInstallBanner.value = false;

    if (deferredInstallPrompt.value) {
        deferredInstallPrompt.value.prompt();
        const { outcome } = await deferredInstallPrompt.value.userChoice;
        deferredInstallPrompt.value = null;
        if (outcome === 'accepted') {
            return;
        }
    }
    showInstallModal.value = true;
};

const handleBeforeInstallPrompt = (e) => {
    e.preventDefault();
    deferredInstallPrompt.value = e;
};

const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
};

onMounted(async () => {
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    const ua = window.navigator.userAgent;
    const isIos = /iphone|ipad|ipod/i.test(ua);
    const isAndroid = /android/i.test(ua);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    installPlatform.value = isIos ? 'ios' : isAndroid ? 'android' : 'desktop';

    if (!isStandalone && !hasDismissCookie()) {
        showInstallBanner.value = true;
    }

    if ('serviceWorker' in navigator && 'PushManager' in window && page.props.vapidPublicKey) {
        pushSupported.value = true;
        try {
            const registration = await navigator.serviceWorker.ready;
            const sub = await registration.pushManager.getSubscription();
            pushSubscribed.value = !!sub;
        } catch {
            pushSupported.value = false;
        }
    } else if (isIos && !isStandalone) {
        showIosInstallHint.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});

const togglePush = async () => {
    if (showIosInstallHint.value) {
        installPlatform.value = 'ios';
        showInstallModal.value = true;
        return;
    }
    if (!pushSupported.value || pushLoading.value) return;

    pushLoading.value = true;
    try {
        const registration = await navigator.serviceWorker.ready;
        const existing = await registration.pushManager.getSubscription();

        if (existing) {
            await axios.post(route('push-subscriptions.destroy'), { endpoint: existing.endpoint });
            await existing.unsubscribe();
            pushSubscribed.value = false;
            toast.info('Notificaciones desactivadas en este dispositivo');
        } else {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                toast.warning('Debes permitir las notificaciones en tu navegador');
                return;
            }
            const sub = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(page.props.vapidPublicKey),
            });
            const subJson = sub.toJSON();
            const encoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
            await axios.post(route('push-subscriptions.store'), {
                endpoint: subJson.endpoint,
                keys: subJson.keys,
                contentEncoding: encoding,
            });
            pushSubscribed.value = true;
            toast.success('Notificaciones activadas');
        }
    } catch {
        toast.error('No se pudieron configurar las notificaciones');
    } finally {
        pushLoading.value = false;
    }
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100" id="nav-auth">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Terraza
                                </NavLink>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('directory')" :active="route().current('directory')">
                                    Directorio
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <button
                                v-if="pushSupported || showIosInstallHint"
                                type="button"
                                @click="togglePush"
                                :disabled="pushLoading"
                                :title="pushSubscribed ? 'Notificaciones activas (clic para desactivar)' : 'Activar notificaciones en este dispositivo'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium border transition ease-in-out duration-150"
                                :class="pushSubscribed
                                    ? 'text-sky-700 bg-sky-50 border-sky-200 hover:bg-sky-100'
                                    : 'text-gray-600 bg-white border-gray-200 hover:bg-gray-50'"
                            >
                                <i :class="pushSubscribed ? 'bi bi-bell-fill' : 'bi bi-bell'"></i>
                                <span>{{ pushSubscribed ? 'Alertas activas' : 'Activar alertas' }}</span>
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                        <DropdownLink v-if="$page.props.auth.user.role_id === 1" :href="route('admin')">
                                            Panel Administrador
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Cerrar sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center gap-1 sm:hidden">
                            <button
                                v-if="pushSupported || showIosInstallHint"
                                type="button"
                                @click="togglePush"
                                :disabled="pushLoading"
                                :title="pushSubscribed ? 'Notificaciones activas' : 'Activar notificaciones'"
                                class="inline-flex items-center justify-center p-2 rounded-md transition duration-150 ease-in-out"
                                :class="pushSubscribed ? 'text-sky-600 bg-sky-50' : 'text-gray-500 hover:bg-gray-100'"
                            >
                                <i :class="pushSubscribed ? 'bi bi-bell-fill text-lg' : 'bi bi-bell text-lg'"></i>
                            </button>

                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Terraza
                        </ResponsiveNavLink>

                        <ResponsiveNavLink :href="route('directory')" :active="route().current('directory')">
                            Directorio
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="$page.props.auth.user.role_id === 1" :href="route('admin')"> Panel
                                Administrador </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Cerrar sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- PWA Install Banner (omitted when already installed or dismissed in last 14 days) -->
            <div v-if="showInstallBanner" class="bg-sky-900 text-white border-b border-sky-800">
                <div class="max-w-7xl mx-auto px-4 py-2.5 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i class="bi bi-phone-fill text-sky-300 text-base shrink-0"></i>
                        <p class="text-xs sm:text-sm font-medium truncate">
                            Instala <span class="font-semibold">Versalles Marsella</span> en tu inicio para acceso rápido y recibir notificaciones.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            @click="openInstallGuide"
                            class="px-3 py-1 rounded-md bg-white text-sky-900 hover:bg-sky-50 text-xs font-semibold transition"
                        >
                            Cómo instalar
                        </button>
                        <button
                            type="button"
                            @click="dismissInstallBanner"
                            title="Cerrar aviso por 14 días"
                            class="p-1 rounded-md text-sky-200 hover:text-white hover:bg-sky-800 transition"
                        >
                            <i class="bi bi-x-lg text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Step-by-Step Install Modal -->
            <Modal :show="showInstallModal" maxWidth="md" @close="showInstallModal = false">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <img src="/images/icon-192.png" alt="Marsella" class="w-10 h-10 rounded-lg border border-gray-200" />
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Instalar Versalles Marsella</h3>
                                <p class="text-xs text-gray-500">Agrégala a tu pantalla de inicio en 3 pasos</p>
                            </div>
                        </div>
                        <button type="button" @click="showInstallModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Platform Tabs -->
                    <div class="flex gap-1 p-1 bg-gray-100 rounded-lg mb-4 text-xs font-semibold">
                        <button
                            type="button"
                            @click="installPlatform = 'ios'"
                            class="flex-1 py-1.5 rounded-md transition"
                            :class="installPlatform === 'ios' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <i class="bi bi-apple mr-1"></i> iPhone / iPad
                        </button>
                        <button
                            type="button"
                            @click="installPlatform = 'android'"
                            class="flex-1 py-1.5 rounded-md transition"
                            :class="installPlatform === 'android' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <i class="bi bi-android2 mr-1"></i> Android
                        </button>
                        <button
                            type="button"
                            @click="installPlatform = 'desktop'"
                            class="flex-1 py-1.5 rounded-md transition"
                            :class="installPlatform === 'desktop' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <i class="bi bi-laptop mr-1"></i> PC / Mac
                        </button>
                    </div>

                    <!-- iOS Steps -->
                    <ol v-if="installPlatform === 'ios'" class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                            <div>Abre esta página desde <strong>Safari</strong> y toca el botón <strong>Compartir</strong> <i class="bi bi-box-arrow-up text-sky-600"></i> en la barra inferior.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                            <div>Desliza hacia abajo en el menú y selecciona <strong>"Agregar a inicio"</strong> <i class="bi bi-plus-square text-sky-600"></i>.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                            <div>Toca <strong>"Agregar"</strong> arriba a la derecha, abre la app desde tu inicio y presiona <strong>"Activar alertas"</strong>.</div>
                        </li>
                    </ol>

                    <!-- Android Steps -->
                    <ol v-else-if="installPlatform === 'android'" class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                            <div>Toca el menú de tres puntos <i class="bi bi-three-dots-vertical text-sky-600"></i> en la esquina superior derecha de <strong>Chrome</strong>.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                            <div>Selecciona <strong>"Instalar aplicación"</strong> o <strong>"Agregar a la pantalla principal"</strong>.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                            <div>Confirma tocando <strong>"Instalar"</strong> y activa las notificaciones con el botón <strong>"Activar alertas"</strong>.</div>
                        </li>
                    </ol>

                    <!-- Desktop Steps -->
                    <ol v-else class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                            <div>En la barra de direcciones de Chrome o Edge (a la derecha), haz clic en el ícono de <strong>Instalar</strong> <i class="bi bi-download text-sky-600"></i>.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                            <div>Confirma haciendo clic en <strong>"Instalar"</strong> en la ventana emergente.</div>
                        </li>
                        <li class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                            <div>Activa el botón <strong>"Activar alertas"</strong> en la barra superior para recibir avisos de tus reservas.</div>
                        </li>
                    </ol>

                    <div class="mt-5 flex justify-end">
                        <button
                            type="button"
                            @click="showInstallModal = false"
                            class="px-4 py-2 bg-sky-700 hover:bg-sky-800 text-white text-xs font-semibold rounded-md transition"
                        >
                            Entendido
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
</template>
