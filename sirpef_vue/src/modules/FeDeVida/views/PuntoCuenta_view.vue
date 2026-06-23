<script lang="ts" setup>
import { reactive } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Configuración básica para el toolbar de Quill
const minimalToolbar = ['bold', 'clean'];

const form = reactive({
  numero_punto: '',
  fecha: new Date().toISOString().substring(0, 10),
  anexos: false,
  presentado_a: '',
  cargo_a: '',
  resolucion_2: '',
  presentado_por: '',
  cargo_por: '',
  resolucion_1: '',
  decision: 'VISTO',
  asunto: '',
  exposicion_motivos: '',
  propuesta: '',
  otras_instrucciones: ''
});

// Helper para formatear la fecha de YYYY-MM-DD a DD/MM/YYYY en la vista de impresión
const formatearFecha = (fechaStr: string) => {
  if (!fechaStr) return 'dd/mm/aaaa';
  const parts = fechaStr.split('-');
  if (parts.length !== 3) return fechaStr;
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

// Disparador de impresión nativa del sistema
const printWindow = () => {
  window.print();
};
</script>

<template>
  <div class="app-container">
    
    <div class="editor-panel no-print">
      <header class="form-header">
        <h2>Generador de Punto de Cuenta</h2>
        <p>Rellene los datos del formulario a la izquierda. A la derecha verá la vista previa oficial que se enviará a la impresora.</p>
      </header>

      <form @submit.prevent="printWindow">
        <fieldset class="form-section">
          <legend>Información General</legend>
          <div class="grid-3">
            <div class="form-group">
              <label>Punto N°:</label>
              <input type="text" v-model="form.numero_punto" placeholder="Ej: 001-2026" />
            </div>
            <div class="form-group">
              <label>Fecha:</label>
              <input type="date" v-model="form.fecha" />
            </div>
            <div class="form-group">
              <label>¿Posee Anexos?</label>
              <select v-model="form.anexos">
                <option :value="true">Sí</option>
                <option :value="false">No</option>
              </select>
            </div>
          </div>
        </fieldset>

        <fieldset class="form-section">
          <legend>Presentación y Autoridades</legend>
          <div class="grid-1">
            <div class="card-box">
              <h3>Presentado A (Destinatario)</h3>
              <div class="form-group">
                <label>Nombre:</label>
                <input type="text" v-model="form.presentado_a" placeholder="Escriba el nombre" />
              </div>
              <div class="form-group">
                <label>Cargo:</label>
                <input type="text" v-model="form.cargo_a" placeholder="Escriba el cargo" />
              </div>
              <div class="form-group">
                <label>Resolución / Decisión Destinatario:</label>
                <input type="text" v-model="form.resolucion_2" placeholder="Ej: Autorizado / Visto" />
              </div>
            </div>

            <div class="card-box">
              <h3>Presentado Por (Remitente)</h3>
              <div class="form-group">
                <label>Nombre:</label>
                <input type="text" v-model="form.presentado_por" placeholder="Escriba el nombre" />
              </div>
              <div class="form-group">
                <label>Cargo:</label>
                <input type="text" v-model="form.cargo_por" placeholder="Escriba el cargo" />
              </div>
              <div class="form-group">
                <label>Resolución / Nota Remitente:</label>
                <input type="text" v-model="form.resolucion_1" placeholder="Ej: Conforme" />
              </div>
            </div>
          </div>
        </fieldset>

        <fieldset class="form-section">
          <legend>Contenido del Punto</legend>
          <div class="form-group ql-custom">
            <label>Asunto:</label>
            <QuillEditor theme="snow" v-model:content="form.asunto" contentType="html" :toolbar="minimalToolbar" />
          </div>
          <div class="form-group ql-custom">
            <label>Exposición de Motivos:</label>
            <QuillEditor theme="snow" v-model:content="form.exposicion_motivos" contentType="html" :toolbar="minimalToolbar" />
          </div>
          <div class="form-group ql-custom">
            <label>Propuesta:</label>
            <QuillEditor theme="snow" v-model:content="form.propuesta" contentType="html" :toolbar="minimalToolbar" />
          </div>
          <div class="form-group ql-custom">
            <label>Otras Instrucciones:</label>
            <QuillEditor theme="snow" v-model:content="form.otras_instrucciones" contentType="html" :toolbar="minimalToolbar" />
          </div>
        </fieldset>

        <fieldset class="form-section">
          <legend>Decisión Final</legend>
          <div class="radio-group">
            <label v-for="opt in ['APROBADO', 'NEGADO', 'VISTO', 'DIFERIDO', 'OTRO']" :key="opt" class="radio-label">
              <input type="radio" :value="opt" v-model="form.decision" />
              {{ opt }}
            </label>
          </div>
        </fieldset>

        <div class="actions-panel">
          <button type="submit" class="btn btn-print">
            🖨️ Imprimir Punto de Cuenta
          </button>
        </div>
      </form>
    </div>

    <div class="print-document-container">
      <div class="document-page">
        
        <div class="cintillo-container">
          <img src="/cintillo.png" alt="Cintillo Institucional" class="cintillo-img" />
        </div>

        <div class="header-table-bar">
          <div class="bar-left">
            <div class="title-ministerio">MINISTERIO DEL PODER POPULAR DE ECONOMÍA Y FINANZAS</div>
            <div class="title-documento">PUNTO DE CUENTA</div>
          </div>
          <div class="bar-right-box">
            PUNTO N° {{ form.numero_punto || '' }}
          </div>
        </div>

        <div class="presentacion-block">
          <div class="p-label-side">PRESENTADO</div>
          <div class="p-content-side">
            <div class="row-p line-bottom">
              <strong>A:</strong> 
              <span class="text-uppercase">{{ form.presentado_a }}</span>
              <br/>
              <span class="text-cargo">{{ form.cargo_a }}</span>
            </div>
            <div class="row-p">
              <strong>Por:</strong> 
              <span class="text-uppercase">{{ form.presentado_por }}</span>
              <br/>
              <span class="text-cargo">{{ form.cargo_por }}</span>
            </div>
          </div>
          <div class="p-info-side">
            <div class="info-cell line-bottom">
              <strong>Nº Páginas:</strong>
              <div class="page-count-eval">1/1</div>
            </div>
            <div class="info-cell">
              <strong>Fecha:</strong>
              <div>{{ formatearFecha(form.fecha) }}</div>
            </div>
          </div>
        </div>

        <div class="section-box-document">
          <div class="section-title-bar">ASUNTO:</div>
          <div class="section-content-html" v-html="form.asunto || '<p>&nbsp;</p>'"></div>
        </div>

        <div class="section-box-document">
          <div class="section-title-bar">EXPOSICIÓN DE MOTIVO:</div>
          <div class="section-content-html" v-html="form.exposicion_motivos || '<p>&nbsp;</p></div>'"></div>
        </div>

        <div class="section-box-document">
          <div class="section-title-bar">PROPUESTA:</div>
          <div class="section-content-html" v-html="form.propuesta || '<p>&nbsp;</p>'"></div>
        </div>

        <div class="section-box-document">
          <div class="section-title-bar">DECISIÓN:</div>
          <div class="decision-options-row">
            <div v-for="opt in ['APROBADO', 'NEGADO', 'VISTO', 'DIFERIDO', 'OTRO']" :key="opt" class="decision-box-cell">
              <span class="opt-title">{{ opt }}</span>
              <div class="check-square">
                {{ form.decision === opt ? 'X' : '' }}
              </div>
            </div>
          </div>
        </div>

        <div class="instrucciones-anexos-block">
          <div class="instrucciones-side">
            <strong>Otras instrucciones:</strong>
            <div class="section-content-html-flat" v-html="form.otras_instrucciones || ' '"></div>
          </div>
          <div class="anexos-side">
            <span class="anexos-title">Anexos:</span>
            <div class="anexo-option">
              <span>Si</span>
              <div class="check-square-small">{{ form.anexos === true ? 'X' : '' }}</div>
            </div>
            <div class="anexo-option">
              <span>No</span>
              <div class="check-square-small">{{ form.anexos === false ? 'X' : '' }}</div>
            </div>
          </div>
        </div>

        <div class="footer-signatures-block">
          <div class="signature-col">
            <div class="signature-line"></div>
            <div class="sig-name text-uppercase">{{ form.presentado_por }}</div>
            <div class="sig-cargo">{{ form.cargo_por }}</div>
            <div class="sig-res">{{ form.resolucion_1 }}</div>
          </div>
          <div class="signature-col">
            <div class="signature-line"></div>
            <div class="sig-name text-uppercase">{{ form.presentado_a }}</div>
            <div class="sig-cargo">{{ form.cargo_a }}</div>
            <div class="sig-res">{{ form.resolucion_2 }}</div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
/* --- DISEÑO DE LA INTERFAZ EN PANTALLA (WORKSPACE) --- */
.app-container {
  display: flex;
  gap: 2rem;
  max-width: 1600px;
  margin: 1rem auto;
  padding: 0 1rem;
}

.editor-panel {
  flex: 1;
  background: #fdfdfd;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  max-height: 92vh;
  overflow-y: auto;
}

.print-document-container {
  width: 216mm; /* Ancho de Carta */
  background: #525659;
  padding: 20px;
  border-radius: 4px;
  display: flex;
  justify-content: center;
}

/* Hoja simulada en pantalla */
.document-page {
  width: 216mm;
  min-height: 279mm;
  background: #ffffff;
  padding: 7mm;
  box-sizing: border-box;
  color: #000000;
  font-family: 'Helvetica', Arial, sans-serif;
  display: flex;
  flex-direction: column;
}

/* --- ESTILOS COMPONENTES DEL FORMULARIO INTERNO --- */
.form-header { border-bottom: 3px solid #c00000; margin-bottom: 1.5rem; }
.form-header h2 { color: #c00000; margin: 0; }
.form-section { border: 1px solid #ccc; border-radius: 6px; padding: 1rem; margin-bottom: 1.2rem; }
.form-section legend { font-weight: bold; color: #c00000; }
.grid-1 { display: flex; flex-direction: column; gap: 1rem; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.card-box { background: #f9f9f9; padding: 0.8rem; border: 1px solid #eee; border-radius: 4px; }
.card-box h3 { margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #555; border-bottom: 1px solid #ddd; }
.form-group { display: flex; flex-direction: column; margin-bottom: 0.8rem; }
.form-group label { font-size: 0.85rem; font-weight: bold; margin-bottom: 0.2rem; }
.form-group input, .form-group select { padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
.ql-custom :deep(.ql-container) { min-height: 90px; }
.radio-group { display: flex; gap: 1rem; }
.radio-label { font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 0.2rem; }
.actions-panel { display: flex; justify-content: flex-end; margin-top: 1rem; }
.btn-print { background: #c00000; color: white; border: none; padding: 0.8rem 1.5rem; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 1rem; }
.btn-print:hover { background: #900000; }

/* --- ESTRUCTURA DEL CUADRO OFICIAL DEL PUNTO DE CUENTA --- */
.cintillo-container { width: 100%; margin-bottom: 2px; }
.cintillo-img { width: 100%; height: 13mm; object-fit: contain; }

.header-table-bar {
  display: flex;
  border: 0.2mm solid #000;
  background: #c00000;
}
.bar-left {
  flex: 1;
  text-align: center;
  color: #fff;
  padding: 2px;
}
.title-ministerio { font-size: 10.5pt; font-weight: bold; }
.title-documento { font-size: 11pt; font-weight: bold; margin-top: 2px; border-top: 0.1mm solid #fff; padding-top: 2px;}
.bar-right-box {
  width: 48mm;
  background: #fff;
  color: #c00000;
  font-weight: bold;
  font-size: 11pt;
  display: flex;
  align-items: center;
  justify-content: center;
  border-left: 0.2mm solid #000;
}

/* Bloque Presentado A / Por */
.presentacion-block {
  display: flex;
  border: 0.2mm solid #000;
  border-top: none;
  min-height: 18mm;
}
.p-label-side {
  width: 30mm;
  font-weight: bold;
  font-size: 11pt;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 0.2mm solid #000;
}
.p-content-side {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.row-p { padding: 3px 6px; font-size: 10pt; }
.line-bottom { border-bottom: 0.2mm solid #000; }
.text-uppercase { text-transform: uppercase; font-weight: bold; }
.text-cargo { font-size: 9.5pt; font-weight: normal; }

.p-info-side {
  width: 48mm;
  border-left: 0.2mm solid #000;
  display: flex;
  flex-direction: column;
}
.info-cell {
  flex: 1;
  font-size: 10pt;
  text-align: center;
  padding: 2px;
}
.page-count-eval { font-size: 10pt; margin-top: 1px; }

/* Cajas de Secciones de Contenido */
.section-box-document {
  border: 0.2mm solid #000;
  border-top: none;
  display: flex;
  flex-direction: column;
}
.section-title-bar {
  background: #c00000;
  color: #fff;
  font-weight: bold;
  font-size: 11pt;
  padding: 2px 6px;
}
.section-content-html {
  padding: 6px;
  font-size: 11pt;
  line-height: 1.25;
  min-height: 20mm;
  text-align: left;
  word-wrap: break-word;
}
/* Forzar que el HTML interno de Quill mantenga el estilo nativo de impresión */
.section-content-html :deep(p), .section-content-html-flat :deep(p) { margin: 0 0 4px 0; }
.section-content-html :deep(strong), .section-content-html-flat :deep(strong) { font-weight: bold; }

/* Fila de la Decisión */
.decision-options-row {
  display: flex;
  width: 100%;
}
.decision-box-cell {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 4px 2px;
  border-right: 0.2mm solid #000;
}
.decision-box-cell:last-child { border-right: none; }
.opt-title { font-weight: bold; font-size: 11pt; margin-bottom: 4px; }
.check-square {
  width: 4mm;
  height: 4mm;
  border: 0.2mm solid #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 10pt;
}

/* Instrucciones y Anexos combinados */
.instrucciones-anexos-block {
  display: flex;
  border: 0.2mm solid #000;
  border-top: none;
  min-height: 16mm;
}
.instrucciones-side {
  flex: 1;
  padding: 4px 6px;
  font-size: 11pt;
}
.section-content-html-flat { font-size: 11pt; margin-top: 2px; }
.anexos-side {
  width: 48mm;
  border-left: 0.2mm solid #000;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}
.anexos-title { font-weight: bold; font-size: 11pt; }
.anexo-option { display: flex; align-items: center; gap: 4px; font-size: 11pt; font-weight: bold;}
.check-square-small {
  width: 4mm;
  height: 4mm;
  border: 0.2mm solid #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9pt;
}

/* Firmas en la parte inferior */
.footer-signatures-block {
  margin-top: auto; /* Empuja las firmas al fondo de la hoja carta */
  display: flex;
  justify-content: space-between;
  padding-top: 15mm;
}
.signature-col {
  width: 45%;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.signature-line {
  width: 80%;
  border-top: 0.3mm solid #000;
  margin-bottom: 6px;
}
.sig-name { font-weight: bold; font-size: 9pt; }
.sig-cargo { font-size: 9pt; color: #333; }
.sig-res { font-size: 9pt; margin-top: 2px; font-style: italic; }

/* --- REGLAS CRÍTICAS PARA IMPRESIÓN POR SISTEMA OPERATIVO --- */
@media print {
  /* Ocultar interfaz del sistema de edición completa */
  .no-print, .editor-panel {
    display: none !important;
  }
  
  /* Resetear contenedores globales */
  .app-container {
    margin: 0;
    padding: 0;
    display: block;
  }
  
  .print-document-container {
    background: transparent;
    padding: 0;
    margin: 0;
    width: 100%;
  }

  .document-page {
    width: 216mm;
    height: 279mm; /* Forzado de tamaño carta física */
    padding: 7mm;
    margin: 0;
    border: none;
    box-shadow: none;
    page-break-after: avoid;
    page-break-inside: avoid;
  }

  /* Forzar al motor web del navegador a imprimir los fondos rojo vino (Color-Backgrounds) */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>