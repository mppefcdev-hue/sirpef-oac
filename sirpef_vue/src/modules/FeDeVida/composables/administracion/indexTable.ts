// src/modules/OAC/composables/useCasesTable.ts
import { reactive, onMounted, ref, watch } from "vue"
import { onBeforeRouteUpdate } from "vue-router" // Eliminamos useRoute porque ya lo tenemos desde useTableGrid
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
    rows: [],
    links: [],
    page: "1",
    search: "",
    sort: "",
    direction: ""
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
  } = useTableGrid(data, "/cases");

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
        data.rows = response.data.data;
        data.links = response.data.rows.links;
        data.search = response.data.search || '';
        data.sort = response.data.sort || '';
        data.direction = response.data.direction || '';
      })
      .catch((error) => {
        console.log("Error al cargar casos:", error.response?.data || error.message);
      });
  };


  const getCaseTypes = async () => {
    try {
      const response = await getTypeCase();
      types.value = response.data;
    } catch (error) {
      console.error("Error al obtener los tipos de caso:", error);
    }
  };


  const deleteCaso = async (registro_id: string) => {
    const choice = await alertQuestion('Info', `¿Estas seguro que deseas eliminar el caso?`, 'question')

    if (!choice) return
    try {
      const response = await deleteCasoService(registro_id);
      alerta("Correcto", response.msg, "success")
      await getCases(new URLSearchParams(route.query as Params));
    } catch (error) {
      console.error("Error al obtener los tipos de caso:", error);
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
    } catch (error) {
      if (error.response?.status != 200) {
        alerta("Error", error.response.data.msg, "info")
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

    router.push({ path: '/cases', query });
  };


  onBeforeRouteUpdate(async (to, from) => {
    if (to.query !== from.query) {
      selectedTypeCase.value = (to.query.tipo_caso_id as string) || '';
      await getCases(
        new URLSearchParams(to.query as Params)
      );
    }
  });

  onMounted(() => {
    selectedTypeCase.value = (route.query.tipo_caso_id as string) || '';

    getCases(
      new URLSearchParams(route.query as Params)
    );
    getCaseTypes();
  });

  watch(() => data.rows, (newRows) => {
    if (route.query.search && newRows.length > 0) {
      const search_query = (route.query.search as string).trim();
      // Try to find exact match by cedula
      const found = newRows.find((r: any) => r.cedula == search_query);
      if (found) {
        foundCaseId.value = found.registro_id;
      }
    }
  });

  return {
    errors,
    data,
    router,
    result,
    types,
    selectedTypeCase,
    setSearch,
    setSort,
    GetUser,
    filterByCaseType,
    deleteCaso,
    foundCaseId
  };
}