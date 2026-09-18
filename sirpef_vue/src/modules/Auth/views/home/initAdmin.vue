<script setup lang="ts">
import { onMounted, ref} from 'vue'
import useDashboard from '@/composables/useDashboard';
import RankingCard from "@/components/Votos/cardRanking.vue"
import Participacion from "@/components/sirpef/participacion.vue"
import BannerTitle from "@/components/sirpef/bannerTitle.vue"
import Loader from "@/components/Votos/loader.vue"

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineController,
  LineElement
} from 'chart.js'
import { Bar, Doughnut} from 'vue-chartjs'
import Datalabels from 'chartjs-plugin-datalabels';
import { useAuthStore } from "../../stores"

import TableDashToSee from '@/components/Votos/TableDashToSee.vue';
import FilteredDashboard from '../../components/sirpef/FilteredDashboard.vue';
import Graph_edades from '../../components/sirpef/Graph_edades.vue';
import Welcome from '@/components/sirpef/welcome.vue';
import { useEventsName } from '@/stores/nameEvent';
import router from '@/router';
import ButtonAction from '@/components/ButtonAction.vue';
import { alerta } from '@/utils/alert';
import CardsFedeVida from '../../../FeDeVida/components/cardsFedeVida.vue';
import GraficaSolicitudes from '../../components/GraficaSolicitudes.vue';


ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement, PointElement, LineController, LineElement, Datalabels)

const { 
  DatosResumen, 
  PieData, 
  Unidades_Ascritas, 
  PostUnidAsc, 
  Info, 
  PiePersonas, 
  Top_Unidades, 
  DataFiltered,
  FilterPersonas,
  PieSex,
  ChangueSexData,
  TableAges,
  DonutSex,
  enlaces,
  graphToSee,
  ChangueDashboard,
  SetDates,
  typesCases,
  solicitudesData,
  setTypeCase,
  redirectToStatic
} = useDashboard()


const data_labels = {
  color: 'black',
  align: 'top',
  font: {
    size: 16
  },
  anchor: 'end',
}

const store = useAuthStore()
const NameEvent = useEventsName()

const SectionFloat = ref(false)

const hidden = (e?: PointerEvent) => {
        const target = e.target as HTMLElement
        if (e && target.tagName === "SECTION") {
          SectionFloat.value = false
          DataFiltered.value = {
            data: [],
            texto: "",
            total: -1,
            selectedUnid: ''
          }
        } else {
          SectionFloat.value = true
        }
}

const loadData = async (texto: string, total: number, unid?: string, uni_or_est?: string) => {
  let actives = Unidades_Ascritas.value.find(element => element.active == true)
  const selectedUnid = actives ? actives.nombre : 'Ninguna'
  if(selectedUnid == 'Ninguna') return alerta('Info', 'Selecciona una unidad', 'info')
  
  SectionFloat.value = true
  
    let data = []
  
    data = await FilterPersonas(texto, DataFiltered.value.total || total, unid || selectedUnid, uni_or_est)
    total = data.length


  DataFiltered.value = {
      data,
      texto: texto ? texto : unid,
      total,
      selectedUnid
  }


}

const loadCort = async () => await NameEvent.GetUserInfo()

onMounted(loadCort)

</script>

<template>

<Welcome
    :title="`Monitoreo | ${NameEvent.name || 'Sin evento'}`"
    :subtitle="NameEvent.subtitle || 'Aqui podras monitorear el sistema con las graficas'"
>
    <div class="h-[60vh] md:w-[50vw] mt-[-10vh] overflow-hidden" v-if="solicitudesData.length > 0">
        <GraficaSolicitudes @redirectToStatic="({id}) => redirectToStatic(id)" :solicitudesData="solicitudesData"/>
    </div>

