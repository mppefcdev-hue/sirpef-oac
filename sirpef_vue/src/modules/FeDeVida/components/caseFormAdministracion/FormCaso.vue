<script setup lang="ts">
import Controls from '@/components/sirpef/form/Controls.vue';
import { ref } from 'vue';

const props = defineProps<{
  emitForm: (event: Event) => void,
  step: number,
  values: any,
}>()

const newRecaudo = ref({
  nombre: '',
  archivo: null as File | null
});

const previewImage = ref({
  show: false,
  url: '',
  nombre: ''
});

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    newRecaudo.value.archivo = target.files[0];
  }
};

const addRecaudo = () => {
  if (newRecaudo.value.nombre && newRecaudo.value.archivo) {
    props.values.recaudos.push({
      nombre: newRecaudo.value.nombre,
      archivo: newRecaudo.value.archivo
    });
    newRecaudo.value = { nombre: '', archivo: null };
  }
};

const removeRecaudo = (index: number) => {
  props.values.recaudos.splice(index, 1);
};

const showPreview = (recaudo: any) => {
  if (recaudo.archivo.type.startsWith('image/')) {
    const url = URL.createObjectURL(recaudo.archivo);
    previewImage.value = {
      show: true,
      url,
      nombre: recaudo.nombre
    };
  } else {
    console.log(recaudo.archivo);
  }
};

const closePreview = () => {
  if (previewImage.value.url) {
    URL.revokeObjectURL(previewImage.value.url);
  }
  previewImage.value.show = false;
};
</script>

<template>
  <form @submit.prevent="emitForm">
    <div class="grid grid-cols-1 gap-5 my-3">

      <div class="flex items-center gap-4 w-full">
        <input type="text" v-model="newRecaudo.nombre" placeholder="Nombre del recaudo" class="p-2 border rounded">
        <input type="file" accept="application/pdf,image/*" @change="handleFileChange" class="p-2 border rounded">
        <button type="button"
          class="block w-[30%] bg-[#1F52C7] text-white py-2 rounded-full transition-all hover:bg-[#010C41]"
          @click="addRecaudo">
          <font-awesome-icon icon="plus" />
        </button>
      </div>

      <div class="h-[25vh] border-[1px] rounded-2xl overflow-hidden p-4"
        :class="`${values.recaudos.length === 0 ? 'grid place-items-center' : ''}`">
        <div v-if="values.recaudos.length > 0" class="w-full">
          <div v-for="(recaudo, index) in values.recaudos" :key="index"
            class="flex items-center justify-between mb-2 p-2 bg-gray-100 rounded">
            <div>
              <span class="font-medium">{{ recaudo.nombre }}</span> -
              <span class="text-sm text-gray-600 hover:text-blue-600 hover:underline cursor-pointer"
                @click="showPreview(recaudo)">
                {{ recaudo.archivo ? recaudo.archivo.name : recaudo.nombre}}
              </span>
            </div>
            <button type="button" @click="removeRecaudo(index)" class="text-red-500 hover:text-red-700">
              ×
            </button>
          </div>
        </div>

        <p class="text-gray-500" v-if="values.recaudos.length === 0">
          Sin data
        </p>
      </div>
    </div>
    <Controls :step="step" />

    <div v-if="previewImage.show"
      class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[3000] p-4"
      @click.self="closePreview">
      <div class="bg-white rounded-lg max-w-4xl max-h-[90vh] overflow-auto">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-medium">{{ previewImage.nombre }}</h3>
          <button @click="closePreview" class="text-gray-500 hover:text-gray-700">
            <font-awesome-icon icon="times" />
          </button>
        </div>
        <div class="p-4">
          <img :src="previewImage.url" alt="Vista previa" class="max-w-full max-h-[70vh] mx-auto">
        </div>
      </div>
    </div>
  </form>
</template>