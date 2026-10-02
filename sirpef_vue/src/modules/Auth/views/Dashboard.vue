<script setup lang="ts">
import { useAuthStore } from "../stores"
import { onMounted, ref } from 'vue';
import InitAdmin from './home/initAdmin.vue';
import InitUser from './home/initUser.vue';
import Loader from "@/components/Votos/loader.vue";

const store = useAuthStore()
const userRole = ref(0)

const loadUser = async () => {
  const user = await store.getAuthUser()
  userRole.value = user.role_id
}

onMounted(() => loadUser())

</script>



<template>
  <InitAdmin v-if="userRole >= 1 || userRole <= 3"/>
  <InitUser v-else-if="userRole == 4"/>
  <div class="grid items-center pt-10 justify-center h-[40vh]" v-else>
    <Loader/>
  </div>
</template>