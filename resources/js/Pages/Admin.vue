<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminNavbar from '@/Admin/AdminNavbar.vue';
import Users from '@/Admin/Users.vue';
import Addresses from '@/Admin/Addresses.vue';
import Club from '@/Admin/Club.vue';
import Events from '@/Admin/Events.vue';
import AddEvent from '@/Admin/AddEvent.vue';
import Requests from '@/Admin/Requests.vue';
import Restrictions from '@/Admin/Restrictions.vue';
import RestrictionList from '@/Admin/RestrictionList.vue';
import Directory from '@/Admin/Directory.vue';
import Access from '@/Admin/Access.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import LoadingScreen from '@/Components/LoadingScreen.vue';

const store = useAdminStore();

const isLoading = computed(() => store.isLoading);
const activeTab = computed(() => store.activeTab);

const showingAdminSidebar = ref(false);

watch(() => store.activeTab, () => {
    showingAdminSidebar.value = false;
});

</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>
        <div class="sm:hidden bg-black text-white flex items-center justify-between px-4 py-2">
            <h3 class="font-bold">Administrador</h3>
            <button @click="showingAdminSidebar = !showingAdminSidebar"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ hidden: showingAdminSidebar, 'inline-flex': !showingAdminSidebar }"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ hidden: !showingAdminSidebar, 'inline-flex': showingAdminSidebar }"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row">
            <div :class="{ block: showingAdminSidebar, hidden: !showingAdminSidebar }"
                class="sm:block sm:w-1/5 bg-black text-white py-3 admin-container">
                <h3 class="bd-white font-bold text-center pb-3 hidden sm:block">Administrador</h3>
                <AdminNavbar />
            </div>

            <div id="body" class="w-full sm:w-3/5 px-4 sm:mx-auto mt-3 bg-white rounded">
                <Users v-if="activeTab === 'users'" />

                <Addresses v-if="activeTab === 'address'" />

                <Club v-if="activeTab === 'club'" />

                <Requests v-if="activeTab === 'clubRequests'" />

                <Events v-if="activeTab === 'events'" />

                <AddEvent v-if="activeTab === 'addEvent'" />

                <Restrictions v-if="activeTab === 'restrictions'" />

                <RestrictionList v-if="activeTab === 'restrictionsList'" />

                <Directory v-if="activeTab === 'directory'" />

                <Access v-if="activeTab === 'access'" />
            </div>
        </div>
        <LoadingScreen :show="isLoading" />
    </AuthenticatedLayout>
</template>