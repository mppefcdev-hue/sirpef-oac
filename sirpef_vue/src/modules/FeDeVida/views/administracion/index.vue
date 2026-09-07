<script setup lang="ts">
// @ts-nocheck
import Welcome from "@/components/sirpef/welcome.vue";
import { ref, watch, computed } from "vue";
import { useAuthStore } from '@/modules/Auth/stores';
import AppPaginationD from "@/components/AppPaginationD.vue";
import FormInput from "@/modules/SIRPEF/components/FormInput.vue";
import ModalInfo from "../../components/modalInfo.vue";
import ModalDescripcion from "../../components/ModalDescripcion.vue";
import CardInfoUser from '@/components/Votos/CardInfoUser.vue';
import AdministracionTable from "@/modules/FeDeVida/composables/administracion/indexTable";

const store = useAuthStore()
const casePersona_id = ref(null)
const descripcion = ref(null)
const showFilters = ref(true)

const {
  errors,
  data,
  filters,
  router,
  result,
  confirmacion,
  setSearch,
  setSort,
  applyFilters,
  clearFilters,
  GetUser,
  deleteCaso,
  foundCaseId
} = AdministracionTable()

const hasActiveFilters = computed(() => {
  return !!(
    (data.search && data.search.trim()) ||
    filters.mes ||
    (filters.factura && filters.factura.trim()) ||
    (filters.proveedor && filters.proveedor.trim()) ||
    (filters.paciente && filters.paciente.trim()) ||
    (filters.punto_cuenta && filters.punto_cuenta.trim()) ||
    (filters.orden_pago && filters.orden_pago.trim())
  );
});

watch(foundCaseId, (newId) => {
  if (newId) {
    casePersona_id.value = newId;
  }
});

