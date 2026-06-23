import { onBeforeUnmount, ref, watch } from "vue";

let youtubeApiPromise = null;

function extractYouTubeVideoId(url) {
  if (!url) {
    return null;
  }

  try {
    const parsedUrl = new URL(url);
    const host = parsedUrl.hostname.replace(/^www\./, "");

    if (host === "youtu.be") {
      return parsedUrl.pathname.split("/").filter(Boolean)[0] || null;
    }

    if (host === "youtube.com" || host === "m.youtube.com") {
      if (parsedUrl.pathname === "/watch") {
        return parsedUrl.searchParams.get("v");
      }

      if (parsedUrl.pathname.startsWith("/embed/")) {
        return parsedUrl.pathname.split("/embed/")[1]?.split("/")[0] || null;
      }
    }
  } catch {
    return null;
  }

  return null;
}

function loadYouTubeApi() {
  if (typeof window === "undefined") {
    return Promise.reject(new Error("L'API YouTube n'est pas disponible."));
  }

  if (window.YT?.Player) {
    return Promise.resolve(window.YT);
  }

  if (!youtubeApiPromise) {
    youtubeApiPromise = new Promise((resolve, reject) => {
      const existingScript = document.querySelector(
        'script[src="https://www.youtube.com/iframe_api"]',
      );

      const previousCallback = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = () => {
        previousCallback?.();
        resolve(window.YT);
      };

      if (!existingScript) {
        const script = document.createElement("script");
        script.src = "https://www.youtube.com/iframe_api";
        script.async = true;
        script.onerror = () =>
          reject(new Error("Impossible de charger l'API YouTube."));
        document.head.appendChild(script);
      }
    }).catch((error) => {
      youtubeApiPromise = null;
      throw error;
    });
  }

  return youtubeApiPromise;
}

export function useYouTubePlayer(videoUrl, options = {}) {
  const containerRef = ref(null);
  const apiError = ref("");
  const playerReady = ref(false);

  let player = null;
  let trackedForCurrentVideo = false;

  const destroyPlayer = () => {
    if (player?.destroy) {
      player.destroy();
    }

    player = null;
    playerReady.value = false;
  };

  const mountPlayer = async (videoId) => {
    destroyPlayer();
    apiError.value = "";
    trackedForCurrentVideo = false;

    if (!videoId || !containerRef.value) {
      return;
    }

    try {
      const YT = await loadYouTubeApi();

      player = new YT.Player(containerRef.value, {
        videoId,
        playerVars: {
          rel: 0,
          modestbranding: 1,
        },
        events: {
          onReady: () => {
            playerReady.value = true;
          },
          onStateChange: (event) => {
            if (
              event.data === YT.PlayerState.PLAYING &&
              !trackedForCurrentVideo
            ) {
              trackedForCurrentVideo = true;
              options.onFirstPlay?.();
            }
          },
          onError: () => {
            apiError.value = "La lecture YouTube a rencontré une erreur.";
          },
        },
      });
    } catch (error) {
      apiError.value =
        error instanceof Error
          ? error.message
          : "Impossible d'initialiser YouTube.";
    }
  };

  watch(
    [containerRef, () => extractYouTubeVideoId(videoUrl?.value ?? videoUrl)],
    ([container, videoId]) => {
      if (!container || !videoId) {
        destroyPlayer();
        return;
      }

      mountPlayer(videoId);
    },
    { immediate: true },
  );

  onBeforeUnmount(() => {
    destroyPlayer();
  });

  return {
    containerRef,
    apiError,
    playerReady,
    videoId: ref(extractYouTubeVideoId(videoUrl?.value ?? videoUrl)),
  };
}
