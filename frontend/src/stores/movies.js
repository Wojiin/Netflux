import { defineStore } from "pinia";
import apiClient, { getErrorMessage, normalizeCollection } from "../api/axios";

export const useMovieStore = defineStore("movies", {
  state: () => ({
    movies: [],
    movie: null,
    actor: null,
    director: null,
    genres: [],
    totalItems: 0,
    loading: false,
    error: "",
  }),

  actions: {
    clearError() {
      this.error = "";
    },

    startLoading() {
      this.loading = true;
      this.error = "";
    },

    stopLoading() {
      this.loading = false;
    },

    async fetchMovies(params = {}) {
      this.startLoading();

      try {
        const { data } = await apiClient.get("/movies", { params });
        this.movies = normalizeCollection(data);
        this.totalItems = data?.totalItems ?? this.movies.length;
      } catch (error) {
        this.error = getErrorMessage(error);
      } finally {
        this.stopLoading();
      }
    },

    async fetchMovie(id) {
      this.startLoading();
      this.movie = null;

      try {
        const { data } = await apiClient.get(`/movies/${id}`);
        this.movie = data;
      } catch (error) {
        this.error = getErrorMessage(error);
      } finally {
        this.stopLoading();
      }
    },

    async fetchActor(id) {
      this.startLoading();
      this.actor = null;

      try {
        const { data } = await apiClient.get(`/actors/${id}`);
        this.actor = data;
      } catch (error) {
        this.error = getErrorMessage(error);
      } finally {
        this.stopLoading();
      }
    },

    async fetchDirector(id) {
      this.startLoading();
      this.director = null;

      try {
        const { data } = await apiClient.get(`/directors/${id}`);
        this.director = data;
      } catch (error) {
        this.error = getErrorMessage(error);
      } finally {
        this.stopLoading();
      }
    },

    async fetchGenres() {
      this.startLoading();

      try {
        const { data } = await apiClient.get("/genres", {
          params: {
            itemsPerPage: 100,
            order: { name: "asc" },
          },
        });

        this.genres = normalizeCollection(data);
      } catch (error) {
        this.genres = [];
        this.error = getErrorMessage(error);
      } finally {
        this.stopLoading();
      }
    },
  },
});
