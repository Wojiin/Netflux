import { computed, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useNotifications } from "./useNotifications";
import { useUserStore } from "../stores/user";
import { getPostAuthRedirect } from "../utils/authRedirect";

export function useLoginForm() {
  const router = useRouter();
  const route = useRoute();
  const userStore = useUserStore();
  const { notifyError, notifySuccess } = useNotifications();
  const loading = ref(false);
  const error = ref("");
  const infoMessage = computed(() =>
    route.query.updated ? "Profil mis a jour. Reconnecte-toi avec tes identifiants." : "",
  );
  const loginForm = reactive({
    email: "",
    password: "",
  });

  async function submitLogin() {
    loading.value = true;
    error.value = "";

    try {
      await userStore.login(loginForm.email, loginForm.password);
      notifySuccess("Connexion reussie.");
      await router.push(getPostAuthRedirect(userStore.user, route.query.redirect));
    } catch (err) {
      error.value = err instanceof Error ? err.message : "Connexion impossible.";
      notifyError(error.value);
    } finally {
      loading.value = false;
    }
  }

  function goToRegister() {
    return router.push("/register");
  }

  return {
    loading,
    error,
    infoMessage,
    loginForm,
    submitLogin,
    goToRegister,
  };
}
