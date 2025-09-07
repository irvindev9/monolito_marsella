<script setup>
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormAccess from './Access/FormAccess.vue';
import AccessList from './Access/AccessList.vue';
import { ref } from 'vue';

const showForm = ref(false);
const isUpdate = ref(false);
const accessValue = ref(null);

function editAccess(access) {
    isUpdate.value = true;
    accessValue.value = access;
    showForm.value = true;
}

function openForm() {
    if (showForm.value) {
        showForm.value = false;
    } else {
        isUpdate.value = false;
        accessValue.value = null;
        showForm.value = true;
    }
}
</script>

<template>
    <Head title="Accesos" />

    <div class="add_div">
        <PrimaryButton @click="openForm">
            {{ showForm ? 'Cancelar' : 'Agregar' }}
        </PrimaryButton>
    </div>

    <h4 class="font-bold my-3">
        {{ showForm ? 'Agregar acceso' : 'Accesos' }}
    </h4>

    <div v-if="showForm">
        <FormAccess @close="showForm = false" :update="isUpdate" :access="accessValue" />
    </div>

    <div v-else>
        <AccessList @edit="editAccess" />
    </div>
</template>