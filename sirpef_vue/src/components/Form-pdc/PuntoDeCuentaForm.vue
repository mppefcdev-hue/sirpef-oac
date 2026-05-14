<script setup lang="ts">
import { reactive, ref, onMounted, computed } from 'vue';
import { createPDC, getPDC, updatePDC } from '@/modules/OAC/services';
import { alerta } from '@/utils/alert';
import { useRoute, useRouter } from 'vue-router';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { marked } from 'marked';
import TurndownService from 'turndown';
import { useToast } from 'vue-toast-notification';

// --- Interfaces (sin cambios) ---
interface Firma {
  nombre: string;
  cargo: string;
  resolucion1: string;
  resolucion2?: string;
}
interface PuntoDeCuentaData {
  puntoNro: string;
  nroPaginas: string;
  fechaDocumento: string;
  presentadoA_nombre: string;
  presentadoA_cargo: string;
  presentadoPor_nombre: string;
  presentadoPor_cargo: string;
  asunto: string;
  decision: 'N/A' | 'APROBADO' | 'NEGADO' | 'VISTO' | 'DIFERIDO' | 'OTRO' | '';
  otrasInstrucciones: string;
  anexos: 'Si' | 'No' | '';
  firmaIzquierda: Firma;
  firmaDerecha: Firma;
  exposicionDeMotivosCompleta: string;
  propuestaCompleta: string;
}

// --- Lógica del Componente ---
const createInitialState = (): PuntoDeCuentaData => ({
  puntoNro: '',
  nroPaginas: '1/1',
  fechaDocumento: new Date().toISOString().slice(0, 10),
  asunto: '',
  exposicionDeMotivosCompleta: '',
  propuestaCompleta: '',
  decision: '',
  otrasInstrucciones: '',
  anexos: 'Si',
  presentadoA_nombre: 'ANMY IVONETT PÉREZ GONZÁLEZ',
  presentadoA_cargo: 'Directora General de la Consultoría Jurídica',
  presentadoPor_nombre: 'OLIVER EZEQUIEL RIVAS PAREDES',
  presentadoPor_cargo: 'Director General (E) de la Oficina de Atención al Ciudadano',
  firmaIzquierda: {
    nombre: 'OLIVER EZEQUIEL RIVAS PAREDES',
    cargo: 'Director General (E) de la Oficina de Atención al Ciudadano',
    resolucion1: 'Resolución No. 006-2024, publicada en la Gaceta Oficial de la República Bolivariana de Venezuela No. 42.958, de fecha 06 de septiembre de 2024.',
    resolucion2: ''
  },
  firmaDerecha: {
    nombre: 'ANMY IVONETT PÉREZ GONZÁLEZ',
    cargo: 'Directora General de la Consultora Jurídica',
    resolucion1: '',
    resolucion2: 'Resolución N° 004-2024 de fecha 02 de septiembre del 2024, publicada en Gaceta Oficial de la R.B.V N° 42.955 de fecha 03 de septiembre del 2024 y actuando según delegación de firma en Resolución N° 010-2024 publicada en la Gaceta Oficial de la R.B.V N° 42.975 de fecha 30/09/2024.',
  },
});

const turndownService = new TurndownService();
turndownService.addRule('strong', { filter: ['strong', 'b'], replacement: (content) => `**${content}**` });

const form = reactive<PuntoDeCuentaData>(createInitialState());
const $toast = useToast(); 

const asuntoHtml = ref('');
const exposicionHtml = ref('');
const propuestaHtml = ref('');

// <<<--- 1. PROPIEDAD COMPUTADA PARA FORMATEAR LA FECHA --- >>>
const fechaFormateadaParaVista = computed(() => {
  if (!form.fechaDocumento) return 'dd/mm/aaaa';
  const [year, month, day] = form.fechaDocumento.split('-');
  return `${day}/${month}/${year}`;
});


const updateMarkdown = (htmlContent: string, key: 'asunto' | 'exposicionDeMotivosCompleta' | 'propuestaCompleta') => {
  form[key] = turndownService.turndown(htmlContent);
};

const updateEditorContent = () => {
    asuntoHtml.value = marked(form.asunto) as string;
    exposicionHtml.value = marked(form.exposicionDeMotivosCompleta) as string;
    propuestaHtml.value = marked(form.propuestaCompleta) as string;
}

const route = useRoute();
const router = useRouter();
const id = route.params.id as string;
const pdc_id = route.params.pdc_id as string;

