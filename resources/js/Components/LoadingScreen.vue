<script setup>
import { FingerprintSpinner } from 'epic-spinners'
import { defineProps } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="loading-screen" v-if="props.show">
        <div class="spinner">
            <fingerprint-spinner :animation-duration="1500" :size="64" color="#2F8FE8" />
            Cargando...
        </div>
    </div>
</template>