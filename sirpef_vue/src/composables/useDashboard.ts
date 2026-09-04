import { onMounted, onUnmounted, ref } from "vue"
import Http from "@/utils/Http/index"
import { getDay } from "@/utils/GetDay"
import ApexCharts from 'apexcharts';
import { LoadNotificacion } from "@/utils/notificaciones"
import { getCorte } from "@/modules/SIRPEF/services/cortesia";
import { getItemsCounted } from "@/modules/FeDeVida/services";


export default () => {

  const enlaces = [
    {
      path: "oneVote",
      title: "participación individual",
      icon: "fa-solid fa-hand-point-up",
      descripcion: "Ingrese el número de cédula que desea registrar en el sistema en el formulario"
    },
    {
      path: "masive",
      title: "Reporte masivo",
      icon: "fa-solid fa-user-group",
      descripcion: "Ingrese los números de cédulas que desea registrar en el sistema en el formulario"
    },
    {
      path: "authpart",
      title: "Participación autorizados",
      icon: "fa-solid fa-handshake",
      descripcion: "Ingrese los números de cédulas de los autorizados que desea registrar en el sistema en el formulario"
    },

    {
      path: "loadforcsv",
      title: "Reporte CSV",
      icon: "fa-solid fa-file-csv",
      descripcion: "Suba un archivo CSV en el sistema con las cédulas que desea registrar su participación"
    },

    {
      path: "fedevida/presencial",
      title: "Fe de vida",
      icon: "heart-pulse",
      descripcion: "Ingrese los números de cédulas de los jubilados que desea registrar en el sistema en el formulario"
    },

    {
      path: "personal",
      title: "Personal",
      icon: "fa-solid fa-people-group",
      descripcion: "Administre y gestione la información de los empleados del sistema"
    },
    {
      path: "events",
      title: "Eventos",
      icon: "fa-solid fa-calendar-days",
      descripcion: "Cree, edite y gestione eventos del ministerio en el sistema"
    },
    {
      path: "users",
      title: "Usuarios",
      icon: "fa-solid fa-users",
      descripcion: "Maneje usuarios del sistema, incluyendo creación, edición y eliminación de cuentas"
    },

    {
      path: "/fedevida/presencial",
      title: "Fe de vida",
      icon: "fa-solid fa-heart-pulse",
      descripcion: "Gestionar la participación de jubilados o pensionados de forma presencial."
    },

    {
      path: "/fedevida/correo",
      title: "Fe de vida (Correo)",
      icon: "fa-solid fa-hand-holding-heart",
      descripcion: "Gestionar la participación de jubilados o pensionados que hayan enviado correos."
    },

    {
      path: "/fedevida/Solicitudes",
      title: "Fe de vida (Solicitudes)",
      icon: "fa-solid fa-list-check",
      descripcion: "Acepte o rechaze solicitudes de los jubilados o pensionados"
    },

    {
      path: "personal",
      title: "Personal",
      icon: "fa-solid fa-people-group",
      descripcion: "Administre y gestione la información de los empleados del sistema"
    },
  ]

  const itemsCounted = ref([] as any[])

  const DonutCortesias = ref({
    labels: ["Disponibles", "Entregadas"],
    datasets: [{
      label: 'Cortesias',
      data: [1, 1],
      backgroundColor: ["#31A9D9", "#C40B0B"],
    }],
  } as any)

  const DatosResumen = ref({
    "d": [
      "Si Particip\u00f3",
      0
    ],
    "e": [
      "No Particip\u00f3",
      0
    ],
    "b": [
      "Total de Participaci\u00f3n",
      0
    ],
    "c": [
      "Participantes faltantes",
      0
    ],
    "a": [
      "Total de Personal",
      0
    ]
  } as any)

  const PieData = ref({
    labels: ["Si participó", "No participó", "Participantes Faltantes"],
    datasets: [{
      label: 'Resultados de Participación',
      data: [0, 0, 0],
      backgroundColor: ["#31A9D9", "#C40B0B", "#1A4968"],

    }]
  } as any);

  const PieSex = ref({
    data: {
      "M": {
        "Si": 0,
        "No": 0,
        "faltantes": 0
      },
      "F": {
        "Si": 0,
        "No": 0,
        "faltantes": 0
      },
      "Total_de_Personal": 0
    },
    active: "M",
    graph: {
      labels: ["Si Participaron", "No Participaron", `Participantes Faltantes`],
      datasets: [{
        label: 'Han participado un total',
        data: [0, 0, 0],
        backgroundColor: ["#1c55ff", "#879ddd", "#0d47a1"]
      }],
    },
    backgroundColor: ["#1c55ff", "#879ddd", "#0d47a1"]
  } as any);

  const DonutSex = ref({
    labels: ["Masculino", "Femenino"],
    datasets: [{
      label: 'Resultados de Participación',
      data: [1, 1],
      backgroundColor: ["#31A9D9", "#C40B0B"],
    }],
  } as any);

  const TableAges = ref({
    "M": {
      "Si": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "No": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "faltantes": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "Total": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      }
    },
    "F": {
      "Si": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "No": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "faltantes": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      },
      "Total": {
        "18-25": 0,
        "26-33": 0,
        "34-41": 0,
        "42-49": 0,
        "50-55": 0,
        "56+": 0
      }
    }
  } as any);

  const PiePersonas = ref({
    labels: ["Si participó", "No participó", "Participantes Faltantes"],
    datasets: [{
      label: 'Resultados de Participación',
      data: [0, 0, 0],
      backgroundColor: ["#31A9D9", "#C40B0B"],
    }]
  } as any);


  const Unidades_Ascritas = ref([]);


  const Top_Unidades = ref({
    mayor: [{
      id: 1,
      name: "OAI",
      total_votos: 2,
      total_personas: 12,
      porcentaje_participacion: 0.4
    },
    {
      id: 2,
      name: "OTIC",
      total_votos: 1,
      total_personas: 12,
      porcentaje_participacion: 0.4
    }
    ],
    menor: [
      {
        id: 1,
        name: "OAI",
        total_votos: 2,
        total_personas: 12,
        porcentaje_participacion: 0.4
      },
      {
        id: 2,
        name: "OTIC",
        total_votos: 1,
        total_personas: 12,
        porcentaje_participacion: 0.4
      }
    ],
    todos: [
      {
        id: 1,
        nombre: "OAI",
        total_votos: 2,
        total_personas: 12,
        porcentaje_participacion: 0.4
      },
      {
        id: 2,
        nombre: "OTIC",
        total_votos: 1,
        total_personas: 12,
        porcentaje_participacion: 0.4
      }
    ]
  });

  const solicitudesData = ref([])

  const DataFiltered = ref({
    data: [],
    texto: "",
    total: -1,
    selectedUnid: ''
  })


  const FechaOn = ref(getDay())
  const FechaHas = ref(getDay())
  const Info = ref([])

  const intervalId = ref(null)
  const intervalo = ref(60000)

  const typesCases = ref({
    seletected: '0',
    types: []
  })
  const graphToSee = ref([] as Number[])


  const changeInterval = (interv: number) => {
    clearInterval(intervalId.value);
    intervalId.value = setInterval(() => LoadALl(true), interv);
    intervalo.value = interv
  }


  function obtenerHorasDelDia(minHour, maxHour) {
    var horas = [];
    for (var i = minHour; i <= maxHour; i++) {
      horas.push(String(i).padStart(2, '0') + ':00');
    }
    return horas;
  }

  const GetData = async () => {
    try {
      const url = `/api/registro/count/${FechaOn.value}/${FechaHas.value}/${typesCases.value.seletected}`
      const url2 = `/api/registro/get-hourly-vote-data/${FechaOn.value}/${FechaHas.value}`
      const response = await Http.get(url);
      DatosResumen.value = response.data
      const { data } = await Http.get(url2);

      Info.value = Object.keys(response.data).filter(e => e != "a").map(element => {
        return {
          label: response.data[element][0],
          data: response.data[element][1],
          bg: response.data[element][2],
          // bg: element == "d" ? '#C80036' : element == "b" ? " #31A9D9" : "#1A4968"
        }
      })

      PieData.value = {
        labels: Info.value.map(e => e.label),
        datasets: [{
          label: 'Resultados de Participación',
          data: Info.value.map(e => e.data),
          backgroundColor: Info.value.map(e => e.bg),
        }]
      }

      const totalKey = response.data?.a ? 'a' : Object.keys(response.data || {})[0];
      const totalLabel = totalKey && response.data[totalKey] ? response.data[totalKey][0] : 'Total de Casos';
      const totalValue = totalKey && response.data[totalKey] ? response.data[totalKey][1] : 0;

      PiePersonas.value = {
        labels: [totalLabel],
        datasets: [{
          label: 'Resultados de Participación',
          data: [totalValue],
          backgroundColor: '#1A4968',
        }]
      }

      loadEstLine(data)
    } catch (error) {
      loadEstLine()
      Info.value = Object.keys(DatosResumen.value).filter(e => e != "a").map(element => {
        return {
          label: DatosResumen.value[element][0],
          data: DatosResumen.value[element][1],
          bg: element == "d" ? '#C80036' : element == "e" ? '#8B5CF6' : element == "b" ? " #31A9D9" : "#1A4968"
        }
      })
    }

  }


  const GetTop5Unidades = async (SendNoti: boolean) => {
    try {
      const response = await Http.get(`/api/registro/porcentaje-participacion/${FechaOn.value}/${FechaHas.value}`);
      const info = response.data.filter((obj1) => Unidades_Ascritas.value.some((obj2) => obj1.id == obj2.id));
      const dataCopiaMayor = [...info];
      const dataCopiaMenor = [...info];

      const topMayor = dataCopiaMayor.sort((a, b) => b.porcentaje_participacion - a.porcentaje_participacion).splice(0, 100);
      const topMenor = dataCopiaMenor.sort((a, b) => a.porcentaje_participacion + b.porcentaje_participacion).splice(0, 100);

      if (topMenor.length > 0) Top_Unidades.value.menor = topMenor
      if (topMayor.length > 0) Top_Unidades.value.mayor = topMayor
      //RenderChartBar(info)

      if (!SendNoti) return
      //LoadNotificacion(`Estos entes tienen poca participación: ${topMenor.map(element => element.name.split("-")[0]).join(", ")}.`)
      //LoadNotificacion(`Entes con mayor participación: ${topMayor.map(element => element.name.split("-")[0]).join(", ")}.`)
    } catch (error) {
      console.log(error)
    }
  }



  const getItems = async () => {
    try {
      const response = await getItemsCounted(FechaOn.value, FechaHas.value)
      if (response.length == 0) return
      itemsCounted.value = response
    } catch (error) {
      console.log(error)
    }
  }


  const GetUnidAsc = async () => {
    const response = await Http.get("/api/registro/unidades-adscritas");
    Unidades_Ascritas.value = response.data.unidad_adscrita
    Top_Unidades.value.todos = response.data.unidad_adscrita

  }

   const GetTypesCasesChart = async () => {
    const response = await Http.get("/api/registro/registro-total");
    solicitudesData.value = response.data.map(e => {
      return {
        value: e.cantidad_registros,
        id: e.id,
        name: e.tipo,
        nombre: e.tipo,
        color: e.color ? e.color : '#e97a18'
      }
    })
  }

  const GetTypesCases = async () => {
    const response = await Http.get("/api/oac/tipoCaso");
    const types = response.data.map(e => {
      return {
        id: e.id,
        nombre: e.tipo
      }
    })

    const seletected = localStorage.getItem('typeCase')

    typesCases.value = {
      types,
      seletected: seletected ? seletected : '0',
    }

    typesCases.value.types.push({
      id: 0,
      nombre: 'Todos'
    })
  }

  const redirectToStatic = async (id: any) => {
    const caseID = id ? id : '0'
    localStorage.setItem('typeCase', caseID)
    typesCases.value.seletected = caseID
    ChangueDashboard(1)
  }

  const GetSex = async () => {
    const response = await Http.get(`/api/registro/countSex/${FechaOn.value}/${FechaHas.value}`);


    PieSex.value = {
      data: response.data,
      active: PieSex.value.active || "F"
    }
    //[response.data.M.Si || 1, response.data.F.Si || 1],
    // [response.data.total_masculino, response2.data.total_femenino]
    DonutSex.value = {
      labels: ["Masculino", "Femenino"],
      datasets: [{
        label: 'Resultados de Participación',
        data: [response.data.M.Si, response.data.F.Si],
        backgroundColor: ["#31A9D9", "#C40B0B"],
      }]
    }
    ChangueSexData(PieSex.value.active)
  }

  const GetSexAge = async () => {
    const response = await Http.get(`/api/registro/countSexAge/${FechaOn.value}/${FechaHas.value}`);
    TableAges.value = response.data
  }

  const PostUnidAsc = async (id: Number) => {
    await Http.post("/api/registro/obtener-unidad-adscrita", {
      unidad_adscrita_id: id
    });
    LoadALl(false)
  }

  const setTypeCase = async (case_id: string) => {
    const caseID = case_id ? case_id : '0'
    localStorage.setItem('typeCase', caseID)
    typesCases.value.seletected = caseID
    
    LoadALl(false)
  }


  const loadEstLine = (data?: any) => {

    const options = {
      series: [
        {
          name: "Si participó",
          data: data.map(e => e.votos_true) || [0, 0, 0],
          color: "#C80036",
        },
        {
          name: "No participó",
          data: data.map(e => e.votos_false) || [0, 0, 0],
          color: "#0C359E",
        },
      ],
      chart: {
        height: "100%",
        maxWidth: "100%",
        type: "area",
        dropShadow: {
          enabled: false,
        },
        toolbar: {
          show: false,
        },
      },
      tooltip: {
        enabled: true,
      },
      legend: {
        show: false
      }, // legend
      fill: {
        type: "gradient",
        gradient: {
          opacityFrom: 0.55,
          opacityTo: 0,
        },
      },
      dataLabels: {
        enabled: false,
        style: {
          cssClass: 'rounded-3xl text-xs font-medium'
        },
      },
      xaxis: {
        categories: obtenerHorasDelDia(data[0].hora || 1, data[data.length - 1].hora || 24),
        labels: {
          show: true,
        },
        axisBorder: {
          show: false,
        },
        axisTicks: {
          show: false,
        },
      },
      yaxis: {
        show: true,
        labels: {
          formatter: (value) => {
            return value;
          },
        }
      },
    }


    if (document.getElementById("data-series-chart") && typeof ApexCharts !== 'undefined') {
      document.getElementById("data-series-chart").innerHTML = ""
      const chart = new ApexCharts(document.getElementById("data-series-chart"), options);
      chart.render();
    }
  }



  const LoadALl = (SendNoti: boolean) => {
    GetData()
    GetUnidAsc()
    GetTop5Unidades(SendNoti)
    GetSex()
    GetTypesCasesChart()
    GetSexAge()
    getCortesias()
    getItems()
  }


  const FiltrarUnidad = (texto: string, total: number) => {
    let contador = 0
    let UnidFil = []


    if (texto == "Si Participó" || texto == "No Participó") {
      const elementToView = texto == "Si Participó" ? "votos_true" : "votos_false"

      const unidades = Top_Unidades.value.todos.sort((a, b) => b[elementToView] - a[elementToView])
      for (let index = 0; index < unidades.length; index++) {
        const element = unidades[index];
        if (element[elementToView] > 0) {
          contador += element[elementToView]

          if (contador > total) break
          UnidFil.push(element)
        }
      }


    } else {
      UnidFil = Top_Unidades.value.todos.filter(element => texto == 'Total de Participación' ? element.total_votos > 0 : (element.total_personas - element.total_votos) != 0)

    }

    UnidFil = UnidFil.sort((a, b) => b.porcentaje_participacion - a.porcentaje_participacion).map(element => {
      return {
        ...element,
        total_votos: texto == "Si Participó" ? element.votos_true : texto == "No Participó" ? element.votos_false : element.total_votos,
      }
    })


    return UnidFil
  }

  const FilterPersonas = async (texto: string, total: number, unidad: string, uni_or_est: string) => {
    let voto_status

    /*
      0 = Rechazado
      1 = si participo
      2 = total participantes
      3 = sin participacion
      4 = pendiente
    */


    let encontrado = false;
    for (const letra in DatosResumen.value) {
      if (DatosResumen.value[letra][0] === texto || DatosResumen.value[letra][0] === uni_or_est) {
        voto_status = letra;
        encontrado = true;
        break;
      }
    }

    if (!encontrado && uni_or_est == 'unidades') {
      voto_status = 1;
    }
    else if (!encontrado) {
      voto_status = 4;
    }

    const unid_id = Top_Unidades.value.todos.filter(e => e.nombre == unidad)[0].id

    const { data } = await Http.get(`/api/registro/get_per_x_unid/${unid_id}/${voto_status}/${typesCases.value.seletected}/${FechaOn.value}/${FechaHas.value}`)

    const dataMaped = data.map(e => {
      if (e.descripcion) {
        return {
          ...e,
        }
      } else {
        return {
          ...e,
          descripcion: e.voto == false ? 'Rechazado' : 'Pendiente'
        }
      }
    })
    return dataMaped
  }



  onMounted(() => {
    GetTypesCases()
    LoadALl(false)
    intervalId.value = setInterval(() => LoadALl(true), intervalo.value);
  })

  onUnmounted(() => {
    clearInterval(intervalId.value);
  })

  const ChangueSexData = (gender: string) => {
    const info = PieSex.value.data[gender]
    const stringLabel = gender == "F" ? "Femenino" : "Masculino"
    const backgroundColor = gender == "F" ? ["#A70505", "#C80036", "#C40B0B"] : ["#1c55ff", "#879ddd", "#0d47a1"]

    const data = {
      labels: ["Si Participaron", "No Participaron", `Participantes Faltantes`],
      datasets: [{
        label: 'Han participado un total',
        data: [info.Si, info.No, info.faltantes],
        backgroundColor
      }]
    }

    PieSex.value = {
      data: PieSex.value.data,
      active: gender,
      graph: data,
      backgroundColor
    }
  }

  const RenderChartBar = (unidades: any) => {
    const sortUnidades = unidades.sort((a, b) => b.porcentaje_participacion - a.porcentaje_participacion)
    let options = {
      series: [{
        data: sortUnidades.map(e => `${e.porcentaje_participacion.toFixed(0)}%`),
        color: "#010C41",
        name: "Porcentaje de Participaron",

      }],
      chart: {
        type: 'bar',
        dropShadow: {
          enabled: false,
        },
        toolbar: {
          show: false,
        },
      },
      plotOptions: {
        bar: {
          horizontal: true,
        }
      },
      dataLabels: {
        enabled: false
      },
      xaxis: {
        categories: sortUnidades.map(e => e.name.split("-")[0])
      }
    };

    if (document.getElementById("barUnid") && typeof ApexCharts !== 'undefined') {
      document.getElementById("barUnid").innerHTML = ""
      var chart = new ApexCharts(document.querySelector("#barUnid"), options);
      chart.render();
    }


  }




  const ChangueDashboard = (value: number) => {
    graphToSee.value = [value]
    LoadALl(false)

    window.scrollTo({
      top: 880,
      left: 0,
      behavior: 'smooth'
    });
  }


  const SetDates = (e: any) => {
    const form = new FormData(e.currentTarget as HTMLFormElement)
    FechaOn.value = form.get("Desde") as string
    FechaHas.value = form.get("Hasta") as string
    LoadALl(false)
  }

  const getCortesias = async () => {
    try {
      const res = await getCorte()
      const { cortesia_entregada, cortesia, cortesias_faltantes } = res.data

      DonutCortesias.value = {
        labels: ["Disponibles", "Entregadas"],
        datos: { cortesia_entregada, cortesia, cortesias_faltantes },
        datasets: [{
          label: 'Cortesias',
          data: [cortesias_faltantes, cortesia_entregada],
          backgroundColor: ["#db3823", "#5176b2"],
        }],
      }

    } catch (error) {
      console.error(error)
    }
  }


  return {
    DatosResumen,
    PieData,
    LoadALl,
    Unidades_Ascritas,
    itemsCounted,
    PostUnidAsc,
    Info,
    PiePersonas,
    typesCases,
    FechaOn,
    FechaHas,
    changeInterval,
    intervalo,
    Top_Unidades,
    print,
    DataFiltered,
    FiltrarUnidad,
    FilterPersonas,
    PieSex,
    ChangueSexData,
    TableAges,
    DonutSex,
    enlaces,
    graphToSee,
    ChangueDashboard,
    SetDates,
    DonutCortesias,
    setTypeCase,
    redirectToStatic,
    solicitudesData
  }
}