</Welcome>
  
  <section class="bg-white p-10 md:min-h-[20vh]">
    <hr class="w-[80%] mx-auto text-gray-500">
    <div class="grid md:flex md:w-[80%] md:justify-around mx-auto mt-2 md:h-[12vh] justify-center" v-if="NameEvent.id">
      <ButtonAction :action="() => ChangueDashboard(1)" text="Graficas iniciales" icon="fa-solid fa-chart-column"/>
      <ButtonAction :action="() => ChangueDashboard(2)" text="Géneros" icon="fa-solid fa-person-half-dress"/>
      <ButtonAction :action="() => ChangueDashboard(3)" text="Departamentos" icon="fa-solid fa-building"/>
      <ButtonAction :action="() => router.push('map')" text="Mapa" icon="fa-solid fa-map-location-dot"/>
      <ButtonAction :action="() => router.push('events')" text="Eventos" icon="fa-solid fa-calendar-days"/>
    </div>

    <div class="grid md:flex md:w-[80%] md:justify-around mx-auto mt-2 md:h-[12vh] justify-center" v-else>
      <ButtonAction :action="() => router.push('personal')" text="Personal" icon="users"/>
      <ButtonAction :action="() => router.push('cargar')" text="Cargar personal" icon="file-import"/>
      <ButtonAction :action="() => router.push('events')" text="Eventos" icon="fa-solid fa-calendar-days"/>
    </div>
  </section>


    <section v-if="SectionFloat" class="fixed bg-black bg-opacity-30 w-full h-full z-50 grid place-content-center top-0 left-0 cursor-pointer" @click="(e) => hidden(e as PointerEvent)">
        <div class="fadeout w-full h-[90%] mt-[5vh] md:w-[60vw] md:h-[80vh] bg-white rounded-2xl shadow overflow-auto p-10 cursor-default">
          <Loader class="mt-[25vh]" v-if="DataFiltered.total == -1"/>
          <div v-else>
            <h3 class="mx-5 bg-[#ECA008] hover:bg-[#010c41] text-white font-bold py-3 px-10 rounded-3xl my-5 text-center">{{DataFiltered.texto}}</h3>
            <p class="text-center my-3">Total: {{ DataFiltered.total.toLocaleString("es-ES", ) }}</p>
            <TableDashToSee v-if="DataFiltered.data.length > 0" :personas="DataFiltered.data" :text="DataFiltered.selectedUnid.includes('Todos') || DataFiltered.selectedUnid.includes('Todas ') ? DataFiltered.texto : DataFiltered.selectedUnid" />
            <p v-else class="text-center my-[17vh]">No hay datos</p>
          </div>
        </div>
    </section>

  
  <main class="main relative" v-if="NameEvent.id">

    <FilteredDashboard 
        v-if="Unidades_Ascritas.length > 0"
        :PostUnidAsc="PostUnidAsc"
        :typesCases="typesCases.types"
        :selectedCase="typesCases.types.find(e => e.id == typesCases.seletected)?.nombre"
        :Unidades_Ascritas="Unidades_Ascritas"
        :setTypeCase="setTypeCase"
        @ChangueDate="SetDates"
      />

      <BannerTitle v-if="graphToSee.length > 0" :title="
        graphToSee.find(e => e == 1) 
          ? 'Estadisticas principales'
          : graphToSee.find(e => e == 2)  
            ? 'Análisis por Edades y Géneros'
            : 'Clasificación por Departamentos'
      "/>


<div class="md:w-[80%] h-auto mx-auto my-8" v-if="graphToSee.find(e => e == 1) && Info.length > 0">
  <CardsFedeVida :items="Info"/> 
