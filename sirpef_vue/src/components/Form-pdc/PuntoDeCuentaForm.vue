<script setup lang="ts">
import { reactive, ref, onMounted, computed } from 'vue';
import { createPDC, getPDC, updatePDC } from '@/modules/FeDeVida/services';
import { alerta } from '@/utils/alert';
import { useRoute, useRouter } from 'vue-router';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { marked } from 'marked';
import TurndownService from 'turndown';
import { useToast } from 'vue-toast-notification';

// --- Interfaces ---
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

// --- Control de Estatus para Edición e Impresión ---
const estatusPDC = ref(true);

// --- Perfiles predefinidos ---
const perfilesPresentadoA = [
  {
    id: 1,
    etiqueta: 'Consultoría Jurídica (Anmy Pérez)',
    nombre: 'ANMY IVONETT PÉREZ GONZÁLEZ',
    cargo: 'Directora General (E) de la Consultoría Jurídica',
    resolucion: 'Resolución N° 001-2026 de fecha 10 de febrero del 2026, published en Gaceta Oficial de la República Bolivariana de Venezuela N° 43.319 de fecha 19 de febrero del 2026'
  },
  {
    id: 2,
    etiqueta: 'HÉCTOR JOSÉ CASTILLO RIERA (Despacho)',
    nombre: 'HÉCTOR JOSÉ CASTILLO RIERA',
    cargo: 'Director General (E) del Despacho',
    resolucion: 'Resolución N° 001-2024 de fecha 02 de septiembre de 2024, publicada en Gaceta Oficial ordinaria de la República Bolivariana de Venezuela N° 42.955 de fecha 03 de septiembre de 2024: y actuando según delegación de firma publicada en la Resolución N° 009-2024 de fecha 30/09/2024'
  },
  {
    id: 3,
    etiqueta: 'ANABEL PEREIRA FERNÁNDEZ (ministra)',
    nombre: 'ANABEL PEREIRA FERNÁNDEZ',
    cargo: 'Ministra del Poder Popular de Economia y Finanzas',
    resolucion: 'Decreto N° 4.981 de fecha 27 de agosto de 2024, publicada en Gaceta Oficial Extraordinario de la República Bolivariana de Venezuela N° 6.830 de 27 de agosto de 2024'
  },
];

const perfilSeleccionado = ref('');

const aplicarPerfil = (event: Event) => {
  const target = event.target as HTMLSelectElement;
  FindPerfil(target.value);
};

const FindPerfil = (nombre_perfil: any) => {
  const perfil = perfilesPresentadoA.find(p => p.nombre === nombre_perfil);
  if (perfil) {
    form.presentadoA_nombre = perfil.nombre;
    form.presentadoA_cargo = perfil.cargo;
    form.firmaDerecha.nombre = perfil.nombre;
    form.firmaDerecha.cargo = perfil.cargo;
    form.firmaDerecha.resolucion2 = perfil.resolucion;
  }
};

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
  presentadoA_nombre: '',
  presentadoA_cargo: '',
  presentadoPor_nombre: 'OLIVER EZEQUIEL RIVAS PAREDES',
  presentadoPor_cargo: 'Director General (E) de la Oficina de Atención al Ciudadano',
  firmaIzquierda: {
    nombre: 'OLIVER EZEQUIEL RIVAS PAREDES',
    cargo: 'Director General (E) de la Oficina de Atención al Ciudadano',
    resolucion1: 'Resolución No. 006-2024, publicada en la Gaceta Oficial de la República Bolivariana de Venezuela No. 42.958, de fecha 06 de septiembre de 2024.',
    resolucion2: ''
  },
  firmaDerecha: {
    nombre: '',
    cargo: '',
    resolucion1: '',
    resolucion2: '',
  },
});

const turndownService = new TurndownService();
turndownService.addRule('strong', { filter: ['strong', 'b'], replacement: (content) => `**${content}**` });

const form = reactive<PuntoDeCuentaData>(createInitialState());
const $toast = useToast();

const asuntoHtml = ref('');
const exposicionHtml = ref('');
const propuestaHtml = ref('');

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
};

const route = useRoute();
const router = useRouter();
const id = route.params.id as string;
const pdc_id = route.params.pdc_id as string;

const getPDCToEdit = async () => {
  if (!pdc_id) return;
  try {
    const response = await getPDC(pdc_id);
    const punto = response.data;

    // Asignamos el valor del estatus proveniente del backend
    estatusPDC.value = punto.estatus ?? false;

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
    perfilSeleccionado.value = 'ANMY IVONETT PÉREZ GONZÁLEZ';
    FindPerfil(perfilSeleccionado.value);
    estatusPDC.value = false; // Si no hay pdc_id, es un nuevo punto de cuenta, por lo que permitimos edición
  }
});

