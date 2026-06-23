<script lang="ts" setup>
import convertDateISO from '@/utils/convertDateISO';
import { onMounted, onUnmounted } from 'vue';
import useModalInfo from '../composables/useModalInfo';
import CardHistory from './cardHistory.vue';
import { useRoute, useRouter } from 'vue-router';

const {
    caseData,
    store,
    showTypes,
    showHistory,
    formStatus,
    status,
    getStatus,
    getInfo,
    createPunto,
    sendToCheck,
    changeStatus,
    editPunto
} = useModalInfo()


const props = defineProps<{
    casePersona_id: any
}>()

const $emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()

const hidden = (e?: PointerEvent) => {
    if(route.name == 'case-fedevida') router.back()

    const target = e.target as HTMLElement
    if (e && target.tagName === "P") {
        $emit('close')
    }
}


const openModalStatus = async () => {
    getStatus()
    showTypes.value = true
}


onMounted(() => {

    if (props.casePersona_id || route.params.casePersona_id) {
        getInfo(props.casePersona_id || route.params.casePersona_id)
    }
    document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
    document.body.style.overflow = 'auto'
})

const openRecaudo = (reca: any) => {
    window.open(reca.path, '_blank', 'noopener, noreferrer');
}

</script>

<template>
    <section
        class="fixed inset-0 flex justify-center overflow-hidden items-center z-[200] cursor-pointer md:rounded-2xl"
        @click="hidden">

        <CardHistory :idRegister="caseData.registro_id" v-if="showHistory" @close="showHistory = false" @click.stop />

        <article class="!fixed !inset-0 bg-black bg-opacity-50 flex items-center justify-center h-screen z-50"
            v-if="showTypes" @click="showTypes = false">
            <div @click.stop
                class="bg-white cursor-default rounded-lg shadow-xl p-6 w-full max-w-sm mx-auto transform transition-all modal-enter-from modal-leave-to">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Realizar Cambio</h2>

                <div class="mb-4">
                    <label for="opcion" class="block text-gray-700 text-sm font-bold mb-2">Selecciona una
                        opción:</label>
                    <select id="opcion" name="opcion" v-model="formStatus.status"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Seleccionar</option>
                        <option value="En Tramite">En Tramite</option>
                        <option value="Orientado">Orientado</option>
                        <option value="Resultado Directo">Resultado Directo</option>
                        <option value="Remitido a Otro">Remitido a Otro</option>
                        <option value="Cerrado">Cerrado</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="opcion" class="block text-gray-700 text-sm font-bold mb-2">Selecciona una opción
                        (status):</label>
                    <select id="opcion2" name="opcion2" v-model="formStatus.status2" v-if="status.length > 0"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Seleccionar</option>
                        <option :value="statu.id" v-for="statu in status">{{ statu.nombre_estatus }}</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="observacion" class="block text-gray-700 text-sm font-bold mb-2">Observación:</label>
                    <textarea id="observacion" name="observacion" rows="4" v-model="formStatus.observacion"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline resize-none"
                        placeholder="Escribe tus observaciones aquí..."></textarea>
                </div>

                <div class="flex justify-end space-x-4">
                    <button id="closeModalBtn" @click="showTypes = false"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <button class="bg-[#030C41] hover:bg-[#1F52C7] text-white font-bold py-2 px-4 rounded"
                        @click="changeStatus">
                        Cambiar
                    </button>
                </div>
            </div>
        </article>

        <article id="image"
            class="h-screen block relative overflow-auto justify-between gap-10 shadow col-span-2 fadeout bg-[#061E42] w-full cursor-default md:overflow-hidden xl:flex">

            <p class="absolute right-8 top-32 text-black md:text-white font-semibold text-xl z-[300] cursor-pointer transition-all md:top-4 hover:scale-105"
                @click="$emit('close')">X</p>

            <aside
                class="w-full py-10 relative mb-28 bg-[#fafafa] md:p-5 overflow-auto shadow-xl md:h-full md:py-0 xl:w-[40%]">

                <div class="">

                    <div class="px-4 flex items-center justify-between py-5 sm:px-6">
                        <div class="flex gap-4 justify-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Ficha del caso</h3>
                            <button
                                @click="$router.push({ name: 'fedevidaPresencial', params: { id: props.casePersona_id } })">
                                <font-awesome-icon icon="pen-to-square" />
                            </button>
                            <button @click="showHistory = true">
                                <font-awesome-icon icon="timeline" />
                            </button>
                        </div>

                        <div class="flex items-center justify-between font-bold gap-2"
                            :class="`${caseData?.voto == true ? 'text-green-600' : caseData?.voto == false ? 'text-red-600' : 'text-orange-600'}`">
                            Caso {{ caseData?.voto == true ? 'Atendido' : caseData?.voto == false ? 'por revisar' : 'En tramite' }}
                            <div class="h-5 w-5 rounded-full flex justify-center items-center animation-pulse"
                                :class="`${caseData?.voto == true ? 'bg-green-600' : caseData?.voto == false ? 'bg-red-600' : 'bg-orange-600'}`"
                                :style="`--pulse-color: ${caseData?.voto == true ? '#16a34a79' : caseData?.voto === false ? '#dc262694' : '#d4640891'}`">
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="address-card" />
                                    <p>Cedula</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData?.persona?.cedula || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="id-card-clip" />
                                    <p>Nombre Completo</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData?.persona?.nombre_completo }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="venus-mars" />
                                    <p>Genéro</p>
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 font-bold flex items-center justify-left gap-5 sm:mt-0">
                                    <p>{{ caseData?.persona?.genero }}</p>
                                    <font-awesome-icon title="Femenino" v-if="caseData?.persona?.genero == 'Femenino'"
                                        class="text-pink-600 scale-[1.6]" icon="venus" />
                                    <font-awesome-icon title="Masculino"
                                        v-else-if="caseData?.persona?.genero == 'Masculino'"
                                        class="text-blue-500 scale-[1.6]" icon="mars" />
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="cake-candles" />
                                    <p>Fecha de Nacimiento</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ convertDateISO(caseData?.persona?.fecha_nacimiento) || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon icon="phone" class="scale-[1.3]" />
                                    <p>Celular</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData?.persona?.telefono || 'Sin información' }}
                                </dd>
                            </div>

                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon icon="globe" class="scale-[1.3]" />
                                    <p>Dirección</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ `${caseData?.persona?.estado?.nombre || 'Sin información'} -
                                    ${caseData?.persona?.municipio?.nombre || 'Sin información'} -
                                    ${caseData?.persona?.parroquia?.nombre || 'Sin información'}` || 'Sin información'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="px-4 py-5 text-center sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Información del caso
                        </h3>
                    </div>

                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="file-signature" />
                                    <p>Estado del caso</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData.estatus_caso || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="genderless" />
                                    <p>Tipo de caso</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData.tipo_caso?.tipo || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 my-5 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="file-lines" />
                                    <p>Descripción</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                                    {{ caseData.descripcion || 'Sin información' }}
                                </dd>
                            </div>

                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 my-5 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="file-lines" />
                                    <p>Ultima observación</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                                    {{ caseData.observacion || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="book" />
                                    <p>Punto de cuenta</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ caseData?.punto_cuenta?.numero_punto || 'Sin información' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="px-4 py-5 text-center sm:px-6" v-if="caseData?.proveedores?.length > 0">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Provedores
                        </h3>
                    </div>

                    <div class="border-t border-gray-200 px-4 py-5  sm:p-0" v-if="caseData?.proveedores?.length > 0">
                        <dl class="sm:divide-y sm:divide-gray-200" v-for="prov in caseData?.proveedores">
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="genderless" />
                                    <p>Provedores</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ prov.nombre || 'Sin información' }}
                                </dd>
                            </div>
                            <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-800 flex items-center justify-left gap-5">
                                    <font-awesome-icon class="scale-[1.3]" icon="dollar-sign" />
                                    <p>Monto</p>
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0">
                                    {{ prov.monto || 'Sin información' }}
                                </dd>
                            </div>
                        </dl>
                    </div>



                </div>

                <nav class="flex justify-between items-center my-10">
                    <button v-if="!store?.authUser?.isAdmin && caseData.voto == null" @click="sendToCheck"
                        class="w-[60%] py-5 mx-auto bg-[#010c41] block rounded-md font-bold hover:bg-[#1F52C7] text-white cursor-pointer transition-all">
                        Enviar a revisión
                        <font-awesome-icon icon="paper-plane" class="ml-1" />
                    </button>
                    <button v-else-if="store?.authUser?.isAdmin" @click="openModalStatus"
                        class="w-[60%] py-5 mx-auto bg-[#010c41] block rounded-md font-bold hover:bg-[#1F52C7] text-white cursor-pointer transition-all">
                        Decisión del caso
                        <font-awesome-icon icon="gavel" class="ml-1" />
                    </button>

                    <button v-if="store?.authUser?.isAdmin && caseData?.punto_cuenta?.estatus == true"
                        @click="editPunto"
                        class="w-[20%] py-5 mx-auto bg-[#010c41] block rounded-md font-bold hover:bg-[#1F52C7] text-white cursor-pointer transition-all">
                        PDC
                        <font-awesome-icon icon="pen-to-square" class="ml-1" />
                    </button>
                </nav>

            </aside>

            <div class="w-full h-[100vh] relative md:p-5 xl:w-[70%] md:h-auto">
                <div class="px-4 py-5 text-center sm:px-6">
                    <h3 class="text-2xl font-bold text-white">
                        Recaudos
                    </h3>
                    <p class="mt-1 text-lg text-white gap-5">
                        Recaudos de {{ caseData?.persona?.nombre_completo }}
                    </p>
                </div>
                <div class="relative flex w-full gap-5 overflow-y-scroll text-gray-700 rounded-xl md:h-[70%]">

                    <div @click="openRecaudo(reca)" v-for="reca in caseData?.recaudos"
                        v-if="caseData?.recaudos?.length > 0"
                        class="border cursor-pointer w-[200px] h-[300px] border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between shadow-md hover:shadow-2xl transition-shadow duration-300 bg-white">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-medium text-gray-700 capitalize truncate">{{ reca.nombre }}</h3>
                        </div>

                        <div class="flex justify-center items-center p-4 min-h-[150px]">
                            <img v-if="reca.mime_type?.toLowerCase().includes('image')" :src="reca.path"
                                :alt="reca.nombre" class="max-w-full max-h-[180px] object-contain rounded">
                            <div v-else class="text-5xl text-gray-500">
                                📄
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 text-center">
                            <button
                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors duration-200">
                                <font-awesome-icon icon="eye" />
                                Ver
                            </button>
                        </div>
                    </div>

                </div>

                <nav class="w-full rounded-full bottom-10 flex items-center justify-left absolute mx-auto py-2">

                    <div @click="createPunto"
                        class="border cursor-pointer w-[200px] h-[300px] border-gray-200 rounded-lg overflow-hidden flex flex-col justify-between shadow-md hover:shadow-2xl transition-shadow duration-300 bg-white">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-medium text-gray-700 capitalize truncate">Punto de cuenta</h3>
                        </div>

                        <div class="flex justify-center items-center p-4 overflow-hidden min-h-[150px]">
                            <img class="max-w-full max-h-[180px] object-contain rounded"
                                :src="`/${caseData.punto_cuenta && caseData?.punto_cuenta?.estatus == true ? 'punto-cuenta.png' : 'form-punto.png'}`"
                                alt="" />
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 text-center">
                            <button
                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors duration-200">
                                <font-awesome-icon icon="eye" />
                                {{ caseData.punto_cuenta && caseData?.punto_cuenta?.estatus == true ? 'Ver punto de cuenta' : 'Punto de cuenta' }}
                            </button>
                        </div>
                    </div>

                </nav>
            </div>

        </article>
    </section>
</template>

<style scoped>
.animation-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(0.8);
        box-shadow: 0 0 0 0 var(--pulse-color);
    }

    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px var(--pulse-color);
    }

    100% {
        transform: scale(0.8);
    }
}

#image {
    background-position: right bottom 0px;
    background-image: url(/Bg-welcome.svg) !important;
    background-repeat: no-repeat;
    background-size: cover;
}
</style>