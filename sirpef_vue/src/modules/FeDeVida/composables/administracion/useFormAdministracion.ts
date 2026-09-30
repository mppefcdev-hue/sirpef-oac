import { alerta } from "@/utils/alert";
import { onMounted, ref, watch, computed } from "vue"
import { editCase, getCaseSingle, registerPay, getPagoSingle, updatePagoService } from "../../services";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import Http from "@/utils/Http";
import { useAuthStore } from "@/modules/Auth/stores/index";

export default (punto: any) => {
    const router = useRouter()
    const route = useRoute()
    const store = useAuthStore()

    const pagoId = (route.query.pago_id || route.params.id) as string
    const currentPagoId = ref(pagoId || '')

    // Permisos de pasos según los menús asignados al rol del usuario
    const canAccessPaso1 = computed(() => {
        const roleId = Number(store.authUser?.role_id);
        if (store.authUser?.isAdmin || roleId === 1 || roleId === 2 || roleId === 4) return true;
        if (roleId === 6) return false;
        return store.authUser?.menus_id?.some((m: any) => 
            m.nombre?.toLowerCase().includes('paso 1') || 
            m.id === 35 || 
            m.nombre?.toLowerCase() === 'formulario'
        ) ?? true;
    });

    const canAccessPaso2y3 = computed(() => {
        const roleId = Number(store.authUser?.role_id);
        if (store.authUser?.isAdmin || roleId === 1 || roleId === 2 || roleId === 6) return true;
        if (roleId === 4) return false;
        return store.authUser?.menus_id?.some((m: any) => 
            m.nombre?.toLowerCase().includes('paso 2') || 
            m.id === 39
        ) ?? false;
    });

    // Determinar paso inicial según la ruta, query param, o permisos del usuario
    const routeName = route.name as string;
    const getInitialStep = (): number => {
        if (route.query.step) return parseInt(route.query.step as string);
        if (routeName === 'CasesAdminFormPaso2') return 2;
        if (routeName === 'CasesAdminFormPaso1') return 1;
        return canAccessPaso1.value ? 1 : 2;
    };

    const step = ref(getInitialStep() as any);
    const estado = ref([] as number[])

    const UserInfo = ref({
        tipo_pago: "",
        nro_referencia_pago: "",
        proveedor: "",
        contacto: "",
        rif_proveedor: "",
        monto: "" as any,
        nro_orden_pago: "",
        fecha_orden_pago: "",
        nro_factura: "",
        estatus: "",
        descripcion: "",
        beneficiario: "",
        diagnostico: "",
        fecha_pago_financiero: '',
        saldo_deudor: '',
        saldo_acreedor: '',
        tiene_factura: false,
        factura_pendiente: false,
        recaudos: [] as any[]
    })

    const mode = ref(pagoId ? 'PUT' : 'POST')

    watch(
      () => [UserInfo.value.monto, UserInfo.value.saldo_acreedor],
      ([nuevoMonto, nuevoAcreedor]) => {
        const monto = parseFloat(nuevoMonto as any) || 0;
        const acreedor = parseFloat(nuevoAcreedor as any) || 0;
        UserInfo.value.saldo_deudor = (monto - acreedor).toFixed(2);
      }
    );

    const submitFormData = async (continuar: boolean = false) => {
        const formData = new FormData();

        formData.append('tipo_pago_id', UserInfo.value.tipo_pago);
        formData.append('nro_referencia_pago', UserInfo.value.nro_referencia_pago);
        formData.append('proveedor', UserInfo.value.proveedor);
        formData.append('rif_proveedor', UserInfo.value.rif_proveedor);
        formData.append('contacto', UserInfo.value.contacto.toString());
        formData.append('monto', (UserInfo.value.monto !== '' && UserInfo.value.monto !== null && UserInfo.value.monto !== undefined) ? UserInfo.value.monto.toString() : '0');
        formData.append('orden_pago', UserInfo.value.nro_orden_pago || 'PENDIENTE');
        formData.append('fecha_orden_pago', UserInfo.value.fecha_orden_pago || '');
        formData.append('nro_factura', UserInfo.value.nro_factura || '');
        formData.append('estatus_pago_id', UserInfo.value.estatus || '');
        formData.append('descripcion', UserInfo.value.descripcion || '');
        formData.append('beneficiario', UserInfo.value.beneficiario || '');
        formData.append('diagnostico', UserInfo.value.diagnostico || '');
        formData.append('saldo_deudor', UserInfo.value.saldo_deudor.toString());
        formData.append('saldo_acreedor', UserInfo.value.saldo_acreedor.toString());
        formData.append('tiene_factura', UserInfo.value.tiene_factura ? '1' : '0');
        formData.append('factura_pendiente', UserInfo.value.factura_pendiente ? '1' : '0');

        const proveedoresEnvio = [
            {
                monto_relacionado: UserInfo.value.monto || 0,
                cedula_rif: UserInfo.value.rif_proveedor,
                nombre: UserInfo.value.proveedor,
                contacto: UserInfo.value.contacto
            }
        ];

        proveedoresEnvio.forEach((p, index) => {
            formData.append(`proveedores[${index}][monto_relacionado]`, p.monto_relacionado.toString());
            formData.append(`proveedores[${index}][cedula_rif]`, p.cedula_rif);
            formData.append(`proveedores[${index}][nombre]`, p.nombre);
            formData.append(`proveedores[${index}][contacto]`, p.contacto);
        });

        UserInfo.value.recaudos.forEach((recaudo, index) => {
            formData.append(`recaudos[${index}][nombre]`, recaudo.nombre);
            if (recaudo.archivo) {
                formData.append(`recaudos[${index}][archivo]`, recaudo.archivo);
            }
        });

        try {
            let res: any = null;
            const targetId = currentPagoId.value || pagoId;

            if (mode.value == 'POST' && !targetId) {
                res = await registerPay(punto.registro_id, formData);
                if (res?.data?.pago?.id) {
                    currentPagoId.value = res.data.pago.id.toString();
                }
            } else {
                res = await updatePagoService(targetId, formData);
            }

            if (!continuar) {
                estado.value.push(4)
                step.value = 4
                alerta("Éxito", `El registro se ha procesado correctamente`, "success")
                router.push('/casos/administracion')
            }

            return res;
        } catch (error: any) {
            const { response } = error
            if (response?.data) {
                alerta("error", `
                    ${response.data.message || 'Ocurrió un error'}
                    <br><p>${response.data.errors ? response.data.errors[Object.keys(response.data.errors)[0]] : 'Error en el servidor'}</p>
                    `, "info");
            } else {
                alerta("error", 'Ocurrió un error inesperado', "info")
            }
            return null;
        }
    }

    const emitForm = async (e: Event) => {
        if (step.value == 1) {
            // Preguntar si posee factura
            const tieneFactura = await Swal.fire({
                title: '¿Posee factura?',
                html: '¿Tiene la factura para registrarla en este momento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, la tengo',
                cancelButtonText: 'No',
                reverseButtons: true
            });

            // Sin importar si coloca Sí o No, el caso se registra
            if (tieneFactura.isConfirmed) {
                // Marcó SÍ: la factura está pendiente de colocar
                UserInfo.value.tiene_factura = true;
                UserInfo.value.factura_pendiente = true;

                const res = await submitFormData(true);
                if (res) {
                    estado.value.push(1);
                    // Si estamos en la ruta de paso-1 solamente, redirigir
                    if (routeName === 'CasesAdminFormPaso1') {
                        if (canAccessPaso2y3.value) {
                            // Redirigir al formulario paso 2 y 3 con el pago_id
                            const targetPagoId = currentPagoId.value || pagoId;
                            alerta("Caso Registrado", "El caso se guardó exitosamente con factura pendiente. Complete los datos de la factura en el formulario paso 2 y 3.", "success");
                            router.push({
                                name: 'CasesAdminFormPaso2',
                                query: {
                                    pago_id: targetPagoId,
                                    punto: route.query.punto as string || '',
                                    registro_id: route.query.registro_id as string || undefined
                                }
                            });
                        } else {
                            alerta("Éxito", "El registro se ha procesado correctamente. La factura ha quedado pendiente de colocar por el área correspondiente.", "success");
                            router.push('/casos/administracion');
                        }
                    } else if (canAccessPaso2y3.value) {
                        // Ruta genérica: avanzar al paso 2 en el mismo formulario
                        mode.value = 'PUT';
                        step.value = 2;
                        alerta("Caso Registrado", "El caso se guardó exitosamente con factura pendiente. Ahora complete los datos de la factura.", "success");
                    } else {
                        alerta("Éxito", "El registro se ha procesado correctamente. La factura ha quedado pendiente de colocar por el área correspondiente.", "success");
                        router.push('/casos/administracion');
                    }
                }
            } else {
                // Marcó NO: sin factura
                UserInfo.value.tiene_factura = false;
                UserInfo.value.factura_pendiente = false;

                const res = await submitFormData(false);
                if (res) {
                    estado.value.push(1);
                }
            }
        } else if (step.value == 2) {
            if (!UserInfo.value.nro_factura) {
                const confirmarFactura = await Swal.fire({
                    title: 'Factura pendiente',
                    html: 'El proveedor no ha entregado la factura. Se registrará como pendiente en el expediente. ¿Desea continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Completar factura'
                });
                if (!confirmarFactura.isConfirmed) return;
            }

            const deudor = parseFloat(UserInfo.value.saldo_deudor as any) || 0;
            if (deudor !== 0) {
                const deudorFormatted = deudor.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const confirmarDeudor = await Swal.fire({
                    title: '¿Continuar con Saldo Deudor?',
                    html: `El saldo deudor es de <b>Bs. ${deudorFormatted}</b> (no es cero). Se gestionará un reintegro.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, registrar deudor',
                    cancelButtonText: 'Corregir montos'
                });
                if (!confirmarDeudor.isConfirmed) return;
            }

            estado.value.push(2)
            step.value = 3
        } else if (step.value == 3) {
            estado.value.push(3)
            // Si colocó número de factura o subió recaudos, la factura ya no está pendiente
            if (UserInfo.value.nro_factura || (UserInfo.value.recaudos && UserInfo.value.recaudos.length > 0)) {
                UserInfo.value.factura_pendiente = false;
            }
            await submitFormData(false)
        }
    }

    const getInfo = async (pId: string) => {
        try {
            const response = await getPagoSingle(pId)
            const data = response.data

            let nroFactura = data.nro_factura || '';
            if (!nroFactura && data.descripcion) {
                const match = data.descripcion.match(/\[Factura:\s*([^\]]+)\]/i);
                if (match && match[1].trim().toUpperCase() !== 'PENDIENTE') {
                    nroFactura = match[1].trim();
                }
            }

            const tieneFactura = data.tiene_factura ?? (data.nro_factura || data.factura_pendiente ? true : false);
            const facturaPendiente = data.factura_pendiente ?? (data.descripcion ? striposPendiente(data.descripcion) : false);

            UserInfo.value = {
                tipo_pago: data.tipo_pago_id || '',
                nro_referencia_pago: data.nro_referencia_pago || '',
                proveedor: data.proveedores?.[0]?.nombre || '',
                contacto: data.proveedores?.[0]?.contacto || '',
                rif_proveedor: data.proveedores?.[0]?.cedula_rif || '',
                monto: data.monto || '',
                nro_orden_pago: data.orden_pago || '',
                fecha_orden_pago: data.fecha_orden_pago || '',
                nro_factura: nroFactura,
                estatus: data.estatus_pago_id || '',
                descripcion: data.descripcion || '',
                beneficiario: data.beneficiario || '',
                diagnostico: data.diagnostico || '',
                recaudos: data.recaudos?.map((e: any) => ({ ...e, type: e.mime_type })) || [],
                fecha_pago_financiero: data.fecha_pago_financiero || '',
                saldo_deudor: data.saldo_deudor || '',
                saldo_acreedor: data.saldo_acreedor || '',
                tiene_factura: tieneFactura,
                factura_pendiente: facturaPendiente,
            }

            currentPagoId.value = pId;
            mode.value = 'PUT';

            // Si se está editando o continuando en paso 2/3, marcar paso 1 como superado
            if (step.value >= 2) {
                if (!estado.value.includes(1)) estado.value.push(1);
            }
        } catch (error) {
            console.error(error)
            alerta("error", `Error al obtener los datos del pago`, "error")
        }
    }

    const striposPendiente = (desc: string): boolean => {
        return desc.toUpperCase().includes('[FACTURA: PENDIENTE]') || desc.toUpperCase().includes('FACTURA PENDIENTE');
    }

    const DataOGA = async (fechaDesde: string | null = null, fechaHasta: string | null = null, tipoCasoId: number = 0) => {
        try {
            const desde = fechaDesde || 'null';
            const hasta = fechaHasta || 'null';
            const res = await Http.get(`/api/registro/count/${desde}/${hasta}/${tipoCasoId}`);
            return res.data;
        } catch (error) {
            console.error("Error al obtener DataOGA:", error);
            return null;
        }
    };

    onMounted(() => {
        const idToLoad = pagoId || currentPagoId.value;
        if (idToLoad) getInfo(idToLoad)
        // Precargar beneficiario desde el punto de cuenta si existe
        if (punto?.beneficiario && !UserInfo.value.beneficiario) {
            UserInfo.value.beneficiario = punto.beneficiario
        }
    })

    return {
        step,
        estado,
        emitForm,
        UserInfo,
        DataOGA,
        canAccessPaso1,
        canAccessPaso2y3,
    }
}