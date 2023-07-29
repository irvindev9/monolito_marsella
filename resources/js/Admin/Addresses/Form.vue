<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { defineProps } from 'vue';

const props = defineProps({
    address: {
        type: Array,
        required: false,
        default: () => {
            return {
                street: {
                    name: '',
                },
                house_number: '',
            };
        },
    },
});
</script>
<template>
    <div class="flex">
        <div class="w-1/2 px-1">
            <div class="flex flex-col">
                <label class="font-bold my-3">
                    Calle
                </label>
                <select type="text" class="border rounded p-2">
                    <option value="1">Calle 1</option>
                    <option value="2">Calle 2</option>
                    <option value="3">Calle 3</option>
                </select>
            </div>
        </div>
        <div class="w-1/2 px-1">
            <div class="flex flex-col">
                <label class="font-bold my-3">
                    Número
                </label>
                <input type="text" class="border rounded p-2" />
            </div>
        </div>
    </div>
</template>