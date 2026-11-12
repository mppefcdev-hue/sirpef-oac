<template>
  <div class="space-y-5">
    <!-- Tab Navigation -->
    <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
      <button
        type="button"
        @click="activeTab = 'info'"
        :class="['flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition-all', activeTab === 'info' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
      >
        <span>📝</span> Información
      </button>
      <button
        type="button"
        @click="activeTab = 'assets'"
        :class="['flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition-all', activeTab === 'assets' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
      >
        <span>🖼️</span> Recursos visuales
      </button>
    </div>

    <!-- Info Tab -->
    <div v-if="activeTab === 'info'" class="space-y-5">
      <!-- Destinatario / Remitente -->
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">1</span>
          <div>
            <h3 class="form-card-title">Destinatario y remitente</h3>
            <p class="form-card-subtitle">Seleccione quién recibe y quién emite el memorándum</p>
          </div>
        </header>
        <div class="space-y-4 p-4">
          <div>
            <label class="form-label">Para</label>
            <select v-model="form.para_nombre" @change="updateCargo('para')" class="form-input">
              <option v-for="opt in personasPara" :key="opt.nombre" :value="opt.nombre">{{ opt.nombre }}</option>
            </select>
            <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] italic leading-relaxed text-slate-600">
              {{ form.para_cargo }}
            </div>
          </div>

          <div>
            <label class="form-label">De</label>
            <select v-model="form.de_nombre" @change="updateCargo('de')" class="form-input">
              <option v-for="opt in personasDe" :key="opt.nombre" :value="opt.nombre">{{ opt.nombre }}</option>
            </select>
            <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] italic leading-relaxed text-slate-600">
              {{ form.de_cargo }}
            </div>
          </div>
        </div>
      </section>

      <!-- Datos principales -->
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">2</span>
          <div>
            <h3 class="form-card-title">Datos del memorándum</h3>
            <p class="form-card-subtitle">Vincule el punto de cuenta y complete la información general</p>
          </div>
        </header>
        <div class="space-y-4 p-4">
          <div>
            <label class="form-label">N° Memorándum</label>
            <input v-model="form.codigo" placeholder="Dejar vacío para autogenerar" class="form-input" />
          </div>

          <div>
            <label class="form-label">N° Punto de cuenta</label>
            <div class="flex gap-2">
              <input
                v-model="form.tabla.pto_cta"
                @keyup.enter="buscarPuntoCuenta(form.tabla.pto_cta)"
                placeholder="Ej: 000/2026"
                class="form-input flex-1"
              />
              <button
                type="button"
                @click="buscarPuntoCuenta(form.tabla.pto_cta)"
                class="flex h-[34px] w-[38px] shrink-0 items-center justify-center rounded-lg bg-blue-600 text-sm text-white transition-colors hover:bg-blue-700"
                title="Buscar punto de cuenta"
              >
                <span v-if="!loadingSearch">🔍</span>
                <span v-else class="inline-block animate-spin">⏳</span>
              </button>
            </div>
            <p v-if="form.punto_cuenta_id" class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-[9px] font-bold text-green-700">
              ✓ Punto de cuenta vinculado
            </p>
            <p v-else-if="form.tabla.pto_cta && form.tabla.pto_cta.length >= 3" class="mt-2 inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[9px] font-bold text-red-600">
              ⚠ No vinculado
            </p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label class="form-label">Fecha del punto de cuenta</label>
              <input v-model="form.tabla.fecha" type="date" class="form-input" />
            </div>
            <div>
              <label class="form-label">Cédula del solicitante</label>
              <input v-model="form.tabla.cedula" placeholder="V-00000000" class="form-input" />
            </div>
          </div>

          <div>
            <label class="form-label">Solicitante</label>
            <input v-model="form.tabla.solicitante" placeholder="Nombre del solicitante" class="form-input" />
          </div>
        </div>
      </section>

      <!-- Proveedores -->
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">3</span>
          <div class="flex-1">
            <h3 class="form-card-title">Proveedores y montos</h3>
            <p class="form-card-subtitle">Busque por RIF para autocompletar o registre un proveedor nuevo</p>
          </div>
        </header>
        <div class="space-y-3 p-4">
          <div
            v-for="(item, index) in form.tabla.proveedores"
            :key="index"
            class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3"
          >
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold uppercase tracking-wide text-slate-500">
                Proveedor {{ index + 1 }}
              </span>
              <button
                v-if="form.tabla.proveedores?.length > 1"
                type="button"
                @click="removeProveedor(index)"
                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold text-red-600 transition-colors hover:bg-red-50"
              >
                ✕ Quitar
              </button>
            </div>

            <div>
              <label class="form-label">RIF del proveedor</label>
              <div class="relative">
                <input
                  v-model="form.tabla.proveedores[index].cedula_rif"
                  @input="onRifInput(index)"
                  @focus="onRifFocus(index)"
                  @blur="closeProveedorSugerencias(index)"
                  placeholder="Ej: J-12345678-9"
                  class="form-input"
                />
                <div
                  v-if="dropdownAbierto[index]"
                  class="absolute left-0 right-0 z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"
                >
                  <div v-if="buscandoProveedor[index]" class="p-3 text-[10px] text-slate-500">
                    Buscando proveedores...
                  </div>
                  <template v-else>
                    <button
                      v-for="prov in (sugerenciasProveedores[index] || [])"
                      :key="prov.id"
                      type="button"
                      @mousedown.prevent="seleccionarProveedor(index, prov)"
                      class="block w-full border-b border-slate-100 px-3 py-2 text-left transition-colors last:border-b-0 hover:bg-blue-50"
                    >
                      <span class="block text-[10px] font-bold text-slate-700">{{ prov.cedula_rif }}</span>
                      <span class="block truncate text-[10px] text-slate-500">{{ prov.nombre }}</span>
                    </button>
                    <p v-if="sinResultadosProveedor[index]" class="p-3 text-[10px] leading-relaxed text-amber-600">
                      Proveedor no registrado. Escriba el nombre y se guardará con el memorándum.
                    </p>
                  </template>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
              <div>
                <label class="form-label">Nombre del proveedor</label>
                <input
                  v-model="form.tabla.proveedores[index].nombre"
                  :disabled="!nombreHabilitado[index]"
                  :placeholder="nombreHabilitado[index] ? 'Nombre del proveedor' : 'Escriba un RIF nuevo para habilitar'"
                  class="form-input"
                />
              </div>
              <div>
                <label class="form-label">Monto (Bs.)</label>
                <input
                  v-model.number="form.tabla.proveedores[index].monto"
                  type="number"
                  step="0.01"
                  placeholder="0.00"
                  class="form-input"
                />
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addProveedor"
            class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-500 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
          >
            + Agregar proveedor
          </button>

          <div class="flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
            <span class="text-[10px] font-bold uppercase tracking-wide text-blue-700">Monto total calculado</span>
            <span class="text-lg font-bold text-blue-800">Bs. {{ calcularTotal }}</span>
          </div>
        </div>
      </section>

      <!-- Motivo -->
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">4</span>
          <div>
            <h3 class="form-card-title">Motivo del memorándum</h3>
            <p class="form-card-subtitle">Describa el motivo que se anexará al cuerpo del documento</p>
          </div>
        </header>
        <div class="space-y-3 p-4">
          <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-[10px] italic leading-relaxed text-slate-600">
            "Tengo a bien dirigirme a usted, en la oportunidad de remitir Punto de Cuenta N° [N°] de fecha [FECHA],
            <span class="font-bold text-blue-600 underline">...MOTIVO...</span>, debidamente aprobado, el cual se especifica a continuación:"
          </div>
          <textarea
            v-model="form.motivo"
            class="form-input resize-none"
            rows="3"
            placeholder="Para sufragar monto para..."
          ></textarea>
        </div>
      </section>

      <!-- Resolución -->
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">5</span>
          <div>
            <h3 class="form-card-title">Resolución (firma)</h3>
            <p class="form-card-subtitle">Texto legal que aparecerá debajo de la firma</p>
          </div>
        </header>
        <div class="p-4">
          <textarea v-model="form.resolucion" class="form-input resize-none" rows="3"></textarea>
        </div>
      </section>
    </div>

    <!-- Assets Tab -->
    <div v-if="activeTab === 'assets'" class="space-y-5">
      <section class="form-card">
        <header class="form-card-header">
          <span class="form-step">🖼️</span>
          <div>
            <h3 class="form-card-title">Imágenes del documento</h3>
            <p class="form-card-subtitle">Personalice el cintillo, la firma y el pie del memorándum</p>
          </div>
        </header>
        <div class="space-y-4 p-4">
          <!-- Cintillo -->
          <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
            <div class="mb-2 flex items-center justify-between gap-2">
              <div>
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Cintillo del encabezado</p>
                <p class="text-[9px] text-slate-400">Por defecto se usa el cintillo del punto de cuenta.</p>
              </div>
              <button
                v-if="form.header_img"
                type="button"
                @click="usarCintilloPorDefecto"
                class="shrink-0 rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-blue-600 ring-1 ring-blue-200 transition-colors hover:bg-blue-50"
              >
                Restablecer cintillo
              </button>
            </div>
            <div class="mb-3 flex h-16 items-center justify-center rounded-lg border border-slate-200 bg-white p-2">
              <img :src="form.header_img || '/cintillo.png'" alt="Cintillo" class="max-h-12 w-auto max-w-full object-contain" />
            </div>
            <input
              type="file"
              accept="image/*"
              @change="handleImage($event, 'header')"
              class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>

          <!-- Firma -->
          <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-600">Firma digital</p>
            <div class="mb-3 flex h-20 items-center justify-center rounded-lg border border-slate-200 bg-white p-2">
              <img v-if="form.firma_img" :src="form.firma_img" alt="Firma" class="max-h-16 w-auto max-w-full object-contain" />
              <span v-else class="text-[10px] italic text-slate-400">Sin firma cargada</span>
            </div>
            <input
              type="file"
              accept="image/*"
              @change="handleImage($event, 'firma')"
              class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>

          <!-- Pie -->
          <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-600">Logo pie de página</p>
            <div class="mb-3 flex h-16 items-center justify-center rounded-lg border border-slate-200 bg-white p-2">
              <img v-if="form.footer_img" :src="form.footer_img" alt="Pie de página" class="max-h-12 w-auto max-w-full object-contain" />
              <span v-else class="text-[10px] italic text-slate-400">Sin imagen de pie</span>
            </div>
            <input
              type="file"
              accept="image/*"
              @change="handleImage($event, 'footer')"
              class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Http } from '@/utils/Http';
