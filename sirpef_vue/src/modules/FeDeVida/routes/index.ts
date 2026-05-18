import auth from "@/middleware/auth"
import admin from "@/middleware/admin"

export default [
    {
        path: "/cases",
        name: "casesIndex",
        meta: { middleware: [auth] },
        component: () => import("@/modules/FeDeVida/views/index.vue").then(m => m.default)
    },

    {
        path: "/fedevida/form",
        name: "fedevidaForm",
        meta: { layout: "empty" },
        component: () => import("@/modules/FeDeVida/views/Register.vue").then(m => m.default)
    },

    {
        path: "/fedevida/presencial/:id?",
        name: "fedevidaPresencial",
        meta: { middleware: [auth] },
        component: () => import("@/modules/FeDeVida/views/participacionPresencial.vue").then(m => m.default)
    },

    {
        path: "/fedevida/correo",
        name: "fedevidaCorreo",
        meta: { middleware: [auth, admin] },
        component: () => import("@/modules/FeDeVida/views/participacionEmail.vue").then(m => m.default)
    },
    {
        path: "/fedevida/Solicitudes",
        name: "fedevidaSolis",
        meta: { middleware: [auth, admin] },
        component: () => import("@/modules/FeDeVida/views/solicitudesJubi.vue").then(m => m.default),
    },
    {
        path: "/fedevida/checkpdf/:cedula",
        meta: { layout: "empty" },
        name: "checkpdf",
        component: () => import("@/modules/FeDeVida/views/checkPDF.vue").then(m => m.default),
    },

      {
        path: '/oac/memos',
        name: 'oac.memos.create',
        meta: { middleware: [auth], layout: "default" },
        component: () => import('@/modules/OAC/views/Memos/MemoCreate.vue'),
    },
    {
        path: "/cases/types",
        name: "types-cases",
        meta: { middleware: [auth], layout: "default" },
        component: () => import("@/modules/FeDeVida/views/casosTypes.vue").then(m => m.default)
    },

    {
        path: "/cases/pdc/:id/:pdc_id?",
        name: "pdc",
        meta: { middleware: [auth], layout: "default" },
        component: () => import("@/modules/FeDeVida/views/View_punto_de_venta.vue").then(m => m.default)
    },

       {
        path: "/pdf-tester",
        name: "pdf",
        meta: { middleware: [auth], layout: "default" },
        component: () => import("@/components/Form-pdc/PDFTester.vue").then(m => m.default)
    },
    {
        path: '/ficha-test',
        name: 'fichaTest',
        meta: { middleware: [auth] }, // Protegida por autenticación
        component: () => import('@/components/Form-pdc/FichaOficio.vue')
    },
       {
        path: '/oficio-reintegro-test',
        name: 'oficioReintegroTest',
        meta: { middleware: [auth] },
        component: () => import('@/components/Form-pdc/OficioReintegro.vue')
    },

    {
        path: "/fedevida/cases/:casePersona_id",
        name: "case-fedevida",
        meta: { middleware: [auth, admin], layout: "default" },
        component: () => import("@/modules/FeDeVida/components/modalInfo.vue").then(m => m.default)
    },

        {
        path: "/casos/administracion",
        name: "casesAdminIndex",
        meta: { middleware: [auth] },
        component: () => import("@/modules/FeDeVida/views/administracion/index.vue").then(m => m.default)
    },

    {
        path: "/casos/administracion/form",
        name: "CasesAdminForm",
        meta: { middleware: [auth]},
        component: () => import("@/modules/FeDeVida/views/administracion/form.vue").then(m => m.default)
    },
]