const formatCurrency = (value: any) => {
  if (value === undefined || value === null || value === '') return '0,00';
  const number = typeof value === 'string' ? parseFloat(value) : value;
  if (isNaN(number)) return '0,00';
  return number.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

</script>

<template>

  <Welcome 
    title="Gestión de Pagos" 
    subtitle="Aquí encontrarás los pagos registrados en el sistema. Puedes buscar, filtrar y gestionar los pagos de manera eficiente."
  >
    <FormInput :FunGetUser="GetUser" :finger="false" />
  </Welcome>

  <ModalDescripcion v-if="descripcion" :descripcion="descripcion" @close="descripcion = null" />

  <ModalInfo v-if="casePersona_id" :casePersona_id="casePersona_id" @close="casePersona_id = null" />

  <div class="col-start-2 col-end-4 mx-auto w-[90%] panel" v-if="Object.keys(result).length == 0">

    <div>
      <!-- SECCIÓN DE BÚSQUEDA Y FILTROS -->
      <div class="mb-6 p-4 sm:p-5 bg-white shadow-sm rounded-2xl border border-gray-200">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pb-3 border-b border-gray-100">
          <!-- Input de Buscador Rápido -->
          <div class="relative flex-1 w-full min-w-0">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none">
              <font-awesome-icon icon="magnifying-glass" class="text-sm" />
            </span>
            <input
              v-model="data.search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Buscar por orden de pago, factura, proveedor, paciente o punto de cuenta..."
              class="filter-input search-input w-full min-w-0 pl-10 pr-4 py-2.5 bg-gray-50 hover:bg-gray-100/70 focus:bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm text-gray-800 placeholder-gray-400"
            />
          </div>

          <!-- Botones de Acción -->
          <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0">
            <button
              type="button"
              @click="showFilters = !showFilters"
              class="flex-1 sm:flex-none justify-center flex items-center gap-2 px-3.5 py-2.5 rounded-xl border text-sm font-medium transition cursor-pointer"
              :class="showFilters || hasActiveFilters 
                ? 'bg-blue-50 text-blue-700 border-blue-200' 
                : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
            >
              <font-awesome-icon icon="sliders" />
              <span>Filtros</span>
              <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-blue-600"></span>
            </button>

            <button
              type="button"
              @click="applyFilters"
              class="flex-1 sm:flex-none justify-center flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2052C7] hover:bg-blue-800 text-white text-sm font-semibold shadow-sm hover:shadow transition cursor-pointer"
            >
              <font-awesome-icon icon="magnifying-glass" />
              <span>Buscar</span>
            </button>

            <button
              type="button"
              v-if="hasActiveFilters"
              @click="clearFilters"
              title="Limpiar todos los filtros"
              class="flex-1 sm:flex-none justify-center flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition cursor-pointer"
            >
              <font-awesome-icon icon="rotate-left" />
              <span>Limpiar</span>
            </button>
          </div>
        </div>

        <!-- Cuadrícula de Filtros Específicos -->
        <transition name="fade">
          <div v-show="showFilters" class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
            <!-- 1. Filtrar por mes -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Mes">Mes</label>
              <select
                v-model="filters.mes"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos los meses</option>
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
              </select>
            </div>

            <!-- 2. Filtro por factura -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Factura">Factura</label>
              <input
                v-model="filters.factura"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Nro. o con/sin factura"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>

            <!-- 3. Filtro por proveedor -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Proveedor">Proveedor</label>
              <input
                v-model="filters.proveedor"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Nombre o RIF"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>

            <!-- 4. Filtro por paciente -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Paciente">Paciente</label>
              <input
                v-model="filters.paciente"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Nombre o Cédula"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>

            <!-- 5. Filtro por punto de cuenta -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Punto de Cuenta">Punto de Cuenta</label>
              <input
                v-model="filters.punto_cuenta"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Nro. punto cuenta"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>

            <!-- 6. Filtro por orden de pago -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Orden de Pago">Orden de Pago</label>
              <input
                v-model="filters.orden_pago"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Nro. orden pago"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>
          </div>
        </transition>
      </div>

      <div class="table-data__wrapper">
        <table class="table-data">
          <thead>
            <tr class="">
              <th>ID / Orden</th>
              <th>Fecha Orden</th>
              <th class="w-[15%] text-left">Proveedor</th>
              <th class="w-[15%] text-left">Beneficiario</th>
              <th>Monto</th>
              <th>Factura</th>
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

              <td class="text-left capitalize">
                <div v-if="row.beneficiario">
                  <p class="font-semibold text-blue-600">{{ row.beneficiario }}</p>
                  <p v-if="row.diagnostico" class="text-xs text-gray-500">Diag: {{ row.diagnostico }}</p>
                </div>
                <span v-else class="text-gray-400 text-xs">Sin beneficiario</span>
              </td>

              <td class="text-center font-bold text-green-700">
                {{ formatCurrency(row.monto) }}
              </td>

              <td class="text-center font-bold">
                <span v-if="row.nro_factura" class="text-green-600 flex items-center justify-center gap-1">
                  <font-awesome-icon icon="check-circle" /> Sí
                </span>
                <span v-else class="text-red-600 flex items-center justify-center gap-1">
                  <font-awesome-icon icon="times-circle" /> No
                </span>
              </td>

              <td class="text-center text-red-600">
                {{ formatCurrency((parseFloat(row.saldo_deudor) || 0) - (parseFloat(row.saldo_acreedor) || 0)) }}
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
                    class="bg-[#2052C7] text-white p-2 rounded-lg hover:opacity-80 cursor-pointer"
                    @click="() => casePersona_id = row.registro_id">
                    Ver punto
                  </button>
                  
                  <button title="Eliminar"
                    class="bg-red-700 text-white p-2 rounded-lg hover:bg-red-900 cursor-pointer"
                    @click="deleteCaso(row.id)">
                    <font-awesome-icon icon="trash-can" />
                  </button>
                </div>
              </td>
            </tr>
            
            <tr v-if="data.rows.length == 0">
              <td colspan="10" class="text-center py-10 text-gray-500">
                No se encontraron registros de pagos que coincidan con los filtros de búsqueda.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <span v-if="Object.keys(errors).length > 0" class="text-red-500">{{ errors }}</span>
      <AppPaginationD v-if="data.links && data.links.length > 0" :links="data.links"></AppPaginationD>
    </div>

  </div>

  <section class="results my-10 w-full md:w-3/4" v-if="Object.keys(result).length > 0">
    <CardInfoUser :UserData="result[0]" title="Datos personales" icon="fa-solid fa-user" />
    <CardInfoUser :UserData="result[1]" title="Punto de cuenta" icon="fa-solid fa-location-dot"
      @seePDC="({ registro_id }) => casePersona_id = registro_id"
      @deletePDC="(data) => deleteCaso(data.registro_id)" />
  </section>

</template>

<style scoped>
/* Anulación de width fijo y reglas rígidas impuestas por panels.css (.panel > div input { width: 500px!important; }) */
.panel :deep(input),
.panel :deep(select),
.panel input,
.panel select,
.filter-input {
  width: 100% !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
}

.panel :deep(.min-w-0),
.panel .min-w-0,
.panel :deep(.grid > div),
.panel .grid > div {
  width: 100% !important;
}

.panel input.filter-input {
  border-radius: 0.5rem !important;
}

.panel input.search-input {
  border-radius: 0.75rem !important;
}

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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
