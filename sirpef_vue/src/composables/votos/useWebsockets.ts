import { io } from "socket.io-client";
import {LoadNotificacion} from "@/utils/notificaciones"
import * as AuthService from "@/modules/Auth/services";
import {ref } from "vue";
import type { userInterface } from "@/types/UserInterface";

export default () => {
    const response = ref({} as userInterface)     
    const connect = ref(false)

    let socket

    const GetUser = async () => {
        const result = await AuthService.getAuthUser(); 
        response.value = result.data
    }

    const ConnectSocket = () => {
        if (connect.value) return

        socket = io(import.meta.env.VITE_WEBSOCKET_URL, {
            auth: response.value
        });
        
        socket.on("connect", () => {
            connect.value = true
        });

        
        socket.on("notificacion", (msg) => {
            (response.value.isAdmin || response.value.role_id === 6) && LoadNotificacion(msg)
        })
    }

    const DisconnectSocket = () => {
        if (!connect.value) return
        socket.close()
    }

  return {
    DisconnectSocket, 
    ConnectSocket,
    GetUser
  }
}