<template>
  <div class="memo-paper bg-white print:shadow-none mx-auto p-[1.5cm] pt-[1cm] text-black text-[12pt] font-arial leading-snug w-[21.59cm] min-h-[27.94cm] relative flex flex-col">
    <!-- Header Image -->
    <div v-if="data.header_img || data.headerImg" class="mb-4">
      <img :src="data.header_img || data.headerImg" class="w-full h-auto" />
    </div>

    <!-- Metadata -->
    <div class="flex justify-between mb-6">
      <div class="font-bold uppercase">{{ data.codigo }}</div>
      <div class="text-right">Caracas, {{ formatDisplayDate(data.tabla?.fecha) }}</div>
    </div>

    <div class="text-center font-bold text-lg mb-6">MEMORÁNDUM</div>

    <!-- Receiver/Sender -->
    <div class="space-y-4 mb-8">
      <div class="flex">
        <span class="font-bold w-24 shrink-0">PARA:</span>
        <div class="flex-1">
          <div class="font-bold">{{ data.para_nombre }}</div>
          <div class="whitespace-pre-line">{{ data.para_cargo }}</div>
        </div>
      </div>
      <div class="flex">
        <span class="font-bold w-24 shrink-0">DE:</span>
        <div class="flex-1">
          <div class="font-bold">{{ data.de_nombre }}</div>
          <div class="whitespace-pre-line">{{ data.de_cargo }}</div>
        </div>
      </div>
      <div class="flex">
        <span class="font-bold w-24">ASUNTO:</span>
        <div class="flex-1 font-bold">Remisión de Punto de Cuenta N° {{ data.tabla?.pto_cta }}.</div>
      </div>
    </div>

    <div class="border-t-4 border-double border-black mb-6"></div>

    <!-- Body -->
    <div class="text-justify mb-6 whitespace-pre-line">
      Tengo a bien dirigirme a usted, en la oportunidad de remitir Punto de Cuenta N°{{ data.tabla?.pto_cta || '000/2026' }} de fecha {{ formatDisplayDate(data.tabla?.fecha) }}, {{ data.motivo }}, debidamente aprobado, el cual se especifica a continuación:
    </div>

    <!-- Table -->
    <table class="w-full border-collapse border border-gray-400 mb-6 text-[11px] table-fixed">
      <thead>
        <tr class="bg-[#e60000] text-white font-bold h-10">
          <th class="border border-gray-400 p-2 text-center align-middle w-[15%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">Pto/Cta</th>
          <th class="border border-gray-400 p-2 text-center align-middle w-[15%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">Fecha</th>
          <th class="border border-gray-400 p-2 text-center align-middle w-[18%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">Solicitante</th>
          <th class="border border-gray-400 p-2 text-center align-middle w-[12%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">C.I.</th>
          <th class="border border-gray-400 p-2 text-center align-middle w-[15%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">Monto (Bs.)</th>
          <th class="border border-gray-400 p-2 text-center align-middle w-[25%]" style="background-color: #e60000 !important; -webkit-print-color-adjust: exact;">Proveedor</th>
        </tr>
      </thead>
      <tbody>
        <tr class="h-16 text-center align-middle">
          <td class="border border-gray-400 p-2">{{ data.tabla?.pto_cta }}</td>
          <td class="border border-gray-400 p-2">{{ formatDisplayDate(data.tabla?.fecha) }}</td>
          <td class="border border-gray-400 p-2">{{ data.tabla?.solicitante }}</td>
          <td class="border border-gray-400 p-2">{{ data.tabla?.cedula }}</td>
          <td class="border border-gray-400 p-2">{{ data.tabla?.monto }}</td>
          <td class="border border-gray-400 p-2">{{ data.tabla?.proveedor }}</td>
        </tr>
        <tr class="h-10 text-center align-middle">
          <td class="border-x border-gray-400"></td>
          <td class="border-x border-gray-400"></td>
          <td class="border-x border-gray-400"></td>
          <td class="border border-gray-400 p-2 font-bold text-center bg-gray-50" style="-webkit-print-color-adjust: exact;">TOTAL</td>
          <td class="border border-gray-400 p-2 font-bold">{{ data.tabla?.total }}</td>
          <td class="border border-gray-400"></td>
        </tr>
      </tbody>
    </table>

    <div class="text-justify mb-10 whitespace-pre-line">
      {{ data.cuerpo_final }}
    </div>

    <!-- Signature -->
    <div class="mt-auto mb-16 text-center uppercase relative">
      <div class="mb-10 lowercase">Atentamente,</div>
      
      <!-- Digital Signature Image -->
      <div v-if="data.firma_img || data.firmaImg" class="absolute left-1/2 -translate-x-1/2 -top-6 w-36 h-auto pointer-events-none z-0">
        <img :src="data.firma_img || data.firmaImg" class="w-full opacity-90" />
      </div>

      <div class="font-bold relative z-10">{{ data.de_nombre }}</div>
      <div class="text-[11pt] leading-tight max-w-lg mx-auto">{{ data.de_cargo }}</div>
      <div v-if="data.resolucion" class="text-[10pt] mt-1 leading-tight max-w-sm mx-auto text-gray-600 font-arial lowercase">
        {{ data.resolucion }}
      </div>
    </div>


    <!-- Footer Image -->
    <div v-if="data.footer_img || data.footerImg" class="mt-auto absolute bottom-4 left-[1.5cm] right-[1.5cm]">
      <img :src="data.footer_img || data.footerImg" class="w-full h-auto" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  data: Object
});

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '00/00/0000';
  const [year, month, day] = dateStr.split('-');
  if (!year || !month || !day) return dateStr;
  return `${day}/${month}/${year}`;
};
</script>

<style scoped>
.memo-paper {
  aspect-ratio: 21.59 / 27.94;
  -webkit-print-color-adjust: exact !important;
  print-color-adjust: exact !important;
  font-family: Arial, Helvetica, sans-serif !important;
}

.font-arial {
  font-family: Arial, Helvetica, sans-serif !important;
}

@media print {
  .memo-paper {
    box-shadow: none !important;
    margin: 0 !important;
    width: 21.59cm !important;
    height: 27.94cm !important;
    padding: 1.5cm !important;
    padding-top: 1cm !important;
    padding-bottom: 1.5cm !important;
    border: none !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    position: relative !important;
  }
  
  /* Ensure background colors in table headers appear */
  th {
    background-color: #e60000 !important;
    color: white !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  table, th, td {
    border: 0.5pt solid #9ca3af !important;
    border-collapse: collapse !important;
  }
}
</style>
