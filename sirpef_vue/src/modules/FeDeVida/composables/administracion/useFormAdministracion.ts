import { alerta } from "@/utils/alert";
import { onMounted, ref } from "vue"
import { editCase, getCaseSingle, registerPay } from "../../services";
import { useRoute, useRouter } from "vue-router";

export default (punto: any) => {
    const router = useRouter()
    const route = useRoute()
    const id = route.params.id as string

    const step = ref(1 as any);
    const estado = ref([] as number[])

    const UserInfo = ref({
        tipo_pago: "",
        nro_referencia_pago: "",
        proveedor: "",
        rif_proveedor: "",
        monto: 0,
        nro_orden_pago: "",
        fecha_orden_pago: "",
        nro_factura: "",
        estatus: "",
        descripcion: "",
        fecha_pago_financiero: '',
        saldo_deudor: '',
        saldo_acreedor: '',
        cuota_compromiso_disponible: '',
        recaudos: [] as any[]
    })

    const mode = ref('POST')

    const emitForm = async (e: Event) => {
        if (step.value == 1) {
            step.value = 2
            estado.value.push(1)
        } else if (step.value == 2) {
            estado.value.push(2)
            step.value = 3
        } else if (step.value == 3) {
            estado.value.push(3)

            const formData = new FormData();

            formData.append('tipo_pago_id', UserInfo.value.tipo_pago);
            formData.append('nro_referencia_pago', UserInfo.value.nro_referencia_pago);
            //formData.append('proveedor', UserInfo.value.proveedor);
            //formData.append('rif_proveedor', UserInfo.value.rif_proveedor);
            formData.append('monto', UserInfo.value.monto.toString());
            formData.append('orden_pago', UserInfo.value.nro_orden_pago);
            formData.append('fecha_orden_pago', UserInfo.value.fecha_orden_pago);
            formData.append('nro_factura', UserInfo.value.nro_factura);
            formData.append('estatus_pago_id', UserInfo.value.estatus);
            formData.append('descripcion', UserInfo.value.descripcion);

            const proveedoresEnvio = [
                {
                    monto_relacionado: UserInfo.value.monto,
                    cedula_rif: UserInfo.value.rif_proveedor,
                    nombre: UserInfo.value.proveedor
                }
            ];

            proveedoresEnvio.forEach((p, index) => {
                formData.append(`proveedores[${index}][monto_relacionado]`, p.monto_relacionado.toString());
                formData.append(`proveedores[${index}][cedula_rif]`, p.cedula_rif);
                formData.append(`proveedores[${index}][nombre]`, p.nombre);
            });

            UserInfo.value.recaudos.forEach((recaudo, index) => {
                formData.append(`recaudos[${index}][nombre]`, recaudo.nombre);
                if (recaudo.archivo) {
                    formData.append(`recaudos[${index}][archivo]`, recaudo.archivo);
                }
            });

            try {
                if (mode.value == 'POST') await registerPay(punto.registro_id, formData)
                else if (mode.value == 'PUT') await editCase(id, formData)

                alerta("Éxito", `El registro se ha procesado correctamente`, "success")
                router.push('/casos/administracion')
            } catch (error: any) {
                const { response } = error
                if (response?.data) return alerta("error", `
                    ${response.data.message || 'Ocurrió un error'}
                    <br><p>${response.data.errors ? response.data.errors[Object.keys(response.data.errors)[0]] : 'Error en el servidor'}</p>
                    `, "info")
                alerta("error", 'Ocurrió un error inesperado', "info")
            }
        }
    }

    const getInfo = async (id: string) => {
        try {
            const response = await getCaseSingle(id)
            const data = response.data

            UserInfo.value = {
                tipo_pago: data.tipo_pago || '',
                nro_referencia_pago: data.nro_referencia_pago || '',
                proveedor: data.proveedor || '',
                rif_proveedor: data.rif_proveedor || '',
                monto: data.monto || 0.0,
                nro_orden_pago: data.nro_orden_pago || '',
                fecha_orden_pago: data.fecha_orden_pago || '',
                nro_factura: data.nro_factura || '',
                estatus: data.estatus || '',
                descripcion: data.descripcion || '',
                recaudos: data.recaudos?.map((e: any) => ({ ...e, type: e.mime_type })) || [],
                fecha_pago_financiero: data.fecha_pago_financiero || '',
                saldo_deudor: data.saldo_deudor || '',
                saldo_acreedor: data.saldo_acreedor || '',
                cuota_compromiso_disponible: data.cuota_compromiso_disponible || '',
            }

            mode.value = 'PUT'
        } catch (error) {
            console.error(error)
            alerta("error", `Error al obtener los datos del caso`, "error")
        }
    }

    onMounted(() => {
        if (id) getInfo(id)
    })

    return {
        step,
        estado,
        emitForm,
        UserInfo,
    }
}