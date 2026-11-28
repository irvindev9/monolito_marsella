<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import terraza1 from '../../assets/terraza1.jpeg';
import terraza2 from '../../assets/terraza2.jpeg';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Inicio" />

    <div class="min-h-screen bg-[#f8fafc] text-[#1c2633] flex flex-col justify-between selection:bg-[#2A4B6D] selection:text-white">
        <main class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 flex-1 flex flex-col justify-center">
            <!-- Encabezado y Acciones Principales -->
            <div class="text-center max-w-xl mx-auto">
                <div class="flex justify-center">
                    <ApplicationLogo class="w-20 h-20 sm:w-24 sm:h-24 drop-shadow-sm" />
                </div>

                <h1 class="mt-5 text-2xl sm:text-4xl font-bold tracking-tight text-[#1c2633]">
                    Versalles Marsella
                </h1>
                <p class="mt-2 text-sm sm:text-base text-[#5d6b7a]">
                    Portal de residentes para reservación de terraza y directorio vecinal.
                </p>

                <!-- Botones de Acceso Directo -->
                <div v-if="canLogin" class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5 sm:gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-lg bg-[#1c2633] hover:bg-[#2A4B6D] text-white font-semibold text-base shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#2A4B6D] focus:ring-offset-2"
                    >
                        <i class="bi bi-calendar-check"></i>
                        <span>Ir a Terraza</span>
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="w-full sm:w-auto sm:min-w-[200px] inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-lg bg-[#1c2633] hover:bg-[#2A4B6D] text-white font-semibold text-base shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#2A4B6D] focus:ring-offset-2"
                        >
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Iniciar sesión</span>
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="w-full sm:w-auto sm:min-w-[200px] inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-lg bg-white hover:bg-slate-50 text-[#1c2633] border border-[#cbd5e1] font-semibold text-base shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#2A4B6D] focus:ring-offset-2"
                        >
                            <i class="bi bi-person-plus"></i>
                            <span>Crear cuenta</span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Las 2 mejores fotos del fraccionamiento -->
            <div class="mt-10 sm:mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                <figure class="group relative overflow-hidden rounded-xl bg-white border border-[#e2e8f0] shadow-md">
                    <img
                        :src="terraza2"
                        alt="Acceso principal Versalles Marsella"
                        class="w-full h-64 sm:h-72 object-cover object-center group-hover:scale-[1.02] transition-transform duration-300"
                    />
                    <figcaption class="px-4 py-3 bg-white border-t border-[#f1f5f9] flex items-center justify-between text-xs sm:text-sm text-[#475569]">
                        <span class="font-semibold text-[#1c2633]">Acceso Principal</span>
                        <span>Fraccionamiento Marsella</span>
                    </figcaption>
                </figure>

                <figure class="group relative overflow-hidden rounded-xl bg-white border border-[#e2e8f0] shadow-md">
                    <img
                        :src="terraza1"
                        alt="Terraza y área común Versalles Marsella"
                        class="w-full h-64 sm:h-72 object-cover object-center group-hover:scale-[1.02] transition-transform duration-300"
                    />
                    <figcaption class="px-4 py-3 bg-white border-t border-[#f1f5f9] flex items-center justify-between text-xs sm:text-sm text-[#475569]">
                        <span class="font-semibold text-[#1c2633]">Terraza y Área Común</span>
                        <span>Espacio para eventos y juegos</span>
                    </figcaption>
                </figure>
            </div>
        </main>

        <footer class="py-6 px-4 text-center text-sm text-[#5d6b7a]">
            <a
                href="http://irvin-lopez.com"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 hover:text-[#1c2633] transition-colors"
            >
                <i class="bi bi-lightning-charge"></i>
                <span>Irvin Raúl López</span>
            </a>
        </footer>
    </div>
</template>
