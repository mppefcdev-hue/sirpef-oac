<script setup lang="ts">
import Controls from '@/components/sirpef/form/Controls.vue';
import { onMounted, ref, computed, watch } from 'vue';
import Http from '@/utils/Http';

const props = defineProps<{
    FormEvent: (event: Event, menus: any[]) => void,
    step: number,
    values: any[],
    roleId?: string | number,
}>();

interface MenuItem {
    id: number;
    title: string;
    path: string;
    menu_id: number | null;
    alias?: string;
    icon?: string;
}

interface ModuleGroup {
    id: number;
    title: string;
    icon?: string;
    children: MenuItem[];
}

const loading = ref(true);
const allMenus = ref<MenuItem[]>([]);
const roles = ref<any[]>([]);
const searchQuery = ref('');
const selectedIds = ref<number[]>([]);

// Normalizar valores entrantes (pueden ser objetos { id } o números)
const initializeSelected = () => {
    if (!props.values || props.values.length === 0) {
        selectedIds.value = [];
        return;
    }
    selectedIds.value = props.values
        .map((v: any) => (typeof v === 'object' && v !== null ? v.id : Number(v)))
        .filter((id: number) => !isNaN(id) && id > 0);
};

// Cargar menús y roles de la API
const loadData = async () => {
    loading.value = true;
    try {
        const [menusRes, rolesRes] = await Promise.all([
            Http.get('api/menus'),
            Http.get('api/roles/helperTables')
        ]);

        const rawMenus: MenuItem[] = (menusRes.data?.data || []).map((e: any) => ({
            id: Number(e.id),
            title: e.title,
            path: e.path,
            menu_id: e.menu_id ? Number(e.menu_id) : null,
            alias: e.alias,
            icon: e.icon
        }));

        allMenus.value = rawMenus;
        roles.value = rolesRes.data?.roles || [];
        initializeSelected();
    } catch (err) {
        console.error('Error cargando menús:', err);
    } finally {
        loading.value = false;
    }
};

watch(() => props.values, () => {
    initializeSelected();
}, { deep: true });

onMounted(async () => {
    await loadData();
});

// Estructurar módulos con sus vistas hijas
const modules = computed<ModuleGroup[]>(() => {
    const parents = allMenus.value.filter(m => m.menu_id === null);
    const groups: ModuleGroup[] = [];

    parents.forEach(p => {
        const children = allMenus.value.filter(m => m.menu_id === p.id);
        groups.push({
            id: p.id,
            title: p.title,
            icon: p.icon,
            children: children
        });
    });

    return groups;
});

// Filtrado por buscador
const filteredModules = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return modules.value;

    return modules.value.map(group => {
        const moduleMatches = group.title.toLowerCase().includes(q);
        const matchedChildren = group.children.filter(child => 
            child.title.toLowerCase().includes(q) ||
            getViewLabel(child).toLowerCase().includes(q) ||
            getViewBadge(child).toLowerCase().includes(q)
        );

        if (moduleMatches) {
            return group;
        }

        return {
            ...group,
            children: matchedChildren
        };
    }).filter(group => group.children.length > 0 || group.title.toLowerCase().includes(q));
});

// Etiquetas y badges contextuales
const getViewLabel = (item: MenuItem): string => {
    if (item.id === 35 || item.path === 'CasesAdminFormPaso1' || item.title?.toLowerCase().includes('paso 1')) {
        return 'Formulario (Paso 1)';
    }
    if (item.id === 39 || item.path === 'CasesAdminFormPaso2' || item.title?.toLowerCase().includes('paso 2')) {
        return 'Formulario (Paso 2 y 3)';
    }
    if (item.id === 34 || item.path === 'casesAdminIndex') {
        return 'Pagos (Listado)';
    }
    if (item.id === 38 || item.path === 'casesAdminCuotas') {
        return 'Cuota de Compromiso';
    }
    return item.title;
};

const getViewBadge = (item: MenuItem): string => {
    if (item.id === 35 || item.path === 'CasesAdminFormPaso1' || item.title?.toLowerCase().includes('paso 1')) {
        return 'Llenado inicial de datos (Rol User)';
    }
    if (item.id === 39 || item.path === 'CasesAdminFormPaso2' || item.title?.toLowerCase().includes('paso 2')) {
        return 'Facturas y recaudos (Rol Analista)';
    }
    if (item.id === 34) {
        return 'Tabla y control de pagos';
    }
    if (item.id === 38) {
        return 'Presupuesto mensual';
    }
    return '';
};

// Manejo de selecciones
const isSelected = (id: number): boolean => {
    return selectedIds.value.includes(id);
};

const toggleItem = (id: number) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx !== -1) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const isModuleAllSelected = (group: ModuleGroup): boolean => {
    if (group.children.length === 0) return isSelected(group.id);
    return group.children.every(c => isSelected(c.id));
};