const getPDCToEdit = async () => {
  if (!pdc_id) return;
  try {
    const response = await getPDC(pdc_id);
    const punto = response.data;
    
    form.puntoNro = punto.numero_punto || '';
    form.fechaDocumento = punto.fecha;
    form.asunto = punto.asunto || '';
    form.exposicionDeMotivosCompleta = punto.exposicion_motivos || '';
    form.propuestaCompleta = punto.propuesta || '';
    form.decision = punto.decision || '';
    form.otrasInstrucciones = punto.otras_instrucciones || '';
    form.anexos = punto.anexos ? 'Si' : 'No';
    form.presentadoA_nombre = punto.presentado_a || '';
    form.presentadoA_cargo = punto.cargo_a || '';
    form.presentadoPor_nombre = punto.presentado_por || '';
    form.presentadoPor_cargo = punto.cargo_por || '';
    form.firmaIzquierda.nombre = punto.presentado_por || '';
    form.firmaIzquierda.cargo = punto.cargo_por || '';
    form.firmaIzquierda.resolucion1 = punto.resolucion_1 || '';
    form.firmaDerecha.nombre = punto.presentado_a || '';
    form.firmaDerecha.cargo = punto.cargo_a || '';
    form.firmaDerecha.resolucion2 = punto.resolucion_2 || '';

    updateEditorContent();
  } catch (error) {
    console.error("Error al cargar el punto de cuenta:", error);
    alerta('Error', 'No se pudo cargar la información del punto de cuenta.', 'error');
  }
};

onMounted(() => {
  if (pdc_id) {
    getPDCToEdit();
  } else {
    updateEditorContent();
  }
});

const sendInfo = async () => {
  try {
    const data = {
      anexos: form.anexos === 'Si',
      presentado_a: form.presentadoA_nombre,
      presentado_por: form.presentadoPor_nombre,
      fecha: form.fechaDocumento,
      numero_punto: form.puntoNro,
      asunto: form.asunto,
      exposicion_motivos: form.exposicionDeMotivosCompleta,
      propuesta: form.propuestaCompleta,
      cargo_a: form.presentadoA_cargo,
      cargo_por: form.presentadoPor_cargo,
      resolucion_1: form.firmaIzquierda.resolucion1,
      resolucion_2: form.firmaDerecha.resolucion2,
      otras_instrucciones: form.otrasInstrucciones,
      decision: form.decision === 'N/A' ? null : form.decision,
      register_id: id,
    };
    const response = !pdc_id ? await createPDC(id, data) : await updatePDC(pdc_id, data);
    alerta('Completado', response.msg, 'success');
    router.push('/cases');
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response.data.errors;
      let errorMessages = '<ul>';
      for (const field in validationErrors) {
        errorMessages += `<li>${validationErrors[field][0]}</li>`;
      }
      errorMessages += '</ul>';

      $toast.error(`Por favor, corrija los siguientes errores:<br>${errorMessages}`, {
        position: 'top-right',
        duration: 8000,
        dismissible: true,
      });
    } else {
      const { response } = error;
      alerta("Error", response?.data?.data?.message || 'Ocurrió un error inesperado.', "info");
    }
  }
};
</script>

