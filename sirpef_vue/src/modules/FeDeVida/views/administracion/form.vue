<script setup lang="ts">
import { ref } from 'vue';
import Welcome from '../../../../components/sirpef/welcome.vue';
import Form from '../../components/caseFormAdministracion/index.vue';
import FormInput from "@/modules/SIRPEF/components/FormInput.vue";
import Http from '@/utils/Http';
import { alerta } from '@/utils/alert';

const punto = ref({
  punto_cuenta_id: null,
  registro_id: null,
  fecha_punto: null,
  numero_punto: null,
})

const getPunto = async (numero: string) => {
  try {
    const res = await Http.get(`/api/oac/preparar-pago/${numero}`);
    punto.value = res.data.data
  } catch (error) {
    if (error.response?.status != 200) {
      alerta("Error", error.response.data.message, "info")
    }
  }
};

</script>

<template>
  <Welcome :title="punto.numero_punto ? `Numero de punto: ${punto.numero_punto}` : 'Registro de casos'" :subtitle="punto.fecha_punto ? `Fecha del punto ${punto.fecha_punto}` : 'Ingrese la información del personal para llevar a cabo el registro'">
    <Form :punto="punto" v-if="punto.punto_cuenta_id" />
    <FormInput label="Ingrese el número de punto de cuenta" :noParse="true" :FunGetUser="getPunto" v-else />
  </Welcome>
</template>

<style scoped>
#reportes {
  width: 100%;
  max-height: 90vh;
  margin: 0 auto;
}

#busqueda input:hover~button {
  background-color: #0057b3;
}

.results {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 20px;
  margin: 30px auto;
}
</style>