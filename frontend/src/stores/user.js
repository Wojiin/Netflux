import { defineStore } from "pinia";
import { jwtDecode } from "jwt-decode";
import apiClient, {
  clearStoredSession,
  getErrorMessage,
  getStoredAccessToken,
  normalizeCollection,
  registerSessionHandlers,
  saveAccessToken,
} from "../api/axios";
import { useNotifications } from "../composables/useNotifications";
import { useMovieStore } from "./movies";

let favoritesPromise = null;
let watchedPromise = null;
let ratingsPromise = null;
let initPromise = null;
const { notifyError } = useNotifications();

// Normalise les erreurs applicatives pour renvoyer un message unique aux vues.
function toErrorMessage(error) {
  return error instanceof Error ? error.message : getErrorMessage(error);
}

// Décode localement le JWT pour reconstruire l'identité minimale sans appel
// réseau supplémentaire.
function decodeUserFromToken(token) {
  if (!token) {
    return null;
  }

  try {
    const decodedToken = jwtDecode(token);
    const normalizedId =
      typeof decodedToken.id === "number"
        ? decodedToken.id
        : typeof decodedToken.id === "string"
          ? Number.parseInt(decodedToken.id, 10) || null
          : null;

    return {
      id: normalizedId,
      email:
        typeof decodedToken.email === "string"
          ? decodedToken.email
          : typeof decodedToken.username === "string"
            ? decodedToken.username
            : null,
      roles: Array.isArray(decodedToken.roles) ? decodedToken.roles : [],
    };
  } catch {
    return null;
  }
}

// Répercute le résumé des notes sur une instance de film déjà présente dans le
// state, afin d'éviter un rechargement complet de collection.
function applyRatingSummary(movie, summary) {
  if (!movie) {
    return;
  }

  movie.averageRate = summary.averageRate ?? null;
  movie.ratingsCount = summary.ratingsCount ?? 0;
}

