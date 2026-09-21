// src/modules/FeDeVida/composables/administracion/indexTable.ts
import { reactive, onMounted, ref, watch } from "vue"
import { onBeforeRouteUpdate } from "vue-router"
import useTableGrid from "@/composables/useTableGrid"
import useHttp from "@/composables/useHttp"
import { deleteCasoService, getPagosCasos, getTypeCase } from "../../services"
import { alerta } from "@/utils/alert"
import Http from "@/utils/Http"
import convertDateISO from "@/utils/convertDateISO"
import { alertQuestion } from "@/utils/alertQuestion"

type Params = string | string[][] | Record<string, string> | URLSearchParams | undefined

export default () => {
  const data = reactive({
    rows: [] as any[],
    links: [] as any[],
    page: "1",
    search: "",
    sort: "",
    direction: ""
  });

  const filters = reactive({
    mes: '',
    factura: '',
    tiene_factura: '',
    tipo_pago: '',
    saldo_deudor: '',
    saldo_acreedor: '',
    proveedor: '',
    paciente: '',
    punto_cuenta: '',
    orden_pago: '',
    estatus_pago: ''
  });

  const result = ref({});
  const types = ref([]);
  const selectedTypeCase = ref('');
  const foundCaseId = ref(null);

  const { errors } = useHttp();

  const {
    route,
    router,
    setSearch,
    setSort,
  } = useTableGrid(data, "/casos/administracion");

  const syncFiltersFromRoute = (query: Record<string, any>) => {
    data.search = (query.search as string) || '';
    filters.mes = (query.mes as string) || '';
    filters.factura = (query.factura as string) || '';
    filters.tiene_factura = (query.tiene_factura as string) || '';
    filters.tipo_pago = (query.tipo_pago as string) || '';
    filters.saldo_deudor = (query.saldo_deudor as string) || '';
    filters.saldo_acreedor = (query.saldo_acreedor as string) || '';
    filters.proveedor = (query.proveedor as string) || '';
    filters.paciente = (query.paciente as string) || '';
    filters.punto_cuenta = (query.punto_cuenta as string) || '';
    filters.orden_pago = (query.orden_pago as string) || '';
    filters.estatus_pago = (query.estatus_pago as string) || '';
    selectedTypeCase.value = (query.tipo_caso_id as string) || '';
  };

  const getCases = (routeQuery: URLSearchParams) => {
    const filteredParams = new URLSearchParams();
    Array.from(routeQuery.entries()).forEach(([key, value]) => {
      if (value) {
        filteredParams.append(key, value as string);
      }
    });

    const queryString = filteredParams.toString();

    return getPagosCasos(queryString)
      .then((response) => {
        data.rows = response.data.rows || response.data.data || [];
        data.links = response.data.links || response.data.meta?.links || response.data.rows?.links || [];
        data.search = response.data.search || data.search || '';
        data.sort = response.data.sort || '';
        data.direction = response.data.direction || '';
      })
      .catch((error) => {
        console.error("Error al cargar pagos:", error.response?.data || error.message);
      });
  };

  const applyFilters = () => {
    const query: Record<string, string> = { ...route.query as Record<string, string>, page: '1' };

    if (data.search && data.search.trim()) query.search = data.search.trim();
    else delete query.search;

    if (filters.mes) query.mes = filters.mes;
    else delete query.mes;

    if (filters.factura && filters.factura.trim()) query.factura = filters.factura.trim();
    else delete query.factura;

    if (filters.tiene_factura) query.tiene_factura = filters.tiene_factura;
    else delete query.tiene_factura;

    if (filters.tipo_pago) query.tipo_pago = filters.tipo_pago;
    else delete query.tipo_pago;

    if (filters.saldo_deudor) query.saldo_deudor = filters.saldo_deudor;
    else delete query.saldo_deudor;

    if (filters.saldo_acreedor) query.saldo_acreedor = filters.saldo_acreedor;
    else delete query.saldo_acreedor;

    if (filters.proveedor && filters.proveedor.trim()) query.proveedor = filters.proveedor.trim();
    else delete query.proveedor;

    if (filters.paciente && filters.paciente.trim()) query.paciente = filters.paciente.trim();
    else delete query.paciente;

    if (filters.punto_cuenta && filters.punto_cuenta.trim()) query.punto_cuenta = filters.punto_cuenta.trim();
    else delete query.punto_cuenta;

    if (filters.orden_pago && filters.orden_pago.trim()) query.orden_pago = filters.orden_pago.trim();
    else delete query.orden_pago;

    if (filters.estatus_pago) query.estatus_pago = filters.estatus_pago;
    else delete query.estatus_pago;

    router.push({ path: '/casos/administracion', query });
  };

  const clearFilters = () => {
    data.search = '';
    filters.mes = '';
    filters.factura = '';
    filters.tiene_factura = '';
    filters.tipo_pago = '';
    filters.saldo_deudor = '';
    filters.saldo_acreedor = '';
    filters.proveedor = '';
    filters.paciente = '';
    filters.punto_cuenta = '';
    filters.orden_pago = '';
    filters.estatus_pago = '';
    selectedTypeCase.value = '';
    router.push({ path: '/casos/administracion' });
  };

  const getCaseTypes = async () => {
    try {
      const response = await getTypeCase();
      types.value = response.data;
    } catch (error) {
      console.error("Error al obtener los tipos de caso:", error);
    }
  };

  const deleteCaso = async (pago_id: string | number) => {
    const choice = await alertQuestion('Info', `¿Estás seguro que deseas eliminar el registro de pago?`, 'question')

    if (!choice) return
    try {
      const response = await deleteCasoService(pago_id);
      alerta("Correcto", response.msg || "Pago eliminado exitosamente", "success")
      await getCases(new URLSearchParams(route.query as Params));
    } catch (error: any) {
      console.error("Error al eliminar el pago:", error);
      alerta("Error", error.response?.data?.msg || "Error al eliminar", "error")
    }
  };

  const GetUser = async (cedula: string) => {
    result.value = {}
    try {
      const res = await Http.get(`/api/oac/findByCedula/${cedula}`);
      if (res.data[1] && res.data[1].Fecha) {
        res.data[1].Fecha = convertDateISO(res.data[1].Fecha)
      }
      result.value = res.data
      return true
    } catch (error: any) {
      if (error.response?.status != 200) {
        alerta("Error", error.response?.data?.msg || "Persona no encontrada", "info")
      }
      return false
    }
  };

  const filterByCaseType = () => {
    const query = { ...route.query };

    if (selectedTypeCase.value) {
      query.tipo_caso_id = selectedTypeCase.value;
    } else {
      delete query.tipo_caso_id;
    }
    query.page = '1';

    router.push({ path: '/casos/administracion', query });
  };

  onBeforeRouteUpdate(async (to, from) => {
    if (to.query !== from.query) {
      syncFiltersFromRoute(to.query);
      await getCases(new URLSearchParams(to.query as Params));
    }
  });

  onMounted(() => {
    syncFiltersFromRoute(route.query);
    getCases(new URLSearchParams(route.query as Params));
    getCaseTypes();
  });

  watch(() => data.rows, (newRows) => {
    if (route.query.search && newRows.length > 0) {
      const search_query = (route.query.search as string).trim();
      const found = newRows.find((r: any) => r.cedula == search_query || r.orden_pago == search_query);
      if (found) {
        foundCaseId.value = found.registro_id;
      }
    }
  });

  return {
    route,
    errors,
    data,
    filters,
    router,
    result,
    types,
    selectedTypeCase,
    setSearch,
    setSort,
    applyFilters,
    clearFilters,
    GetUser,
    filterByCaseType,
    deleteCaso,
    foundCaseId
  };
}
