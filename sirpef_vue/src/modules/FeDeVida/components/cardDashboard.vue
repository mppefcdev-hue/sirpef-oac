<script lang="ts" setup>


const props = defineProps<{
    title: string,
    value: string,
    color: string,
}>()


const checkIcon = () => {
    const t = props.title.toLowerCase()
    if (t.includes('cerrado')) return "person-circle-check"
    else if (t.includes('remitido')) return "person-circle-check"
    else if (t.includes('faltante')) return "person-circle-check"
    else if (t.includes('resultado directo')) return "person-circle-check"
    else if (t.includes('orientado')) return 'person-circle-xmark'
    else if (t.includes('trámite') || t.includes('tramite')) return 'person-circle-xmark'
    else if (t.includes('normal')) return 'file-lines'
    else if (t.includes('financier')) return 'coins'
    else if (t.includes('regulariz')) return 'circle-check'
    else if (t.includes('sin factura')) return 'file-circle-xmark'
    else if (t.includes('factura')) return 'receipt'
    else if (t.includes('reintegro')) return 'money-bill-transfer'
    else if (t.includes('cierre admin')) return 'folder-closed'
    return 'chart-pie'
}

</script>

<template>
    <div class="flex flex-col items-center justify-between bg-white border border-gray-200 rounded-xl overflow-hidden shadow hover:scale-[1.03] transition-all cursor-pointer h-full min-h-[220px]">
        <div class="p-4 flex items-center justify-center w-full h-[45%]" :style="{
            'background-color': color
          }">
          <font-awesome-icon :icon="checkIcon()" class="block scale-[1.3] mx-auto text-white h-[50px]" />
        </div>
        <div class="px-3 text-gray-700 h-[55%] flex flex-col items-center justify-center py-3 text-center w-full capitalize">
            <h3 class="text-base sm:text-lg font-bold tracking-wide leading-tight line-clamp-2">{{ title }}</h3>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-gray-800">{{ value }}</p>
        </div>
    </div>
</template>