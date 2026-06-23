<script setup>
import { computed } from "vue";
import { formatMovieDate } from "../composables/useMovies";
import { getContentTypeLabel } from "../constants/contentTypes";
import AddFavorite from "./AddFavorite.vue";

const props = defineProps({
  movie: {
    type: Object,
    required: true,
  },
});

const genreLabel = computed(() => props.movie.genreNames?.join(", ") || "Genre inconnu");
const directorLabel = computed(() => {
  if (props.movie.directorDetails?.length) {
    return props.movie.directorDetails.map((director) => director.fullName).join(", ");
  }

  if (props.movie.directorNames?.length) {
    return props.movie.directorNames.join(", ");
  }

  return props.movie.directorName || "";
});
const yearLabel = computed(() => {
  if (!props.movie.releasedAt) {
    return "Annee inconnue";
  }

  return new Date(props.movie.releasedAt).getFullYear();
});

const typeLabel = computed(() => getContentTypeLabel(props.movie.type));
const displayedRateLabel = computed(() => {
  if (typeof props.movie.averageRate === "number") {
    return props.movie.averageRate.toFixed(1);
  }

  if (typeof props.movie.rate === "number") {
    return props.movie.rate.toFixed(1);
  }

  return "N/A";
});
</script>

<template>
  <article class="grid h-full gap-4">
    <router-link :to="`/movies/${movie.id}`" class="group relative overflow-hidden rounded-[1.35rem]">
      <img
        :src="movie.imgLink"
        :alt="`Affiche du film ${movie.title}`"
        class="aspect-[7/10] w-full rounded-[1.35rem] object-cover transition duration-300 group-hover:scale-[1.03] group-hover:brightness-110"
      />
      <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_45%,rgba(5,8,22,0.92)_100%)]"></div>
      <div class="pointer-events-none absolute inset-x-4 bottom-4 flex items-center justify-between text-xs uppercase tracking-[0.2em] text-cyan-100">
        <span>{{ yearLabel }}</span>
        <span class="rounded-full border border-cyan-300/46 bg-slate-950/60 px-3 py-1 text-[0.65rem]">{{ typeLabel }}</span>
      </div>
    </router-link>

    <div class="flex h-full flex-col gap-3">
      <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ formatMovieDate(movie.releasedAt) }}</p>

      <h2 class="text-xl font-semibold leading-tight text-white">
        <router-link :to="`/movies/${movie.id}`" class="transition hover:text-cyan-200">
          {{ movie.title }}
        </router-link>
      </h2>

      <p class="inline-flex w-fit items-center rounded-full border border-pink-300/40 bg-pink-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-pink-100">
        {{ genreLabel }}
      </p>
      <p class="text-sm text-slate-300"><strong class="text-slate-100">Annee :</strong> {{ yearLabel }}</p>
      <p class="text-sm text-slate-300"><strong class="text-slate-100">Duree :</strong> {{ movie.duration ?? "N/A" }} min</p>

      <p v-if="directorLabel" class="text-sm text-slate-300">
        Réalisation : {{ directorLabel }}
      </p>

      <p class="text-sm text-amber-200">
        Note : {{ displayedRateLabel }}/5
        <span class="text-slate-400">({{ movie.ratingsCount ?? 0 }} avis)</span>
      </p>
      <AddFavorite :movie="movie" />
    </div>
  </article>
</template>