</div>


    <section class="w-[90%] grid grid-cols-3 gap-3 mx-auto text-white fadeout text-center" id="Estadisticas">

      <article v-if="!NameEvent.whitCortesia && graphToSee.find(e => e == 1)" id="barras" class="md:h-[50vh] bg-white rounded-lg px-2 py-3 shadow-xl z-10 fadeout border col-start-1 col-end-2">
        <h1 class="text-black text-left ml-4">Total de participación</h1>
        <Bar :data="PieData" :options="{  maintainAspectRatio: false,  indexAxis: 'y', plugins: { legend: {display: false}, datalabels: {...data_labels, color: 'white', display: true, align: 'end', offset: -40, anchor: 'end'} }}" v-if="Object.keys(PieData).length > 0" />
      </article>

      <article v-if="graphToSee.find(e => e == 1)" class="dona_and_leyend md:h-[50vh] block md:flex items-center gap-2 rounded-xl bg-white text-[#010c41] p-2 shadow-lg border col-start-2 col-end-4 overflow-hidden">
        <div id="dona" class="relative mx-auto">
          <p class="absolute pointer-events-none inset-0 flex items-center justify-center text-2xl">
            {{DatosResumen.a ? ((DatosResumen.b[1]/ DatosResumen.a[1]) * 100).toFixed(2) : 0 }}%
          </p>
          <Doughnut :data="PieData" :options="{maintainAspectRatio: false, plugins: { legend: {display: false}, datalabels: {display: false} }}" v-if="Object.keys(PieData).length > 0"/>
        </div>

          <div id="leyend" class="w-full md:w-3/4">
            <div :style="{'background': `linear-gradient(to right, ${item.bg} ${DatosResumen.a ? ((item.data / DatosResumen.a[1]  ) * 100) : 0 }%, #F0F0F0 10%)`}"  
            class="bg-[#F0F0F0] w-full grid grid-cols-6 gap-10 text-center items-center my-3 border mx-auto justify-beeteen rounded-md overflow-hidden" v-for="(item, index) in Info">
            <h1 :style="{'background-color': item.bg}"
            class="w-16 p-2.5 text-white font-bold col-start-1 col-end-2 hover:cursor-pointer hover:rounded-2xl hover:text-xl text-2xl transition-all" 
            @click="() => loadData(item.label, item.data, item.nombre)">{{ item.data.toLocaleString('es-ES', { useGrouping: true }) }}</h1>

            <p class="text-lg font-bold w-max col-start-3 col-end-5  text-gray-600 capitalize text-center" :class="((item.data / DatosResumen.a[1]  ) * 100) > 50 ? 'text-white font-bold' : null"  >{{ item.label }}</p>
            <label class="text-xl col-start-5 col-end-7 font-bold" :class="((item.data / DatosResumen.a[1]  ) * 100) > 80 ? 'text-white ' : null">
              {{DatosResumen.a ? ((item.data / DatosResumen.a[1]  ) * 100).toFixed(2) : 0 }}%</label>
          </div>
        </div>
      </article>
      


      <article v-if="graphToSee.find(e => e == 1)" id="articlehourGraph" class="bg-white rounded-lg px-2 py-3 shadow-xl z-10 fadeout border col-start-1 col-end-3 ">
        <h1 class="text-black text-left ml-4">Histórico de participación</h1>
          <div id="hourGraph" class="bg-white">
            <div id="data-series-chart" class="text-black"></div>
          </div>
      </article>


      <article v-if="graphToSee.find(e => e == 1)" id="donaPersonal" class="bg-white rounded-b-2xl p-2 text-white text-center md:rounded-2xl md:grid md:content-center rounded-xl shadow-lg border col-start-3 col-end-4">

        <Doughnut :data="PieData" :options="{maintainAspectRatio: false, plugins: { legend: {display: true}, datalabels: {display: false}}}" v-if="Object.keys(PiePersonas).length > 0"/>

        <div class="absolute pointer-events-none inset-0 flex items-center mt-10 justify-center text-2xl text-black">
        <p class="text-4xl font-bold md:text-1xl text-gray-600">{{ DatosResumen.a ? DatosResumen.a[1].toLocaleString('es-ES', { useGrouping: true }) : 0 }} <small class="block text-sm font-bold text-light-lavender">{{ DatosResumen.a ? DatosResumen.a[0] : 0 }}</small></p>
      </div>
    </article>

    
    <Graph_edades 
      v-if="graphToSee.find(e => e == 2)"  
      :PieSex="PieSex" 
      :DonutSex="DonutSex" 
      :TableAges="TableAges" 
      :ChangueSexData="ChangueSexData"/>

    </section>

    <br>
    <div class="mb-10 w-[90%] mx-auto" v-if="graphToSee.find(e => e == 3) && store.authUser.isAdmin" >

      <section class="statsAdmi block md:grid grid-cols-2 gap-3 mx-auto text-white fadeout text-center mt-5 relative">
            <article class="bg-white rounded-b-2xl p-10 text-white text-center md:rounded-2xl content-center rounded-xl p-2 shadow-lg border grid">
              <h3 class="text-black mx-5 bg-[#ECA008] hover:bg-[#010c41] text-white font-bold py-3 px-10 rounded-3xl my-5 text-center">Departamentos con mayor Participación</h3>
              <RankingCard :data="Top_Unidades.mayor" :loadData="loadData" whatis="unidades"/>
            </article>

            <article class="bg-white rounded-b-2xl p-10 text-white text-center md:rounded-2xl content-center rounded-xl p-2 shadow-lg border grid">
              <h3 class="text-black mx-5 bg-[#010c41] hover:bg-[#ECA008] text-white font-bold py-3 px-10 rounded-3xl my-5 text-center">Departamentos con menor Participación</h3>
              <RankingCard :data="Top_Unidades.menor" :loadData="loadData" whatis="unidades"/>
            </article>

      </section>
    </div>
    <div v-if="graphToSee.find(e => e == 4) && store.authUser.isAdmin">
      
      <div id="barUnidContainer"  class="p-2 w-[100%] mx-auto">
          <BannerTitle title="Comparativa Oficinas"/>
          <div id="barUnid" class="w-[90%]  mx-auto text-black bg-white shadow-lg rounded-2xl"></div>
        </div>
    </div>

    <section>
      <!--
      <BannerTitle title="Registro de participación"/>
      <div class="bg-white border p-10">
        <Participacion :enlaces="enlaces.filter((_, i) => i < 4).filter(e => e.title != 'Participación autorizados')"/>
      </div>
