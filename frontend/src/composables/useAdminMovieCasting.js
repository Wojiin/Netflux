import { computed, onMounted, ref, unref } from "vue";
import axios from "axios";
import apiClient, { getErrorMessage, normalizeCollection } from "../api/axios";

function getDirectorIri(id) {
  return `/api/directors/${id}`;
}

function getRoleIri(id) {
  return `/api/roles/${id}`;
}

function extractResourceIri(response, fallbackBasePath = "") {
  const data = response?.data;

  if (typeof data?.["@id"] === "string" && data["@id"]) {
    return data["@id"];
  }

  if (data?.id && fallbackBasePath) {
    return `${fallbackBasePath}/${data.id}`;
  }

  const locationHeader = response?.headers?.location || response?.headers?.Location;

  if (typeof locationHeader === "string" && locationHeader) {
    try {
      const parsedUrl = new URL(locationHeader, window.location.origin);
      return parsedUrl.pathname;
    } catch {
      return locationHeader.replace(window.location.origin, "");
    }
  }

  return "";
}

function extractResourceIdFromIri(iri) {
  if (!iri) {
    return null;
  }

  const parts = String(iri).split("/").filter(Boolean);
  const lastPart = parts.at(-1);
  const numericId = Number(lastPart);

  return Number.isFinite(numericId) ? numericId : null;
}

function toPositiveId(value) {
  const numericId = Number(value);
  return Number.isInteger(numericId) && numericId > 0 ? numericId : null;
}

function formatBackendErrorDetails(error) {
  if (!axios.isAxiosError(error)) {
    return "";
  }

  const data = error.response?.data;

  if (!data) {
    return "";
  }

  const violations = Array.isArray(data.violations)
    ? data.violations.map((violation) => violation.message).filter(Boolean)
    : [];

  if (violations.length) {
    return violations.join(" ");
  }

  if (typeof data["hydra:description"] === "string" && data["hydra:description"]) {
    return data["hydra:description"];
  }

  if (typeof data.detail === "string" && data.detail) {
    return data.detail;
  }

  if (typeof data.message === "string" && data.message) {
    return data.message;
  }

  try {
    return JSON.stringify(data);
  } catch {
    return "";
  }
}

function toDisplayErrorMessage(error) {
  if (error instanceof Error && error.message) {
    return formatBackendErrorDetails(error) || error.message;
  }

  return formatBackendErrorDetails(error) || getErrorMessage(error);
}