import init from '@/utils/Http/init';
import { alerta } from '@/utils/alert';

const props = defineProps({
  form: Object
});

const route = useRoute();
const http = new Http(init);
const loadingSearch = ref(false);

// Búsqueda de proveedores existentes por RIF
const sugerenciasProveedores = ref<Record<number, any[]>>({});
const buscandoProveedor = ref<Record<number, boolean>>({});
const sinResultadosProveedor = ref<Record<number, boolean>>({});
const dropdownAbierto = ref<Record<number, boolean>>({});
const nombreHabilitado = ref<Record<number, boolean>>({});
const proveedorSeleccionado = ref<Record<number, number | null>>({});
const rifTimers: Record<number, any> = {};

const onRifFocus = (index: number) => {
  const rif = String(props.form?.tabla?.proveedores?.[index]?.cedula_rif || '').trim();
  if (rif.length >= 2 && (sugerenciasProveedores.value[index]?.length || sinResultadosProveedor.value[index])) {
    dropdownAbierto.value[index] = true;
  }
};

const onRifInput = (index: number) => {
  const rif = String(props.form?.tabla?.proveedores?.[index]?.cedula_rif || '').trim();

  if (rifTimers[index]) {
    clearTimeout(rifTimers[index]);
  }

  // Si se cambia el RIF después de elegir un proveedor, se limpia el nombre anterior
  if (proveedorSeleccionado.value[index]) {
    proveedorSeleccionado.value[index] = null;
    if (props.form?.tabla?.proveedores?.[index]) {
      props.form.tabla.proveedores[index].nombre = '';
    }
  }

  sinResultadosProveedor.value[index] = false;
  nombreHabilitado.value[index] = false;

  if (rif.length < 2) {
    sugerenciasProveedores.value[index] = [];
    dropdownAbierto.value[index] = false;
    return;
  }

  rifTimers[index] = setTimeout(() => buscarProveedoresPorRif(index, rif), 350);
};

