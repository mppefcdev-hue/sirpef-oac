<template>
  <div class="punto-paper bg-white print:shadow-none mx-auto p-[1cm] text-black text-[10pt] font-arial leading-tight w-[21.59cm] min-h-[27.94cm] relative flex flex-col border border-gray-300">
    <!-- Header -->
    <div class="flex justify-between items-start mb-2">
      <img src="/logo-ministerio.png" class="h-16 w-auto" />
      <img src="/logo-200.png" class="h-16 w-auto" />
    </div>

    <div class="bg-[#c00000] text-white text-center py-1 font-bold text-sm mb-4 uppercase">
      Ministerio del Poder Popular de Economía y Finanzas
    </div>

    <div class="text-center font-bold text-lg mb-4 underline uppercase">Punto de Cuenta</div>

    <!-- Info Table -->
    <table class="w-full border-collapse border border-black mb-4 text-[9pt]">
      <tr>
        <td rowspan="3" class="border border-black p-2 font-bold w-32 align-middle text-center uppercase">Presentado</td>
        <td class="border border-black p-2">
          <span class="font-bold">Por:</span> OLIVER EZEQUIEL RIVAS PAREDES<br/>
          <span class="text-[8pt] italic">Director General (E) de la Oficina de Atención al Ciudadano</span>
        </td>
        <td class="border border-black p-2 font-bold w-24">PUNTO N°</td>
        <td class="border border-black p-2 w-32">{{ data.numero_punto }}</td>
      </tr>
      <tr>
        <td class="border border-black p-2">
          <span class="font-bold">A:</span> ANMY IVONETT PÉREZ GONZÁLEZ<br/>
          <span class="text-[8pt] italic">Directora General de la Consultoría Jurídica</span>
        </td>
        <td class="border border-black p-2 font-bold">N° Páginas:</td>
        <td class="border border-black p-2">1</td>
      </tr>
      <tr>
        <td class="border border-black p-2">&nbsp;</td>
        <td class="border border-black p-2 font-bold">Fecha:</td>
        <td class="border border-black p-2">{{ formatDisplayDate(data.fecha_registro || data.created_at) }}</td>
      </tr>
    </table>

    <!-- Sections -->
    <div class="mb-4">
      <div class="bg-[#c00000] text-white px-2 py-0.5 font-bold text-[9pt] uppercase">Asunto:</div>
      <div class="border border-black p-2 text-justify min-h-[40px] uppercase font-bold text-[9pt]">
        {{ data.asunto }}
      </div>
    </div>

    <div class="mb-4">
      <div class="bg-[#c00000] text-white px-2 py-0.5 font-bold text-[9pt] uppercase">Exposición de Motivos:</div>
      <div class="border border-black p-2 text-justify text-[8pt]">
        {{ data.exposicion_motivos || data.descripcion }}
      </div>
    </div>

    <div class="mb-4">
      <div class="bg-[#c00000] text-white px-2 py-0.5 font-bold text-[9pt] uppercase">Propuesta:</div>
      <div class="border border-black p-2 text-justify text-[8pt]">
        {{ data.propuesta || 'Se somete a su consideración la aprobación de la presente solicitud.' }}
      </div>
    </div>

    <div class="mb-4">
      <div class="bg-[#c00000] text-white px-2 py-0.5 font-bold text-[9pt] uppercase">Decisión:</div>
      <div class="border border-black p-4 flex justify-around items-center">
        <div class="flex items-center gap-2"><div class="w-4 h-4 border border-black flex items-center justify-center font-bold">{{ data.decision === 'APROBADO' ? 'X' : '' }}</div> APROBADO</div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 border border-black flex items-center justify-center font-bold">{{ data.decision === 'NEGADO' ? 'X' : '' }}</div> NEGADO</div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 border border-black flex items-center justify-center font-bold">{{ data.decision === 'VISTO' ? 'X' : '' }}</div> VISTO</div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 border border-black flex items-center justify-center font-bold">{{ data.decision === 'DIFERIDO' ? 'X' : '' }}</div> DIFERIDO</div>
      </div>
    </div>

    <!-- Footer Signatures Area -->
    <div class="mt-auto grid grid-cols-2 gap-10 text-center text-[8pt] pt-10">
      <div>
        <div class="font-bold">OLIVER EZEQUIEL RIVAS PAREDES</div>
        <div class="italic">Director General (E) de la Oficina de Atención al Ciudadano</div>
      </div>
      <div>
        <div class="font-bold">ANMY IVONETT PÉREZ GONZÁLEZ</div>
        <div class="italic">Directora General de la Consultoría Jurídica</div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  data: Object
});

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '';
  if (dateStr.includes(' ')) return dateStr.split(' ')[0];
  return dateStr;
};
</script>

<style scoped>
.punto-paper {
  aspect-ratio: 21.59 / 27.94;
  font-family: Arial, sans-serif;
}
@media print {
  .punto-paper { border: none !important; }
}
</style>
