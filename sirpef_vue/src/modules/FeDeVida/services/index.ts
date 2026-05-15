import Http from "@/utils/Http"

export const getJubilados = async () => {
    const response  = await Http.get(`/api/registro/Jubilados`)
    return response.data
}

export const getItemsCounted = async (fecha_inicio: string, fecha_desde: string) => {
    const response  = await Http.get(`/api/registro/fe-vida-count/${fecha_inicio}/${fecha_desde}`)
    return response.data
}

export const getJubilado = async (cedula: string) => {
    const response  = await Http.get(`/api/findByCedula/fedevida/${cedula}`)
    return response.data
}

export const getSolicitudes = async () => {
    const response  = await Http.get(`/api/registro/solicitudes/fe-de-vida`)
    return response.data
}

export const AcceptSolicitud = async (payload: any) => {
    const response  = await Http.post(`/api/registro/solicitud-estatus/`, payload)
    return response.data
}

export const sendJubilado = async (id: string, payload: any) => {
    const response  = await Http.post(`/api/feDeVida/${id}`, payload)
    return response.data
}



// casos OAC

export const getAllCasos = async (query: string) => {
    const response  = await Http.get(`/api/oac/index?${query}`)
    return response
}

export const getCaseCedula = async (cedula: string) => {
    const response  = await Http.get(`/api/oac/find-cedu/${cedula}`)
    return response.data
}

export const getCaseSingle = async (id: string) => {
    const response  = await Http.get(`/api/oac/show/${id}`)
    return response.data
}

export const getTypeCase = async () => {
    const response  = await Http.get(`/api/oac/tipoCaso`)
    return response
}

export const getPDC = async (id: string) => {
    const response  = await Http.get(`/api/oac/punto-cuenta/${id}`)
    return response.data
}

export const registerCase = async (payload: any) => {
    const response  = await Http.post(`/api/oac/create`, payload)
    return response.data
}

export const editCase = async (id: string, payload: any) => {
    const response  = await Http.post(`/api/oac/casos/${id}`, payload)
    return response.data
}

export const getCase = async (payload: any) => {
    const response  = await Http.post(`/api/oac/create`, payload)
    return response.data
}

export const getHistorialCaso = async (id: string) => {
    const response  = await Http.get(`/api/oac/historial/${id}`)
    return response.data
}

export const deleteCasoService = async (id: any) => {
    const response  = await Http.delete(`/api/oac/pagos/${id}`)
    return response.data
}

/*PUNTO DE CUENTA*/

export const createPDC = async (id: string, payload: any) => {
    const response  = await Http.post(`/api/oac/crear-punto/${id}`, payload)
    return response.data
}

export const enableEditPDC = async (id: string) => {
    const response  = await Http.put(`/api/oac/estatus-puntos-cuenta/${id}`, {})
    return response.data
}

export const updatePDC = async (id: string, payload: any) => {
    const response  = await Http.put(`/api/oac/puntos-cuenta/${id}`, payload)
    return response.data
}


/*export const changeStatusService = async (registro_id: string, data: any) => {
    const response = await Http.post(`/api/oac/casos-estatus/${registro_id}`, data)
    return response
}*/


export const changeStatusService = async (registro_id: string, data: any) => {
    const response = await Http.post(`/api/oac/casos-estatus/${registro_id}/${data.estatus_2}`, data)
    return response
}

export const sendToCheckService = async (registro_id: string) => {
    const response = await Http.put(`/api/oac/casos-revision/${registro_id}`, {})
    return response
}

export const getTreeviewData = async () => {
    const response = await Http.get(`/api/registro/treeview-stats`);
    return response.data;
}





// status

export const getTramiteStatus = async () => {
    const response  = await Http.get(`/api/oac/estatus-tramite/`)
    return response.data
}



// administracion


export const getPagosCasos = async (query: string) => {
    const response  = await Http.get(`/api/oac/pagos?${query}`)
    return response
}

export const registerPay = async (id: string, payload: any) => {
    const response  = await Http.post(`/api/oac/registrar-pago/${id}`, payload)
    return response.data
}