const sendInfo = async () => {
  // Si está aprobado/completado (true), no permitimos re-enviar para edición
  if (estatusPDC.value) {
    alerta('Acción Bloqueada', 'No se puede modificar un punto de cuenta que ya ha sido aprobado.', 'warning');
    return;
  }

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

const imprimirDocumento = () => {
  const elementosAOcultar = document.querySelectorAll('.fixed, .sidebar, .navbar');
  
  elementosAOcultar.forEach((el) => {
    (el as HTMLElement).style.display = 'none';
  });

  window.print();

  setTimeout(() => {
    elementosAOcultar.forEach((el) => {
      (el as HTMLElement).style.display = '';
    });
  }, 500);
};
</script>

<template>
  <div class="workspace-layout" :class="{ 'justify-center': estatusPDC }">
    
    <!-- LADO IZQUIERDO: FORMULARIO DE EDICIÓN -->
    <!-- Se oculta por completo si estatusPDC es true -->
    <div v-if="!estatusPDC" class="editor-panel no-print">
      <h1 class="text-3xl font-bold mb-8 text-center text-sky-700 dark:text-sky-400">
        Formulario Punto de Cuenta
      </h1>

      <form @submit.prevent="sendInfo" class="space-y-8">
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">
            Datos Generales
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
              <label for="puntoNro" class="block text-sm font-medium text-gray-700 dark:text-gray-300"><strong>PUNTO N°</strong></label>
              <input type="text" id="puntoNro" v-model="form.puntoNro" class="mt-1 inputForm" />
            </div>
            <div>
              <label for="fechaDocumento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Documento</label>
              <input type="date" id="fechaDocumento" v-model="form.fechaDocumento" class="mt-1 inputForm" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b pb-2 border-sky-200 dark:border-slate-700">
            <h2 class="text-xl font-semibold text-sky-600 dark:text-sky-300">Presentado</h2>
            <div class="flex items-center gap-2 mt-2 sm:mt-0">
              <span class="text-xs font-bold text-gray-500 uppercase">Auto-completar destino:</span>
              <select v-model="perfilSeleccionado" @change="aplicarPerfil"
                class="text-sm border rounded-md p-1 bg-sky-50 dark:bg-slate-700 dark:text-white border-sky-300 focus:ring-2 focus:ring-sky-500">
                <option value="" disabled>Seleccione una autoridad...</option>
                <option v-for="perfil in perfilesPresentadoA" :key="perfil.id" :value="perfil.nombre">
                  {{ perfil.etiqueta }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            <div>
              <label for="presentadoA_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300"><strong>A (Nombre)</strong></label>
              <input type="text" id="presentadoA_nombre" v-model="form.presentadoA_nombre" class="mt-1 inputForm" />

              <label for="presentadoA_cargo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Cargo (A)</label>
              <input type="text" id="presentadoA_cargo" v-model="form.presentadoA_cargo" class="mt-1 inputForm" />
            </div>
            <div>
              <label for="presentadoPor_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300"><strong>Por (Nombre)</strong></label>
              <input type="text" id="presentadoPor_nombre" v-model="form.presentadoPor_nombre" class="mt-1 inputForm" />

              <label for="presentadoPor_cargo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Cargo (Por)</label>
              <input type="text" id="presentadoPor_cargo" v-model="form.presentadoPor_cargo" class="mt-1 inputForm" />
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">Asunto</h2>
          <QuillEditor theme="snow" :toolbar="[['bold', 'italic'], [{ 'align': ['', 'justify'] }]]" contentType="html" v-model:content="asuntoHtml"
            @update:content="updateMarkdown(asuntoHtml, 'asunto')" />
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">Exposición de Motivos</h2>
          <QuillEditor theme="snow" :toolbar="[['bold', 'italic'], [{ 'align': ['', 'justify'] }]]" contentType="html" v-model:content="exposicionHtml"
            @update:content="updateMarkdown(exposicionHtml, 'exposicionDeMotivosCompleta')" style="min-height: 250px;" />
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">Propuesta</h2>
          <QuillEditor theme="snow" :toolbar="[['bold', 'italic'], [{ 'align': ['', 'justify'] }]]" contentType="html" v-model:content="propuestaHtml"
            @update:content="updateMarkdown(propuestaHtml, 'propuestaCompleta')" style="min-height: 250px;" />
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">Decisión e Instrucciones</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><strong>DECISIÓN</strong></label>
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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anexos:</label>
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
          <h2 class="text-xl font-semibold mb-4 text-sky-600 dark:text-sky-300 border-b pb-2 border-sky-200 dark:border-slate-700">Firmantes</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
              <h3 class="font-medium mb-2 text-center text-gray-800 dark:text-gray-200">Firma Izquierda</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><strong>Nombre</strong></label>
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
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><strong>Nombre</strong></label>
                  <input type="text" v-model="form.firmaDerecha.nombre" class="mt-1 inputForm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo</label>
                  <input type="text" v-model="form.firmaDerecha.cargo" class="mt-1 inputForm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resolución 2</label>
                  <textarea v-model="form.firmaDerecha.resolucion2" rows="4" class="mt-1 inputForm resize-y"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-10 flex justify-end items-center">
          <button type="submit" class="px-8 py-3 bg-sky-600 text-white rounded-md font-semibold hover:bg-sky-700 shadow shadow-sky-500/20 transition-all">
            {{ pdc_id ? 'Editar' : 'Crear' }} punto de cuenta
          </button>
        </div>
      </form>
    </div>

    <!-- LADO DERECHO: VISTA PREVIA -->
    <div class="preview-panel">
      <!-- Si el estatus es true, agregamos un botón de impresión flotante o superior sobre la vista previa -->
      <div v-if="estatusPDC" class="no-print absolute top-4 right-4 z-50">
        <button type="button" @click="imprimirDocumento" class="px-6 py-3 bg-emerald-600 text-white rounded-md font-semibold hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
          🖨️ Imprimir Documento Autorizado
        </button>
      </div>

      <div class="document-page">
        
        <!-- Cintillo de Logos Oficiales -->
        <div class="cintillo-logos">
          <img src="/cintillo.png" alt="Ministerio Finanzas" class="logo-left" onerror="this.style.display='none'" />
        </div>

        <!-- Encabezado Rojo Institucional -->
        <div class="header-table-bar">
          <div class="bar-left">
            <div class="title-ministerio">MINISTERIO DEL PODER POPULAR DE ECONOMÍA Y FINANZAS</div>
            <div class="title-documento">PUNTO DE CUENTA</div>
          </div>
          <div class="bar-right-box">
            <div class="punto-title">PUNTO N° {{ form.puntoNro || '      ' }}</div>
          </div>
        </div>

        <!-- Tabla Estricta de Presentación -->
        <table class="presentacion-table">
          <tr>
            <td rowspan="2" class="p-label-cell">PRESENTADO</td>
            <td class="p-content-cell border-bottom">
              <strong>A:</strong> <span class="font-bold text-uppercase">{{ form.presentadoA_nombre }}</span><br/>
              <span class="text-cargo-view">{{ form.presentadoA_cargo }}</span>
            </td>
            <td class="p-info-cell border-bottom border-left">
              <div class="info-label">Nº Páginas:</div>
              <div class="info-val">{{ form.nroPaginas }}</div>
            </td>
          </tr>
          <tr>
            <td class="p-content-cell">
              <strong>Por:</strong> <span class="font-bold text-uppercase">{{ form.presentadoPor_nombre }}</span><br/>
              <span class="text-cargo-view">{{ form.presentadoPor_cargo }}</span>
            </td>
            <td class="p-info-cell border-left">
              <div class="info-label">Fecha:</div>
              <div class="info-val">{{ fechaFormateadaParaVista }}</div>
            </td>
          </tr>
        </table>

        <!-- Sección: Asunto -->
        <div class="section-container">
          <div class="section-hdr">ASUNTO:</div>
          <div class="section-body text-justify font-bold text-uppercase" v-html="asuntoHtml || '<p>&nbsp;</p>'"></div>
        </div>

        <!-- Sección: Exposición de Motivos -->
        <div class="section-container">
          <div class="section-hdr">EXPOSICIÓN DE MOTIVO:</div>
          <div class="section-body text-justify" v-html="exposicionHtml || '<p>&nbsp;</p>'"></div>
        </div>

        <!-- Sección: Propuesta -->
        <div class="section-container">
          <div class="section-hdr">PROPUESTA:</div>
          <div class="section-body text-justify" v-html="propuestaHtml || '<p>&nbsp;</p>'"></div>
        </div>

        <!-- Sección: Decisión -->
        <div class="section-container">
          <div class="section-hdr">DECISIÓN:</div>
          <div class="decision-grid">
            <div v-for="opt in ['APROBADO', 'NEGADO', 'VISTO', 'DIFERIDO', 'OTRO']" :key="opt" class="decision-cell">
              <div class="opt-text">{{ opt }}</div>
              <div class="check-box-square">{{ form.decision === opt ? 'X' : '' }}</div>
            </div>
          </div>
        </div>

        <!-- Sección: Otras Instrucciones / Anexos -->
        <table class="instrucciones-anexos-table">
          <tr>
            <td class="instrucciones-cell">
              <strong>Otras instrucciones:</strong>
              <div class="instrucciones-text text-justify">{{ form.otrasInstrucciones }}</div>
            </td>
            <td class="anexos-cell border-left">
              <span class="font-bold mr-2">Anexos:</span>
              <div class="anexo-item">
                <span>Si</span>
                <div class="check-box-square-sm">{{ form.anexos === 'Si' ? 'X' : '' }}</div>
              </div>
              <div class="anexo-item">
                <span>No</span>
                <div class="check-box-square-sm">{{ form.anexos === 'No' ? 'X' : '' }}</div>
              </div>
            </td>
          </tr>
        </table>

        <!-- Firmas Inferiores Distribuidas en Paralelo -->
        <div class="signatures-wrapper">
          <div class="sig-column">
            <div class="sig-line"></div>
            <div class="sig-name">{{ form.firmaIzquierda.nombre }}</div>
            <div class="sig-post">{{ form.firmaIzquierda.cargo }}</div>
            <div class="sig-resolution">{{ form.firmaIzquierda.resolucion1 }}</div>
          </div>
          <div class="sig-column">
            <div class="sig-line"></div>
            <div class="sig-name">{{ form.firmaDerecha.nombre }}</div>
            <div class="sig-post">{{ form.firmaDerecha.cargo }}</div>
            <div class="sig-resolution">{{ form.firmaDerecha.resolucion2 }}</div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
/* --- Distribución y Flexibilidad en Pantalla --- */
.workspace-layout {
  display: flex;
  gap: 1.5rem;
  max-width: 1750px;
  margin: 0 auto;
  padding: 1rem;
  position: relative;
}
.editor-panel {
  flex: 1;
  max-height: 94vh;
  overflow-y: auto;
  padding-right: 0.5rem;
}
.preview-panel {
  width: 222mm;
  background: #525659;
  padding: 40px 10px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  max-height: 94vh;
  overflow-y: auto;
  border-radius: 8px;
  position: relative;
}

/* --- Hoja de Formato Fiel --- */
.document-page {
  width: 215.9mm;
  min-height: 279.4mm;
  background: #ffffff;
  padding: 5mm 12mm 10mm 12mm;
  box-sizing: border-box;
  color: #000000;
  font-family: Arial, sans-serif;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* Cintillo superior */
.cintillo-logos {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  min-height: 12mm;
}
.logo-left { height: 12mm; object-fit: contain; }

/* Barra Roja de Encabezado */
.header-table-bar {
  display: flex;
  border: 0.35mm solid #000000;
  background: #bd0000;
}
.bar-left {
  flex: 1;
  text-align: center;
  color: #ffffff;
  padding: 6px;
}
.title-ministerio { font-size: 10.5pt; font-weight: bold; letter-spacing: 0.2px; }
.title-documento { font-size: 11pt; font-weight: bold; margin-top: 2px; }

.bar-right-box {
  width: 54mm;
  background: #bd0000;
  border-left: 0.35mm solid #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.punto-title { 
  font-size: 11pt; 
  font-weight: bold; 
  color: #ffffff;
  text-align: center;
}

/* Tabla del bloque PRESENTADO */
.presentacion-table {
  width: 100%;
  border-collapse: collapse;
  border: 0.35mm solid #000000;
  border-top: none;
}
.p-label-cell {
  width: 32mm;
  font-size: 10pt;
  font-weight: bold;
  text-align: center;
  vertical-align: middle;
  border-right: 0.35mm solid #000000;
  background: #ffffff;
  letter-spacing: 0.5px;
}
.p-content-cell {
  padding: 5px 8px;
  font-size: 10pt;
  line-height: 1.3;
  text-align: left;
}
.text-cargo-view { font-size: 9.5pt; font-weight: normal; color: #1a1a1a; }
.p-info-cell {
  width: 54mm;
  text-align: center;
  vertical-align: middle;
  font-size: 10pt;
}
.info-label { font-weight: bold; }
.info-val { margin-top: 1px; }

/* Estructura Modular de Secciones */
.section-container {
  border: 0.35mm solid #000000;
  border-top: none;
  display: flex;
  flex-direction: column;
}
.section-hdr {
  background: #bd0000;
  color: #ffffff;
  font-size: 10pt;
  font-weight: bold;
  padding: 3px 8px;
  text-align: left;
}
.section-body {
  padding: 8px;
  font-size: 10pt;
  line-height: 1.45;
  min-height: 20mm;
}
.section-body :deep(p) { margin: 0 0 6px 0; }

/* Cuadrícula de Decisiones */
.decision-grid {
  display: flex;
  width: 100%;
}
.decision-cell {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 6px 0;
}
.opt-text { font-weight: bold; font-size: 10pt; margin-bottom: 5px; }
.check-box-square {
  width: 4.8mm;
  height: 4.8mm;
  border: 0.3mm solid #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 10.5pt;
}

/* Fila de Otras Instrucciones y Anexos */
.instrucciones-anexos-table {
  width: 100%;
  border-collapse: collapse;
  border: 0.35mm solid #000000;
  border-top: none;
}
.instrucciones-cell {
  padding: 6px 8px 0px 8px;
  font-size: 10pt;
  vertical-align: top;
  text-align: left;
}
.instrucciones-text { margin-top: 3px; min-height: 5mm; white-space: pre-line; }
.anexos-cell {
  width: 54mm;
  vertical-align: middle;
  text-align: center;
  padding: 6px 0;
}
.anexo-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-left: 10px;
  font-size: 10pt;
  font-weight: bold;
}
.check-box-square-sm {
  width: 4.2mm;
  height: 4.2mm;
  border: 0.3mm solid #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 9.5pt;
}

/* Área de Firmantes Inferior */
.signatures-wrapper {
  margin-top: auto;
  display: flex;
  justify-content: space-between;
  padding-top: 16mm;
  padding-bottom: 4mm;
}
.sig-column {
  width: 46%;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.sig-line {
  width: 85%;
  border-top: 0.35mm solid #000000;
  margin-bottom: 5px;
}
.sig-name { font-weight: bold; font-size: 9.5pt; }
.sig-post { font-size: 8.5pt; margin-top: 2px; line-height: 1.25; color: #111; }
.sig-resolution { font-size: 7.5pt; margin-top: 4px; color: #222; text-align: center; max-width: 98%; line-height: 1.2; }

/* Auxiliares Estilos Globales */
.border-bottom { border-bottom: 0.35mm solid #000000; }
.border-left { border-left: 0.35mm solid #000000; }
.text-justify { text-align: justify; }
.text-uppercase { text-transform: uppercase; }
.inputForm { @apply block w-full rounded-md border-gray-300 shadow-sm p-2 focus:border-sky-500 focus:ring-sky-500 sm:text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-gray-200; }

/* --- MANEJO ESTRICTO DE IMPRESIÓN --- */
@media print {
  /* 1. Ocultar absolutamente todo en el viewport raíz */
  html, body {
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
    background-color: #ffffff !important;
  }

  /* Ocultar cualquier contenedor ancestro o ajeno a la vista previa */
  body > *:not(.workspace-layout),
  .editor-panel,
  .no-print,
  header,
  nav,
  aside,
  footer {
    display: none !important;
    height: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  /* 2. Forzar al layout a no heredar flex o márgenes que empujen el contenido */
  .workspace-layout {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    max-width: none !important;
  }

  /* 3. Forzar al contenedor a subir al origen real absoluto (0,0) de la hoja física */
  .preview-panel {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 215.9mm !important; /* Forzar el ancho exacto de la carta */
    height: auto !important;
    max-height: none !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    display: block !important;
    overflow: visible !important;
  }

  /* 4. Dimensiones exactas de la hoja tamaño Carta sin márgenes del sistema */
  .document-page {
    display: flex !important;
    width: 215.9mm !important;
    height: 279.4mm !important;
    min-height: 279.4mm !important;
    padding: 5mm 12mm 10mm 12mm !important; /* Ajusta este padding superior si necesitas calibrar el cintillo */
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background-color: #ffffff !important;
    page-break-inside: avoid !important;
    page-break-after: avoid !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
  }

  /* Restablecer comportamiento de tablas y flexbox internos */
  .header-table-bar,
  .decision-grid,
  .signatures-wrapper {
    display: flex !important;
  }
  
  .presentacion-table,
  .instrucciones-anexos-table {
    display: table !important;
    width: 100% !important;
  }
  .presentacion-table tr, .instrucciones-anexos-table tr {
    display: table-row !important;
  }
  .presentacion-table td, .instrucciones-anexos-table td {
    display: table-cell !important;
  }

  /* Forzar al navegador a ignorar sus márgenes por defecto en tamaño carta */
  @page {
    size: letter;
    margin: 0mm !important;
  }

  /* Mantener el color rojo institucional y fondos en la impresión */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-scheme: light !important;
  }
}
</style>