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

export function useAdminGenreForm(id = null) {
  const router = useRouter();
  const loading = ref(false);
  const error = ref("");
  const genreId = computed(() => unref(id) || null);
  const isEdit = computed(() => !!genreId.value);
  const form = reactive({
    name: "",
  });

  async function loadGenre() {
    if (!isEdit.value) {
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get(`/genres/${genreId.value}`);
      form.name = data.name || "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  async function handleSubmit() {
    loading.value = true;
    error.value = "";

    try {
      const payload = {
        name: form.name.trim(),
      };

      if (isEdit.value) {
        await apiClient.patch(`/genres/${genreId.value}`, payload, {
          headers: MERGE_PATCH_HEADERS,
        });
      } else {
        await apiClient.post("/genres", payload, {
          headers: JSON_LD_HEADERS,
        });
      }

      await router.push("/admin/genres");
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    loadGenre();
  });

  return {
    genreId,
    isEdit,
    loading,
    error,
    form,
    handleSubmit,
  };
}
