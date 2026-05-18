import { ref } from "vue"
import { AcceptSolicitud, sendJubilado } from "../services";
import { alerta } from "@/utils/alert";
import { obtenerHoraActual } from "@/utils/GetHora";

export default (emits: any) => {

    const step = ref(1 as any);
    const estado = ref([] as number[])
    const hasValues = ref(false)
    const loading = ref(false)
    const where = ref('')

    const dataForm = ref({
        id: '',
        nombre_completo: "", 
        cedula: "",
        telefono: "",
        telefono_2: "",
        direccion: "",
        causa_pension: "",
        fecha_nacimiento: "",
        tipo_empleado_id: '0',
        pais_id: '0',
        estado_id: '0',
        parroquia_id: '0',
        municipio_id: '0',
        correo_electronico: ''
    })

    const mode = ref('POST')

    const emitForm = async (e: FormDataEvent) => {
        const form = new FormData(e.currentTarget as HTMLFormElement)
        
        if (step.value == 1) {
            step.value = 2
            estado.value.push(1)
        } 
        else if (step.value == 2) {
            step.value = 3
            estado.value.push(2)
            if(where.value != 'Formulario') saveForm(form)
        } 
        else if (step.value == 3) {
            estado.value.push(2)
            step.value = 3
            saveForm(form)
        }
    }

    const setInfo = (values: any, _where: string) => {
        if(!values) return 
        dataForm.value = {
            ...values,
       }

       where.value = _where

       if(_where != 'presencial') return
       hasValues.value = true
    }

    const setParticipation = async () => {
        const payload = {
            vote: true,
            descripcion: where.value,
            hora_voto: obtenerHoraActual(),
            persona_id: dataForm.value.id 
        }
        try {
            const response = await AcceptSolicitud(payload)
            alerta('completado', response.msg, 'success')
        } catch (error) {
            const {response} = error
            alerta("error", response.data.data.message, "info")
        }
        finally{
            resetData()
        }
    }



    const saveForm = async (form: any) => {

        for (const key in dataForm.value) {
            if (dataForm.value.hasOwnProperty(key)) {
                form.append(key, dataForm.value[key]);
            }
        }

        form.append('where', where.value)

      try {
        loading.value = true
        const response  = await sendJubilado(dataForm.value.id, form)
        if(where.value != 'Formulario') await setParticipation()
        emits('hide')
        alerta('completado', response.message, 'success')
      } catch (error) {
        const {response} = error
        if(response.data) return alerta("error", response.data.msg || 'Ocurrio un error', "info")
        alerta("error", 'Ocurrio un error', "info")
      } finally{
        loading.value = false
      }
    }




const resetData = () => {
    dataForm.value = {
        id: '',
        nombre_completo: "", 
        cedula: "",
        telefono: "",
        telefono_2: "",
        direccion: "",
        causa_pension: "",
        tipo_empleado_id: null,
        pais_id: '0',
        estado_id: '0',
        parroquia_id: '0',
        municipio_id: '0',
        correo_electronico: '',
        fecha_nacimiento: ''
    }

    mode.value = 'POST'
    step.value = 1
    estado.value = []
}

    return {
        step,
        estado,
        dataForm,
        loading,
        emitForm,
        setInfo
    }
}
