import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import apiClient, { getErrorMessage } from "../api/axios";
import { useUserStore } from "../stores/user";
import { validateUserCredentials } from "../utils/userValidation";

const MERGE_PATCH_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/merge-patch+json",
};

export function useProfileForm() {
  const router = useRouter();
  const userStore = useUserStore();
  const loading = ref(false);
  const saving = ref(false);
  const error = ref("");
  const success = ref(false);
  const form = reactive({
    email: "",
    plainPassword: "",
  });

  async function loadProfile() {
    if (!userStore.isAuthenticated) {
      error.value = "";
      return;
    }

    loading.value = true;
    error.value = "";
    success.value = false;

    try {
      const { data } = await apiClient.get("/me");
      form.email = data.email || userStore.user?.email || "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function buildPayload() {
    const payload = {
      email: form.email,
    };

    if (form.plainPassword) {
      payload.plainPassword = form.plainPassword;
    }

    return payload;
  }

  async function submitProfile() {
    if (!userStore.isAuthenticated) {
      error.value = "";
      return;
    }

    const validationMessage = validateUserCredentials({
      email: form.email,
      plainPassword: form.plainPassword,
      passwordOptional: true,
    });

    if (validationMessage) {
      error.value = validationMessage;
      return;
    }

    saving.value = true;
    error.value = "";
    success.value = false;

    try {
      await apiClient.patch("/me", buildPayload(), {
        headers: MERGE_PATCH_HEADERS,
      });

      try {
        await userStore.refresh();
      } catch {
        userStore.patchCurrentUser({ email: form.email });
      }

      success.value = true;
      userStore.patchCurrentUser({ email: form.email });
      form.plainPassword = "";
      await loadProfile();
      await router.push({ name: "profile", query: { updated: "1" } });
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      saving.value = false;
    }
  }

  function goBack() {
    return router.push("/movies");
  }

  onMounted(() => {
    loadProfile();
  });

  return {
    loading,
    saving,
    error,
    success,
    form,
    submitProfile,
    goBack,
  };
}