const toggleModule = (group: ModuleGroup) => {
    if (isModuleAllSelected(group)) {
        // Deseleccionar todas las vistas del módulo
        group.children.forEach(c => {
            const idx = selectedIds.value.indexOf(c.id);
            if (idx !== -1) selectedIds.value.splice(idx, 1);
        });
        const pIdx = selectedIds.value.indexOf(group.id);
        if (pIdx !== -1) selectedIds.value.splice(pIdx, 1);
    } else {
        // Seleccionar todas las vistas del módulo y el módulo padre
        group.children.forEach(c => {
            if (!selectedIds.value.includes(c.id)) {
                selectedIds.value.push(c.id);
            }
        });
        if (!selectedIds.value.includes(group.id)) {
            selectedIds.value.push(group.id);
        }
    }
};

// Cargar vistas por defecto del rol seleccionado
const loadRoleDefaults = () => {
    if (!props.roleId || props.roleId === '0') return;
    const currentRole = roles.value.find((r: any) => r.id == props.roleId);
    if (currentRole && currentRole.menu_ids) {
        selectedIds.value = [...currentRole.menu_ids];
    }
};

const roleName = computed(() => {
    if (!props.roleId || props.roleId === '0') return '';
    const r = roles.value.find((role: any) => role.id == props.roleId);
    return r ? r.name : '';
});

// Enviar formulario al siguiente paso
const handleSubmit = (e: Event) => {
    // Asegurar que si hay hijos seleccionados, el ID de su padre esté presente
    const finalSet = new Set<number>(selectedIds.value);
    selectedIds.value.forEach(id => {
        const item = allMenus.value.find(m => m.id === id);
        if (item && item.menu_id) {
            finalSet.add(item.menu_id);
        }
    });

    const itemsToSend = Array.from(finalSet).map(id => {
        const m = allMenus.value.find(menu => menu.id === id);
        return {
            id,
            nombre: m ? m.title : ''
        };
    });

    props.FormEvent(e, itemsToSend);
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <input type="hidden" name="menus" value="ok" />

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-[#010c41]">Asignación de Vistas y Permisos</h3>
                <p class="text-sm text-gray-600">
                    Seleccione las vistas y ventanas a las que este usuario tendrá acceso de lectura y edición.
                </p>
                <div v-if="roleName" class="mt-1 flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-semibold px-2 py-0.5 rounded">
                        Rol asignado: {{ roleName }}
                    </span>
                    <button 
                        type="button" 
                        @click="loadRoleDefaults" 
                        class="text-xs text-blue-600 hover:underline cursor-pointer"
                        title="Restablece las vistas a las predeterminadas de este rol"
                    >
                        ↻ Cargar vistas por defecto del rol
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium bg-gray-100 border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg">
                    {{ selectedIds.length }} vistas seleccionadas
                </span>
            </div>
        </div>

        <!-- Buscador rápido -->
        <div class="mb-5">
            <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Buscar módulo o vista (ej: Formulario, Pagos, Usuarios)..." 
                class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg p-2.5 text-sm focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        <!-- Contenedor con scroll para los módulos -->
        <div v-if="loading" class="text-center py-10 text-gray-500">
            Cargando vistas disponibles...
        </div>

        <div v-else class="space-y-4 max-h-[480px] overflow-y-auto pr-2 mb-6 border border-gray-200 rounded-xl p-4 bg-gray-50/50">
            <div 
                v-for="group in filteredModules" 
                :key="group.id" 
                class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow transition-shadow"
            >
                <!-- Cabecera del Módulo -->
                <div class="bg-gray-100/80 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#010c41] uppercase tracking-wide text-sm">
                            {{ group.title }}
                        </span>
                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">
                            {{ group.children.length }} vistas
                        </span>
                    </div>

                    <button 
                        type="button" 
                        @click="toggleModule(group)" 
                        class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors"
                    >
                        {{ isModuleAllSelected(group) ? 'Deseleccionar todas' : 'Seleccionar todas' }}
                    </button>
                </div>

                <!-- Lista de Vistas del Módulo -->
                <div class="p-4">
                    <div v-if="group.children.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label 
                            v-for="child in group.children" 
                            :key="child.id" 
                            class="flex items-start gap-3 p-3 rounded-lg border transition-all cursor-pointer select-none"
                            :class="isSelected(child.id) ? 'bg-blue-50/60 border-blue-300 shadow-xs' : 'bg-white border-gray-200 hover:bg-gray-50'"
                        >
                            <input 
                                type="checkbox" 
                                :checked="isSelected(child.id)" 
                                @change="toggleItem(child.id)"
                                class="mt-1 h-4 w-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300"
                            />
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold capitalize" :class="isSelected(child.id) ? 'text-blue-900' : 'text-gray-800'">
                                        {{ getViewLabel(child) }}
                                    </span>
                                </div>
                                <span v-if="getViewBadge(child)" class="text-xs text-gray-500 block mt-0.5">
                                    {{ getViewBadge(child) }}
                                </span>
                            </div>
                        </label>
                    </div>

                    <div v-else class="text-sm text-gray-400 italic">
                        Este módulo no tiene sub-vistas configurables directamente.
                    </div>
                </div>
            </div>

            <div v-if="filteredModules.length === 0" class="text-center py-8 text-gray-500 text-sm">
                No se encontraron vistas que coincidan con "{{ searchQuery }}"
            </div>
        </div>

        <Controls :step="step" />
    </form>
</template>

<style scoped>
/* Scrollbar estilizado para la lista */
div::-webkit-scrollbar {
    width: 6px;
}
div::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
div::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
div::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>