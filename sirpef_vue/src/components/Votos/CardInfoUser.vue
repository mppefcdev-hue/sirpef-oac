<script setup lang="ts">

const props = defineProps<{
    UserData?: any,
    UserRegister?: any,
    icon: string
    title: string
    hidden?: Function,
    envioData?: Function,
    canDelete?: boolean,
}>()

</script>

<template>
      <div class="bg-white dark:bg-slate-800 rounded-lg px-6 py-8 ring-1 ring-slate-900/5 shadow-xl z-10 fadeout">
        <div class="flex place-content-center mx-auto">
          <span class="inline-flex items-center justify-center p-2 bg-[#010c41] rounded-3xl shadow-lg w-96">
              <font-awesome-icon class="h-6 w-6 text-white" :icon='props.icon'/>
              <h2 class="mx-3 text-white">{{ title }}</h2>
          </span>
        </div>

        <div v-if="UserData" v-for="key in Object.keys(UserData).filter(k => k !== 'id' && k !== 'registro_id')">
            <h3 class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight capitalize">{{key.replace(/_/g, ' ')}}</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm capitalize">{{ key == "cédula" ? `V-${UserData[key].toLocaleString("es-ES")}` : UserData[key] }}</p>
        </div>
        
        <div v-else>
            <h3 class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight">¿Ha participado?: </h3>
        <div class="grid">
          <p class="my-5 mx-auto">{{ UserRegister.registro_existente ? "Voto registrado" : "No" }}.</p>
          <font-awesome-icon icon="fa-solid fa-circle-check" class="text-green-600 h-32 mx-auto aling-center my-5" v-if="UserRegister.registro_existente"/>
        </div>

  
        <div class="hg-30 grid grid-cols-2 gap-1" v-if="!UserRegister.registro_existente">
          <button name="si" class=" bg-[#ECA008] hover:bg-[#010c41] text-white font-bold py-2 px-10 rounded-3xl my-8 " @click="(e) => hidden(e as MouseEvent, 1)">Participó</button>
          <button name="si" class="bg-[#010c41] hover:bg-[#ECA008] text-white font-bold py-3 px-10 rounded-3xl my-8" @click="(e) => hidden(e as MouseEvent, 2)">No participó</button>
        </div>
        </div>


         <div class="hg-30 mx-auto grid grid-cols-2 place-content-center" v-if="UserData.registro_id">
          <button name="si" class="w-[80%] mx-auto bg-[#ECA008] hover:bg-[#010c41] text-white font-bold py-2 px-10 rounded-3xl my-8 " @click="$emit('seePDC', {registro_id: UserData.registro_id})">
            <font-awesome-icon icon="eye" />
            Ver
          </button>
          <button v-if="props.canDelete !== false" name="si" class="w-[80%] mx-auto hover:bg-[#ECA008] bg-[#010c41] text-white font-bold py-2 px-10 rounded-3xl my-8 " @click="$emit('deletePDC', {registro_id: UserData.registro_id})">
            <font-awesome-icon icon="trash-can" />
            Eliminar
          </button>
        </div>
      </div>    
</template>
