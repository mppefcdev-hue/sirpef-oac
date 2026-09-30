import {createRouter, createWebHistory} from 'vue-router'
import type {RouteRecordRaw} from 'vue-router'
import { computed } from "vue"
import { useAuthStore } from '@/modules/Auth/stores'
import auth from "@/middleware/auth"
import middlewarePipeline from "@/router/middlewarePipeline"
import AuthRoutes from "@/modules/Auth/routes"
import AuthorizationRoutes from "@/modules/Authorization/routes"
import UserRoutes from "@/modules/User/routes"
import SirpefRoutes from "@/modules/SIRPEF/routes"
import AutorizadosRoutes from "@/modules/Autorizados/routes"
import feDeVidaRoutes from "@/modules/FeDeVida/routes"

const storeAuth = computed(() => useAuthStore())

const routes: Array<RouteRecordRaw> = [
  {
    path: "/#",
    name: "#",
    meta: { middleware: [auth]},
    component: () => import("@/views/map.vue").then(m => m.default)
  },

  {
    path: "/map",
    name: "map",
    meta: { middleware: [auth]},
    component: () => import("@/views/map.vue").then(m => m.default)
  },
  {
    path: "/cargar",
    name: "cargar",
    meta: { middleware: [auth] },
    component: () => import("@/views/LoadExel.vue").then(m => m.default)
  },

  {
    path: "/unidades_adscritas",
    name: "unidades_adscritas",
    meta: { middleware: [auth] },
    component: () => import("@/views/unidades_adscritas.vue").then(m => m.default)
  },
  {
    path: "/oac/memos",
    name: "oac.memos",
    meta: { middleware: [auth] },
    component: () => import("@/modules/Memos/views/MemoListView.vue").then(m => m.default)
  },
  {
    path: "/oac/memos/form",
    name: "oac.memos.form",
    meta: { middleware: [auth] },
    component: () => import("@/modules/Memos/views/MemoView.vue").then(m => m.default)
  },

  ...AuthRoutes.map(route => route),
  ...AuthorizationRoutes.map(route => route),
  ...UserRoutes.map(route => route),
  ...SirpefRoutes.map(route => route),
  ...AutorizadosRoutes.map(route => route),
  ...feDeVidaRoutes.map(route => route)
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),  
  routes,
  scrollBehavior(to, from, savedPosition): any {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { left: 0, top: 0 };
    }
  },
});

router.beforeEach((to, from, next) => {
  const middleware = to.meta.middleware;
  const context = { to, from, next, storeAuth };

  if (!middleware) {
    return next();
  }

  middleware[0]({
    ...context,
    next: middlewarePipeline(context, middleware, 1),
  });
});

export default router
