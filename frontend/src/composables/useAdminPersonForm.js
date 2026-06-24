import { computed, onMounted, reactive, ref, unref } from "vue";
import { useRouter } from "vue-router";
import apiClient, { getErrorMessage } from "../api/axios";

const JSON_LD_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/ld+json",
};
const MERGE_PATCH_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/merge-patch+json",
};

export function useAdminPersonForm(id = null) {
  const router = useRouter();
  const loading = ref(false);
  const error = ref("");
  const personId = computed(() => unref(id) || null);
  const isEdit = computed(() => !!personId.value);
  const form = reactive({
    firstName: "",
    lastName: "",
    gender: "",
    birthday: "",
    portraitLink: "",
  });
  const genderOptions = ["male", "female", "non-binary", "other"];

  async function loadPerson() {
    if (!isEdit.value) {
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get(`/people/${personId.value}`);
      form.firstName = data.firstName || "";
      form.lastName = data.lastName || "";
      form.gender = data.gender || "";
      form.birthday = data.birthday ? String(data.birthday).slice(0, 10) : "";
      form.portraitLink = data.portraitLink || "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function buildPayload() {
    return {
      firstName: form.firstName.trim(),
      lastName: form.lastName.trim(),
      gender: form.gender.trim(),
      birthday: form.birthday,
      portraitLink: form.portraitLink.trim(),
    };
  }

  async function handleSubmit() {
    loading.value = true;
    error.value = "";

    try {
      const payload = buildPayload();

      if (isEdit.value) {
        await apiClient.patch(`/people/${personId.value}`, payload, {
          headers: MERGE_PATCH_HEADERS,
        });
      } else {
        await apiClient.post("/people", payload, {
          headers: JSON_LD_HEADERS,
        });
      }

      await router.push("/admin/people");
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    loadPerson();
  });

  return {
    personId,
    isEdit,
    loading,
    error,
    form,
    genderOptions,
    handleSubmit,
  };
}
