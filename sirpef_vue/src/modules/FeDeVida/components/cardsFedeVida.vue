<script lang="ts" setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import CardDashboard from './cardDashboard.vue';

interface CardItem {
  label: string;
  data: any;
  bg: string;
  nombre?: string;
}

const props = defineProps<{
  items: CardItem[];
}>();

const router = useRouter();

const STORAGE_KEY = 'sirpef_selected_statistic_cards';
const MAX_VISIBLE = 5;

const showSelectorModal = ref(false);
const selectedLabels = ref<string[]>([]);
const tempSelectedLabels = ref<string[]>([]);
const errorMessage = ref('');

// Inicializar y sincronizar selección con items
watch(
  () => props.items,
  (newItems) => {
    if (!newItems || newItems.length === 0) {
      selectedLabels.value = [];
      return;
    }

    if (newItems.length <= MAX_VISIBLE) {
      selectedLabels.value = newItems.map((i) => i.label);
      return;
    }

    // Si hay más de 5, intentar recuperar preferencia guardada
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const parsed = JSON.parse(saved) as string[];
        if (Array.isArray(parsed) && parsed.length > 0) {
          const validSaved = parsed.filter((lbl) =>
            newItems.some((item) => item.label === lbl)
          );
          if (validSaved.length > 0) {
            // Si hay guardadas válidas, completar hasta 5 si es necesario
            const available = newItems.map((i) => i.label);
            const remaining = available.filter((l) => !validSaved.includes(l));
            selectedLabels.value = [...validSaved, ...remaining].slice(0, MAX_VISIBLE);
            return;
          }
        }
      }
    } catch (e) {
      console.warn('No se pudo leer la preferencia de tarjetas guardadas', e);
    }

    // Por defecto: seleccionar las primeras 5
    selectedLabels.value = newItems.slice(0, MAX_VISIBLE).map((i) => i.label);
  },
  { immediate: true, deep: true }
);

// Tarjetas visibles (máximo 5)
const visibleItems = computed(() => {
  if (!props.items || props.items.length === 0) return [];
  if (props.items.length <= MAX_VISIBLE) return props.items;
  
  // Filtrar según selectedLabels manteniendo el orden de selección
  const itemsMap = new Map(props.items.map((item) => [item.label, item]));
  const orderedVisible: CardItem[] = [];

  for (const label of selectedLabels.value) {
    const item = itemsMap.get(label);
    if (item) orderedVisible.push(item);
  }

  // Fallback si por alguna razón no alcanza 5
  if (orderedVisible.length === 0) {
    return props.items.slice(0, MAX_VISIBLE);
  }

  return orderedVisible.slice(0, MAX_VISIBLE);
});

// Tarjetas guardadas (no visibles)
const hiddenItems = computed(() => {
  if (!props.items || props.items.length <= MAX_VISIBLE) return [];
  return props.items.filter((item) => !selectedLabels.value.includes(item.label));
});

const openSelectorModal = () => {
  tempSelectedLabels.value = [...selectedLabels.value];
  errorMessage.value = '';
  showSelectorModal.value = true;
};

const toggleCardSelection = (label: string) => {
  errorMessage.value = '';
  const idx = tempSelectedLabels.value.indexOf(label);

  if (idx !== -1) {
    if (tempSelectedLabels.value.length <= 1) {
      errorMessage.value = 'Debe mantener al menos una tarjeta visible.';
      return;
    }
    tempSelectedLabels.value.splice(idx, 1);
  } else {
    if (tempSelectedLabels.value.length >= MAX_VISIBLE) {
      errorMessage.value = `Máximo ${MAX_VISIBLE} tarjetas visibles simultáneamente. Desmarque una para agregar otra.`;
      return;
    }
    tempSelectedLabels.value.push(label);
  }
};

const saveSelection = () => {
  if (tempSelectedLabels.value.length === 0) {
    errorMessage.value = 'Debe seleccionar al menos una tarjeta.';
    return;
  }
  selectedLabels.value = [...tempSelectedLabels.value];
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedLabels.value));
  } catch (e) {
    console.warn('Error guardando tarjetas en localStorage', e);
  }
  showSelectorModal.value = false;
};