function buildRowKey(prefix = "new") {
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

function sortByName(items) {
  return [...items].sort((left, right) => {
    const leftName = left.fullName || left.actorName || "";
    const rightName = right.fullName || right.actorName || "";
    return leftName.localeCompare(rightName, "fr");
  });
}

function createEmptyCastRow() {
  return {
    key: buildRowKey(),
    playId: null,
    roleId: null,
    actorId: "",
    roleFirstName: "",
    roleLastName: "",
    saving: false,
    deleting: false,
    isNew: true,
  };
}

function splitRoleName(roleName) {
  const normalizedName = String(roleName || "").trim();

  if (!normalizedName) {
    return {
      characterFirstName: "",
      characterLastName: "",
    };
  }

  const [characterFirstName = "", ...rest] = normalizedName.split(" ");

  return {
    characterFirstName,
    characterLastName: rest.join(" ").trim(),
  };
}

function mapCastRows(entries = []) {
  return entries.map((entry) => {
    const role = splitRoleName(entry.roleName);

    return {
      key: buildRowKey(`play-${entry.id}`),
      playId: entry.id,
      roleId: entry.roleId ?? null,
      actorId: entry.actorId ? String(entry.actorId) : "",
      roleFirstName: role.characterFirstName,
      roleLastName: role.characterLastName,
      saving: false,
      deleting: false,
      isNew: false,
    };
  });
}

export function useAdminMovieCasting(id = null) {
  const movieId = computed(() => unref(id) || null);
  const movie = ref(null);
  const directorOptions = ref([]);
  const actorOptions = ref([]);
  const selectedDirectorIds = ref([]);
  const castRows = ref([]);
  const loading = ref(false);
  const directorsSaving = ref(false);
  const error = ref("");
  const successMessage = ref("");

  async function loadOptions() {
    const [directorsResult, actorsResult] = await Promise.all([
      apiClient.get("/directors", { params: { itemsPerPage: 100 } }),
      apiClient.get("/actors", { params: { itemsPerPage: 100 } }),
    ]);

    directorOptions.value = sortByName(normalizeCollection(directorsResult.data));
    actorOptions.value = sortByName(normalizeCollection(actorsResult.data));
  }

  async function refreshMovie() {
    const { data } = await apiClient.get(`/movies/${movieId.value}`);
    movie.value = data;
    selectedDirectorIds.value = (movie.value.directorDetails || [])
      .map((director) => director.id)
      .filter(Boolean)
      .map(String);
    castRows.value = mapCastRows(movie.value.cast || []);
  }

  async function loadData() {
    loading.value = true;
    error.value = "";

    try {
      await Promise.all([loadOptions(), refreshMovie()]);
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  async function saveDirectors() {
    directorsSaving.value = true;
    error.value = "";
    successMessage.value = "";

    try {
      await apiClient.patch(
        `/movies/${movieId.value}`,
        {
          directors: selectedDirectorIds.value.map(getDirectorIri),
        },
        {
          headers: {
            Accept: "application/ld+json, application/json",
            "Content-Type": "application/merge-patch+json",
          },
        },
      );

      await refreshMovie();
      successMessage.value = "Réalisateurs mis à jour.";
    } catch (err) {
      error.value = toDisplayErrorMessage(err);
    } finally {
      directorsSaving.value = false;
    }
  }

  function addCastRow() {
    castRows.value = [...castRows.value, createEmptyCastRow()];
  }

  function removeUnsavedRow(rowKey) {
    castRows.value = castRows.value.filter((row) => row.key !== rowKey);
  }

  function validateCastRow(row) {
    if (!row.actorId) {
      return "Selectionne un acteur.";
    }

    if (!row.roleFirstName.trim()) {
      return "Le prénom du rôle est obligatoire.";
    }

    return "";
  }

  async function createRole(row) {
    const response = await apiClient.post(
      "/roles",
      {
        characterFirstName: row.roleFirstName.trim(),
        characterLastName: row.roleLastName.trim() || null,
      },
      {
        headers: {
          Accept: "application/ld+json, application/json",
          "Content-Type": "application/ld+json",
        },
      },
    );

    const createdRoleId = toPositiveId(response?.data?.id);

    if (createdRoleId) {
      return createdRoleId;
    }

    const { data } = await apiClient.get("/roles", {
      params: {
        itemsPerPage: 20,
        order: {
          id: "desc",
        },
      },
    });
    const roles = normalizeCollection(data);

    const createdRole = roles.find((role) => {
      const firstName = role.characterFirstName?.trim() || "";
      const lastName = role.characterLastName?.trim() || "";

      return (
        firstName === row.roleFirstName.trim()
        && lastName === (row.roleLastName.trim() || "")
      );
    });

    return toPositiveId(createdRole?.id);
  }

  async function updateRole(row) {
    await apiClient.patch(
      `/roles/${row.roleId}`,
      {
        characterFirstName: row.roleFirstName.trim(),
        characterLastName: row.roleLastName.trim() || null,
      },
      {
        headers: {
          Accept: "application/ld+json, application/json",
          "Content-Type": "application/merge-patch+json",
        },
      },
    );
  }

  function buildPlayPayload(row, roleId) {
    const filmId = toPositiveId(movieId.value);
    const actorId = toPositiveId(row.actorId);
    const nextRoleId = toPositiveId(roleId);

    if (!filmId || !actorId || !nextRoleId) {
      throw new Error(
        `Payload play invalide (filmId=${movieId.value}, actorId=${row.actorId}, roleId=${roleId}).`,
      );
    }

    return {
      filmId,
      actorId,
      roleId: nextRoleId,
    };
  }

  async function createPlay(row, roleId) {
    return apiClient.post(
      "/plays",
      buildPlayPayload(row, roleId),
      {
        headers: {
          Accept: "application/ld+json, application/json",
          "Content-Type": "application/ld+json",
        },
      },
    );
  }

  async function updatePlay(row) {
    return apiClient.patch(
      `/plays/${row.playId}`,
      buildPlayPayload(row, row.roleId),
      {
        headers: {
          Accept: "application/ld+json, application/json",
          "Content-Type": "application/merge-patch+json",
        },
      },
    );
  }

  async function saveCastRow(row) {
    const validationMessage = validateCastRow(row);

    if (validationMessage) {
      error.value = validationMessage;
      return;
    }

    row.saving = true;
    error.value = "";
    successMessage.value = "";

    try {
      if (row.isNew) {
        let createdRoleId = null;

        createdRoleId = await createRole(row);

        if (!createdRoleId) {
          throw new Error("Impossible de récupérer le rôle créé.");
        }

        row.roleId = createdRoleId;

        try {
          await createPlay(row, createdRoleId);
        } catch (playError) {
          if (createdRoleId) {
            try {
              await apiClient.delete(`/roles/${createdRoleId}`);
            } catch {
            }
          }

          row.roleId = null;
          throw playError;
        }
      } else {
        await updateRole(row);

        await updatePlay(row);
      }

      await refreshMovie();
      successMessage.value = "Casting mis à jour.";
    } catch (err) {
      error.value = toDisplayErrorMessage(err);
    } finally {
      row.saving = false;
    }
  }

  async function deleteCastRow(row) {
    if (row.isNew) {
      removeUnsavedRow(row.key);
      return;
    }

    if (!window.confirm("Supprimer cette ligne de casting ?")) {
      return;
    }

    row.deleting = true;
    error.value = "";
    successMessage.value = "";

    try {
      await apiClient.delete(`/plays/${row.playId}`);

      if (row.roleId) {
        try {
          await apiClient.delete(`/roles/${row.roleId}`);
        } catch {
        }
      }

      await refreshMovie();
      successMessage.value = "Ligne de casting supprimée.";
    } catch (err) {
      error.value = toDisplayErrorMessage(err);
    } finally {
      row.deleting = false;
    }
  }

  onMounted(() => {
    loadData();
  });

  return {
    movieId,
    movie,
    directorOptions,
    actorOptions,
    selectedDirectorIds,
    castRows,
    loading,
    directorsSaving,
    error,
    successMessage,
    saveDirectors,
    addCastRow,
    saveCastRow,
    deleteCastRow,
  };
}
