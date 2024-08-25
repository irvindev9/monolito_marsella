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
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAdminStore } from '@/Stores/adminStore';
import LoadingScreen from '@/Components/LoadingScreen.vue';

const store = useAdminStore();

const isLoading = computed(() => store.isLoading);
const activeTab = computed(() => store.activeTab);

</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>
        <div class="flex">
            <div class="w-1/5 bg-black text-white py-3 admin-container">
                <h3 class="bd-white font-bold text-center pb-3">Administrador</h3>
                <AdminNavbar />
            </div>

            <div id="body" class="w-3/5 px-4 mx-auto mt-3 bg-white rounded">
                <Users v-if="activeTab === 'users'" />

                <Addresses v-if="activeTab === 'address'" />

                <Club v-if="activeTab === 'club'" />

                <Requests v-if="activeTab === 'clubRequests'" />

                <Events v-if="activeTab === 'events'" />

                <AddEvent v-if="activeTab === 'addEvent'" />

                <Restrictions v-if="activeTab === 'restrictions'" />

                <RestrictionList v-if="activeTab === 'restrictionsList'" />

                <Directory v-if="activeTab === 'directory'" />
            </div>
        </div>
        <LoadingScreen :show="isLoading" />
    </AuthenticatedLayout>
</template>