const buscarProveedoresPorRif = async (index: number, rif: string) => {
  dropdownAbierto.value[index] = true;
  buscandoProveedor.value[index] = true;

  try {
    const response = await http.get(`api/oac/memorandum/proveedores/buscar?q=${encodeURIComponent(rif)}`);
    const data = response?.data?.data || [];

    // Ignoramos respuestas obsoletas si el RIF cambió mientras se consultaba
    const rifActual = String(props.form?.tabla?.proveedores?.[index]?.cedula_rif || '').trim();
    if (rifActual !== rif) return;

    sugerenciasProveedores.value[index] = data;
    sinResultadosProveedor.value[index] = data.length === 0;
    // El nombre solo se habilita si el RIF no existe (se va a crear un proveedor nuevo)
    nombreHabilitado.value[index] = data.length === 0;
  } catch (error) {
    sugerenciasProveedores.value[index] = [];
    sinResultadosProveedor.value[index] = true;
    nombreHabilitado.value[index] = true;
  } finally {
    buscandoProveedor.value[index] = false;
  }
};

const seleccionarProveedor = (index: number, prov: any) => {
  if (!props.form?.tabla?.proveedores?.[index]) return;

  props.form.tabla.proveedores[index].cedula_rif = prov.cedula_rif;
  props.form.tabla.proveedores[index].nombre = prov.nombre;
  proveedorSeleccionado.value[index] = prov.id;
  nombreHabilitado.value[index] = false;
  dropdownAbierto.value[index] = false;
};