-->
      <BannerTitle title="Administración"/>
      <div class="bg-white border p-10">
        <Participacion :enlaces="enlaces.filter((_, i) => i > 4 && i < 8)"/>
      </div>
    </section>

  </main>
  



</template>

<style>

#filters .my_input {
  @apply md:w-5/12 rounded-3xl;
  height: 30px;
  text-align: center;
}


#barras canvas{
  height: 90%!important;
}

#articlehourGraph #hourGraph{
  height: 95%!important;
}


#leyend{
  width: 60%
}

#dona{
  width: 50%;
  height: 100%;
}

.dona_and_leyend div canvas{
  width: 100%!important;
  height: auto!important;
  object-fit: cover;
}

.dona_and_leyend .btn-Floats{
  position: absolute;
  right: 20px;
  bottom: 10px;
}

.dona_and_leyend .btn-Floats button{
  width: 40px;
}

#tableAge{
    height: 50vh;
}


#barGender{
  height: 40vh!important;
}


#hourGraph{
  width: 100%;
  height: 100%;
}

section article{
    height: 30vh;
    position: relative;
}

 section:nth-child(2) article:nth-child(3), .statsAdmi article, section #donaPersonal, #donaSex, #articlehourGraph{
    position: relative;
    height: 40vh;
}

section article:last-child canvas{
    position: relative;
    width: 100%!important;
}

@media (max-width: 1200px) {
  .main {
  width: 100%;
}

#barUnidContainer{
  display: none;
}

#barras{
  height: 30vh!important;
}

#tableAge{
    height: auto;
}

#barGender{
  height: auto!important;
}




.main>section {
    width: 100%;
    display: block;
    gap: 10px;
    margin: 0px 0px;
  }


  section article{
    margin-top: 10px;
    min-height: 30vh;
    height: auto;
  }

  #leyend{
  width: 100%
}

#filters {
  input, button{
  margin: 10px auto;
  text-align: center;
  display: block;
}

div{
  margin: 10px auto;
  text-align: center;
}


}

}

.ExportTemp {
  display: grid!important;
  width: 1494px!important;
  height: 664px!important;
  border: none!important;
  outline: none!important;
  padding: 2px;
  min-height: 26vh;
  margin-bottom: 30px;
}

.ExportTemp  *{
  border: none!important;
  outline: none!important;
}

.ExportTemp #dona {
  width: 50%!important;
  height: 100%!important;
}

.ExportTemp .dona_and_leyend {
  display: flex!important;
}

.ExportTemp #leyend {
  width: 60%!important;
}

.ExportTemp #hourGraph {
  width: 950px!important;
  margin-left: 10px!important;
  box-shadow: none!important;
}

.ExportTemp #donaPersonal{
    height: 100%!important;
}

.ExportTemp #barGender, .ExportTemp #tableAge, .ExportTemp #TitleGender, .ExportTemp #donaSex{
    display: none!important;
}

.ExportTemp .dona_and_leyend div canvas{
  width: 100%!important;
  height: auto!important;
}

.ExportTemp article:last-child, .ExportTemp:nth-child(2) article:nth-child(3), .ExportTemp .statsAdmi article {
    position: relative!important;
    height: 40vh!important;
}

.ExportTemp article{
    height: 28vh!important;
    position: relative!important;
}

.ExportTemp article:last-child canvas{
    position: relative!important;
    width: 100%!important;
}

.ExportTemp article:last-child canvas{
    position: relative!important;
    width: 100%!important;
}





</style>