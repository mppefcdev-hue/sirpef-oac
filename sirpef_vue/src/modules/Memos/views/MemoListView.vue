<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Http } from '@/utils/Http';
import init from '@/utils/Http/init';
import { alerta } from '@/utils/alert';
import Welcome from "@/components/sirpef/welcome.vue";
import MemoPreview from '../components/MemoPreview.vue';
import ModalInfo from "../../FeDeVida/components/modalInfo.vue";

const http = new Http(init);
const router = useRouter();

const memos = ref([]);
const loading = ref(false);
const memoToPrint = ref(null);
const casePersona_id = ref(null);
const search = ref('');

const fetchMemos = async () => {
  loading.value = true;
  try {
    const response = await http.get('api/oac/memorandum');
    if (response.data && response.data.success) {
      memos.value = response.data.data;
    }
  } catch (error) {
    console.error("Error al cargar los memorándums:", error);
  } finally {
    loading.value = false;
  }
};

const filteredMemos = computed(() => {
  if (!memos.value) return [];
  if (!search.value) return memos.value;
  const s = search.value.toLowerCase();
  return memos.value.filter(m => 
    (m.codigo?.toLowerCase().includes(s)) || 
    (m.tabla?.solicitante?.toLowerCase().includes(s)) ||
    (m.asunto?.toLowerCase().includes(s))
  );
});

const editMemo = (memo) => {
  localStorage.setItem('editing_memo', JSON.stringify(memo));
  router.push({ path: '/oac/memos/form', query: { id: memo.id } });
};

const deleteMemo = async (id) => {
  const result = await alerta('Confirmar', '¿Está seguro de eliminar este memorándum? Esta acción no se puede deshacer.', 'question');
  if (result.isConfirmed) {
    try {
      const response = await http.delete(`api/oac/memorandum/${id}`);
      if (response.data && response.data.success) {
        alerta('Eliminado', 'El memorándum ha sido eliminado exitosamente', 'success');
        fetchMemos();
      }
    } catch (error) {
      alerta('Error', 'Hubo un error al intentar eliminar el registro', 'error');
    }
  }
};

const printMemo = async (memo) => {
  memoToPrint.value = memo;
  await nextTick();
  window.print();
};

onMounted(() => {
  fetchMemos();
});
</script>

<template>
  <Welcome title="Listado de Memorándums" subtitle="Gestión y control de documentos generados">
    <div class="flex justify-center">
        <router-link to="/oac/memos/form" class="bg-[#ECA008] hover:bg-[#010c41] text-white font-bold py-3 px-8 rounded-3xl transition-all shadow-lg flex items-center">
          <font-awesome-icon icon="plus" class="mr-2" />
          Nuevo Memorándum
        </router-link>
    </div>
  </Welcome>

  <ModalInfo v-if="casePersona_id" :casePersona_id="casePersona_id" @close="casePersona_id = null" />

  <div class="w-[90%] mx-auto mt-10">
    <div class="mb-6 flex justify-between items-center">
      <div class="flex items-center w-full max-w-md">
        <input class="miinput w-full" type="text" v-model="search" placeholder="Buscar por código, solicitante..." />
      </div>
    </div>

    <div class="table-data__wrapper">
      <table class="table-data">
        <thead>
          <tr>
            <th class="text-center">Código</th>
            <th class="text-center">Fecha</th>
            <th class="text-center">Solicitante</th>
            <th class="text-center">Asunto</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="memo in filteredMemos" :key="memo.id">
            <td class="text-center font-bold">{{ memo.codigo }}</td>
            <td class="text-center">{{ memo.created_at }}</td>
            <td class="text-center">
              <div class="font-bold">{{ memo.tabla.solicitante }}</div>
              <div class="text-xs text-gray-400">CI: {{ memo.tabla.cedula }}</div>
            </td>
            <td class="text-center max-w-xs truncate" :title="memo.asunto">{{ memo.asunto }}</td>
            <td class="text-center">
              <div class="flex justify-center items-center space-x-2">
                <button v-if="memo.registro_id" title="Ver Caso" class="bg-[#ECA008] text-white p-2 rounded-2xl w-10 h-10 hover:bg-[#010c41] transition-colors" @click="casePersona_id = memo.registro_id">
                  <font-awesome-icon icon="eye" />
                </button>
                <button title="Editar" class="bg-[#2052C7] text-white p-2 rounded-2xl w-10 h-10 hover:bg-[#010c41] transition-colors" @click="editMemo(memo)">
                  <font-awesome-icon icon="pen-to-square" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="loading">
            <td colspan="5" class="p-10 text-center text-gray-400">Cargando registros...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Hidden Printable Area -->
  <div v-if="memoToPrint" id="memo-printable-hidden" class="hidden">
     <MemoPreview :data="memoToPrint" />
  </div>
</template>

<style scoped>
.panel {
  background: white;
  padding: 2rem;
  border-radius: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

@media print {
  body * {
    visibility: hidden;
  }
  #memo-printable-hidden, #memo-printable-hidden * {
    visibility: visible;
  }
  #memo-printable-hidden {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    display: block !important;
  }
}
</style>
