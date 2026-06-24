import { computed, onMounted, watch } from "vue";
import { useMovies } from "./useMovies";

function toYouTubeEmbedUrl(url) {
  if (!url) {
    return null;
  }

  try {
    const parsedUrl = new URL(url);
    const host = parsedUrl.hostname.replace(/^www\./, "");

    if (host === "youtu.be") {
      const videoId = parsedUrl.pathname.split("/").filter(Boolean)[0];
      return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    }

    if (host === "youtube.com" || host === "m.youtube.com") {
      if (parsedUrl.pathname === "/watch") {
        const videoId = parsedUrl.searchParams.get("v");
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
      }

      if (parsedUrl.pathname.startsWith("/embed/")) {
        return url;
      }

      if (parsedUrl.pathname === "/results") {
        return null;
      }
    }
  } catch {
    return null;
  }

  return null;
}

function toTrailerFallbackUrl(url) {
  if (!url) {
    return null;
  }

  try {
    const parsedUrl = new URL(url);
    const host = parsedUrl.hostname.replace(/^www\./, "");

    if (host === "youtube.com" || host === "m.youtube.com" || host === "youtu.be") {
      return url;
    }
  } catch {
    return null;
  }

  return null;
}

export function useMovieDetail(id) {
  const { movie, loading, error, fetchMovie } = useMovies();
  const genreLabel = computed(() => movie.value?.genreNames?.join(", ") || "Non renseigné");
  const trailerEmbedUrl = computed(() => toYouTubeEmbedUrl(movie.value?.videoLink));
  const trailerFallbackUrl = computed(() => toTrailerFallbackUrl(movie.value?.videoLink));

  async function loadMovie(movieId = id?.value ?? id) {
    if (movieId) {
      await fetchMovie(movieId);
    }
  }

  onMounted(() => {
    loadMovie();
  });

  watch(
    () => id?.value ?? id,
    (movieId) => {
      loadMovie(movieId);
    },
  );

  return {
    movie,
    loading,
    error,
    fetchMovie,
    genreLabel,
    trailerEmbedUrl,
    trailerFallbackUrl,
  };
}
