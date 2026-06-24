import { computed, onMounted, reactive, ref, unref } from "vue";
import { useRouter } from "vue-router";
import apiClient, { getErrorMessage, normalizeCollection } from "../api/axios";
import { CONTENT_TYPES } from "../constants/contentTypes";

const JSON_LD_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/ld+json",
};
const MERGE_PATCH_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/merge-patch+json",
};

function getGenreIri(genre) {
  return genre?.id ? `/api/genres/${genre.id}` : "";
}

export function useAdminMovieForm(id = null) {
  const router = useRouter();
  const loading = ref(false);
  const genresLoading = ref(false);
  const error = ref("");
  const genreOptions = ref([]);
  const movieId = computed(() => unref(id) || null);
  const isEdit = computed(() => !!movieId.value);
  const form = reactive({
    title: "",
    type: "film",
    releasedAt: "",
    imgLink: "",
    videoLink: "",
    duration: 90,
    synopsis: "",
    rate: "",
    genre: "",
  });
  const typeOptions = CONTENT_TYPES;

  async function loadGenres() {
    genresLoading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get("/genres", {
        params: {
          itemsPerPage: 100,
          order: { name: "asc" },
        },
      });

      genreOptions.value = normalizeCollection(data);
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      genresLoading.value = false;
    }
  }

  async function loadMovie() {
    if (!isEdit.value) {
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const { data: movie } = await apiClient.get(`/movies/${movieId.value}`);
      form.title = movie.title || "";
      form.type = movie.type || "film";
      form.releasedAt = movie.releasedAt ? String(movie.releasedAt).slice(0, 10) : "";
      form.imgLink = movie.imgLink || "";
      form.videoLink = movie.videoLink || "";
      form.duration = movie.duration ?? 90;
      form.synopsis = movie.synopsis || "";
      form.rate = movie.rate ?? "";

      const movieGenreName = movie.genreNames?.[0] || "";
      const matchedGenre = genreOptions.value.find((genre) => genre.name === movieGenreName);
      form.genre = matchedGenre ? getGenreIri(matchedGenre) : "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function buildPayload() {
    const payload = {
      title: form.title.trim(),
      type: form.type,
      releasedAt: form.releasedAt,
      imgLink: form.imgLink.trim(),
      videoLink: form.videoLink.trim(),
      duration: Number(form.duration),
      synopsis: form.synopsis.trim(),
      genre: form.genre,
    };

    if (form.rate !== "" && form.rate !== null) {
      payload.rate = Number(form.rate);
    }

    return payload;
  }

  async function handleSubmit() {
    loading.value = true;
    error.value = "";

    try {
      const payload = buildPayload();

      if (isEdit.value) {
        await apiClient.put(`/movies/${movieId.value}`, payload, {
          headers: JSON_LD_HEADERS,
        });
      } else {
        await apiClient.post("/movies", payload, {
          headers: JSON_LD_HEADERS,
        });
      }

      await router.push("/admin/movies");
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  onMounted(async () => {
    await loadGenres();
    await loadMovie();
  });

  return {
    movieId,
    isEdit,
    loading,
    genresLoading,
    error,
    form,
    genreOptions,
    typeOptions,
    getGenreIri,
    handleSubmit,
  };
}
