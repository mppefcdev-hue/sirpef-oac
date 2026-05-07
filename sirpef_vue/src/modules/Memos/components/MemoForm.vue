<template>
  <div class="space-y-4">
    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200">
      <button 
        @click="activeTab = 'info'"
        :class="['px-4 py-2 text-xs font-bold uppercase transition-colors', activeTab === 'info' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700']"
      >
        Información
      </button>
      <button 
        @click="activeTab = 'assets'"
        :class="['px-4 py-2 text-xs font-bold uppercase transition-colors', activeTab === 'assets' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700']"
      >
        Recursos Visuales
      </button>
    </div>

    <!-- Info Tab -->
    <div v-if="activeTab === 'info'" class="space-y-4">
      <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
      <h3 class="text-sm font-bold text-blue-800 border-b pb-1">DESTINATARIO Y REMITENTE</h3>
      
      <div class="space-y-3">
        <div class="p-2 bg-white rounded border">
          <label class="block text-[10px] font-bold text-gray-500 uppercase">PARA:</label>
          <select v-model="form.para_nombre" @change="updateCargo('para')" class="w-full text-xs p-1 border-b bg-transparent outline-none">
            <option v-for="opt in personasPara" :key="opt.nombre" :value="opt.nombre">{{ opt.nombre }}</option>
          </select>
          <div class="mt-2 p-1 bg-gray-50 text-[10px] text-gray-600 italic border rounded">
            {{ form.para_cargo }}
          </div>
        </div>

        <div class="p-2 bg-white rounded border">
          <label class="block text-[10px] font-bold text-gray-500 uppercase">DE:</label>
          <select v-model="form.de_nombre" @change="updateCargo('de')" class="w-full text-xs p-1 border-b bg-transparent outline-none">
            <option v-for="opt in personasDe" :key="opt.nombre" :value="opt.nombre">{{ opt.nombre }}</option>
          </select>
          <div class="mt-2 p-1 bg-gray-50 text-[10px] text-gray-600 italic border rounded">
            {{ form.de_cargo }}
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
      <h3 class="text-sm font-bold text-blue-800 border-b pb-1">TABLA DE DATOS</h3>
      <div class="grid grid-cols-2 gap-3">
        <div class="flex flex-col">
          <label class="text-[10px] font-bold text-gray-500">N° PUNTO CUENTA</label>
          <div class="flex space-x-1">
            <input v-model="form.tabla.pto_cta" @keyup.enter="buscarPuntoCuenta(form.tabla.pto_cta)" placeholder="000/2026" class="flex-1 border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
            <button @click="buscarPuntoCuenta(form.tabla.pto_cta)" class="bg-blue-600 hover:bg-blue-700 text-white px-2 rounded text-xs transition-colors">
              <span v-if="!loadingSearch">🔍</span>
              <span v-else class="animate-spin inline-block">⏳</span>
            </button>
          </div>
          <p v-if="form.punto_cuenta_id" class="text-[9px] text-green-600 font-bold mt-1">✓ Punto de Cuenta vinculado (ID: {{ form.punto_cuenta_id }})</p>
          <p v-else-if="form.tabla.pto_cta && form.tabla.pto_cta.length >= 3" class="text-[9px] text-red-500 mt-1">⚠ No vinculado</p>
        </div>
        <div class="flex flex-col">
          <label class="text-[10px] font-bold text-gray-500">FECHA PUNTO CUENTA</label>
          <input v-model="form.tabla.fecha" type="date" class="border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
        </div>
        <div class="flex flex-col">
          <label class="text-[10px] font-bold text-gray-500">SOLICITANTE</label>
          <input v-model="form.tabla.solicitante" class="border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
        </div>
        <div class="flex flex-col">
          <label class="text-[10px] font-bold text-gray-500">CÉDULA</label>
          <input v-model="form.tabla.cedula" class="border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
        </div>
        <div class="col-span-2 space-y-2">
          <div class="flex justify-between items-center">
            <label class="text-[10px] font-bold text-gray-500 uppercase">Proveedores y Montos</label>
            <button @click="addProveedor" class="text-[10px] bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">+ Agregar</button>
          </div>
          <div v-for="(item, index) in form.tabla.proveedores" :key="index" class="flex gap-2 items-end">
            <div class="flex-1">
              <label class="text-[9px] font-bold text-gray-400 uppercase">Proveedor</label>
              <input v-model="item.nombre" placeholder="Nombre del proveedor" class="w-full border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
            </div>
            <div class="w-32">
              <label class="text-[9px] font-bold text-gray-400 uppercase">Monto (Bs.)</label>
              <input v-model="item.monto" type="number" step="0.01" placeholder="0.00" class="w-full border p-2 rounded text-xs focus:ring-1 focus:ring-blue-400 outline-none" />
            </div>
            <button @click="removeProveedor(index)" v-if="form.tabla.proveedores?.length > 1" class="text-red-500 p-2 mb-0.5">✕</button>
          </div>
        </div>
      </div>
      <div class="flex flex-col mt-2">
        <label class="text-[10px] font-bold text-gray-500">MONTO TOTAL CALCULADO (BS.)</label>
        <div class="border p-2 rounded text-xs font-bold bg-blue-50 text-blue-800">
          {{ calcularTotal }}
        </div>
      </div>
    </div>

    <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
      <h3 class="text-sm font-bold text-blue-800 border-b pb-1">MOTIVO DEL MEMORÁNDUM</h3>
      <div class="space-y-2">
        <label class="block text-[10px] font-bold text-gray-500 uppercase">MOTIVO (Editable):</label>
        <div class="text-[10px] text-gray-600 bg-white p-2 rounded border mb-1 italic">
          "Tengo a bien dirigirme a usted, en la oportunidad de remitir Punto de Cuenta N° [N°] de fecha [FECHA], <span class="text-blue-600 font-bold underline">...MOTIVO...</span>, debidamente aprobado, el cual se especifica a continuación:"
        </div>
        <textarea v-model="form.motivo" class="w-full text-xs p-2 border rounded focus:ring-1 focus:ring-blue-400 outline-none resize-none" rows="3" placeholder="para sufragar monto para..."></textarea>
      </div>
    </div>

      <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
        <label class="block text-[10px] font-bold text-gray-500 uppercase">RESOLUCIÓN (Firma):</label>
        <textarea v-model="form.resolucion" class="w-full text-[10px] p-2 mt-1 border rounded focus:ring-1 focus:ring-blue-400 outline-none resize-none" rows="3"></textarea>
      </div>
    </div>

    <!-- Assets Tab -->
    <div v-if="activeTab === 'assets'" class="space-y-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
      <h3 class="text-sm font-bold text-blue-800 border-b pb-1 uppercase">Imágenes y Recursos</h3>
      
      <div class="space-y-4">
        <div class="p-3 bg-white rounded border">
          <label class="block text-[10px] font-bold text-gray-700 uppercase mb-2">Logo Encabezado</label>
          <input type="file" @change="handleImage($event, 'header')" class="block w-full text-xs text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700" />
          <p class="text-[9px] text-gray-400 mt-1">Sustituye la imagen superior del documento.</p>
        </div>

        <div class="p-3 bg-white rounded border">
          <label class="block text-[10px] font-bold text-gray-700 uppercase mb-2">Firma Digital</label>
          <input type="file" @change="handleImage($event, 'firma')" class="block w-full text-xs text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700" />
          <p class="text-[9px] text-gray-400 mt-1">Se mostrará sobre el nombre del remitente.</p>
        </div>

        <div class="p-3 bg-white rounded border">
          <label class="block text-[10px] font-bold text-gray-700 uppercase mb-2">Logo Pie de Página</label>
          <input type="file" @change="handleImage($event, 'footer')" class="block w-full text-xs text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700" />
          <p class="text-[9px] text-gray-400 mt-1">Sustituye la imagen inferior del documento.</p>
        </div>
      </div>
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

