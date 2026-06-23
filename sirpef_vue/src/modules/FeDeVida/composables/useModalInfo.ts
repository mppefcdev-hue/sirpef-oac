import { ref } from "vue";
import { changeStatusService, enableEditPDC, getCaseSingle, getPDC, getTramiteStatus, sendToCheckService } from "../services";
import { useRouter } from "vue-router";
import generatePuntoDeCuentaPDF from "../utils/pdf-punto";
import { useAuthStore } from "@/modules/Auth/stores";
import { alerta } from "@/utils/alert";
import { alertQuestion } from "@/utils/alertQuestion";

export default () => {
    const caseData = ref<any>({});
    const url = `${import.meta.env.VITE_APP_API_URL}/storage`;
    const router = useRouter();
    const store = useAuthStore();

    const showTypes = ref(false);
    const showHistory = ref(false);
    const status = ref([]);
    const formStatus = ref({
        observacion: '',
        status: '',
        status2: ''
    });

    const case_id = ref('0');

    const getInfo = async (id: string) => {
        try {
            const response = await getCaseSingle(id);
            const recaudos = response.data.recaudos.map(e => ({
                ...e,
                path: `${url}/${e.path}`
            }));
            case_id.value = id;
            caseData.value = {
                ...response.data,
                recaudos
            };
console.log(response.data)

        } catch (error) {
            console.error("Error al obtener la información del caso:", error);
        }
    };

    const createPunto = async () => {
       const params = {
                id: caseData.value.registro_id,
                pdc_id: caseData.value.punto_cuenta?.id || ''
            };

            router.push({
                name: 'pdc',
                params
            });

            console.log(params)
            return
        if (caseData.value.punto_cuenta && caseData.value.punto_cuenta?.estatus === true) {
            try {
                const punto = await getPDC(caseData.value.punto_cuenta.id);
                generatePuntoDeCuentaPDF(punto.data);
            } catch (e) {
                console.error("Error al generar PDF:", e);
                alerta('Error', 'No se pudo generar el PDF del punto de cuenta.', 'error');
            }
        } else {
            const params = {
                id: caseData.value.registro_id,
                pdc_id: caseData.value.punto_cuenta?.id || ''
            };
            router.push({
                name: 'pdc',
                params
            });
        }
    };
     
    const editPunto = async () => {
        try {
            const result = await alertQuestion('Info', '¿Estás seguro de habilitar la edición del punto de cuenta?', 'info');
            if (!result) return;
            await enableEditPDC(caseData.value.punto_cuenta.id);
            await getInfo(case_id.value); 

            alerta('Listo', 'El punto de cuenta ahora es editable.', 'success');
        } catch (error) {
            alerta('error', 'Ha ocurrido un error al habilitar la edición.', 'error');
        }
    };

    const sendToCheck = async () => {
        const result = await alertQuestion('Info', '¿Estás seguro de enviar a revisión?', 'info');
        if (!result) return;
        try {
            await sendToCheckService(caseData.value.registro_id);
            await getInfo(case_id.value); 
            alerta('Listo', 'Se ha cambiado correctamente.', 'success');
        } catch (error) {
            alerta('error', 'Ha ocurrido un error.', 'error');
        }
    };

    const changeStatus = async () => {
        try {
            const data = {
                observacion: formStatus.value.observacion,
                estatus_caso: formStatus.value.status,
                estatus_2: formStatus.value.status2,
            };
            await changeStatusService(caseData.value.registro_id, data);
            await getInfo(case_id.value); 
            alerta('Listo', 'Se ha cambiado correctamente.', 'success');
            showTypes.value = false;
        } catch (error) {
            console.error("Error al cambiar el estatus:", error);
        }
    };

    const getStatus = async () => {
          try {
              const response = await getTramiteStatus();
              status.value = response;
          } catch (error) {
              console.error("Error al obtener los estatus:", error);
          }
    };

    return {
        caseData,
        store,
        showTypes,
        formStatus,
        status,
        showHistory,
        getInfo,
        getStatus,
        createPunto,
        sendToCheck,
        changeStatus,
        editPunto,
    };
};