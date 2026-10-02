import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import useHttp from "@/composables/useHttp";
import UserService from "@/modules/User/services";
import type Role from "../types/Role"
import type User from "../types/User"
import type Unid from "../types/Unid_Ad"
import { alerta } from '@/utils/alert';

export default (userId?: string) => {
  const router = useRouter();
  
  const user: User = reactive({    
    name: "",
    email: "", 
    password: "", 
    role_id: "", 
    Unid_id: "", 
    cedula: "",
    oficinas_ids: [], 
    menus_id: [],
    eventos_id: []
  })
 
  const roles = ref<Role[]>([])
  const UnidadesAds = ref<Unid[]>([])
  
  const {  
    errors,
    sending,
    loading,
    getError
  } = useHttp()
  
  onMounted(async () => {

    loading.value = true
    UserService.helperTablesGet()
      .then((response) => {
        roles.value = response.data.roles
      })
      .catch((err) => {
        errors.value = getError(err)
      })
      .finally(() => {
        loading.value = false
      })

    if(userId == '0') return

    if (userId) {
      const response = await GetOnlyUser(userId)
      user.name = response.data.data.name
      user.email = response.data.data.email
      user.password = null
      user.role_id = response.data.data.role_id  
      user.cedula = response.data.data.cedula      
      user.oficinas_ids = response.data.data.oficinas_ids
      user.menus_id = response.data.data.menus_id
      user.eventos_id = response.data.data.eventos_id
    }
  
  })

  const GetOnlyUser = async (id: string) => {
    loading.value = true
    const response = await UserService.getUser(id)
    loading.value = false
    return response
  }

  const insertUser = async (user: User) => {  
    sending.value = true
    return UserService.insertUser(user)
      .then((response) => {         
        alerta('completado', `${response.data.message}`, 'success')
        //router.push( { path: '/users' } )
        return {status: true}
      })
      .catch((err) => {                
        alerta('Info', `${err.response.data.message}`, 'info')
        errors.value = getError(err)
        return {status: false}
      })
      .finally(() => {
        sending.value = false
      })
  }

  const updateUser = async (user: User, userId: string) => {
    sending.value= true
    return UserService.updateUser(userId, user)
      .then((response) => {
        alerta('completado', `${response.data.message}`, 'success')
        return {status: true}
        //router.push( { path: '/users' } )
      })
      .catch((err) => {                
        alerta('Info', `${err.response.data.message}`, 'info')
        errors.value = getError(err)
        return {status: false}

      })
      .finally(() => {
        sending.value = false
      })
  }
  
  const submit = async (user: User, userId?: string) => {  
    const data = {
     ...user,
      oficinas_ids: (user.oficinas_ids || []).map(e => (typeof e === 'object' && e !== null ? e.id : Number(e))), 
      menus_id:  (user.menus_id || []).map(e => (typeof e === 'object' && e !== null ? e.id : Number(e))),
      eventos_id: (user.eventos_id || []).map(e => (typeof e === 'object' && e !== null ? e.id : Number(e)))
 }
    const status = !userId ? await insertUser (data)  : await updateUser(data, userId)

    return status
  }


  return {
    user,
    errors,
    roles,
    sending,
    loading,
    router,
    UnidadesAds,
    GetOnlyUser,
    submit    
  }

}
