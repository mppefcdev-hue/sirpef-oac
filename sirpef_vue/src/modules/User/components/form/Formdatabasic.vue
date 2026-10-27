<script setup lang="ts">
import Controls from '@/components/sirpef/form/Controls.vue';
import { ref } from 'vue';
import useCreateOrEdit from '../../composables/useCreateOrEdit';

const visible = ref(false)

const props = defineProps<{
    emitForm: (event: Event) => void,
    step: number,
    values: any,
    id: string
}>()


const {
  roles,  
} = useCreateOrEdit(props.id)



const onRoleChange = () => {
  const selected = roles.value.find((r: any) => r.id == props.values.role_id);
  if (selected && selected.menu_ids) {
    props.values.menus_id = selected.menu_ids.map((id: number) => ({ id }));
  }
};

const getRoleDescription = () => {
  const role = roles.value.find((r: any) => r.id == props.values.role_id);
  return role ? role.description : '';
};
</script>


<template>
    <form @submit.prevent="emitForm">
        <label class="block my-2">Nombre</label>
        <input class="inputForm" type="text" name="name" placeholder="Ingrese el nombre" v-model="values.name" required>


        <div class="lg:grid-cols-2 lg:grid my-5 gap-x-10">
           <div>
            <label class="block my-2">Correo</label>
            <input class="inputForm" type="text" name="email" placeholder="Ingrese el correo" v-model="values.email" required>
           </div>
            <div>
                <label class="block my-2">Cédula</label>
                <input class="inputForm" type="text" name="cedula" placeholder="Ingrese la cedula" v-model="values.cedula" required>
            </div>
        </div>


        <div class="lg:grid-cols-2 lg:grid my-5 gap-x-10">
            <div class="relative">
                <label class="block my-2">Contraseña</label>
                <input class="inputForm" :type="!visible ? 'password' : 'text'" name="password" placeholder="Ingrese la contraseña" v-model="values.password">
                <button type="button" class="bg-transparent absolute top-8 h-[30px] w-[30px] z[4] right-4" @click="visible = !visible">
                    <font-awesome-icon icon="fa-solid fa-eye" v-if="!visible"/>
                    <font-awesome-icon icon="fa-solid fa-eye-slash" v-else/>
                </button>
            </div>

            <div>
                <label class="block my-2">Rol</label>
                <select name="role_id" v-model="values.role_id" @change="onRoleChange" required>
                    <option value="0">Seleccionar</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                        {{ role.name }}
                    </option>
                </select>
                <p v-if="getRoleDescription()" class="text-xs text-gray-500 mt-1 italic">
                    {{ getRoleDescription() }}
                </p>
            </div>
        </div>

        <Controls :step="step"/>
    </form>
</template>