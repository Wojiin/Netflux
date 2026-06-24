import { reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useNotifications } from "./useNotifications";
import { useUserStore } from "../stores/user";
import { getPostAuthRedirect } from "../utils/authRedirect";
import { validateUserCredentials } from "../utils/userValidation";

export function useRegisterForm() {
  const router = useRouter();
  const route = useRoute();
  const userStore = useUserStore();
  const { notifyError, notifySuccess } = useNotifications();
  const loading = ref(false);
  const error = ref("");
  const registerForm = reactive({
    email: "",
    plainPassword: "",
    confirmPassword: "",
  });

  async function submitRegister() {
    const validationMessage = validateUserCredentials({
      email: registerForm.email,
      plainPassword: registerForm.plainPassword,
      confirmPassword: registerForm.confirmPassword,
    });

    if (validationMessage) {
      error.value = validationMessage;
      notifyError(error.value);
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      await userStore.register({
        email: registerForm.email,
        plainPassword: registerForm.plainPassword,
      });

      await userStore.login(registerForm.email, registerForm.plainPassword);
      notifySuccess("Compte créé et session ouverte.");
      await router.push(getPostAuthRedirect(userStore.user, route.query.redirect));
    } catch (err) {
      error.value = err instanceof Error ? err.message : "Inscription impossible.";
      notifyError(error.value);
    } finally {
      loading.value = false;
    }
  }

  function goToLogin() {
    return router.push("/login");
  }

  return {
    loading,
    error,
    registerForm,
    submitRegister,
    goToLogin,
  };
}