export const useUserStore = defineStore("user", {
  state: () => ({
    token: getStoredAccessToken() || null,
    user: decodeUserFromToken(getStoredAccessToken() || null),
    initialized: false,
    favorites: [],
    favoritesLoading: false,
    favoritePendingIds: [],
    favoritesError: "",
    favoritesLoaded: false,
    loadedFavoritesUserId: null,
    watched: [],
    watchedLoading: false,
    watchedPendingIds: [],
    watchedError: "",
    watchedLoaded: false,
    loadedWatchedUserId: null,
    ratings: [],
    ratingsLoading: false,
    ratingPendingIds: [],
    ratingsError: "",
    ratingsLoaded: false,
    loadedRatingsUserId: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.roles?.includes("ROLE_ADMIN") || false,
    userEmail: (state) => state.user?.email || null,
    favoriteIds: (state) => state.favorites.map((movie) => movie.id),
    watchedIds: (state) => state.watched.map((movie) => movie.id),
  },

  actions: {
    // Met à jour le token mémoire et l'identité utilisateur associée.
    setSession(nextToken, nextUser = null) {
      this.token = nextToken || null;
      saveAccessToken(nextToken || null);
      this.user = nextUser || decodeUserFromToken(nextToken);
    },

    // Les méthodes reset remettent à zéro les états dérivés de la session pour
    // éviter qu'un ancien utilisateur laisse des données au suivant.
    resetFavorites() {
      this.favorites = [];
      this.favoritesLoading = false;
      this.favoritePendingIds = [];
      this.favoritesError = "";
      this.favoritesLoaded = false;
      this.loadedFavoritesUserId = null;
      favoritesPromise = null;
    },

    resetWatched() {
      this.watched = [];
      this.watchedLoading = false;
      this.watchedPendingIds = [];
      this.watchedError = "";
      this.watchedLoaded = false;
      this.loadedWatchedUserId = null;
      watchedPromise = null;
    },

    resetRatings() {
      this.ratings = [];
      this.ratingsLoading = false;
      this.ratingPendingIds = [];
      this.ratingsError = "";
      this.ratingsLoaded = false;
      this.loadedRatingsUserId = null;
      ratingsPromise = null;
    },

    clearSession() {
      this.setSession(null, null);
      clearStoredSession();
      this.resetFavorites();
      this.resetWatched();
      this.resetRatings();
    },

    // Login : stocke immédiatement le JWT et le bloc user renvoyés par Lexik.
    async login(email, password) {
      try {
        const { data } = await apiClient.post(
          "/login",
          { email, password },
          {
            headers: {
              Accept: "application/json",
            },
          },
        );

        this.setSession(data.token || null, data.user || null);
        this.initialized = true;
        return data;
      } catch (error) {
        this.clearSession();
        this.initialized = true;
        throw new Error(toErrorMessage(error));
      }
    },

    // Register : crée le compte, mais ne construit pas elle-même la session.
    async register(payload) {
      try {
        const { data } = await apiClient.post("/register", payload, {
          headers: {
            Accept: "application/ld+json, application/json",
            "Content-Type": "application/ld+json",
          },
        });

        return data;
      } catch (error) {
        throw new Error(toErrorMessage(error));
      }
    },

    // Refresh : appelle l'endpoint Gesdinet pour obtenir un nouveau JWT à
    // partir du cookie HTTP-only de refresh.
    async refresh() {
      const { data } = await apiClient.post(
        "/token/refresh",
        {},
        {
          headers: {
            Accept: "application/json",
          },
          skipAuthRefresh: true,
        },
      );

      this.setSession(data.token || null, data.user || null);
      return data;
    },

    // Cette méthode est appelée au montage de l'app et avant certaines routes
    // pour tenter de restaurer une session après reload.
    async ensureInitialized() {
      if (this.initialized) {
        return;
      }

      if (!initPromise) {
        initPromise = (async () => {
          try {
            await this.refresh();
          } catch {
            this.clearSession();
          } finally {
            this.initialized = true;
          }
        })().finally(() => {
          initPromise = null;
        });
      }

      await initPromise;
    },

    // Logout : invalide les refresh tokens persistants puis nettoie le state.
    async logout() {
      try {
        await apiClient.post(
          "/token/invalidate",
          {},
          {
            headers: {
              Accept: "application/json",
            },
            skipAuthRefresh: true,
          },
        );
      } catch {}

      this.clearSession();
      this.initialized = true;
    },

    // Mise à jour locale légère d'un sous-ensemble du profil utilisateur.
    patchCurrentUser(patch) {
      if (!this.user) {
        return;
      }

      this.user = {
        ...this.user,
        ...patch,
      };
    },

    // Les méthodes fetch* relisent les données liées à l'utilisateur courant
    // depuis le backend et mémorisent le résultat tant que l'identité ne change pas.
    async fetchFavorites(force = false) {
      this.favoritesError = "";

      const userId = this.user?.id;

      if (!this.token || !userId) {
        this.resetFavorites();
        return [];
      }

      if (this.loadedFavoritesUserId !== userId) {
        this.resetFavorites();
        this.loadedFavoritesUserId = userId;
      }

      if (this.favoritesLoaded && !force) {
        return this.favorites;
      }

      if (favoritesPromise && !force) {
        return favoritesPromise;
      }

      this.favoritesLoading = true;

      favoritesPromise = (async () => {
        try {
          const { data } = await apiClient.get(`/users/${userId}/favorites`);
          this.favorites = normalizeCollection(data);
          this.favoritesLoaded = true;
          this.loadedFavoritesUserId = userId;
          return this.favorites;
        } catch (error) {
          this.favoritesError = getErrorMessage(error);
          throw error;
        } finally {
          this.favoritesLoading = false;
          favoritesPromise = null;
        }
      })();

      return favoritesPromise;
    },

    isFavorite(movieId) {
      return this.favoriteIds.includes(movieId);
    },

    isFavoritePending(movieId) {
      return this.favoritePendingIds.includes(movieId);
    },

    async fetchWatched(force = false) {
      this.watchedError = "";

      const userId = this.user?.id;

      if (!this.token || !userId) {
        this.resetWatched();
        return [];
      }

      if (this.loadedWatchedUserId !== userId) {
        this.resetWatched();
        this.loadedWatchedUserId = userId;
      }

      if (this.watchedLoaded && !force) {
        return this.watched;
      }

      if (watchedPromise && !force) {
        return watchedPromise;
      }

      this.watchedLoading = true;

      watchedPromise = (async () => {
        try {
          const { data } = await apiClient.get(`/users/${userId}/watched`);
          this.watched = normalizeCollection(data);
          this.watchedLoaded = true;
          this.loadedWatchedUserId = userId;
          return this.watched;
        } catch (error) {
          this.watchedError = getErrorMessage(error);
          throw error;
        } finally {
          this.watchedLoading = false;
          watchedPromise = null;
        }
      })();

      return watchedPromise;
    },

    isWatched(movieId) {
      return this.watchedIds.includes(movieId);
    },

    isWatchedPending(movieId) {
      return this.watchedPendingIds.includes(movieId);
    },

    async fetchRatings(force = false) {
      this.ratingsError = "";

      const userId = this.user?.id;

      if (!this.token || !userId) {
        this.resetRatings();
        return [];
      }

      if (this.loadedRatingsUserId !== userId) {
        this.resetRatings();
        this.loadedRatingsUserId = userId;
      }

      if (this.ratingsLoaded && !force) {
        return this.ratings;
      }

      if (ratingsPromise && !force) {
        return ratingsPromise;
      }

      this.ratingsLoading = true;

      ratingsPromise = (async () => {
        try {
          const { data } = await apiClient.get(`/users/${userId}/ratings`);
          this.ratings = normalizeCollection(data);
          this.ratingsLoaded = true;
          this.loadedRatingsUserId = userId;
          return this.ratings;
        } catch (error) {
          this.ratingsError = getErrorMessage(error);
          throw error;
        } finally {
          this.ratingsLoading = false;
          ratingsPromise = null;
        }
      })();

      return ratingsPromise;
    },

    getMovieRating(movieId) {
      return this.ratings.find((rating) => rating.movieId === movieId)?.rate ?? null;
    },

    isRatingPending(movieId) {
      return this.ratingPendingIds.includes(movieId);
    },

    // Les actions métier suivantes pilotent les routes custom favoris, vu et note.
    async toggleFavorite(movie) {
      const userId = this.user?.id;
      const movieId = movie?.id;

      if (!this.token || !userId || !movieId) {
        return null;
      }

      if (this.isFavoritePending(movieId)) {
        return null;
      }

      this.favoritesError = "";
      this.favoritePendingIds = [...this.favoritePendingIds, movieId];

      try {
        const { data } = await apiClient.post(`/users/${userId}/favorites/${movieId}`);

        if (data.status === "added") {
          if (!this.favorites.some((entry) => entry.id === movieId)) {
            this.favorites = [movie, ...this.favorites];
          }
        } else if (data.status === "removed") {
          this.favorites = this.favorites.filter((entry) => entry.id !== movieId);
        }

        this.favoritesLoaded = true;
        this.loadedFavoritesUserId = userId;
        return data.status || null;
      } catch (error) {
        this.favoritesError = getErrorMessage(error);
        throw error;
      } finally {
        this.favoritePendingIds = this.favoritePendingIds.filter((id) => id !== movieId);
      }
    },

    async markAsWatched(movie) {
      const userId = this.user?.id;
      const movieId = movie?.id;

      if (!this.token || !userId || !movieId || this.isWatched(movieId)) {
        return null;
      }

      if (this.isWatchedPending(movieId)) {
        return null;
      }

      this.watchedError = "";
      this.watchedPendingIds = [...this.watchedPendingIds, movieId];

      try {
        const { data } = await apiClient.post(`/users/${userId}/watched/${movieId}`);

        if (!this.watched.some((entry) => entry.id === movieId)) {
          this.watched = [movie, ...this.watched];
        }

        this.watchedLoaded = true;
        this.loadedWatchedUserId = userId;
        return data.status || null;
      } catch (error) {
        this.watchedError = getErrorMessage(error);
        throw error;
      } finally {
        this.watchedPendingIds = this.watchedPendingIds.filter((id) => id !== movieId);
      }
    },

    async rateMovie(movie, rate) {
      const userId = this.user?.id;
      const movieId = movie?.id;

      if (!this.token || !userId || !movieId || rate < 0 || rate > 5) {
        return null;
      }

      if (this.isRatingPending(movieId)) {
        return null;
      }

      this.ratingsError = "";
      this.ratingPendingIds = [...this.ratingPendingIds, movieId];

      try {
        const { data } = await apiClient.post(`/users/${userId}/ratings/${movieId}/${rate}`);
        const existingRating = this.ratings.find((entry) => entry.movieId === movieId);

        if (existingRating) {
          existingRating.rate = data.rate;
        } else {
          this.ratings = [
            ...this.ratings,
            {
              movieId,
              movieTitle: movie.title || "",
              rate: data.rate,
            },
          ];
        }

        this.ratingsLoaded = true;
        this.loadedRatingsUserId = userId;

        applyRatingSummary(movie, data);

        const movieStore = useMovieStore();
        applyRatingSummary(movieStore.movie?.id === movieId ? movieStore.movie : null, data);
        movieStore.movies
          .filter((entry) => entry.id === movieId)
          .forEach((entry) => applyRatingSummary(entry, data));
        this.favorites
          .filter((entry) => entry.id === movieId)
          .forEach((entry) => applyRatingSummary(entry, data));
        this.watched
          .filter((entry) => entry.id === movieId)
          .forEach((entry) => applyRatingSummary(entry, data));

        return data;
      } catch (error) {
        this.ratingsError = getErrorMessage(error);
        throw error;
      } finally {
        this.ratingPendingIds = this.ratingPendingIds.filter((id) => id !== movieId);
      }
    },
  },
});

// Pont entre Axios et Pinia : quand un refresh réussit ou échoue, le store est
// resynchronisé sans que l'instance Axios connaisse directement Pinia.
registerSessionHandlers({
  onAccessTokenRefreshed: (nextToken) => {
    const userStore = useUserStore();
    userStore.setSession(nextToken, userStore.user);
  },
  onSessionExpired: () => {
    const userStore = useUserStore();
    userStore.clearSession();
    notifyError("Session expiree. Reconnecte-toi.");
  },
});
