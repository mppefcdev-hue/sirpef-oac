<script setup lang="ts">
import { ref, computed } from 'vue';
import { addBitacoraPago } from '../../services';
import { alerta } from '@/utils/alert';

const props = withDefaults(defineProps<{
  items?: any[];
  pagoId?: string | number | null;
  registroId?: string | number | null;
  allowAdd?: boolean;
  title?: string;
  compact?: boolean;
}>(), {
  items: () => [],
  pagoId: null,
  registroId: null,
  allowAdd: true,
  title: 'Bitácora de Recorrido / Contactos con el Proveedor',
  compact: false,
});

const emit = defineEmits<{
  (e: 'entry-added', newEntry: any): void;
}>();

const newContacto = ref({
  tipo_contacto: 'Llamada telefónica',
  contacto_proveedor: '',
  descripcion: '',
});

const isSubmitting = ref(false);
const showAddForm = ref(false);

const tiposContacto = [
  { value: 'Llamada telefónica', label: '📞 Llamada telefónica' },
  { value: 'Correo electrónico', label: '✉️ Correo electrónico' },
  { value: 'WhatsApp / Mensajería', label: '💬 WhatsApp / Mensajería' },
  { value: 'Visita presencial', label: '🏢 Visita presencial' },
  { value: 'Otro', label: '📋 Otro' },
];

const formatDate = (dateStr: string) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleString('es-VE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    });
  } catch {
    return dateStr;
  }
};

const getRoleBadgeClass = (roleName: string) => {
  const r = (roleName || '').toLowerCase();
  if (r.includes('admin')) {
    return 'bg-emerald-100 text-emerald-800 border-emerald-300';
  }
  if (r.includes('director')) {
    return 'bg-amber-100 text-amber-800 border-amber-300';
  }
  if (r.includes('analista')) {
    return 'bg-purple-100 text-purple-800 border-purple-300';
  }
  if (r.includes('user')) {
    return 'bg-blue-100 text-blue-800 border-blue-300';
  }
  return 'bg-gray-100 text-gray-800 border-gray-300';
};

const getChannelIcon = (tipo: string) => {
  const t = (tipo || '').toLowerCase();
  if (t.includes('llamada')) return '📞';
  if (t.includes('correo') || t.includes('email')) return '✉️';
  if (t.includes('whatsapp') || t.includes('mensaje')) return '💬';
  if (t.includes('presencial') || t.includes('visita')) return '🏢';
  return '📋';
};

const sortedItems = computed(() => {
  if (!props.items || props.items.length === 0) return [];
  return [...props.items].sort((a, b) => {
    const dateA = new Date(a.created_at || a.fecha_contacto).getTime();
    const dateB = new Date(b.created_at || b.fecha_contacto).getTime();
    return dateA - dateB; // orden cronológico
  });
});

