<script setup lang="ts">
import Header from "./Header.vue"
import Sidebar from "./Sidebar.vue"
import PageFoot from "./PageFoot.vue"
import useWebsockets from "@/composables/votos/useWebsockets"
import { onMounted, onUnmounted } from "vue"

const { ConnectSocket, DisconnectSocket, GetUser } = useWebsockets()

onMounted(async () => {
  await GetUser()
  //ConnectSocket()
})
onUnmounted(() => {
 // DisconnectSocket()
})
</script>

<template>
  <Header />
  <main class="section pt-[10vh] main-container-print">
    <div id="Containerdelmain" class="flex-1 flex flex-col">
      <main id="ContainerMain" class="flex-1 overflow-y-auto">
        <div class="">
          <slot />
        </div>
      </main>
    </div>
  </main>
</template>

<style>
#ContainerMain{
  overflow-x: hidden;
}

#Containerdelmain{
  overflow: hidden;
}

/* --- SOLUCIÓN DE ESPACIO SUPERIOR PARA IMPRESIÓN --- */
@media print {
  /* Forzamos a que el main pierda el padding-top de 10vh solo al imprimir */
  main.section.pt-\[10vh\],
  .main-container-print {
    padding-top: 0 !important;
    margin-top: 0 !important;
  }

  /* Nos aseguramos de que los contenedores hijos tampoco tengan márgenes remanentes */
  #Containerdelmain,
  #ContainerMain,
  #ContainerMain > div {
    padding-top: 0 !important;
    margin-top: 0 !important;
    overflow: visible !important; /* Evita que se corte el documento si pasa a otra hoja */
  }
}
</style>