const addProveedor = () => {
  if (!props.form.tabla.proveedores) {
    props.form.tabla.proveedores = [];
  }
  props.form.tabla.proveedores.push({ nombre: '', monto: 0 });
};

const removeProveedor = (index) => {
  props.form.tabla.proveedores.splice(index, 1);
};

const calcularTotal = computed(() => {
  const total = (props.form.tabla.proveedores || []).reduce((acc, curr) => acc + (parseFloat(curr.monto) || 0), 0);
  const fixedTotal = total.toFixed(2);
  props.form.tabla.total = fixedTotal;
  props.form.monto = fixedTotal;
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
        props.form.tabla.proveedores = pc.proveedores.map(p => ({ nombre: p.nombre, monto: parseFloat(p.monto) || 0 }));
      } else if (pc.monto || pc.proveedor) {
        props.form.tabla.proveedores = [{ nombre: pc.proveedor || '', monto: parseFloat(pc.monto) || 0 }];
      } else {
        props.form.tabla.proveedores = [{ nombre: '', monto: 0 }];
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
            proveedor: pc.existing_memo.proveedor,
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
  // Actualizamos el código del memorándum basado en el punto de cuenta
  if (newVal) {
    props.form.codigo = `OAC-M N°${newVal}`;
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
    props.form.tabla.proveedores = [{ nombre: '', monto: 0 }];
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
</script>
