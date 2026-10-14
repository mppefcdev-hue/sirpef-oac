<script setup lang="ts">
import Controls from '@/components/sirpef/form/Controls.vue';
import MoneyInput from '@/components/sirpef/form/MoneyInput.vue';
import Http from '@/utils/Http';
import { ref } from 'vue';
import { onMounted } from 'vue';


import BitacoraTimeline from './BitacoraTimeline.vue';

const pagos = ref([])
const cuotaDisponible = ref(0)

const props = defineProps<{
  emitForm: (event: Event) => void,
  step: number,
  values: any,
  pagoId?: string | number | null,
}>()

import { computed } from 'vue';

const tipoPagoNombre = computed(() => {
  if (!props.values.tipo_pago) return '';
  const pago = pagos.value.find((p: any) => p.id === props.values.tipo_pago);
  return pago ? pago.nombre.toLowerCase() : '';
});

const getPay = async () => {
  const res = await Http.get(`/api/oac/tipos-pagos/`);
  pagos.value = res.data.data
};

const getCuota = async () => {
  try {
    const res = await Http.get('/api/oac/cuotas');
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    // Find the current month's quota
    const currentCuota = res.data.data.find((c: any) => c.ano === currentYear && c.mes === currentMonth);
    if (currentCuota) {
      cuotaDisponible.value = currentCuota.monto_disponible;
    }
  } catch (error) {
    console.error("Error obteniendo la cuota:", error);
  }
};

onMounted(() => {
  getPay()
  getCuota()
})


</script>

<template>
  <form @submit.prevent="emitForm">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mt-3">

      <div>
        <label class="block font-medium text-gray-700 ml-1">Tipo de Pago</label>
        <select name="tipo_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg"
          v-model="values.tipo_pago" required>
          <option value="">Sin seleccion</option>
          <option :value="pago.id" v-for="pago in pagos">{{pago.nombre}}</option>
        </select>
      </div>

      <div v-if="tipoPagoNombre.includes('financier')">
        <label class="block font-medium text-gray-700 ml-1">Nro. Referencia Pago Financiero</label>
        <input name="nro_referencia_pago" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Ingrese nro de referencia" v-model="values.nro_referencia_pago" required />
      </div>

      <div v-if="tipoPagoNombre.includes('financier')">
        <label class="block font-medium text-gray-700 ml-1">Fecha de Pago Financiero</label>
        <input name="fecha_pago_financiero" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="date"
          v-model="values.fecha_pago_financiero" required />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Proveedor</label>
        <input name="proveedor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Nombre del proveedor" v-model="values.proveedor" required />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">RIF del Proveedor</label>
        <input name="rif_proveedor" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Ej: J-12345678-9" v-model="values.rif_proveedor" required />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Contacto</label>
        <input name="contacto" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="number"
          placeholder="Ej: 04121234567" v-model="values.contacto" required />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Beneficiario (Paciente)</label>
        <input name="beneficiario" 
          class="w-full bg-gray-200 text-gray-700 font-semibold mt-1 p-3 rounded-lg cursor-not-allowed" 
          type="text" 
          :value="values.beneficiario || 'Sin beneficiario asignado'" 
          readonly 
          disabled
        />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Diagnóstico</label>
        <input name="diagnostico" class="w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg" type="text"
          placeholder="Diagnóstico" v-model="values.diagnostico" required />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Monto</label>
        <MoneyInput name="monto" placeholder="0,00" v-model="values.monto" :required="true" />
      </div>

      <div>
        <label class="block font-medium text-gray-700 ml-1">Cuota de Compromiso Disponible</label>
        <input 
          class="w-full bg-gray-200 text-gray-500 font-bold mt-1 p-3 rounded-lg cursor-not-allowed" 
          type="text" 
          :value="Math.max(0, cuotaDisponible - (parseFloat(values.monto) || 0)).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })" 
          readonly 
          disabled
        />
      </div>

      <!-- Sección de Bitácora de Contacto con el Proveedor (Rol User / Admin / Director) -->
      <div class="col-span-1 md:col-span-2 mt-4">
        <!-- Si ya existen notas en la bitácora (modo edición o consulta previa) -->
        <div v-if="values.bitacoras && values.bitacoras.length > 0" class="mb-4">
          <BitacoraTimeline
            :items="values.bitacoras"
            :pago-id="pagoId"
            :allow-add="false"
            title="Historial de Bitácora del Proveedor"
          />
        </div>

        <!-- Formulario de contacto inicial para el caso -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 md:p-5 shadow-xs">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-xl">📋</span>
            <div>
              <h4 class="font-bold text-gray-800 text-sm md:text-base">Bitácora de Contacto con el Proveedor</h4>
              <p class="text-xs text-gray-500">
                (Opcional) Registre si realizó algún contacto previo con el proveedor para informar al Analista, Director y Administración.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
            <div>
              <label class="block text-xs font-semibold text-gray-700 ml-1">Canal de Contacto</label>
              <select
                v-model="values.bitacora_tipo_contacto"
                class="w-full bg-white text-gray-900 mt-1 p-2.5 rounded-lg border border-gray-300 text-sm outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Llamada telefónica">📞 Llamada telefónica</option>
                <option value="Correo electrónico">✉️ Correo electrónico</option>
                <option value="WhatsApp / Mensajería">💬 WhatsApp / Mensajería</option>
                <option value="Visita presencial">🏢 Visita presencial</option>
                <option value="Otro">📋 Otro</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 ml-1">Persona o Teléfono Contactado (Opcional)</label>
              <input
                type="text"
                v-model="values.bitacora_contacto_proveedor"
                placeholder="Ej: Sr. Roberto / 0414-1234567"
                class="w-full bg-white text-gray-900 mt-1 p-2.5 rounded-lg border border-gray-300 text-sm outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div class="col-span-1 md:col-span-2">
              <label class="block text-xs font-semibold text-gray-700 ml-1">
                Detalle del Contacto / Acuerdos alcanzados
              </label>
              <textarea
                v-model="values.bitacora_descripcion"
                rows="3"
                placeholder="Ejemplo: Se realizó una llamada y quedaron pendiente de enviar la factura..."
                class="w-full bg-white text-gray-900 mt-1 p-3 rounded-lg border border-gray-300 text-sm outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

    </div>
    <Controls :step="step" />
  </form>
</template>