const resetToDefault = () => {
  if (!props.items) return;
  tempSelectedLabels.value = props.items.slice(0, MAX_VISIBLE).map((i) => i.label);
  errorMessage.value = '';
};

const redirect = (estatus_caso: any) => {
  estatus_caso = estatus_caso.replace(/\s*Casos\s*/gi, '');

  switch (estatus_caso) {
    case 'En Trámite':
      estatus_caso = 'En Tramite';
      break;
    case 'Orientados':
      estatus_caso = 'Orientado';
      break;
    case 'con Resultado Directo':
      estatus_caso = 'Resultado Directo';
      break;
    case 'Remitidos a Otro':
      estatus_caso = 'Remitido a Otro';
      break;
    case 'Cerrados':
      estatus_caso = 'Cerrado';
      break;
  }

  router.push({
    path: '/cases',
    query: {
      estatus_caso,
    },
  });
};
</script>

<template>
  <div class="w-full">
    <!-- Barra de control del selector cuando hay más de 5 tarjetas -->
    <div
      v-if="items && items.length > MAX_VISIBLE"
      class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-8 mb-4"
    >
      <!-- Información y tarjetas guardadas -->
      <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
        <span class="inline-flex items-center gap-1.5 font-semibold text-gray-700">
          <font-awesome-icon icon="layer-group" class="text-blue-600" />
          Mostrando {{ visibleItems.length }} de {{ items.length }}
        </span>

        <span
          v-if="hiddenItems.length > 0"
          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
        >
          {{ hiddenItems.length }} guardadas
        </span>

        <!-- Chips de tarjetas guardadas accesibles -->
        <div class="hidden md:flex items-center gap-1.5 ml-2">
          <button
            v-for="hidden in hiddenItems"
            :key="hidden.label"
            @click="openSelectorModal"
            type="button"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-lg border border-dashed border-gray-300 text-gray-600 bg-white hover:bg-gray-50 hover:border-blue-400 hover:text-blue-600 transition-all cursor-pointer shadow-sm"
            :title="`Tarjeta guardada: ${hidden.label}. Clic para cambiar tarjetas visibles.`"
          >
            <span
              class="w-2 h-2 rounded-full flex-shrink-0"
              :style="{ backgroundColor: hidden.bg }"
            ></span>
            <span class="truncate max-w-[140px]">{{ hidden.label }}</span>
            <font-awesome-icon icon="plus" class="text-[10px] text-gray-400" />
          </button>
        </div>
      </div>

      <!-- Botón para abrir el selector modal -->
      <button
        type="button"
        @click="openSelectorModal"
        class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium text-[#010c41] bg-white border border-gray-300 hover:border-[#4B7EB6] hover:bg-blue-50/60 rounded-xl shadow-sm transition-all duration-200 cursor-pointer ml-auto"
      >
        <font-awesome-icon icon="sliders" class="text-[#4B7EB6]" />
        <span>Seleccionar tarjetas</span>
        <span
          class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-[#010c41]"
        >
          {{ visibleItems.length }}/{{ MAX_VISIBLE }}
        </span>
      </button>
    </div>

    <!-- Cuadrícula de tarjetas (siempre máximo 5 para evitar que se desajusten) -->
    <div
      class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4 w-full h-full mx-auto xl:grid-cols-5 sm:px-8"
    >
      <CardDashboard
        v-for="item in visibleItems"
        :key="item.label"
        :title="item.label"
        :value="item.data"
        :color="item.bg"
        @click="redirect(item.label)"
      />
    </div>

    <!-- Modal de Configuración y Selector de Tarjetas -->
    <div
      v-if="showSelectorModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition-opacity"
      @click.self="showSelectorModal = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-200 animate-fadeIn"
      >
        <!-- Modal Header -->
        <div
          class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-[#010c41] to-[#1a2d7c] text-white"
        >
          <div class="flex items-center gap-2.5">
            <font-awesome-icon icon="sliders" class="text-amber-400 text-lg" />
            <div>
              <h3 class="text-lg font-bold">Seleccionar tarjetas a mostrar</h3>
              <p class="text-xs text-gray-200">
                Elige hasta {{ MAX_VISIBLE }} tarjetas visibles. Las demás quedarán guardadas.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="showSelectorModal = false"
            class="text-gray-300 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors"
          >
            <font-awesome-icon icon="xmark" class="text-xl" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
          <!-- Banner contador de tarjetas -->
          <div
            class="flex items-center justify-between p-3 rounded-xl border"
            :class="
              tempSelectedLabels.length === MAX_VISIBLE
                ? 'bg-blue-50 border-blue-200 text-blue-900'
                : 'bg-gray-50 border-gray-200 text-gray-700'
            "
          >
            <div class="text-sm font-medium">
              Tarjetas seleccionadas:
              <span class="font-bold text-base ml-1">
                {{ tempSelectedLabels.length }} / {{ MAX_VISIBLE }}
              </span>
            </div>
            <span
              v-if="tempSelectedLabels.length === MAX_VISIBLE"
              class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-200 text-blue-800"
            >
              Límite alcanzado
            </span>
          </div>

          <!-- Mensaje de aviso/error -->
          <div
            v-if="errorMessage"
            class="p-2.5 text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2"
          >
            <font-awesome-icon icon="circle-exclamation" class="text-red-500 text-sm" />
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Lista interactiva de tarjetas -->
          <div class="space-y-2">
            <div
              v-for="item in items"
              :key="item.label"
              @click="toggleCardSelection(item.label)"
              class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer"
              :class="[
                tempSelectedLabels.includes(item.label)
                  ? 'border-blue-500 bg-blue-50/40 shadow-xs'
                  : 'border-gray-200 hover:bg-gray-50/80 bg-white opacity-70 hover:opacity-100',
                tempSelectedLabels.length >= MAX_VISIBLE &&
                !tempSelectedLabels.includes(item.label)
                  ? 'cursor-not-allowed opacity-50'
                  : '',
              ]"
            >
              <!-- Checkbox y detalles de la tarjeta -->
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="tempSelectedLabels.includes(item.label)"
                  :disabled="
                    tempSelectedLabels.length >= MAX_VISIBLE &&
                    !tempSelectedLabels.includes(item.label)
                  "
                  class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 pointer-events-none"
                />

                <span
                  class="w-4 h-4 rounded-md shadow-xs flex-shrink-0"
                  :style="{ backgroundColor: item.bg }"
                ></span>

                <div>
                  <p class="text-sm font-semibold text-gray-800 capitalize">
                    {{ item.label }}
                  </p>
                  <p class="text-xs text-gray-500">
                    Total registrado:
                    <span class="font-bold text-gray-700">{{ item.data }}</span>
                  </p>
                </div>
              </div>

              <!-- Badge de estado -->
              <span
                class="text-xs font-semibold px-2.5 py-1 rounded-full"
                :class="
                  tempSelectedLabels.includes(item.label)
                    ? 'bg-blue-100 text-blue-800'
                    : 'bg-gray-100 text-gray-500'
                "
              >
                {{ tempSelectedLabels.includes(item.label) ? 'Visible' : 'Guardada' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div
          class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200"
        >
          <button
            type="button"
            @click="resetToDefault"
            class="text-xs font-medium text-gray-600 hover:text-blue-700 inline-flex items-center gap-1.5 underline underline-offset-2 cursor-pointer"
          >
            <font-awesome-icon icon="rotate-left" class="text-xs" />
            Restablecer por defecto (5)
          </button>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showSelectorModal = false"
              class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="saveSelection"
              class="px-4 py-2 text-xs font-semibold text-white bg-[#010c41] hover:bg-[#1a2d7c] rounded-xl shadow transition-colors cursor-pointer"
            >
              Aplicar selección
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>