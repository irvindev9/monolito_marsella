<script setup>
import { ref } from 'vue';

const props = defineProps({
    reservation: {
        type: Object,
        required: true,
    },
    configs: {
        type: Array,
        required: true,
    }
});

const createdAt = ref('-');

const calculateDeadline = () => {
    const daysToPay = (props.configs.find(config => config.slug === 'mdtpr')?.setting) ? parseInt(props.configs.find(config => config.slug === 'mdtpr').setting) : 7;
    const deadline = new Date(props.reservation.created_at);
    deadline.setDate(deadline.getDate() + daysToPay);

    const convertMsToTime = (ms) => {
        const totalSeconds = Math.floor(ms / 1000);
        const days = Math.floor(totalSeconds / (3600 * 24));
        const hours = Math.floor((totalSeconds % (3600 * 24)) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return { days, hours, minutes, seconds };
    };

    if (deadline > new Date()) {
        const now = new Date();
        const diff = deadline - now;
        const timeLeft = convertMsToTime(diff);
        createdAt.value = `${timeLeft.days}d ${timeLeft.hours}h ${timeLeft.minutes}m ${timeLeft.seconds}s`;
    } else {
        createdAt.value = 'El tiempo para realizar el pago ha expirado, la reserva será cancelada en breve.';
    }

    setTimeout(calculateDeadline, 1000);
}

calculateDeadline();
</script>

<template>
    <div class="countdown-timer" v-if="reservation.is_paid == 0 || reservation.is_approved != 1">
        <span>Tiempo restante para hacer el pago:</span>
        <br/>
        <span>{{ createdAt }}</span>
    </div>
</template>

<style scoped>
    .countdown-timer {
        border: 1px solid red;
        padding: 5px 15px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        position: absolute;
        bottom: -19px;
        right: 5px;
        z-index: 10;
        background: rgb(255 11 11 / 50%);
    }
</style>