import { computed, onMounted, ref, watch, watchEffect } from "vue";
import { ALL_CONTENT_TYPES_OPTION, CONTENT_TYPES } from "../constants/contentTypes";
import { useMovies } from "./useMovies";

const ITEMS_PER_PAGE = 12;

const AVAILABLE_TYPES = [
  ALL_CONTENT_TYPES_OPTION,
  ...CONTENT_TYPES.map(({ label, value }) => ({ label, value })),
];

export function useMovieList() {
  const { movies, genres, totalItems, loading, error, fetchMovies, fetchGenres } = useMovies();
  const searchQuery = ref("");
  const selectedGenre = ref("");
  const selectedType = ref("");
  const currentPage = ref(1);
  const pageDirection = ref("next");
  let searchTimeoutId = null;
  let skipNextPageLoad = false;

  const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / ITEMS_PER_PAGE)));
  const paginatedMovies = computed(() => movies.value);
  const paginationTransitionName = computed(() =>
    pageDirection.value === "previous" ? "movies-page-backward" : "movies-page-forward",
  );
  const paginationKey = computed(
    () => `${currentPage.value}-${selectedGenre.value}-${selectedType.value}-${searchQuery.value.trim()}`,
  );

  async function loadMovies() {
    const params = {
      page: currentPage.value,
      itemsPerPage: ITEMS_PER_PAGE,
    };

    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }

    if (selectedGenre.value) {
      params["genre.id"] = selectedGenre.value;
    }

    if (selectedType.value) {
      params.type = selectedType.value;
    }

    await fetchMovies(params);
  }

  async function loadGenres() {
    await fetchGenres();
  }

  function resetPage() {
    currentPage.value = 1;
  }

  function previousPage() {
    if (currentPage.value > 1) {
      pageDirection.value = "previous";
      currentPage.value -= 1;
    }
  }

  function nextPage() {
    if (currentPage.value < totalPages.value) {
      pageDirection.value = "next";
      currentPage.value += 1;
    }
  }

  watchEffect(() => {
    if (currentPage.value > totalPages.value) {
      currentPage.value = totalPages.value;
    }
  });

  watch(currentPage, () => {
    if (skipNextPageLoad) {
      skipNextPageLoad = false;
      return;
    }

    loadMovies();
  });

  watch([selectedGenre, selectedType], () => {
    pageDirection.value = "next";

    if (currentPage.value !== 1) {
      skipNextPageLoad = true;
      resetPage();
    }

    loadMovies();
  });

  watch(searchQuery, () => {
    window.clearTimeout(searchTimeoutId);
    pageDirection.value = "next";

    if (currentPage.value !== 1) {
      skipNextPageLoad = true;
      resetPage();
    }

    searchTimeoutId = window.setTimeout(() => {
      loadMovies();
    }, 250);
  });

  onMounted(async () => {
    await loadGenres();
    await loadMovies();
  });

  return {
    movies,
    totalItems,
    loading,
    error,
    searchQuery,
    genres,
    selectedGenre,
    selectedType,
    currentPage,
    availableTypes: AVAILABLE_TYPES,
    totalPages,
    paginatedMovies,
    paginationKey,
    paginationTransitionName,
    previousPage,
    nextPage,
  };
}