const submitDirectEntry = async () => {
  if (!newContacto.value.descripcion.trim()) {
    alerta('Campo requerido', 'Por favor describe el resultado o detalle del contacto realizado.', 'warning');
    return;
  }

  if (!props.pagoId) {
    // Si no hay pagoId, emitimos al padre para que se gestione en el submit del formulario general
    emit('entry-added', {
      ...newContacto.value,
      rol_nombre: 'Actual',
      created_at: new Date().toISOString(),
    });
    newContacto.value = {
      tipo_contacto: 'Llamada telefónica',
      contacto_proveedor: '',
      descripcion: '',
    };
    showAddForm.value = false;
    return;
  }

  isSubmitting.value = true;
  try {
    const res = await addBitacoraPago(props.pagoId, {
      tipo_contacto: newContacto.value.tipo_contacto,
      contacto_proveedor: newContacto.value.contacto_proveedor,
      descripcion: newContacto.value.descripcion.trim(),
    });

    if (res.success && res.data) {
      alerta('Éxito', 'Nota registrada en la bitácora correctamente', 'success');
      emit('entry-added', res.data);
      newContacto.value = {
        tipo_contacto: 'Llamada telefónica',
        contacto_proveedor: '',
        descripcion: '',
      };
      showAddForm.value = false;
    }
  } catch (err: any) {
    console.error('Error registrando bitácora:', err);
    alerta('Error', err.response?.data?.message || 'No se pudo guardar la nota en la bitácora', 'error');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="bitacora-container bg-white border border-gray-200 rounded-xl p-4 md:p-5 shadow-sm">
    <!-- Header de la Bitácora -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <span class="text-xl">📋</span>
        <div>
          <h3 class="font-bold text-gray-800 text-base md:text-lg">{{ title }}</h3>
          <p class="text-xs text-gray-500">Historial y seguimiento de contactos realizados con el proveedor</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
          {{ sortedItems.length }} {{ sortedItems.length === 1 ? 'contacto' : 'contactos' }}
        </span>
        <button
          v-if="allowAdd && !showAddForm"
          type="button"
          @click="showAddForm = true"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#2052C7] text-white hover:bg-[#163ca0] transition shadow-sm"
        >
          <span>+ Agregar Nota</span>
        </button>
      </div>
    </div>

    <!-- Formulario para agregar nueva entrada (colapsable o directo) -->
    <div
      v-if="allowAdd && showAddForm"
      class="bg-blue-50/50 border border-blue-200 rounded-xl p-4 mb-5 transition-all"
    >
      <div class="flex justify-between items-center mb-3">
        <h4 class="text-sm font-bold text-blue-900 flex items-center gap-1.5">
          <span>✍️</span> Registrar nuevo contacto con el proveedor
        </h4>
        <button
          type="button"
          @click="showAddForm = false"
          class="text-gray-400 hover:text-gray-600 text-sm font-bold"
        >
          ✕
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo / Canal de contacto</label>
          <select
            v-model="newContacto.tipo_contacto"
            class="w-full text-sm bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
          >
            <option v-for="t in tiposContacto" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Persona / Teléfono contactado (opcional)</label>
          <input
            type="text"
            v-model="newContacto.contacto_proveedor"
            placeholder="Ej: Sr. Roberto Pérez / 0414-1234567"
            class="w-full text-sm bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"
          />
        </div>
      </div>

      <div class="mb-3">
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Descripción del contacto / Acuerdos alcanzados <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="newContacto.descripcion"
          rows="3"
          placeholder="Ej: Se realizó una llamada al proveedor y quedaron pendiente de enviar la factura por correo..."
          class="w-full text-sm bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none resize-none"
        ></textarea>
      </div>

      <div class="flex justify-end gap-2">
        <button
          type="button"
          @click="showAddForm = false"
          class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-800 rounded-lg border border-gray-300 bg-white"
        >
          Cancelar
        </button>
        <button
          type="button"
          @click="submitDirectEntry"
          :disabled="isSubmitting || !newContacto.descripcion.trim()"
          class="px-4 py-1.5 text-xs font-bold text-white bg-[#2052C7] hover:bg-[#163ca0] disabled:opacity-50 rounded-lg shadow-sm transition flex items-center gap-1.5"
        >
          <span v-if="isSubmitting">Guardando...</span>
          <span v-else>Guardar en Bitácora</span>
        </button>
      </div>
    </div>

    <!-- Línea de tiempo de entradas registradas -->
    <div v-if="sortedItems.length > 0" class="relative pl-6 md:pl-8 space-y-4 before:absolute before:left-3 md:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-blue-200">
      <div
        v-for="(item, idx) in sortedItems"
        :key="item.id || idx"
        class="relative group"
      >
        <!-- Icono/Punto del timeline -->
        <span class="absolute -left-6 md:-left-8 top-1 flex items-center justify-center w-6 h-6 rounded-full bg-white border-2 border-blue-500 text-xs shadow-sm">
          {{ getChannelIcon(item.tipo_contacto) }}
        </span>

        <!-- Tarjeta de la nota -->
        <div class="bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl p-3.5 transition-all">
          <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <div class="flex items-center gap-2 flex-wrap">
              <!-- Badge del Rol -->
              <span
                class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider border shadow-xs"
                :class="getRoleBadgeClass(item.rol_nombre || item.user?.role?.name)"
              >
                {{ item.rol_nombre || item.user?.role?.name || 'Usuario' }}
              </span>

              <!-- Nombre del usuario -->
              <span class="font-semibold text-gray-800 text-xs md:text-sm">
                {{ item.user?.name || 'Usuario' }}
              </span>

              <!-- Tipo de contacto -->
              <span class="text-xs text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-full">
                {{ item.tipo_contacto || 'Contacto' }}
              </span>
            </div>

            <!-- Fecha y hora -->
            <span class="text-[11px] text-gray-500 font-medium">
              {{ formatDate(item.fecha_contacto || item.created_at) }}
            </span>
          </div>

          <!-- Si se indicó persona contactada -->
          <div v-if="item.contacto_proveedor" class="text-xs text-gray-600 mb-1.5 flex items-center gap-1 font-medium">
            <span class="text-gray-400">Interlocutor:</span> {{ item.contacto_proveedor }}
          </div>

          <!-- Texto descriptivo -->
          <p class="text-gray-700 text-sm whitespace-pre-wrap leading-relaxed">
            {{ item.descripcion }}
          </p>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div
      v-else
      class="text-center py-8 px-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl"
    >
      <div class="text-3xl mb-2">📭</div>
      <h4 class="text-sm font-semibold text-gray-700">Sin historial de contactos</h4>
      <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 mb-3">
        Aún no se han registrado llamadas, correos o visitas con el proveedor para este caso.
      </p>
      <button
        v-if="allowAdd && !showAddForm"
        type="button"
        @click="showAddForm = true"
        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition"
      >
        <span>+ Registrar primer contacto</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.bitacora-container {
  font-family: inherit;
}
</style>
