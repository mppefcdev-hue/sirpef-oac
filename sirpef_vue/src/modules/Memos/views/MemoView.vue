<template>
  <div class="flex flex-col h-screen bg-gray-50 overflow-hidden">
    <div class="p-4 bg-white border-b shadow-sm flex justify-between items-center no-print">
      <div class="flex items-center space-x-6">
        <h1 class="text-xl font-bold text-gray-800">Generador de Memorándum</h1>
        <nav class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
          <button @click="viewMode = 'editor'" :class="['px-3 py-1 text-xs font-bold rounded', viewMode === 'editor' ? 'bg-white shadow text-blue-600' : 'text-gray-500']">EDITOR</button>
          <button @click="viewMode = 'history'" :class="['px-3 py-1 text-xs font-bold rounded', viewMode === 'history' ? 'bg-white shadow text-blue-600' : 'text-gray-500']">HISTORIAL</button>
        </nav>
      </div>
      <div class="flex space-x-2">
        <button v-if="viewMode === 'editor'" @click="saveMemo" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center transition-colors text-sm font-bold">
          Guardar Memo
        </button>
        <button @click="printMemo" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center transition-colors text-sm font-bold">
          Imprimir / PDF
        </button>
      </div>
    </div>
    
    <div class="flex flex-1 overflow-hidden">
      <!-- Editor View -->
      <template v-if="viewMode === 'editor'">
        <!-- Form Side -->
        <div class="w-1/3 overflow-y-auto border-r bg-white p-6 no-print">
          <MemoForm :form="memoData" />
        </div>

        <!-- Preview Side -->
        <div class="w-2/3 overflow-y-auto bg-gray-200 p-8 flex justify-center print:bg-white print:p-0">
          <div id="memo-printable">
            <MemoPreview :data="memoData" />
          </div>
        </div>
      </template>

      <!-- History View -->
      <div v-else class="flex-1 overflow-y-auto p-8 bg-gray-50">
        <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-sm border">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="p-4 font-bold text-gray-600">Pto/Cta</th>
                <th class="p-4 font-bold text-gray-600">Fecha</th>
                <th class="p-4 font-bold text-gray-600">Solicitante</th>
                <th class="p-4 font-bold text-gray-600">Monto</th>
                <th class="p-4 font-bold text-gray-600 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="(memo, index) in history" :key="index" class="hover:bg-gray-50 transition-colors">
                <td class="p-4 font-medium">{{ memo.tabla.pto_cta }}</td>
                <td class="p-4">{{ memo.tabla.fecha }}</td>
                <td class="p-4">{{ memo.tabla.solicitante }}</td>
                <td class="p-4 font-bold text-blue-700">{{ memo.tabla.total || memo.tabla.monto }}</td>
                <td class="p-4 text-right space-x-2">
                  <button @click="loadMemo(memo)" class="text-blue-600 hover:underline font-bold">Cargar</button>
                  <button @click="deleteFromHistory(index)" class="text-red-600 hover:underline font-bold">Eliminar</button>
                </td>
              </tr>
              <tr v-if="history.length === 0">
                <td colspan="5" class="p-10 text-center text-gray-400 italic">No hay memorándums guardados.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MemoForm from '../components/MemoForm.vue';
import MemoPreview from '../components/MemoPreview.vue';
import { alerta } from '@/utils/alert';
import { Http } from '@/utils/Http';
import init from '@/utils/Http/init';

const http = new Http(init);
const router = useRouter();

const viewMode = ref('editor');
const history = ref([]);

const memoData = ref({
  id: null,
  header_img: null,
  footer_img: null,
  firma_img: null,
  punto_cuenta_id: null,
  codigo: 'OAC-M N°000/2026',
  fecha: '01/01/2026',
  para_nombre: 'TAVIANA ELAINE ALQUINZONES FERNÁNDEZ',
  para_cargo: 'Directora General (E) de la Oficina de Gestión Administrativa',
  de_nombre: 'OLIVER EZEQUIEL RIVAS PAREDES',
  de_cargo: 'Director General (E) de la Oficina de Atención al Ciudadano',
  asunto: 'Remisión de Punto de Cuenta N° 000/2026.',
  motivo: 'para sufragar monto para intervención quirúrgica (cesárea)',
  tabla: {
    pto_cta: '000/2026',
    fecha: '2026-01-01',
    solicitante: '',
    ci: '',
    monto: '',
    proveedores: [{ nombre: '', monto: 0 }],
    total: ''
  },
  cuerpo_final: 'Agradeciendo la receptividad que tenga a bien dispensar a la presente, en girar la instrucción correspondiente a fin de realizar el trámite de orden de pago, quedo de usted.',
  resolucion: 'Resolución N° 006-2024 publicada en la Gaceta Oficial de la República Bolivariana de Venezuela N° 42.958 ambos de fecha 06 de septiembre de 2024',
  iniciales: 'AP/NM/YB/da'
});

