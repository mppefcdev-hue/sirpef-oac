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

import { getPagosCasos } from "../../services";
import { alerta } from "@/utils/alert";

const store = useAuthStore()
const casePersona_id = ref(null)
const descripcion = ref(null)
const showFilters = ref(true)
const isExporting = ref(false)

const {
  route,
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
    filters.tiene_factura ||
    filters.tipo_pago ||
    filters.saldo_deudor ||
    filters.saldo_acreedor ||
    (filters.proveedor && filters.proveedor.trim()) ||
    (filters.paciente && filters.paciente.trim()) ||
    (filters.punto_cuenta && filters.punto_cuenta.trim()) ||
    (filters.orden_pago && filters.orden_pago.trim()) ||
    filters.estatus_pago
  );
});

watch(foundCaseId, (newId) => {
  if (newId) {
    casePersona_id.value = newId;
  }
});

import Swal from "sweetalert2";

const formatCurrency = (value: any) => {
  if (value === undefined || value === null || value === '') return '0,00';
  const number = typeof value === 'string' ? parseFloat(value) : value;
  if (isNaN(number)) return '0,00';
  return number.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const verDetallesPago = (row: any) => {
  const proveedorNombre = row.proveedores?.[0]?.nombre || 'Sin proveedor';
  const proveedorRif = row.proveedores?.[0]?.cedula_rif ? `(RIF: ${row.proveedores[0].cedula_rif})` : '';
  const tipoPago = row.tipo_pago?.nombre || row.tipoPago?.nombre || (row.tipo_pago_id === 1 ? 'Financiero' : 'Normal');
  const estatusNombre = row.estatus?.nombre || (row.estatus_pago_id === 1 ? 'Procesado' : 'No Procesado');
  const saldoDeudor = row.saldo_deudor;

  Swal.fire({
    title: `Detalles del Pago #${row.id}`,
    html: `
      <div class="text-left space-y-2 mt-4 text-sm text-gray-700">
        <p><strong>Nro. Orden de Pago:</strong> ${row.orden_pago || 'N/A'}</p>
        <p><strong>Fecha Orden:</strong> ${row.fecha_orden_pago || 'N/A'}</p>
        <p><strong>Proveedor:</strong> ${proveedorNombre} ${proveedorRif}</p>
        <p><strong>Beneficiario:</strong> ${row.beneficiario || 'Sin beneficiario'}</p>
        ${row.diagnostico ? `<p><strong>Diagnóstico:</strong> ${row.diagnostico}</p>` : ''}
        <p class="border-t pt-2"><strong>Monto:</strong> <span class="text-green-700 font-bold">Bs. ${formatCurrency(row.monto)}</span></p>
        <p><strong>Factura:</strong> ${row.nro_factura ? `<span class="text-green-600 font-semibold">${row.nro_factura}</span>` : '<span class="text-red-600 font-semibold">Sin factura</span>'}</p>
        <p><strong>Saldo Facturado (Acreedor):</strong> <span class="text-emerald-700 font-semibold">Bs. ${formatCurrency(row.saldo_acreedor)}</span></p>
        <p><strong>Saldo Deudor:</strong> <span class="${saldoDeudor > 0 ? 'text-red-600 font-bold' : 'text-gray-600'}">Bs. ${formatCurrency(saldoDeudor)}</span></p>
        <p><strong>Tipo de Pago:</strong> <span class="capitalize font-semibold">${tipoPago}</span></p>
        <p><strong>Estatus:</strong> <span class="${row.estatus_pago_id === 1 ? 'text-green-600' : 'text-orange-500'} font-bold">${estatusNombre}</span></p>
        <p class="border-t pt-2"><strong>Descripción:</strong> ${row.descripcion || 'Sin descripción'}</p>
      </div>
    `,
    icon: 'info',
    showCancelButton: !!row.registro_id,
    confirmButtonText: 'Cerrar',
    cancelButtonText: 'Ver Expediente / Punto',
    cancelButtonColor: '#2052C7',
    reverseButtons: true
  }).then((res) => {
    if (res.dismiss === Swal.DismissReason.cancel && row.registro_id) {
      casePersona_id.value = row.registro_id;
    }
  });
};

const editarPago = (row: any) => {
  const puntoNumero = row.registro?.punto_cuenta?.numero_punto || '';
  router.push({
    path: '/casos/administracion/form',
    query: {
      pago_id: row.id,
      punto: puntoNumero || (row.registro_id ? String(row.registro_id) : ''),
      registro_id: row.registro_id ? String(row.registro_id) : undefined
    }
  });
};

const exportToCSV = async () => {
  if (isExporting.value) return;
  isExporting.value = true;

  try {
    // 1. Filtrar parámetros actuales de la URL ignorando la paginación para exportar todo el conjunto
    const filteredParams = new URLSearchParams();
    if (route && route.query) {
      Object.entries(route.query).forEach(([key, value]) => {
        if (value && key !== 'page') {
          filteredParams.append(key, String(value));
        }
      });
    }
    filteredParams.append('all', 'true');

    // 2. Obtener los pagos que coinciden con los filtros actuales
    const response = await getPagosCasos(filteredParams.toString());
    const items = response.data.rows || response.data.data || [];

    if (!items || items.length === 0) {
      alerta("Información", "No hay pagos registrados para exportar con los filtros seleccionados.", "info");
      isExporting.value = false;
      return;
    }

    // 3. Encabezados de las columnas
    const headers = [
      'ID',
      'Orden de Pago',
      'Fecha Orden de Pago',
      'Proveedor',
      'RIF Proveedor',
      'Beneficiario',
      'Diagnostico',
      'Monto (Bs.)',
      'Factura',
      'Saldo Facturado (Bs.)',
      'Saldo Deudor (Bs.)',
      'Tipo de Pago',
      'Estatus',
      'Punto de Cuenta',
      'Fecha Pago Financiero',
      'Descripcion'
    ];

    // Función de escape para caracteres delimitadores en CSV
    const escapeCsv = (val: any) => {
      if (val === null || val === undefined) return '';
      const str = String(val);
      if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r') || str.includes(';')) {
        return `"${str.replace(/"/g, '""')}"`;
      }
      return str;
    };

    // 4. Mapear datos
    const rows = items.map((pago: any) => {
      const prov = pago.proveedores && pago.proveedores.length > 0 ? pago.proveedores[0] : null;
      const provNombre = prov ? prov.nombre : 'Sin proveedor';
      const provRif = prov ? prov.cedula_rif : 'N/A';
      const tipo = pago.tipo_pago?.nombre || pago.tipoPago?.nombre || 'Normal';
      const estatus = pago.estatus?.nombre || (pago.estatus_pago_id === 1 ? 'Procesado' : 'No Procesado');
      const saldoDeudor = (parseFloat(pago.saldo_deudor) || 0) - (parseFloat(pago.saldo_acreedor) || 0);
      const puntoCuenta = pago.registro?.punto_cuenta?.numero_punto || 'N/A';
      const montoFormatted = typeof pago.monto === 'number' ? pago.monto.toFixed(2) : (parseFloat(pago.monto) || 0).toFixed(2);
      const saldoAcreedorFormatted = (parseFloat(pago.saldo_acreedor) || 0).toFixed(2);

      return [
        pago.id,
        pago.orden_pago || 'N/A',
        pago.fecha_orden_pago || 'N/A',
        provNombre,
        provRif,
        pago.beneficiario || 'Sin beneficiario',
        pago.diagnostico || 'N/A',
        montoFormatted,
        pago.nro_factura || 'Sin factura',
        saldoAcreedorFormatted,
        saldoDeudor.toFixed(2),
        tipo,
        estatus,
        puntoCuenta,
        pago.fecha_pago_financiero || 'N/A',
        pago.descripcion ? pago.descripcion.replace(/(\r\n|\n|\r)/gm, " ") : 'Sin descripción'
      ];
    });

    // 5. Construir contenido CSV
    const csvContent = [
      headers.map(escapeCsv).join(','),
      ...rows.map((r: any[]) => r.map(escapeCsv).join(','))
    ].join('\r\n');

    // 6. Generar descarga con BOM UTF-8 (\uFEFF) para abrir correctamente en Excel
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const today = new Date().toISOString().split('T')[0];
    link.setAttribute('href', url);
    link.setAttribute('download', `gestion_pagos_${today}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    alerta("Éxito", `Se exportaron exitosamente ${items.length} pagos en formato CSV.`, "success");
  } catch (error: any) {
    console.error("Error al exportar CSV:", error);
    alerta("Error", "Ocurrió un error al generar el archivo CSV.", "error");
  } finally {
    isExporting.value = false;
  }
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
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none z-10">
              <font-awesome-icon icon="magnifying-glass" class="text-sm" />
            </span>
            <input
              v-model="data.search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Buscar por orden, factura, proveedor, paciente, tipo de pago, estatus..."
              class="filter-input search-input w-full min-w-0 pl-11 pr-4 py-2.5 bg-gray-50 hover:bg-gray-100/70 focus:bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition text-sm text-gray-800 placeholder-gray-400"
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

            <!-- Botón Exportar a CSV (Estilo cuotas de compromiso con lógica de pagos) -->
            <button
              type="button"
              @click="exportToCSV"
              :disabled="isExporting"
              title="Exportar todos los pagos filtrados a archivo CSV"
              class="flex-1 sm:flex-none justify-center flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#ECA008] hover:bg-[#010c41] text-white text-sm font-semibold shadow-sm hover:shadow transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <font-awesome-icon :icon="isExporting ? 'spinner' : 'file-csv'" :spin="isExporting" />
              <span>{{ isExporting ? 'Exportando...' : 'Exportar a CSV' }}</span>
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
          <div v-show="showFilters" class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
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

            <!-- 2. Filtro de tenencia de factura (Tiene factura o no) -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Tiene Factura">Tiene Factura</label>
              <select
                v-model="filters.tiene_factura"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos</option>
                <option value="con_factura">Con factura</option>
                <option value="sin_factura">Sin factura</option>
              </select>
            </div>

            <!-- 3. Filtro por número de factura específico -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Nro. Factura">Nro. Factura</label>
              <input
                v-model="filters.factura"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Ej: FAC-123"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              />
            </div>

            <!-- 4. Filtro por Tipo de Pago (Financiero o Normal) -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Tipo de Pago">Tipo de Pago</label>
              <select
                v-model="filters.tipo_pago"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos</option>
                <option value="normal">Normal</option>
                <option value="financiero">Financiero</option>
              </select>
            </div>

            <!-- 5. Filtro por Saldo Deudor -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Saldo Deudor">Saldo Deudor</label>
              <select
                v-model="filters.saldo_deudor"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos</option>
                <option value="con_saldo">Con saldo deudor</option>
                <option value="sin_saldo">Sin saldo deudor</option>
              </select>
            </div>

            <!-- 6. Filtro por Saldo Acreedor -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Saldo Acreedor">Saldo Acreedor</label>
              <select
                v-model="filters.saldo_acreedor"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos</option>
                <option value="con_saldo">Con saldo acreedor</option>
                <option value="sin_saldo">Sin saldo acreedor</option>
              </select>
            </div>

            <!-- 7. Filtro por proveedor -->
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

            <!-- 8. Filtro por paciente -->
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

            <!-- 9. Filtro por punto de cuenta -->
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

            <!-- 10. Filtro por orden de pago -->
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

            <!-- 11. Filtro por estatus de pago -->
            <div class="min-w-0 w-full">
              <label class="block text-xs font-semibold text-gray-600 mb-1 truncate" title="Estatus de Pago">Estatus</label>
              <select
                v-model="filters.estatus_pago"
                @change="applyFilters"
                class="filter-input w-full min-w-0 px-3 py-2 bg-gray-50 hover:bg-gray-100/70 focus:bg-white border border-gray-200 rounded-lg text-xs sm:text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
              >
                <option value="">Todos</option>
                <option value="procesado">Procesado</option>
                <option value="no_procesado">No Procesado</option>
              </select>
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
                <span class="text-xs text-gray-500">Ord: {{ row.orden_pago || 'N/A' }}</span>
                <div class="mt-1">
                  <span 
                    :class="(row.tipo_pago?.nombre || row.tipoPago?.nombre || '').toLowerCase().includes('financiero') || row.tipo_pago_id === 1 
                      ? 'bg-indigo-50 text-indigo-700 border-indigo-200' 
                      : 'bg-amber-50 text-amber-700 border-amber-200'" 
                    class="px-2 py-0.5 text-[10px] font-semibold rounded-full border inline-block"
                  >
                    {{ row.tipo_pago?.nombre || row.tipoPago?.nombre || (row.tipo_pago_id === 1 ? 'Financiero' : 'Normal') }}
                  </span>
                </div>
              </td>

              <td class="text-center">
                {{ row.fecha_orden_pago || 'N/A' }}
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
                Bs. {{ formatCurrency(row.monto) }}
              </td>

              <td class="text-center font-bold">
                <span v-if="row.nro_factura" class="text-green-600 flex items-center justify-center gap-1">
                  <font-awesome-icon icon="check-circle" /> Sí
                </span>
                <span v-else class="text-red-600 flex items-center justify-center gap-1">
                  <font-awesome-icon icon="times-circle" /> No
                </span>
                <span v-if="row.nro_factura" class="text-[11px] text-gray-500 font-normal block truncate max-w-[120px] mx-auto" :title="row.nro_factura">
                  {{ row.nro_factura }}
                </span>
              </td>

              <td class="text-center text-red-600">
                {{ formatCurrency(row.saldo_deudor) }}
                <!-- {{ formatCurrency(Math.max(0, (parseFloat(row.monto) || 0) - (parseFloat(row.saldo_acreedor) || 0))) }} -->
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
                <div class="flex gap-3 justify-center items-center">
                  <!-- 1. Solo vista (Detalles) -->
                  <button 
                    class="text-blue-600 hover:text-blue-800 hover:scale-110 font-bold transition-all text-sm flex items-center gap-1 cursor-pointer" 
                    @click="verDetallesPago(row)" 
                    title="Solo vista / Ver detalles"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>

                  <!-- 2. Editar (Ir directo al formulario) -->
                  <button 
                    class="text-[#eca008] hover:text-[#d68f07] hover:scale-110 font-bold transition-all text-sm flex items-center gap-1 cursor-pointer" 
                    @click="editarPago(row)" 
                    title="Editar pago"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </button>

                  <!-- 3. Eliminar (Soft delete) -->
                  <button 
                    class="text-red-600 hover:text-red-800 hover:scale-110 font-bold transition-all text-sm flex items-center gap-1 cursor-pointer" 
                    @click="deleteCaso(row.id)" 
                    title="Eliminar (Soft delete)"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
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
  padding-left: 2.75rem !important;
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
