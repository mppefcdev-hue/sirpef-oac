<script setup lang="ts">
// @ts-nocheck
import Welcome from "@/components/sirpef/welcome.vue";
import { ref, onMounted } from "vue";
import { useAuthStore } from '@/modules/Auth/stores';
import { useRouter } from 'vue-router';
import Http from "@/utils/Http";
import { alerta } from "@/utils/alert";
import Swal from 'sweetalert2';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import Buttons from 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';

DataTable.use(DataTablesCore);
DataTable.use(Buttons);

const store = useAuthStore()
const router = useRouter()

// Cuotas
const cuotas = ref([])
const loadingCuotas = ref(false)
const ano = ref(new Date().getFullYear())
const mes = ref(new Date().getMonth() + 1)
const monto = ref(0)

const meses = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]

const formatCurrency = (value: any) => {
  if (value === undefined || value === null || value === '') return '0,00';
  const number = typeof value === 'string' ? parseFloat(value) : value;
  if (isNaN(number)) return '0,00';
  return number.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const loadCuotas = async () => {
  loadingCuotas.value = true;
  try {
    const res = await Http.get('/api/oac/cuotas');
    const data = res.data.data.map((item: any) => ({
      ...item,
      mes_nombre: meses[item.mes - 1]
    }));
    // Sort current month first / descending
    cuotas.value = data.sort((a: any, b: any) => b.ano !== a.ano ? b.ano - a.ano : b.mes - a.mes);
  } catch (error) {
    console.error(error);
  } finally {
    loadingCuotas.value = false;
  }
};

onMounted(() => {
  const user = store.authUser;
  if (!user || ![1, 2].includes(Number(user.role_id))) {
    alerta("Acceso Denegado", "No tienes permisos para acceder a esta sección.", "error");
    router.push('/cases');
    return;
  }
  loadCuotas();
});

const saveCuota = async () => {
  if (monto.value === null || monto.value === undefined || monto.value < 0) {
    alerta("Error", "Por favor ingrese un monto válido mayor o igual a 0.", "error");
    return;
  }
  try {
    const res = await Http.post('/api/oac/cuotas', {
      ano: ano.value,
      mes: mes.value,
      monto: monto.value
    });
    if (res.data.success) {
      alerta("Éxito", res.data.message, "success");
      monto.value = 0;
      loadCuotas();
    }
  } catch (error: any) {
    console.error(error);
    alerta("Error", error.response?.data?.message || "Ocurrió un error al guardar la cuota", "error");
  }
};

const updateCuota = async () => {
  if (monto.value === null || monto.value === undefined || monto.value < 0) {
    alerta("Error", "Por favor ingrese un monto válido mayor o igual a 0.", "error");
    return;
  }
  try {
    const res = await Http.put('/api/oac/cuotas', {
      ano: ano.value,
      mes: mes.value,
      monto: monto.value
    });
    if (res.data.success) {
      alerta("Éxito", res.data.message, "success");
      monto.value = 0;
      loadCuotas();
    }
  } catch (error: any) {
    console.error(error);
    alerta("Error", error.response?.data?.message || "Ocurrió un error al modificar la cuota", "error");
  }
};

const deleteCuota = async (id: number) => {
  if (!confirm('¿Está seguro de que desea eliminar esta cuota?')) return;
  try {
    const res = await Http.delete(`/api/oac/cuotas/${id}`);
    if (res.data.success) {
      alerta("Éxito", res.data.message, "success");
      loadCuotas();
    }
  } catch (error: any) {
    console.error(error);
    alerta("Error", error.response?.data?.message || "Ocurrió un error al eliminar", "error");
  }
};

const handleTableClick = async (e: MouseEvent) => {
  const target = (e.target as HTMLElement).closest('button');
  if (!target) return;
  
  const id = target.getAttribute('data-id');
  if (!id) return;
  
  const rowId = parseInt(id);
  const row = cuotas.value.find((c: any) => c.id === rowId);
  if (!row) return;

  if (target.classList.contains('btn-delete-quota')) {
    deleteCuota(rowId);
  } else if (target.classList.contains('btn-edit-quota')) {
    const { value: nuevoMonto } = await Swal.fire({
      title: 'Modificar Cuota',
      input: 'number',
      inputLabel: `Nuevo monto para ${row.mes_nombre} ${row.ano}`,
      inputValue: row.monto_limite,
      showCancelButton: true,
      confirmButtonText: 'Modificar',
      cancelButtonText: 'Cancelar',
      inputValidator: (value) => {
        if (!value || parseFloat(value) < 0) return 'Ingrese un monto válido';
      }
    });
    if (nuevoMonto) {
      monto.value = parseFloat(nuevoMonto);
      ano.value = row.ano;
      mes.value = row.mes;
      updateCuota();
    }
  } else if (target.classList.contains('btn-extra-quota')) {
    const { value: montoExtra } = await Swal.fire({
      title: 'Agregar Cuota Extra',
      input: 'number',
      inputLabel: `Monto extra para ${row.mes_nombre} ${row.ano}`,
      showCancelButton: true,
      confirmButtonText: 'Agregar',
      cancelButtonText: 'Cancelar',
      inputValidator: (value) => {
        if (!value || parseFloat(value) <= 0) return 'Ingrese un monto válido mayor a 0';
      }
    });
    if (montoExtra) {
      try {
        const res = await Http.post('/api/oac/cuotas/extra', {
          cuota_id: row.id,
          monto: parseFloat(montoExtra)
        });
        if (res.data.success) {
          alerta("Éxito", "Cuota extra agregada correctamente", "success");
          loadCuotas();
        }
      } catch (e: any) {
        console.error(e);
        alerta("Error", e.response?.data?.message || "Error al agregar cuota extra", "error");
      }
    }
  } else if (target.classList.contains('btn-detail-quota')) {
    Swal.fire({
      title: `Detalles de ${row.mes_nombre} ${row.ano}`,
      html: `
        <div class="text-left space-y-2 mt-4">
          <p><strong>Cuota Inicial:</strong> Bs. ${formatCurrency(row.monto_limite)}</p>
          <p><strong>Acumulado Anterior:</strong> Bs. ${formatCurrency(row.monto_acumulado_anterior)}</p>
          <p><strong>Cuota Extra:</strong> Bs. ${formatCurrency(row.monto_extra || 0)}</p>
          <p class="border-t pt-2"><strong>Límite Total:</strong> Bs. ${formatCurrency(row.monto_limite_total)}</p>
          <p><strong>Monto Ejecutado:</strong> Bs. ${formatCurrency(row.monto_ejecutado)}</p>
          <p class="text-lg mt-2 ${parseFloat(row.monto_disponible) >= 0 ? 'text-green-600' : 'text-red-600'}"><strong>Monto Disponible:</strong> Bs. ${formatCurrency(row.monto_disponible)}</p>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Cerrar'
    });
  }
};

const columns = [
  { data: 'mes_nombre', title: 'Mes' },
  { data: 'ano', title: 'Año' },
  { 
    data: 'monto_limite', 
    title: 'Cuota Inicial',
    render: (data) => formatCurrency(data)
  },
  { 
    data: 'monto_acumulado_anterior', 
    title: 'Acumulado Anterior',
    render: (data) => formatCurrency(data)
  },
  { 
    data: 'monto_limite_total', 
    title: 'Límite Total',
    render: (data) => formatCurrency(data)
  },
  { 
    data: 'monto_ejecutado', 
    title: 'Monto Ejecutado',
    render: (data) => formatCurrency(data)
  },
  { 
    data: 'monto_disponible', 
    title: 'Monto Disponible',
    render: (data) => {
      const color = parseFloat(data) >= 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
      return `<span class="${color}">${formatCurrency(data)}</span>`;
    }
  },
  {
    data: 'updated_at',
    title: 'Última Modificación',
    render: (data, type, row) => {
      if (!data || !row.created_at) return '-';
      const created = new Date(row.created_at);
      const updated = new Date(data);
      if (Math.abs(updated.getTime() - created.getTime()) > 2000) {
        const pad = (n: number) => n.toString().padStart(2, '0');
        const day = pad(updated.getDate());
        const month = pad(updated.getMonth() + 1);
        const year = updated.getFullYear();
        const hours = pad(updated.getHours());
        const minutes = pad(updated.getMinutes());
        const seconds = pad(updated.getSeconds());
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
      }
      return 'Sin modificaciones';
    }
  }
];

if (store.authUser?.role_id === 1 || store.authUser?.role_id === 2) {
  columns.push({
    data: null,
    title: 'Acciones',
    render: (data, type, row) => {
      return `
        <div class="flex gap-3 justify-center items-center">
          <button class="btn-detail-quota text-blue-600 hover:text-blue-800 hover:scale-110 font-bold transition-all text-sm" data-id="${row.id}" title="Detalles">Detalles</button>
          <button class="btn-edit-quota text-[#eca008] hover:text-[#d68f07] hover:scale-110 font-bold transition-all text-sm" data-id="${row.id}" title="Modificar">Actualizar</button>
          <button class="btn-delete-quota text-red-600 hover:text-red-800 hover:scale-110 transition-all text-sm flex items-center gap-1" data-id="${row.id}" title="Eliminar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      `;
    }
  });
}

const dtOptions = {
  order: [], // Previene el ordenamiento por defecto para respetar el orden del array
  language: {
    search: "Buscar",
    info: "Mostrando del _START_ a _END_ de _TOTAL_ registros",
    zeroRecords: "No se encuentran resultados",
  },
  dom: 'Bfrtip',
  buttons: [
    {
      extend: 'csv',
      text: 'Exportar a CSV',
      filename: "cuotas_compromiso",
      exportOptions: {
        columns: [0, 1, 2, 3, 4, 5, 6, 7]
      }
    }
  ]
};

</script>

<template>
  <Welcome 
    title="Cuotas de Compromiso" 
    subtitle="Configuración y control de los presupuestos mensuales de compromiso disponible."
  />

  <div class="col-start-2 col-end-4 mx-auto w-[90%] panel mt-6">
    
    <!-- Formulario para cargar/actualizar cuota -->
    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-6">
      <h3 class="text-base font-bold text-gray-900 mb-4">
        Cargar / Actualizar Cuota Mensual
      </h3>
      <form @submit.prevent="saveCuota" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider ml-1 mb-1">Año</label>
          <input type="number" v-model="ano" min="2020" max="2100" class="w-full bg-white border border-gray-300 text-gray-900 p-3 rounded-lg focus:outline-none focus:border-[#eca008]" required />
        </div>
        
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider ml-1 mb-1">Mes</label>
          <select v-model="mes" class="w-full bg-white border border-gray-300 text-gray-900 p-3 rounded-lg focus:outline-none focus:border-[#eca008]" required>
            <option v-for="(mName, index) in meses" :key="index" :value="index + 1">{{ mName }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider ml-1 mb-1">Monto Límite (Bs.)</label>
          <input type="number" step="0.01" min="0" placeholder="0.00" v-model="monto" class="w-full bg-white border border-gray-300 text-gray-900 p-3 rounded-lg focus:outline-none focus:border-[#eca008]" required />
        </div>

        <div class="flex flex-col gap-2">
          <button type="submit" class="w-full bg-[#eca008] hover:bg-[#d68f07] text-white font-bold py-3 px-6 rounded-lg transition duration-150 focus:outline-none">
            Guardar Cuota
          </button>
        </div>
      </form>
    </div>

    <!-- Historial de Cuotas con DataTables -->
    <div id="Tbl_Cuotas" class="w-full" @click="handleTableClick">
      <div v-if="loadingCuotas" class="text-center py-10 text-gray-500">
        Cargando cuotas...
      </div>
      <DataTable 
        v-else 
        :columns="columns" 
        :data="cuotas" 
        :options="dtOptions" 
        id="table"
      >
        <thead>
          <tr>
            <th>Mes</th>
            <th>Año</th>
            <th>Cuota Inicial</th>
            <th>Acumulado Anterior</th>
            <th>Límite Total</th>
            <th>Monto Ejecutado</th>
            <th>Monto Disponible</th>
            <th>Última Modificación</th>
            <th v-if="store.authUser?.role_id === 1">Acciones</th>
          </tr>
        </thead>
      </DataTable>
    </div>

  </div>
</template>

<style scoped>
.results {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 20px;
  margin: 30px auto;
}
</style>

<style>
/* Estilos globales para la tabla de cuotas y datatables */
#Tbl_Cuotas .dt-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  padding-bottom: 20px;
  overflow: auto;
}

#Tbl_Cuotas .dt-container div:first-child {
  grid-column: 1/2;
}

