import { ref } from "vue"
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/modules/Auth/stores'
import { getError } from "@/utils/helpers";
import * as AuthService from "@/modules/Auth/services";
import type { StandaloneLogin } from '@/modules/Auth/types/Auth'

export function useLogin(): StandaloneLogin {
  const router = useRouter();
  const auth = useAuthStore()
  const error = ref(null)
  const sending = ref(false)

  const login = async () => {
    error.value = null;
    try {
      sending.value = true;
      const result = await AuthService.login();      
      localStorage.setItem('SIRPEF-TOKEN', result.data.token)
      const authUser = await auth.getAuthUser();


      if (authUser) {
        auth.setGuest({ value: "isNotGuest" });
        await router.push("/dashboard");
      } else {
        const err = Error(
          "Unable to fetch user after login, check your API settings."
        );
        err.name = "Fetch User";
        throw err;
      }
    } catch (err) {
      error.value = getError(err);
      console.log(err)
      //location.href = `${import.meta.env.VITE_SVA_URL}/login?redirect=/dashboard`;
    } finally {
      sending.value = false;
    }
  }

  return {
    login,
    sending,
    error
  }
}
