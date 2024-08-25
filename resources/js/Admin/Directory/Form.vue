<script setup>
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const position = ref(null);
const name = ref(null);
const phone = ref(null);
const description = ref(null);
const file = ref(null);

const validations = ref({
    position: false,
    name: false,
    phone: false,
    description: false,
    file: false,
});

function fileChange(e) {
    file.value = e.target.files[0];
}

function saveData() {

    if (!validateForm()) {
        console.log('Por favor, llena los campos obligatorios')
        return;
    }

    const formData = new FormData();

    formData.append('position', position.value);
    formData.append('name', name.value);
    formData.append('phone', phone.value);
    formData.append('description', description.value);
    formData.append('file', file.value);

    axios.post('/api/directory', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        console.log(response);
        cleanForm();
        toast.success('Registro guardado');
    }).catch(error => {
        console.error(error);
        toast.error('Error al guardar el registro');
    });
}

function validateForm() {
    let flags = 0;
    const totalFlags = 3;

    if (position.value) {
        flags++;
        validations.value.position = false;
    } else {
        validations.value.position = true;
    }

    if (name.value) {
        flags++;
        validations.value.name = false;
    } else {
        validations.value.name = true;
    }

    const validPhone = phone.value && phone.value.match(/^[0-9]{10}$/);

    if (validPhone || phone.value.trim() === '' || phone.value === null) {
        flags++;
        validations.value.phone = false;
    } else {
        validations.value.phone = true;
    }

    return flags === totalFlags;
}

function cleanForm() {
    position.value = null;
    name.value = null;
    phone.value = null;
    description.value = null;
    file.value = null;
    validations.value = {
        position: false,
        name: false,
        phone: false,
        description: false,
        file: false,
    };
}
</script>

<template>
    <div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col" :class="{ invalid: validations.position }">
                    <label class="font-bold my-3">
                        Cargo / Puesto <span class="required">*</span>
                    </label>
                    <input type="text" class="border rounded p-2" v-model="position" />
                    <small v-if="validations.position" class="text-red-500">Campo obligatorio</small>
                </div>
            </div>
            <div class="w-1/2 px-1">
                <div class="flex flex-col" :class="{ invalid: validations.name }">
                    <label class="font-bold my-3">
                        Nombre <span class="required">*</span>
                    </label>
                    <input type="text" class="border rounded p-2" v-model="name" />
                    <small v-if="validations.name" class="text-red-500">Campo obligatorio</small>
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col" :class="{ invalid: validations.phone }">
                    <label class="font-bold my-3">
                        Whatsapp / Teléfono
                    </label>
                    <input type="text" class="border rounded p-2" v-model="phone" />
                    <small v-if="validations.phone" class="text-red-500">Solo se permiten números (10 números)</small>
                </div>
            </div>
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Descripción cargo
                    </label>
                    <textarea class="border rounded p-2" v-model="description"></textarea>
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <label class="font-bold my-3">
                        Foto
                    </label>
                    <input type="file" class="border rounded p-2" v-on:change="fileChange"
                        accept="image/png, image/gif, image/jpeg" />
                </div>
            </div>
        </div>
        <div class="flex mt-3">
            <div class="w-1/2 px-1">
                <div class="flex flex-col">
                    <small><span class="required">*</span> Campos obligatorios</small>
                </div>
            </div>
        </div>
        <div class="grid justify-items-end">
            <PrimaryButton class="mt-1" @click="saveData">
                Agregar
            </PrimaryButton>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.required {
    color: red;
}

.invalid {
    input {
        border-color: red;
    }
}
</style>