#Tbl_Cuotas .dt-search label {
  font-size: 18px;
  margin: 10px auto;
  display: block;
}

#Tbl_Cuotas .dt-search input {
  border-radius: 20px;
}

#Tbl_Cuotas .dt-buttons button {
  background-color: #ECA008;
  color: white;
  width: 130px;
  height: 50px;
  border-radius: 20px;
  transition: ease .3s;
}

#Tbl_Cuotas .dt-buttons button:hover {
  background-color: #010c41;
}

#Tbl_Cuotas .dt-container > div:nth-child(1) {
  grid-column: 2/-1;
  grid-row: 1/2;
  display: flex;
  align-items: flex-end;
  justify-content: right;
}

#Tbl_Cuotas table {
  grid-column: 1 / -1;
  width: 100% !important;
}

#Tbl_Cuotas tr {
  background-color: white;
  color: black;
  height: 16px;
  padding: 10px;
}

#Tbl_Cuotas td {
  padding: 10px;
}

#Tbl_Cuotas th {
  background-color: #010c41;
  font-weight: bold;
  color: white;
  margin-top: 20px;
  padding: 20px;
}

#Tbl_Cuotas .dt-info {
  grid-column: 1/2;
  grid-row: 3/4;
}

#Tbl_Cuotas .dt-paging {
  grid-column: 2/-1;
  grid-row: 3/4;
  display: flex;
  justify-content: right;
}

#Tbl_Cuotas .dt-paging button {
  border: 1px #010c4160 solid;
  width: 40px;
  height: 40px;
  transition: .3s ease;
  margin-left: 2px;
  margin-right: 2px;
  background-color: white;
}

#Tbl_Cuotas .dt-paging button.current, #Tbl_Cuotas .dt-paging button:hover {
  background-color: #010c41;
  color: white;
  border: #010c41;
  scale: 1.2;
}

@media (max-width: 900px) {
  #Tbl_Cuotas .dt-container {
    display: block;
  }
  #Tbl_Cuotas .dt-container div {
    margin: 10px auto;
  }
}

/* Custom override to make the inputs responsive by bypassing the global 500px width constraint */
.panel form input {
  width: 100% !important;
}
</style>
