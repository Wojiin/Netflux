<script setup>
import { computed } from "vue";
import { formatMovieDate, useMovies } from "../composables/useMovies";
import { usePersonDetail } from "../composables/usePersonDetail";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const directorId = computed(() => props.id);
const { director, loading, error, fetchDirector } = useMovies();
const { portraitUrl } = usePersonDetail({
  entityRef: director,
  fetchEntity: fetchDirector,
  seedLabel: "réalisateur",
  id: directorId,
});
</script>

<template>
  <section class="space-y-6">
    <p v-if="loading" class="rounded-2xl border border-cyan-400/36 bg-slate-950/50 px-5 py-4 text-slate-200">Chargement du réalisateur...</p>
    <p v-else-if="error" class="rounded-2xl border border-pink-400/40 bg-pink-500/10 px-5 py-4 text-pink-100">Erreur : {{ error }}</p>

    <article
      v-else-if="director"
      class="grid gap-8 rounded-[1.75rem] border border-pink-400/36 bg-slate-950/60 p-6 shadow-[0_0_0_1px_rgba(244,114,182,0.14),0_24px_80px_rgba(5,8,22,0.55)] backdrop-blur-md lg:grid-cols-[280px_minmax(0,1fr)]"
    >
      <img :src="portraitUrl" :alt="`Portrait de ${director.fullName}`" class="aspect-[7/10] w-full rounded-[1.5rem] object-cover bg-slate-800" />

      <div class="space-y-5">
        <router-link to="/movies" class="inline-flex text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200 transition hover:text-cyan-100">Retour aux films</router-link>
        <p class="font-display text-[0.62rem] uppercase tracking-[0.28em] text-pink-300">Director Cut</p>
        <h1 class="text-4xl font-semibold text-white sm:text-5xl">{{ director.fullName }}</h1>
        <div class="grid gap-3 text-slate-300 sm:grid-cols-2">
          <p><strong class="text-slate-100">Prénom :</strong> {{ director.firstName }}</p>
          <p><strong class="text-slate-100">Nom :</strong> {{ director.lastName }}</p>
          <p><strong class="text-slate-100">Genre :</strong> {{ director.gender }}</p>
          <p><strong class="text-slate-100">Naissance :</strong> {{ formatMovieDate(director.birthday) }}</p>
        </div>

        <div class="space-y-4 pt-3">
          <h2 class="text-2xl font-semibold text-white">Films réalisés</h2>

          <ul v-if="director.directedMovies?.length" class="space-y-2 pl-5 text-slate-300">
            <li v-for="entry in director.directedMovies" :key="entry.filmId">
              <router-link :to="`/movies/${entry.filmId}`" class="text-cyan-200 transition hover:text-cyan-100">{{ entry.title }}</router-link>
              <span v-if="entry.releasedAt"> ({{ formatMovieDate(entry.releasedAt) }})</span>
            </li>
          </ul>

          <p v-else class="text-slate-300">Aucun film renseigné.</p>
        </div>
      </div>
    </article>
  </section>
</template>
