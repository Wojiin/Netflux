import axios from "axios";

const API_URL = import.meta.env.VITE_API_BASE_URL || "/api";
const REFRESH_ENDPOINT = "/token/refresh";

// Ces variables vivent au niveau du module pour partager l'état de session et
// éviter de multiplier les rafraîchissements concurrents.
let onAccessTokenRefreshed = null;
let onSessionExpired = null;
let refreshPromise = null;
let accessToken = null;

// Instance Axios centrale de l'application : toutes les requêtes API passent
// par elle, avec support des credentials pour le cookie refresh.
const apiClient = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  headers: {
    Accept: "application/ld+json, application/json",
    "Content-Type": "application/json",
  },
});

export function saveAccessToken(token) {
  // Le JWT d'accès est gardé uniquement en mémoire pour limiter son exposition
  // au navigateur. Le cookie de refresh reste, lui, géré par le backend.
  accessToken = token || null;

  if (accessToken) {
    apiClient.defaults.headers.common.Authorization = `Bearer ${accessToken}`;
    return;
  }

  delete apiClient.defaults.headers.common.Authorization;
}

// Ce getter renvoie le JWT actuellement gardé en mémoire.
export function getStoredAccessToken() {
  return accessToken;
}

// Remise à zéro explicite de l'état de session côté client.
export function clearStoredSession() {
  accessToken = null;
  delete apiClient.defaults.headers.common.Authorization;
}

export function registerSessionHandlers(handlers = {}) {
  // Ces callbacks permettent au store utilisateur de rester synchronisé avec
  // les événements réseau sans coupler directement Axios à Pinia.
  onAccessTokenRefreshed =
    typeof handlers.onAccessTokenRefreshed === "function"
      ? handlers.onAccessTokenRefreshed
      : null;
  onSessionExpired =
    typeof handlers.onSessionExpired === "function"
      ? handlers.onSessionExpired
      : null;
}

// API Platform peut répondre en tableau brut ou en format Hydra : ce helper
// unifie la lecture des collections côté frontend.
export function normalizeCollection(data) {
  if (Array.isArray(data)) {
    return data;
  }

  return data["hydra:member"] || data.member || [];
}

// Interceptor de sortie : il injecte automatiquement le Bearer token courant
// sur chaque requête protégée.
apiClient.interceptors.request.use((config) => {
  const token = getStoredAccessToken();
  config.headers = config.headers ?? {};

  if (token) {
    // Chaque requête protégée reçoit le bearer token courant juste avant envoi.
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

// Cette fonction centralise le refresh du JWT. Une seule promesse est partagée
// pour éviter qu'une rafale de 401 ne déclenche plusieurs refresh en parallèle.
async function refreshAccessToken() {
  if (!refreshPromise) {
    // Promesse partagée : une rafale de réponses 401 ne doit déclencher
    // qu'un seul refresh, puis rejouer les requêtes bloquées avec le nouveau token.
    refreshPromise = apiClient
      .post(
        REFRESH_ENDPOINT,
        {},
        {
          headers: {
            Accept: "application/json",
          },
          skipAuthRefresh: true,
        },
      )
      .then(({ data }) => {
        const nextToken = data.token || null;

        if (!nextToken) {
          throw new Error("Aucun token de rafraîchissement reçu.");
        }

        saveAccessToken(nextToken);
        onAccessTokenRefreshed?.(nextToken);

        return nextToken;
      })
      .catch((error) => {
        clearStoredSession();
        onSessionExpired?.();
        throw error;
      })
      .finally(() => {
        refreshPromise = null;
      });
  }

  return refreshPromise;
}

// Interceptor d'entrée : il capte les 401, tente un refresh si possible, puis
// rejoue la requête initiale avec le nouveau token.
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (!axios.isAxiosError(error)) {
      return Promise.reject(error);
    }

    const originalRequest = error.config;
    const status = error.response?.status;

    if (
      !originalRequest ||
      originalRequest.skipAuthRefresh ||
      status !== 401 ||
      originalRequest._retry
    ) {
      return Promise.reject(error);
    }

    if (
      originalRequest.url?.includes("/login") ||
      originalRequest.url?.includes(REFRESH_ENDPOINT)
    ) {
      // On n'essaie jamais de refresher une authentification qui vient déjà
      // d'échouer, sinon on crée une boucle de 401 impossible à sortir.
      return Promise.reject(error);
    }

    originalRequest._retry = true;

    try {
      // Le frontend garde le JWT court en mémoire et s'appuie sur le cookie
      // HTTP-only de refresh pour récupérer silencieusement une session expirée.
      const nextToken = await refreshAccessToken();
      originalRequest.headers = originalRequest.headers ?? {};
      originalRequest.headers.Authorization = `Bearer ${nextToken}`;

      return apiClient(originalRequest);
    } catch (refreshError) {
      return Promise.reject(refreshError);
    }
  },
);

// Helper d'affichage : il extrait un message lisible depuis les erreurs Axios
// ou les violations de validation remontées par l'API.
export function getErrorMessage(error) {
  if (axios.isAxiosError(error)) {
    const violations = error.response?.data?.violations;

    if (Array.isArray(violations) && violations.length) {
      return violations
        .map((violation) => violation.message)
        .filter(Boolean)
        .join(" ");
    }

    return (
      error.response?.data?.["hydra:description"] ||
      error.response?.data?.message ||
      error.message
    );
  }

  return "Une erreur inattendue est survenue.";
}

export default apiClient;
