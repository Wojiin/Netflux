import { storeToRefs } from "pinia";
import { useMovieStore } from "../stores/movies";

export function formatMovieDate(value) {
  if (!value) {
    return "";
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return String(value);
  }

  return new Intl.DateTimeFormat("fr-FR", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  }).format(date);
}

export function useMovies() {
  const movieStore = useMovieStore();
  const { movies, movie, actor, director, genres, totalItems, loading, error } =
    storeToRefs(movieStore);

  return {
    movies,
    movie,
    actor,
    director,
    genres,
    totalItems,
    loading,
    error,
    fetchMovies: movieStore.fetchMovies,
    fetchMovie: movieStore.fetchMovie,
    fetchActor: movieStore.fetchActor,
    fetchDirector: movieStore.fetchDirector,
    fetchGenres: movieStore.fetchGenres,
  };
}
