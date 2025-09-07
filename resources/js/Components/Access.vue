<script setup>
import { ref, onMounted } from 'vue';

const passwords = ref([]);
const isExpanded = ref(false);
const isToday = ref(false);

const props = defineProps({
    passwords: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    reservationId: {
        type: Number,
        required: true,
    },
    reservationDate: {
        type: String,
        required: true,
    },
});

onMounted(() => {
    getPasswords();

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const reservationDateFormatted = new Date(props.reservationDate);
    reservationDateFormatted.setHours(0, 0, 0, 0);
    console.log(reservationDateFormatted);
    console.log(today);
    console.log(reservationDateFormatted == today);
    isToday.value = reservationDateFormatted.toDateString() == today.toDateString();
});

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
};

async function getPasswords() {
    const { data } = await axios.get(`/api/passwords/public/${props.reservationId}`);
    passwords.value = data;
}
</script>

<template>
    <div class="mt-3 sm:w-full md:w-1/2 lg:w-1/4 mb-3">
        <h3 class="font-bold" @click="toggleExpand">
            {{ title }}
            <div class="inline-block" :class="{ 'rotate-90': isExpanded }">
                <i class="bi bi-arrow-right-circle-fill action-button-expand"></i>
            </div>
        </h3>
        <transition name="list">
            <div v-if="isExpanded">
                <small v-if="!isToday" class="text-xs text-gray-500">
                    Se revelarán las contraseñas el día del evento
                </small>
                <div v-for="password in passwords" :key="password.id" class="mb-2 flex flex-col mt-3">
                    <label for="passwords-1" class="text-sm">{{ password.title }}</label>
                    <input type="text" id="passwords-1" class="w-fit p-2 border border-slate-300 rounded-lg bg-gray-100 h-[25px]" readonly :value="password.password" />
                </div>
            </div>
        </transition>
    </div>
</template>

<style lang="scss" scoped>
.action-button-expand {
    cursor: pointer;
}

.action-button-expand:hover {
    color: #1d4ed8;
}

.slide-enter-active {
  transition-duration: 0.3s;
  transition-timing-function: ease-in;
}

.slide-leave-active {
  transition-duration: 0.3s;
  transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
}

.slide-enter-to,
.slide-leave-from {
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  overflow: hidden;
  height: 0;
}

.rotate-90 {
    transform: rotate(90deg);
}
</style>