<template>
  <div class="container mx-auto p-4 md:p-6 lg:p-8 bg-slate-50 dark:bg-slate-900">
    <h1 class="text-3xl font-bold mb-8 text-center text-sky-700 dark:text-sky-400">
      Formulario Punto de Cuenta
    </h1>

    <form @submit.prevent="sendInfo" class="space-y-8">
      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Datos Generales
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
          <div>
            <label for="puntoNro" class="block text-sm font-medium text-gray-700 dark:text-gray-300">**PUNTO N°**</label>
            <input type="text" id="puntoNro" v-model="form.puntoNro" class="mt-1 inputForm" />
          </div>
          <div>
            <!-- <<<--- 2. ACTUALIZACIÓN DEL TEMPLATE --- >>> -->
            <label for="fechaDocumento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Fecha Documento
          
            </label>
            <input type="date" id="fechaDocumento" v-model="form.fechaDocumento" class="mt-1 inputForm" />
          </div>
        </div>
      </div>
      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Presentado
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label for="presentadoA_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">**A (Nombre)**</label>
            <input type="text" id="presentadoA_nombre" v-model="form.presentadoA_nombre" class="mt-1 inputForm" />
            <label for="presentadoA_cargo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Cargo (A)</label>
            <input type="text" id="presentadoA_cargo" v-model="form.presentadoA_cargo" class="mt-1 inputForm" />
          </div>
          <div>
            <label for="presentadoPor_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">**Por (Nombre)**</label>
            <input type="text" id="presentadoPor_nombre" v-model="form.presentadoPor_nombre" class="mt-1 inputForm" />
            <label for="presentadoPor_cargo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Cargo (Por)</label>
            <input type="text" id="presentadoPor_cargo" v-model="form.presentadoPor_cargo" class="mt-1 inputForm" />
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Asunto
        </h2>
        <QuillEditor
            theme="snow"
            :toolbar="[['bold']]"
            contentType="html"
            v-model:content="asuntoHtml"
            @update:content="updateMarkdown(asuntoHtml, 'asunto')"
        />
      </div>

      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Exposición de Motivos
        </h2>
        <QuillEditor
            theme="snow"
            :toolbar="[['bold']]"
            contentType="html"
            v-model:content="exposicionHtml"
            @update:content="updateMarkdown(exposicionHtml, 'exposicionDeMotivosCompleta')"
            style="min-height: 300px;"
        />
      </div>

      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Propuesta
        </h2>
        <QuillEditor
            theme="snow"
            :toolbar="[['bold']]"
            contentType="html"
            v-model:content="propuestaHtml"
            @update:content="updateMarkdown(propuestaHtml, 'propuestaCompleta')"
            style="min-height: 300px;"
        />
      </div>
      
      <div class="bg-white hidden dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Decisión e Instrucciones
        </h2>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">**DECISIÓN**</label>
          <div class="flex flex-wrap gap-x-6 gap-y-3">
            <div v-for="option in ['N/A', 'APROBADO', 'NEGADO', 'VISTO', 'DIFERIDO', 'OTRO']" :key="option" class="flex items-center">
              <input type="radio" :id="`decision-${option}`" :value="option" v-model="form.decision" name="decisionRadio" class="h-4 w-4 text-sky-600 border-gray-300 focus:ring-sky-500" />
              <label :for="`decision-${option}`" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">{{ option }}</label>
            </div>
          </div>
        </div>
        <div class="mt-6">
          <label for="otrasInstrucciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Otras instrucciones:</label>
          <textarea id="otrasInstrucciones" v-model="form.otrasInstrucciones" rows="2" class="mt-1 inputForm resize-y"></textarea>
        </div>
        <div class="mt-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anexos :</label>
          <div class="flex gap-x-6 gap-y-3">
            <div class="flex items-center">
              <input type="radio" id="anexosSi" value="Si" v-model="form.anexos" name="anexosRadio" class="h-4 w-4 text-sky-600 border-gray-300 focus:ring-sky-500" />
              <label for="anexosSi" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">Si</label>
            </div>
            <div class="flex items-center">
              <input type="radio" id="anexosNo" value="No" v-model="form.anexos" name="anexosRadio" class="h-4 w-4 text-sky-600 border-gray-300 focus:ring-sky-500" />
              <label for="anexosNo" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">No</label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
          Firmantes
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <div>
            <h3 class="font-medium mb-2 text-center text-gray-800 dark:text-gray-200">Firma Izquierda</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">**Nombre**</label>
                <input type="text" v-model="form.firmaIzquierda.nombre" class="mt-1 inputForm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo</label>
                <input type="text" v-model="form.firmaIzquierda.cargo" class="mt-1 inputForm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolución 1</label>
                <textarea v-model="form.firmaIzquierda.resolucion1" rows="4" class="mt-1 inputForm resize-y"></textarea>
              </div>
            </div>
          </div>
          <div>
            <h3 class="font-medium mb-2 text-center text-gray-800 dark:text-gray-200">Firma Derecha</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">**Nombre**</label>
                <input type="text" v-model="form.firmaDerecha.nombre" class="mt-1 inputForm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo</label>
                <input type="text" v-model="form.firmaDerecha.cargo" class="mt-1 inputForm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolución 2</label>
                <textarea v-model="form.firmaDerecha.resolucion2" rows="3" class="mt-1 inputForm resize-y"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-10 flex justify-end">
        <button type="submit" class="btn btn-primary px-8 py-3 text-base">
          {{ pdc_id ? 'Editar' : 'Crear' }} punto de cuenta
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.inputForm {
  @apply block w-full rounded-md border-gray-300 shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500 sm:text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-gray-200 dark:placeholder-gray-400;
}
.container {
  max-width: 1024px;
}
</style>