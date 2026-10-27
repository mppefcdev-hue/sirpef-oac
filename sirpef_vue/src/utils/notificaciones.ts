import {useToast} from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import { useAuthStore } from "@/modules/Auth/stores"
import {useNotifications} from "@/stores/notifications"


const UseNoti = useNotifications();

export const LoadNotificacion = (alerta: string) => {
    const store = useAuthStore()


    if (!store.authUser.isAdmin && store.authUser.role_id !== 6) return 
    UseNoti.addItem(alerta)
    //if(store.authUser.cedula != 0) return
    const $toast = useToast();
    let instance = $toast.default(alerta, {
        position: "top-right",
        duration: 5000,
    });
}