<script setup lang="ts">
// @ts-nocheck
import Welcome from "@/components/sirpef/welcome.vue";
import { ref } from "vue";
import { useAuthStore } from '@/modules/Auth/stores';
import AppPaginationD from "@/components/AppPaginationD.vue";
import FormInput from "@/modules/SIRPEF/components/FormInput.vue";
import ListOptions from "@/modules/Personal/components/ListOptions.vue";
import ModalInfo from "../../components/modalInfo.vue";
import ModalDescripcion from "../../components/ModalDescripcion.vue";
import CardInfoUser from '@/components/Votos/CardInfoUser.vue';
import { watch } from "vue";
import AdministracionTable from "@/modules/FeDeVida/composables/administracion/indexTable";

const store = useAuthStore()
const casePersona_id = ref(null)
const descripcion = ref(null)

const {
  errors,
  data,
  router,
  result,
  confirmacion,
  setSearch,
  setSort,
  GetUser,
  deleteCaso,
  foundCaseId
} = AdministracionTable()

watch(foundCaseId, (newId) => {
  if (newId) {
    casePersona_id.value = newId;
  }
});

</script>


<template>

  <Welcome title="Gestión de Pagos" subtitle="Aquí encontrarás los pagos registrados en el sistema. Puedes buscar, filtrar y gestionar los pagos de manera eficiente.">
    <FormInput :FunGetUser="GetUser" :finger="false" />
  </Welcome>

  <ModalDescripcion v-if="descripcion" :descripcion="descripcion" @close="descripcion = null" />

  <ModalInfo v-if="casePersona_id" :casePersona_id="casePersona_id" @close="casePersona_id = null" />

  <div class="col-start-2 col-end-4 mx-auto w-[90%] panel" v-if="Object.keys(result).length == 0">


    <div class="table-data__wrapper">
  <table class="table-data">
    <thead>
      <tr class="">
        <th>ID / Orden</th>
        <th>Fecha Orden</th>
        <th class="w-[20%] text-left">Proveedor</th>
        <th>Monto</th>
        <th>Saldo Deudor</th>
        <th>Estatus</th>
        <th class="ubi_ads">Descripción</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="data.rows.length > 0" v-for="row in data.rows" :key="row.id" class="">
        <td class="text-center font-bold">
          #{{ row.id }} <br>
          <span class="text-xs text-gray-500">Ord: {{ row.orden_pago }}</span>
        </td>

        <td class="text-center">
          {{ row.fecha_orden_pago }}
        </td>

        <td class="text-left capitalize">
          <div v-if="row.proveedores && row.proveedores.length > 0">
            <p class="font-semibold">{{ row.proveedores[0].nombre }}</p>
            <p class="text-xs text-gray-400">RIF: {{ row.proveedores[0].cedula_rif }}</p>
          </div>
          <span v-else>Sin proveedor</span>
        </td>

        <td class="text-center font-bold text-green-700">
          {{ row.monto }}
        </td>

        <td class="text-center text-red-600">
          {{ row.saldo_deudor }}
        </td>

        <td class="text-center">
          <span :class="row.estatus_pago_id === 1 ? 'text-green-600' : 'text-orange-500'" class="font-bold">
            {{ row.estatus?.nombre || "Desconocido" }}
          </span>
        </td>

        <td class="text-center ubi_ads">
          <p class="truncate max-w-[200px] mx-auto">
            {{ row.descripcion || "Sin descripción" }}
          </p>
        </td>

        <td class="text-center">
          <div class="flex justify-center gap-2">
            <button title="Ver Recaudos"
              class="bg-[#2052C7] text-white p-2 rounded-lg hover:opacity-80"
              @click="() => casePersona_id = row.registro_id">
              Ver punto
            </button>
            
            <button title="Eliminar"
              class="bg-red-700 text-white p-2 rounded-lg hover:bg-red-900"
              @click="() => deleteCaso(row.id)">
              <font-awesome-icon icon="trash-can" />
            </button>
          </div>
        </td>
      </tr>
      
      <tr v-if="data.rows.length == 0">
        <td colspan="8" class="text-center py-10">No se encontraron registros de pagos.</td>
      </tr>
    </tbody>
  </table>
</div>

    <span v-if="Object.keys(errors).length > 0" class="text-red-500">{{ errors }}</span>
    <AppPaginationD v-if="data.links" :links="data.links"></AppPaginationD>
  </div>

  <section class="results my-10 w-full md:w-3/4" v-if="Object.keys(result).length > 0">
    <CardInfoUser :UserData="result[0]" title="Datos personales" icon="fa-solid fa-user" />
    <CardInfoUser :UserData="result[1]" title="Punto de cuenta" icon="fa-solid fa-location-dot"
      @seePDC="({ registro_id }) => casePersona_id = registro_id"
      @deletePDC="({ registro_id }) => deleteCaso(registro_id)" />

  </section>

</template>



<style scoped>
.ubi_ads {
  width: 900px !important;
  white-space: wrap;

}


.results {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 20px;
  margin: 30px auto;
}
</style>