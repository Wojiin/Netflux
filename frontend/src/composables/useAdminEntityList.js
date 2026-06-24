import { computed, onMounted, ref } from "vue";
import apiClient, { getErrorMessage, normalizeCollection } from "../api/axios";
import { useNotifications } from "./useNotifications";
import { extractResourceId, resolveItemPath } from "../utils/apiResource";

export function useAdminEntityList(options) {
  const {
    endpoint,
    params,
    searchBy,
    deleteMessage,
  } = options;

  const items = ref([]);
  const { notifyError, notifySuccess } = useNotifications();
  const searchQuery = ref("");
  const loading = ref(false);
  const error = ref("");
  const deletingId = ref(null);

  const itemCount = computed(() => items.value.length);
  const filteredItems = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    if (!query) {
      return items.value;
    }

    return items.value.filter((item) => {
      const value = String(searchBy(item) || "").toLowerCase();
      return value.includes(query);
    });
  });

  async function loadItems() {
    loading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get(endpoint, { params });
      items.value = normalizeCollection(data);
    } catch (err) {
      error.value = getErrorMessage(err);
      notifyError(error.value);
    } finally {
      loading.value = false;
    }
  }

  async function deleteItem(item) {
    if (!window.confirm(deleteMessage(item))) {
      return;
    }

    const itemPath = resolveItemPath(item, endpoint);
    const itemIdentifier = extractResourceId(item) ?? itemPath;

    if (!itemPath || itemPath === endpoint) {
      error.value = "Impossible de supprimer cette ressource : identifiant introuvable.";
      notifyError(error.value);
      return;
    }

    deletingId.value = itemIdentifier;
    error.value = "";

    try {
      await apiClient.delete(itemPath);
      items.value = items.value.filter((entry) => {
        const entryIdentifier = extractResourceId(entry) ?? resolveItemPath(entry, endpoint);
        return entryIdentifier !== itemIdentifier;
      });
      notifySuccess("Élément supprimé avec succès.");
    } catch (err) {
      error.value = getErrorMessage(err);
      notifyError(error.value);
    } finally {
      deletingId.value = null;
    }
  }

  onMounted(() => {
    loadItems();
  });

  return {
    items,
    searchQuery,
    loading,
    error,
    deletingId,
    itemCount,
    filteredItems,
    loadItems,
    deleteItem,
  };
}
