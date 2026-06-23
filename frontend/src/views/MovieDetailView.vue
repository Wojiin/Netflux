<script setup>
import {
  computed,
  nextTick,
  onBeforeUnmount,
  onMounted,
  ref,
  watch,
} from "vue";
import { useRouter } from "vue-router";
import AddFavorite from "../components/AddFavorite.vue";
import RateMovie from "../components/RateMovie.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import { getContentTypeLabel } from "../constants/contentTypes";
import { formatMovieDate } from "../composables/useMovies";
import { useMovieDetail } from "../composables/useMovieDetail";
import { useYouTubePlayer } from "../composables/useYouTubePlayer";
import { useUserStore } from "../stores/user";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const router = useRouter();
const userStore = useUserStore();
const movieId = computed(() => props.id);
const showGlow = ref(false);
const trackedTrailerMovieId = ref(null);
const accentClass = computed(() => {
  const numericId = Number.parseInt(props.id, 10);

  if (Number.isNaN(numericId)) {
    return "border-cyan-400";
  }

  if (numericId % 3 === 1) {
    return "border-cyan-400";
  }

  if (numericId % 3 === 2) {
    return "border-pink-400";
  }

  return "border-amber-300";
});

const {
  movie,
  loading,
  error,
  genreLabel,
  trailerEmbedUrl,
  trailerFallbackUrl,
} = useMovieDetail(movieId);
const typeLabel = computed(() => getContentTypeLabel(movie.value?.type));
const displayedRateLabel = computed(() => {
  if (typeof movie.value?.averageRate === "number") {
    return movie.value.averageRate.toFixed(1);
  }

  if (typeof movie.value?.rate === "number") {
    return movie.value.rate.toFixed(1);
  }

  return "N/A";
});
const { containerRef, apiError } = useYouTubePlayer(
  computed(() => movie.value?.videoLink || ""),
  {
    onFirstPlay: () => {
      markCurrentMovieAsWatched();
    },
  },
);

function markCurrentMovieAsWatched() {
  const currentMovie = movie.value;

  if (!currentMovie?.id || trackedTrailerMovieId.value === currentMovie.id) {
    return;
  }

  trackedTrailerMovieId.value = currentMovie.id;

  userStore.markAsWatched(currentMovie).catch(() => {
    trackedTrailerMovieId.value = null;
  });
}

function closeOverlay() {
  router.push({ name: "movies" });
}

function handleWindowKeydown(event) {
  if (event.key === "Escape") {
    closeOverlay();
  }
}

watch(
  () => props.id,
  () => {
    showGlow.value = false;
    trackedTrailerMovieId.value = null;

    nextTick(() => {
      requestAnimationFrame(() => {
        showGlow.value = true;
      });
    });
  },
  { immediate: true },
);