const closeProveedorSugerencias = (index: number) => {
  setTimeout(() => {
    dropdownAbierto.value[index] = false;
  }, 150);
};

const addProveedor = () => {
  if (!props.form.tabla.proveedores) {
    props.form.tabla.proveedores = [];
  }
  props.form.tabla.proveedores.push({ nombre: '', monto: 0, cedula_rif: '' });
};

const removeProveedor = (index) => {
  if (props.form.tabla.proveedores && props.form.tabla.proveedores.length > 1) {
    props.form.tabla.proveedores.splice(index, 1);
  }
};

const calcularTotal = computed(() => {
  const total = (props.form.tabla.proveedores || []).reduce((acc, curr) => acc + (parseFloat(curr.monto) || 0), 0);
  const fixedTotal = total.toFixed(2);

  if (props.form) {
    if (props.form.tabla) {
      props.form.tabla.total = fixedTotal;
      props.form.tabla.monto = fixedTotal;
    }
    props.form.monto = fixedTotal;
  }
  return fixedTotal;
});

const buscarPuntoCuenta = async (numero) => {
  if (!numero) return;
  loadingSearch.value = true;
  try {
    const response = await http.get(`api/oac/memorandum/buscar-punto-cuenta/${numero}`);

    if (response.data && response.data.success) {
      const pc = response.data.data;
      props.form.punto_cuenta_id = pc.id;
      props.form.tabla.pto_cta = pc.numero_punto;
      props.form.tabla.fecha = pc.fecha;
      props.form.tabla.solicitante = pc.solicitante;
      props.form.tabla.cedula = pc.cedula;
      props.form.tabla.monto = pc.monto;

      if (pc.proveedores && pc.proveedores.length > 0) {
        props.form.tabla.proveedores = pc.proveedores.map(p => ({ nombre: p.nombre, monto: parseFloat(p.monto) || 0, cedula_rif: p.cedula_rif || '' }));
      } else if (pc.monto || pc.proveedor) {
        props.form.tabla.proveedores = [{ nombre: pc.proveedor || '', monto: parseFloat(pc.monto) || 0, cedula_rif: '' }];
      } else {
        props.form.tabla.proveedores = [{ nombre: '', monto: 0, cedula_rif: '' }];
      }

      if (pc.asunto) {
        props.form.asunto = `Remisión de Punto de Cuenta N° ${pc.numero_punto}.`;
      }

      props.form.tabla.pto_cta = pc.numero_punto;

      // Si ya existe un memo, lo cargamos para editar
      if (pc.existing_memo) {
        Object.assign(props.form, {
          ...pc.existing_memo,
          punto_cuenta_id: pc.id,
          header_img: pc.existing_memo.header_img,
          footer_img: pc.existing_memo.footer_img,
          firma_img: pc.existing_memo.firma_img,
          de_nombre: pc.existing_memo.de,
          para_nombre: pc.existing_memo.para,
          motivo: pc.existing_memo.cuerpo,
          tabla: {
            ...props.form.tabla,
            pto_cta: pc.numero_punto,
            fecha: pc.existing_memo.fecha,
            monto: pc.existing_memo.monto,
            total: pc.existing_memo.monto,
            proveedores: pc.existing_memo.proveedores.map(p => ({ nombre: p.nombre, monto: parseFloat(p.monto) || 0, cedula_rif: p.cedula_rif })),
          }
        });
      }
    }
  } catch (error: any) {
    props.form.punto_cuenta_id = null;
    const message = error.response?.data?.message || "Error al buscar el punto de cuenta";
    alerta("Atención", message, "info");
  } finally {
    loadingSearch.value = false;
  }
};

