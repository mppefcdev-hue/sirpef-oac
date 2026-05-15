<script setup lang="ts">
import Controls from '@/components/sirpef/form/Controls.vue';
import Http from '@/utils/Http';
import { ref } from 'vue';
import { onMounted } from 'vue';


const pagos = ref([])

const props = defineProps<{
  emitForm: (event: Event) => void,
  step: number,
  values: any,
}>()

const getPay = async () => {
  const res = await Http.get(`/api/oac/tipos-pagos/`);
  pagos.value = res.data.data
};


onMounted(() => {
  getPay()
})


</script>

<template>
  <form @submit.prevent="emitForm">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mt-3">

      <div>
        <label class="block font-medium text-gray-700 ml-1">Tipo de Pago</label>
        <select name="tipo_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg"
          v-model="values.tipo_pago">
          <option value="">Sin seleccion</option>
          <option :value="pago.id" v-for="pago in pagos">{{pago.nombre}}</option>
        </select>
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Nro. Referencia Pago Financiero</label>
        <input name="nro_referencia_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Ingrese nro de referencia" v-model="values.nro_referencia_pago" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Proveedor / Contacto</label>
        <input name="proveedor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Nombre del proveedor" v-model="values.proveedor" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">RIF del Proveedor</label>
        <input name="rif_proveedor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Ej: J-12345678-9" v-model="values.rif_proveedor" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Monto</label>
        <input name="monto" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="number" step="0.01"
          placeholder="0.00" v-model="values.monto" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Orden de Pago</label>
        <input name="nro_orden_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Nro. de orden" v-model="values.nro_orden_pago" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Fecha de Pago Financiero</label>
        <input name="fecha_pago_financiero" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="date"
          v-model="values.fecha_pago_financiero" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Cuota de Compromiso Disponible</label>
        <input name="cuota_compromiso_disponible" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg"
          type="text" placeholder="Monto disponible" v-model="values.cuota_compromiso_disponible" />
      </div>
    </div>
    <Controls :step="step" />
  </form>
</template>