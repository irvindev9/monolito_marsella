<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { ref, onMounted } from 'vue';

const userDirectory = ref([]);
const message = ref(null);

onMounted(async () => {
    await getUserDirectory();
});

async function getUserDirectory() {
    try {
        const { data } = await axios.get('/api/directory/public');
        userDirectory.value = data.directories;
        message.value = data.message;
    } catch (error) {
        console.log(error);
    }
}

</script>

<template>
    <AuthenticatedLayout>
        <div v-if="message" class="alert alert-warning" role="alert">
            {{ message }}
        </div>
        <div v-else class="card mb-3" v-for="user in userDirectory" :key="user.id">
            <div class="row g-0">
                <div class="col-md-4">
                    <img v-if="user.photo" :src="user.photo" class="img-fluid rounded-start" alt="IMAGE">
                    <img v-else src="../../assets/nophoto.jpg" class="img-fluid rounded-start" alt="IMAGE">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">{{ user.position }}</h5>
                        <p class="card-text">{{ user.name }}</p>
                        <p class="card-text"><small class="text-body-secondary">{{ (user.description && user.description !=
                            'null') ? user.description : '' }}</small>
                        </p>
                        <a v-if="user.phone" :href="`https://api.whatsapp.com/send?phone=521${user.phone}`" target="_blank">
                            <i class="bi bi-whatsapp"></i> {{ user.phone }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss">
.card {
    border: 1px solid #ccc;
    background-color: white;
    border-radius: 5px;
    margin: 0 auto;
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 540px;

    .row {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 10px;

        .col-md-4 {

            grid-column: span 5;

            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 5px 0 0 5px;
            }
        }

        .col-md-8 {
            grid-column: span 7;
            padding: 10px;

            .card-title {
                font-size: 20px;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .card-text {
                font-size: 16px;
                margin-bottom: 10px;
            }

            .text-body-secondary {
                font-size: 14px;
                color: #6c757d;
            }
        }
    }
}

@media (max-width: 600px) {
    .card {
        max-width: 250px;

        .row {
            .col-md-4 {

                grid-column: span 12;

                img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 5px 5px 0 0;
                }
            }

            .col-md-8 {
                grid-column: span 12;
                padding: 10px;

                .card-title {
                    font-size: 20px;
                    font-weight: 600;
                    margin-bottom: 10px;
                }

                .card-text {
                    font-size: 16px;
                    margin-bottom: 10px;
                }

                .text-body-secondary {
                    font-size: 14px;
                    color: #6c757d;
                }
            }
        }
    }
}

.bi-whatsapp {
    color: #25d366;
}

.alert {
    margin: 20px;
    text-align: center;
    font-size: large;
}
</style>