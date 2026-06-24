import { computed, onMounted, reactive, ref, unref } from "vue";
import { useRouter } from "vue-router";
import apiClient, { getErrorMessage } from "../api/axios";
import { validateUserCredentials } from "../utils/userValidation";

const DEFAULT_ROLES = ["ROLE_USER"];
const JSON_LD_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/ld+json",
};
const MERGE_PATCH_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/merge-patch+json",
};

export function useAdminUserForm(id = null) {
  const router = useRouter();
  const loading = ref(false);
  const error = ref("");
  const userId = computed(() => unref(id) || null);
  const isEdit = computed(() => !!userId.value);
  const form = reactive({
    email: "",
    plainPassword: "",
    roles: [...DEFAULT_ROLES],
  });
  const roleOptions = ["ROLE_USER", "ROLE_ADMIN"];

  async function loadUser() {
    if (!isEdit.value) {
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get(`/users/${userId.value}`);
      form.email = data.email || "";
      form.roles = Array.isArray(data.roles) && data.roles.length ? data.roles : [...DEFAULT_ROLES];
      form.plainPassword = "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function buildPayload() {
    const payload = {
      email: form.email,
      roles: form.roles,
    };

    if (form.plainPassword) {
      payload.plainPassword = form.plainPassword;
    }

    return payload;
  }

  function toggleRole(role) {
    if (form.roles.includes(role)) {
      form.roles = form.roles.filter((item) => item !== role);
    } else {
      form.roles = [...form.roles, role];
    }

    if (!form.roles.length) {
      form.roles = [...DEFAULT_ROLES];
    }
  }

  async function handleSubmit() {
    const validationMessage = validateUserCredentials({
      email: form.email,
      plainPassword: form.plainPassword,
      passwordOptional: isEdit.value,
    });

    if (validationMessage) {
      error.value = validationMessage;
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const payload = buildPayload();

      if (isEdit.value) {
        await apiClient.patch(`/users/${userId.value}`, payload, {
          headers: MERGE_PATCH_HEADERS,
        });
      } else {
        await apiClient.post("/users", payload, {
          headers: JSON_LD_HEADERS,
        });
      }

      await router.push("/admin/users");
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    loadUser();
  });

  return {
    userId,
    isEdit,
    loading,
    error,
    form,
    roleOptions,
    toggleRole,
    handleSubmit,
  };
}
