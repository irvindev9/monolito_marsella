<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

</script>

<template>
    <AuthenticatedLayout>
        <div class="card mb-3" v-for="n in 10">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="https://picsum.photos/200" class="img-fluid rounded-start" alt="IMAGE">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">Secretario</h5>
                        <p class="card-text">Irvin Raul Lopez Contreras</p>
                        <p class="card-text"><small class="text-body-secondary">Responsable de tomar notas en las juntas
                                vecinales. Responsable del sitio versallesmarsella.com</small>
                        </p>
                        <a href="https://api.whatsapp.com/send?phone=5216565340038" target="_blank">
                            <i class="bi bi-whatsapp"></i>
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
</style>