onMounted(() => {
  window.addEventListener("keydown", handleWindowKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleWindowKeydown);
});
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-[120] flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8"
    >
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="closeOverlay"
      ></div>

      <div class="relative z-10 w-full max-w-6xl">
        <div
          class="pointer-events-none absolute -inset-4 rounded-[1.75rem] blur-3xl transition-all duration-700 ease-out sm:-inset-6"
          :class="[
            showGlow ? 'scale-100 opacity-100' : 'scale-95 opacity-0',
            accentClass === 'border-pink-400'
              ? 'bg-pink-500/28'
              : accentClass === 'border-amber-300'
                ? 'bg-amber-300/25'
                : 'bg-cyan-400/28',
          ]"
        ></div>

        <div
          class="relative max-h-[90vh] overflow-hidden rounded-[2rem] border-4 bg-slate-950/96 shadow-[0_0_0_1px_rgba(255,255,255,0.06),0_30px_90px_rgba(5,8,22,0.72)]"
          :class="accentClass"
        >
          <div
            class="absolute inset-0 opacity-10 bg-[repeating-linear-gradient(0deg,transparent,transparent_2px,rgba(255,255,255,0.08)_2px,rgba(255,255,255,0.08)_4px)]"
          ></div>

          <div
            class="relative z-10 flex items-center justify-between gap-4 border-b px-5 py-4 sm:px-8"
            :class="accentClass"
          >
            <div>
              <p
                class="font-display text-[0.62rem] uppercase tracking-[0.28em] text-pink-300"
              >
                Feature Presentation
              </p>
              <h2 class="mt-3 text-2xl font-semibold text-white sm:text-3xl">
                {{ movie?.title || "Chargement..." }}
              </h2>
            </div>
            <BaseButton variant="secondary" @click="closeOverlay"
              >Fermer</BaseButton
            >
          </div>

          <div
            class="relative z-10 max-h-[calc(90vh-88px)] overflow-y-auto bg-[radial-gradient(circle_at_top_left,rgba(103,232,249,0.12),transparent_24%),radial-gradient(circle_at_bottom_right,rgba(244,114,182,0.12),transparent_22%),radial-gradient(circle_at_center,rgba(250,204,21,0.06),transparent_34%),rgba(2,6,23,0.56)] backdrop-blur-sm p-5 sm:p-8"
          >
            <p
              v-if="loading"
              class="rounded-2xl border border-cyan-400/36 bg-slate-950/50 px-5 py-4 text-slate-200"
            >
              Chargement du film...
            </p>
            <p
              v-else-if="error"
              class="rounded-2xl border border-pink-400/40 bg-pink-500/10 px-5 py-4 text-pink-100"
            >
              Erreur : {{ error }}
            </p>

            <article
              v-else-if="movie"
              class="relative overflow-hidden rounded-[1.75rem] border-2 border-cyan-300/44 bg-slate-950/60 p-6 shadow-[0_0_0_1px_rgba(103,232,249,0.26),0_0_30px_rgba(34,211,238,0.16),0_24px_80px_rgba(5,8,22,0.55)] before:pointer-events-none before:absolute before:inset-0 before:rounded-[inherit] before:bg-[linear-gradient(135deg,rgba(103,232,249,0.22),transparent_24%,transparent_72%,rgba(103,232,249,0.14))] before:content-['']"
            >
              <div class="relative z-10 grid gap-8 md:grid-cols-[280px_minmax(0,1fr)] lg:grid-cols-[320px_minmax(0,1fr)]">
                <div class="space-y-5">
                <img
                  :src="movie.imgLink"
                  :alt="`Affiche du film ${movie.title}`"
                  class="w-full rounded-[1.5rem] object-cover shadow-[0_20px_50px_rgba(5,8,22,0.45)]"
                />

                <aside
                  v-if="movie.cast?.length"
                  class="space-y-4 rounded-[1.5rem] border border-cyan-300/30 bg-slate-900/45 p-5"
                >
                  <h2 class="text-xl font-semibold text-white">Casting</h2>

                  <ul class="space-y-2 text-sm text-slate-300">
                    <li v-for="entry in movie.cast" :key="entry.id">
                      <router-link
                        :to="`/actors/${entry.actorId}`"
                        class="text-cyan-200 transition hover:text-cyan-100"
                        >{{ entry.actorName }}</router-link
                      >
                      dans le rôle de "{{ entry.roleName }}"
                    </li>
                  </ul>
                </aside>
                </div>

                <div class="space-y-5">
                  <div class="flex flex-wrap items-center gap-3">
                    <router-link
                      to="/movies"
                      class="inline-flex text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200 transition hover:text-cyan-100"
                    >
                      Retour à la liste
                    </router-link>
                    <span
                      class="rounded-full border border-cyan-300/38 bg-cyan-400/10 px-3 py-1 text-xs uppercase tracking-[0.16em] text-cyan-100"
                    >
                      {{ typeLabel }}
                    </span>
                  </div>
                  <h1 class="text-4xl font-semibold text-white sm:text-5xl">
                    {{ movie.title }}
                  </h1>
                  <AddFavorite :movie="movie" />
                  <RateMovie :movie="movie" />
                  <div class="grid gap-3 text-slate-300 sm:grid-cols-2">
                    <p>
                      <strong class="text-slate-100">Date :</strong>
                      {{ formatMovieDate(movie.releasedAt) }}
                    </p>
                    <p>
                      <strong class="text-slate-100">Duree :</strong>
                      {{ movie.duration }} min
                    </p>
                    <p>
                      <strong class="text-slate-100">Type :</strong>
                      {{ typeLabel }}
                    </p>
                    <p>
                      <strong class="text-slate-100">Genres :</strong>
                      {{ genreLabel }}
                    </p>
                  </div>

                  <p v-if="movie.directorDetails?.length" class="text-slate-300">
                    <strong class="text-slate-100">Réalisateurs :</strong>
                    <span
                      v-for="(director, index) in movie.directorDetails"
                      :key="director.id"
                    >
                      <router-link
                        :to="`/directors/${director.id}`"
                        class="text-cyan-200 transition hover:text-cyan-100"
                        >{{ director.fullName }}</router-link
                      >
                      <span v-if="index < movie.directorDetails.length - 1"
                        >,
                      </span>
                    </span>
                  </p>

                  <p class="text-amber-200">
                    <strong class="text-amber-100">Note moyenne :</strong>
                    {{ displayedRateLabel }}/5
                    <span class="text-slate-400">({{ movie.ratingsCount ?? 0 }} avis)</span>
                  </p>
                  <p class="leading-8 text-slate-300">{{ movie.synopsis }}</p>

                  <div
                    v-if="trailerEmbedUrl || trailerFallbackUrl"
                    class="space-y-4 pt-4"
                  >
                    <h2 class="text-2xl font-semibold text-white">
                      Bande-annonce
                    </h2>

                    <div
                      class="relative overflow-hidden rounded-[1.75rem] border border-cyan-300/36 bg-[radial-gradient(circle_at_top,rgba(250,204,21,0.16),transparent_38%),#050816] shadow-[0_24px_60px_rgba(15,23,42,0.22)]"
                    >
                      <img
                        src="/img/wallpaper%20desktop.png"
                        alt="Decor de salle de cinema neon pour la bande-annonce"
                        class="block min-h-[220px] w-full object-cover object-center saturate-110 contrast-105 md:min-h-[320px]"
                      />

                      <div
                        class="absolute left-1/2 top-[11%] aspect-video w-[68%] -translate-x-1/2 overflow-hidden rounded-xl bg-cinema-950 shadow-[0_0_0_6px_rgba(17,24,39,0.9),0_0_45px_rgba(255,244,214,0.18)] md:top-[9%] md:w-[55%] md:max-w-[540px]"
                      >
                        <div
                          v-if="trailerEmbedUrl && !apiError"
                          ref="containerRef"
                          class="h-full w-full bg-slate-900"
                        ></div>

                        <iframe
                          v-else-if="trailerEmbedUrl"
                          :src="trailerEmbedUrl"
                          :title="`Bande-annonce de ${movie.title}`"
                          class="h-full w-full border-0 bg-slate-900"
                          @load="markCurrentMovieAsWatched"
                          allow="
                            accelerometer;
                            autoplay;
                            clipboard-write;
                            encrypted-media;
                            gyroscope;
                            picture-in-picture;
                            web-share;
                          "
                          allowfullscreen
                          referrerpolicy="strict-origin-when-cross-origin"
                        />

                        <div
                          v-else
                          class="grid h-full w-full place-items-center gap-3 bg-[radial-gradient(circle_at_center,rgba(255,248,220,0.12),transparent_45%),linear-gradient(180deg,#101828,#050816)] p-5 text-center"
                        >
                          <p class="m-0 text-slate-300">
                            Cette URL YouTube n'est pas directement lisible en
                            iframe.
                          </p>
                          <a
                            :href="trailerFallbackUrl"
                            target="_blank"
                            rel="noopener"
                            @click="markCurrentMovieAsWatched"
                            class="font-semibold text-amber-200 transition hover:text-amber-100"
                          >
                            Ouvrir la bande-annonce sur YouTube
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