watch(() => props.form.tabla.pto_cta, (newVal) => {
  // Limpiamos el ID si el campo se vacía
  if (!newVal) {
    props.form.punto_cuenta_id = null;
  }
}, { immediate: true });

watch(() => props.form.tabla.monto, (newVal) => {
  if (newVal && String(newVal).includes(',')) {
    alerta("Formato inválido", "El monto no debe contener comas (,). Use punto (.) para los decimales.", "warning");
  }
  props.form.tabla.total = newVal;
});

onMounted(() => {
  if (!props.form.tabla.proveedores || props.form.tabla.proveedores.length === 0) {
    props.form.tabla.proveedores = [{ nombre: '', monto: 0, cedula_rif: '' }];
  }

  if (route.query.numero) {
    props.form.tabla.pto_cta = route.query.numero;
    buscarPuntoCuenta(route.query.numero);
  }
});

const activeTab = ref('info');

const personasPara = [
  { nombre: 'TAVIANA ELAINE ALQUINZONES FERNÁNDEZ', cargo: 'Directora General (E) de la Oficina de Gestión Administrativa' },
];

const personasDe = [
  { nombre: 'OLIVER EZEQUIEL RIVAS PAREDES', cargo: 'Director General (E) de la Oficina de Atención al Ciudadano' },

];

// Se asume que el componente padre maneja el guardado.
// Esta función intercepta los errores de respuesta para usar la utilidad alerta.

const handleSaveError = (error: any) => {
  const message = error.response?.data?.message || "Error al guardar el memorándum";
  alerta("Atención", message, "error");
};

const updateCargo = (type) => {
  if (type === 'para') {
    const selected = personasPara.find(p => p.nombre === props.form.para_nombre);
    if (selected) props.form.para_cargo = selected.cargo;
  } else {
    const selected = personasDe.find(p => p.nombre === props.form.de_nombre);
    if (selected) props.form.de_cargo = selected.cargo;
  }
};

const handleImage = (event, type) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const base64String = e.target.result;
      if (type === 'header') props.form.header_img = base64String;
      if (type === 'footer') props.form.footer_img = base64String;
      if (type === 'firma') props.form.firma_img = base64String;
    };
    reader.readAsDataURL(file);
  }
};

const usarCintilloPorDefecto = () => {
  props.form.header_img = null;
};
</script>

<style scoped>
.form-card {
  @apply rounded-xl border border-slate-200 bg-white shadow-sm;
}

.form-card-header {
  @apply flex items-start gap-3 rounded-t-xl border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-4 py-3;
}

.form-step {
  @apply flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-[11px] font-bold text-white shadow-sm;
}

.form-card-title {
  @apply text-xs font-bold uppercase tracking-wide text-slate-700;
}

.form-card-subtitle {
  @apply mt-0.5 text-[10px] text-slate-400;
}

.form-label {
  @apply mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500;
}

.form-input {
  @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400;
}
</style>
