<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormVue from '@/Admin/Directory/Form.vue';
import Directory from '@/Admin/Directory/Directory.vue';
import { ref } from 'vue';

const showForm = ref(false);
</script>

<template>
    <div>
        <div class="add_div">
            <PrimaryButton @click="showForm = !showForm">
                {{ showForm ? 'Cancelar' : 'Agregar' }}
            </PrimaryButton>
        </div>
        <div v-if="showForm" class="form">
            <FormVue @returnScreen="showForm = false" />
        </div>
        <div v-else>
            <Directory />
        </div>
    </div>
</template>

<style lang="scss">
.add_div {
    display: flex;
    justify-content: end;
    margin: 10px;
}
</style>