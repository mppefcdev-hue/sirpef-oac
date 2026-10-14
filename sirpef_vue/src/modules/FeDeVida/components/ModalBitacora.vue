<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import BitacoraTimeline from './caseFormAdministracion/BitacoraTimeline.vue';
import { getBitacoraPago } from '../services';

const props = defineProps<{
  pagoId: number | string;
  pagoData?: any;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'updated'): void;
}>();

const loading = ref(true);
const items = ref<any[]>([]);

const loadBitacora = async () => {
  if (!props.pagoId) return;
  loading.value = true;
  try {
    const res = await getBitacoraPago(props.pagoId);
    if (res.success && Array.isArray(res.data)) {
      items.value = res.data;
    } else if (Array.isArray(res)) {
      items.value = res;
    }
  } catch (error) {
    console.error('Error al cargar la bitácora:', error);
  } finally {
    loading.value = false;
  }
};

const handleEntryAdded = (newEntry: any) => {
  items.value.push(newEntry);
  emit('updated');
};

const proveedorNombre = () => {
  if (props.pagoData?.proveedores && props.pagoData.proveedores.length > 0) {
    return props.pagoData.proveedores[0].nombre;
  }
  return 'Proveedor no especificado';
};

onMounted(() => {
  // Si ya vienen bitacoras en pagoData, inicializar con ellas
  if (props.pagoData?.bitacoras && Array.isArray(props.pagoData.bitacoras)) {
    items.value = [...props.pagoData.bitacoras];
  }
  loadBitacora();
  document.body.style.overflow = 'hidden';
});

const close = () => {
  document.body.style.overflow = 'auto';
  emit('close');
};
</script>

<template>
  <div
    class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-[999] overflow-y-auto"
    @click.self="close"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden animate-fadeIn"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-[#041e42] to-[#2052c7] text-white flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-xl">📋</span>
            <h2 class="text-lg font-bold">Bitácora de Recorrido y Contactos</h2>
          </div>
          <p class="text-xs text-blue-100 mt-0.5">
            Pago #{{ pagoId }} • {{ proveedorNombre() }}
            <span v-if="pagoData?.orden_pago"> • Orden: {{ pagoData.orden_pago }}</span>
          </p>
        </div>
        <button
          type="button"
          @click="close"
          class="text-white/80 hover:text-white text-2xl font-bold transition p-1 hover:bg-white/10 rounded-lg leading-none"
        >
          &times;
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto flex-1">
        <div v-if="loading && items.length === 0" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
          <p class="text-sm text-gray-500 mt-2">Cargando notas de la bitácora...</p>
        </div>

        <BitacoraTimeline
          v-else
          :items="items"
          :pago-id="pagoId"
          :allow-add="true"
          title="Recorrido de Contactos"
          @entry-added="handleEntryAdded"
        />
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
        <button
          type="button"
          @click="close"
          class="px-5 py-2 text-sm font-semibold rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.97);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