const printMemo = () => {
  window.print();
};

const saveMemo = async () => {
  // Validaciones
  if (!memoData.value.punto_cuenta_id) {
    alerta('Atención', 'Debe vincular un Punto de Cuenta válido existente en el sistema.', 'info');
    return;
  }
  if (!memoData.value.asunto || !memoData.value.motivo) {
    alerta('Atención', 'El asunto y el motivo son obligatorios.', 'info');
    return;
  }

  try {
    const payload = {
      punto_cuenta_id: memoData.value.punto_cuenta_id,
      codigo: memoData.value.tabla.pto_cta,
      de: memoData.value.de_nombre,
      para: memoData.value.para_nombre,
      asunto: memoData.value.asunto,
      fecha: memoData.value.tabla.fecha,
      cuerpo: memoData.value.motivo,
      monto: memoData.value.tabla.monto,
      proveedores: memoData.value.tabla.proveedores.map(p => ({
        nombre: p.nombre,
        monto: p.monto,
        cedula_rif: p.cedula_rif
      })),
      header_img: memoData.value.header_img || null,
      footer_img: memoData.value.footer_img || null,
      firma_img: memoData.value.firma_img || null
    };

    const response = memoData.value.id 
      ? await http.put(`api/oac/memorandum/${memoData.value.id}`, payload)
      : await http.post('api/oac/memorandum', payload);
    
    const status = response?.status;
    const success = response?.data?.success;

    if (status === 200 || status === 201 || success === true) {
      await alerta('Éxito', 'Memorándum guardado exitosamente', 'success');
      router.push('/oac/memos');
    } else {
      const errorMsg = response?.data?.message || 'Error en la respuesta del servidor';
      alerta('Atención', errorMsg, 'error');
    }
  } catch (error: any) {
    const message = error.response?.data?.message || error.message || 'Error al procesar la solicitud';
    alerta('Atención', message, 'error');
  }
};

const loadMemo = (memo) => {
  memoData.value = JSON.parse(JSON.stringify(memo));
  viewMode.value = 'editor';
};

const deleteFromHistory = (index) => {
  alerta('Confirmar', '¿Está seguro de eliminar este registro?', 'question').then((result) => {
    if (result.isConfirmed) {
      history.value.splice(index, 1);
    }
  });
};

const fetchHistory = async () => {
  try {
    const response = await http.get('api/oac/memorandum');
    if (response.data && response.data.success) {
      history.value = response.data.data;
    }
  } catch (error) {
    console.error("Error al cargar el historial:", error);
  }
};

onMounted(() => {
  fetchHistory();
  const savedMemo = localStorage.getItem('editing_memo');
  if (savedMemo) {
    const parsed = JSON.parse(savedMemo);
    memoData.value = {
      ...memoData.value,
      ...parsed,
      // Mapear campos que vienen en 'tabla' si es necesario
      de_nombre: parsed.de_nombre || parsed.de,
      para_nombre: parsed.para_nombre || parsed.para,
      motivo: parsed.motivo || parsed.cuerpo
    };
    localStorage.removeItem('editing_memo');
  }
});
</script>

<style>
@media print {
  /* Hide non-printable elements */
  .no-print, header, nav, .navbar {
    display: none !important;
  }

  /* Reset layout for print */
  body, html {
    background: white !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Expand preview to full width */
  .w-2\/3, #memo-printable, main {
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    overflow: visible !important;
    display: block !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
  }

  /* Remove scrollbars and gray backgrounds */
  .flex-1, .overflow-y-auto {
    overflow: visible !important;
    display: block !important;
  }

  @page {
    size: letter;
    margin: 0;
  }

  /* Force shadows to disappear and container to fill page */
  .memo-paper {
    box-shadow: none !important;
    margin: 0 !important;
    width: 100% !important;
    border: none !important;
  }
}
</style>
