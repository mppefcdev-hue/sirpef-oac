<script setup lang="ts">
import "@/assets/css/formWizard.scss";
import Check from "@/components/sirpef/Check.vue";
import useFormAdministracion from "../../composables/administracion/useFormAdministracion";

import FirstPage from "./FirstPage.vue";
import SecondPage from "./SecondPage.vue";
import FormCaso from "./FormCaso.vue";


const props = defineProps<{
  punto: any
}>()

const {
    step,
    estado,
    emitForm,
    UserInfo,
} = useFormAdministracion(props.punto || null)

const stepperProgress = () => {
  return (100 / 3) * (step.value - 1) + "%";
};

</script>

<template>

<div class="wrapper-stepper cursor-default fadeout">
    <div class="stepper">
      <div class="stepper-progress">
        <div
          class="stepper-progress-bar"
          :style="'width:' + stepperProgress"
        ></div>
      </div>

      <div
        class="stepper-item"
        :class="{ current: step == item, success: step > item }"
        v-for="item in 3"
        :key="item"
      >
        <button class="stepper-item-counter" @click="() => step = item">
          <img
            class="icon-success"
            src="/status/check.png"
            alt=""
            v-if="estado.find(e => e == item)"
          />

          <img
            class="icon-success"
            src="/status/mark.webp"
            alt=""
            v-else
          />
          <span class="number">
            {{ item }}
          </span>
        </button>
        <span class="stepper-item-title">
          {{
            item == 1
              ? "Datos iniciales"
              : item == 2
                ? "Factura y Pago"
                : item == 3
                ? "Recaudos"
                : "Final"
          }}
        </span>
      </div>
    </div>

    <div class="stepper-content">
      <FirstPage :emitForm="emitForm" :values="UserInfo" :step="step" v-if="step == 1"/>
      <SecondPage :emitForm="emitForm" :values="UserInfo" :step="step" v-else-if="step == 2"/>
      <FormCaso :emitForm="emitForm" :values="UserInfo" :step="step" v-else-if="step == 3"/>

      <div class="grid gap-5 mt-10 w-full md:w-full" v-else>

        <article class="w-full text-center mt-5" v-if="estado.find(e => e == 4)">
          <Check :animate="true"/>
          <h2 class="capitalize">registro completado</h2>
        </article>

        <div class="flex justify-between items-center gap-2 md:w-[60%] mx-auto mt-10">
          <p class="text-black text-base">Se ha completado los datos basicos</p>
          <font-awesome-icon icon="fa-solid fa-circle-check" class="text-green-700 h-[20px]" v-if="estado.find(e => e == 1)"/>
          <font-awesome-icon icon="fa-solid fa-circle-xmark" class="text-red-700 h-[20px]" v-else/>
        </div>

        <div class="flex justify-between items-center gap-2 md:w-[60%] mx-auto">
          <p class="text-black text-base">Se ha añadido las oficinas</p>
          <font-awesome-icon icon="fa-solid fa-circle-check" class="text-green-700 h-[20px]" v-if="estado.find(e => e == 2)"/>
          <font-awesome-icon icon="fa-solid fa-circle-xmark" class="text-red-700 h-[20px]"  v-else/>
        </div>

      </div>

    </div>
</div>



</template>



<style scoped>

input,
select,
textarea {
  border-color: #eca008;
  border-radius: 10px;
}
</style>

<style>

.inputForm{
    border: rgba(255, 255, 255, 0);
    border-bottom: solid 2px;
    outline: none;
}

.inputForm:focus{
    border-bottom: solid #041e42;
}



</style>
