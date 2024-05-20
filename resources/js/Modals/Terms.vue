<script setup>
import { defineEmits, ref } from 'vue';
import { VueFinalModal } from 'vue-final-modal';

const emit = defineEmits(['close', 'confirm']);
const accept = ref(false);

</script>


<template>
    <VueFinalModal class="flex justify-center items-center"
        content-class="flex flex-col max-w-xl mx-4 p-4 bg-white dark:bg-gray-900 border dark:border-gray-700 rounded-lg space-y-2">
        <h1 class="text-xl">
            Terminos y condiciones
        </h1>
        <div class="content">
            CONTRATO DE RESERVACION MARSELLA
            CONTRATO DE RESERVACION PARA USO DE AREA COMUN, QUE CELEBRAN POR UNA PARTE EL COMITÉ DE
            VECINOS MARSELLA, REPRESENTADA EN ESTE ACTO POR EL C. __________________________________________,
            EN ADELANTE ‘’COMITÉ DE VECINOS”; Y POR PARTE EL C.____________________________________________,
            EN ADELANTE ‘’EL RESIDENTE’’, Y DE MANERA CONJUNTA COMO ‘’LAS PARTES’’, LAS CUALES SE SUJETAN DE
            CONFORMIDAD A LOS SIGUIENTES, ANTECEDENTES, DECLARACIONES Y CLAUSULAS:
            A N T E C E D E N T E S
            PRIMERO. - Que en fecha del año 2022 ‘’EL RESIDENTE’’ celebró un contrato de compra venta mediante el cual
            adquirió para si la propiedad del inmueble marcado con la dirección ___________________________________
            del fraccionamiento “Marsella”, en esta ciudad. (En adelante ‘’EL CONTRATO’’).
            SEGUNDO.- El uso de la Terraza Grill, es por cuenta y riesgo de los residentes y visitantes, por lo que la
            administración no se hace responsable por los accidentes, robo o cualquier otro acontecimiento que ocasione
            daños o perjuicios.
            D E C L A R A C I O N E S
            I. DECLARA ‘’EL RESIDENTE’’ POR SU PROPIO DERECHO.
            A. Que es de nacionalidad mexicana, identificándose con credencial para votar INE y presenta
            comprobante de domicilio como evidencia de su residencia en el fraccionamiento.
            B. Donde, el residente actualmente ostenta la propiedad del inmueble en la dirección
            ____________________________________ del fraccionamiento ‘Marsella’’ en esta ciudad.
            C. Que para efectos de realizar el evento llamado: ___________________________________, se obliga
            en los términos del presente instrumento.
            II. DECLARA ‘’EL COMITÉ DE VECINOS’’ A TRAVES DE SU REPRESENTANTE
            A. Que actualmente cuenta con la administración de la Terraza Grill ubicada en el fraccionamiento
            ‘’Versalles Privada Marsella’’
            B. Que está de acuerdo en que ‘’EL RESIDENTE’’ realice el evento que señala en sus declaraciones siempre
            y cuando respete los lineamientos señalados en el presente y sus anexos.
            C L A U S U L A S
            PRIMERA.- OBJETO. “EL COMITÉ DE VECINOS” otorga el uso temporal de la Terraza Grill y baños (en adelante ‘’EL
            INMUEBLE’’), así como todo lo que comprende dichas instalaciones a ‘’EL RESIDENTE’’ para llevar a cabo el evento
            llamado: _________________________________ el día ______ del mes _______________ del año __________
            en un horario comprendido entre las __________horas a las _________ horas.
            SEGUNDA.- PAGO. ’’EL RESIDENTE’’ se deberá aportar un pago simbólico al “EL COMITÉ DE VECINOS” por la
            reservación de la terraza por un monto de <b>$100 pesos (M/N).</b> El cual se invertirá para el mantenimiento
            directo
            de la terraza, así como también para cubrir gastos de servicios, tales como agua y luz.
            TERCERA.- INMUEBLE. se recibe en perfecto estado, así como el inventario y se cumplirán con las normas
            establecidas de uso, en cuanto a comportamiento, aforo, estacionamiento y ruido. Este último deberá darse
            termino a las 0:00 horas como tolerancia, y <b>de no respetarse se sancionará con 1 año si poder reservar el
                inmueble</b>,
            tal como se establece en el reglamento.
            CUARTA.- LIMPIEZA. ’’EL RESIDENTE’’ se compromete con la limpieza del inmueble, enviando la evidencia de esta
            labor a un miembro del comité (preferentemente presidente/secretario) Así como llevarse a su domicilio los
            deshechos originados durante del evento y contenedores cercanos a la Terraza Grill.
            <b>El límite de tiempo para llevar a cabo la limpieza serán las 12:00 horas del siguiente día.</b> De lo
            contrario, al
            incumplir esta cláusula y/o no dejar en perfecto estado el inmueble, el residente se hará acreedor a una
            <b>sanción
                de $500 pesos (M/N)</b>, dicho cargo se verá reflejado en la aplicación de recaudación de cuota de
            mantenimiento.
            Además de <b>1 año si poder reservar el inmueble</b>, tal como se establece en el reglamento.
            QUINTA.- REGLAMENTO. ‘’EL RESIDENTE’’ Acepta acatar cada una de las disposiciones contenidas en el
            “REGLAMENTO DE USO Y DISFRUTE DE INSTALACIONES DE LA TERRAZA GRILL DEL FRACCIONAMIENTO PRIVADA
            MARSELLA” publicado por el comité vecinos.
            SEXTA.- INVENTARIO. Al término del evento “EL COMITÉ DE VECINOS” tiene la obligación de realizar un conteo
            de inventario señalando los daños o faltantes que resulten y su valor total líquido, para que posteriormente
            ‘’EL
            RESIDENTE’’ cubra el pago de gastos por daños ocasionados en la misma modalidad de la cláusula SEGUNDA.
            SEPTIMA.- Este contrato no confiere de ninguna manera algún tipo de mandato especial o general ni
            representación alguna por parte de “EL COMITÉ DE VECINOS” a “EL RESIDENTE”, por lo que todas y cada una de
            las obligaciones o responsabilidades que este último contraiga con terceras personas correrán por su cuenta y en
            ningún momento obligaran a “EL COMITÉ DE VECINOS”.
            OCTAVA.- ‘’EL RESIDENTE’’ se obliga expresamente a responsabilizarse de cualquier contingencia o
            responsabilidad civil, laboral, mercantil, penal o de cualquier índole que se interponga en su contra, quedando
            exento “EL COMITÉ DE VECINOS” de toda responsabilidad derivada de lo anterior.
            NOVENA.- Para la interpretación y cumplimiento, así como posibles controversias entre ‘’LAS PARTES’’ ambas
            manifiestan someterse a los tribunales y jurisdicción competente en Ciudad Juárez, Chihuahua, renunciando a
            cualquier otra que por razones de sus domicilios presentes o futuros o por cualquier otra cosa pudiera
            corresponderles. <br>
            LEIDO EL PRESENTE DOCUMENTO Y ENTERADAS LAS PARTES DEL ALCANCE Y FUERZA LEGAL, CONFORMES A LAS
            OBLIGACIONES Y DERECHO CONTENIDOS, SE FIRMA EXPRESANDO SU CONFORMIDAD EN CIUDAD JUAREZ,
            CHIHUAHUA, EL DIA _______ DEL MES _____________________ DEL AÑO _________. <br>
            _____________________________________ <br>
            COMITÉ DE VECINOS MARSELLA.
        </div>
        <div class="accept">
            <input type="checkbox" id="accept" v-model="accept">
            <label for="accept">&nbsp; Acepto los términos y condiciones</label>

        </div>
        <button class="mt-1 ml-auto px-2 border rounded-md" @click="emit('confirm')" :disabled="!accept"
            :class="{ 'bg-slate-50': !accept, 'text-white': !accept }">
            Confirmar
        </button>
        <button class="mt-1 ml-auto px-2 border rounded-md" @click="emit('close')">
            Cancelar
        </button>
    </VueFinalModal>
</template>

<style>
.content {
    overflow-y: auto;
    max-height: 70vh;
}
</style>