<script lang="ts" setup>
import Controls from "@/components/sirpef/form/Controls.vue";
import Http from "@/utils/Http";
import { onMounted } from "vue";
import { ref } from "vue";

const status = ref([])

const props = defineProps<{
  emitForm: (event: Event) => void,
  step: number,
  values: any,
}>()

const getStatus = async () => {
  const res = await Http.get(`/api/oac/estatus-pagos/`);
  status.value = res.data.data
};


onMounted(() => {
  getStatus()
})

</script>

<template>
  <form @submit.prevent="emitForm">

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mt-5">

      <div>
        <label class="block font-medium text-gray-700 ml-1">Saldo Deudor</label>
        <input name="saldo_deudor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="number"
          step="0.01" placeholder="0.00" v-model="values.saldo_deudor" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Saldo Acreedor</label>
        <input name="saldo_acreedor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="number"
          step="0.01" placeholder="0.00" v-model="values.saldo_acreedor" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Fecha de la Orden de Pago</label>
        <input name="fecha_orden_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="date"
          v-model="values.fecha_orden_pago" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Nro. Factura</label>
        <input name="nro_factura" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Número de factura" v-model="values.nro_factura" />
      </div>

      <div class="col-span-2" v-if="status.length > 0">
        <label class="block font-medium text-gray-700 ml-1">Estatus (Procesado en el SIGECOF)</label>
        <select name="estatus" v-model="values.estatus" class="w-full bg-gray-100 mt-1 p-3 rounded-lg">
          <option value="">Ninguno</option>
          <option v-for="e in status" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>

      <div class="col-span-2">
        <label class="block font-medium text-gray-700 ml-1 mt-2">Descripción / Observaciones</label>
        <textarea class="resize-none w-full bg-gray-100 p-3 rounded-lg mt-1" name="description"
          v-model="values.descripcion" rows="4" placeholder="Detalles adicionales..."></textarea>
      </div>

    </div>

    <Controls :step="step" />
  </form>
</template>