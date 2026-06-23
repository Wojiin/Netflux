<script setup>
import MovieItem from "../components/MovieItem.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import NeonScrollPanel from "../components/ui/NeonScrollPanel.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import { useMovieList } from "../composables/useMovieList";

const {
  totalItems,
  loading,
  error,
  searchQuery,
  genres,
  selectedGenre,
  selectedType,
  currentPage,
  availableTypes,
  totalPages,
  paginatedMovies,
  paginationKey,
  paginationTransitionName,
  previousPage,
  nextPage,
} = useMovieList();
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Projection Room"
      title="Catalogue"
      description="Catalogue des films et séries disponibles."
      view-name="movielist"
    />

    <div
      class="grid gap-5 rounded-[1.75rem] border border-cyan-400/32 bg-slate-950/55 p-5 shadow-[0_0_0_1px_rgba(103,232,249,0.14),0_24px_70px_rgba(5,8,22,0.5)] backdrop-blur-md md:grid-cols-3"
    >
      <label class="grid gap-2">
        <span
          class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200"
          >Recherche</span
        >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher par titre..."
          class="rounded-2xl border border-cyan-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/30"
        />
      </label>

      <label class="grid gap-2">
        <span
          class="text-sm font-semibold uppercase tracking-[0.18em] text-pink-200"
          >Genre</span
        >
        <select
          v-model="selectedGenre"
          class="rounded-2xl border border-pink-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition focus:border-pink-300 focus:ring-2 focus:ring-pink-300/30"
        >
          <option value="">Tous les genres</option>
          <option v-for="genre in genres" :key="genre.id" :value="genre.id">
            {{ genre.name }}
          </option>
        </select>
      </label>

      <label class="grid gap-2">
        <span
          class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-200"
          >Type</span
        >
        <select
          v-model="selectedType"
          class="rounded-2xl border border-amber-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition focus:border-amber-300 focus:ring-2 focus:ring-amber-300/30"
        >
          <option
            v-for="type in availableTypes"
            :key="type.value || 'all'"
            :value="type.value"
          >
            {{ type.label }}
          </option>
        </select>
      </label>
    </div>

    <p
      v-if="loading"
      class="rounded-2xl border border-cyan-400/36 bg-slate-950/50 px-5 py-4 text-slate-200"
    >
      Chargement des films...
    </p>
    <p
      v-else-if="error"
      class="rounded-2xl border border-pink-400/40 bg-pink-500/10 px-5 py-4 text-pink-100"
    >
      Erreur : {{ error }}
    </p>

    <template v-else>
      <p class="text-sm uppercase tracking-[0.18em] text-slate-300">
        {{ totalItems }} contenu(s) trouve(s)
      </p>

      <Transition
        v-if="paginatedMovies.length"
        :name="paginationTransitionName"
        mode="out-in"
      >
        <div
          :key="paginationKey"
          class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4"
        >
          <BaseCard
            v-for="movie in paginatedMovies"
            :key="movie.id"
            compact
            class="h-full animate-[fade-in_0.45s_ease]"
          >
            <MovieItem :movie="movie" />
          </BaseCard>
        </div>
      </Transition>

      <p
        v-else
        class="rounded-2xl border border-cyan-400/30 bg-slate-950/45 px-5 py-6 text-slate-300"
      >
        Aucun contenu ne correspond aux filtres sélectionnés.
      </p>
      <nav class="flex items-center justify-center gap-5 pb-1">
        <BaseButton
          :disabled="currentPage <= 1"
          @click="previousPage"
          variant="secondary"
        >
          Precedent
        </BaseButton>

        <span>Page {{ currentPage }} / {{ totalPages }}</span>

        <BaseButton
          :disabled="currentPage >= totalPages"
          @click="nextPage"
          variant="secondary"
        >
          Suivant
        </BaseButton>
      </nav>
    </template>

    <router-view v-slot="{ Component }">
      <Transition
        enter-active-class="transition duration-500 ease-out"
        enter-from-class="translate-y-6 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-400 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-6 opacity-0"
      >
        <component :is="Component" />
      </Transition>
    </router-view>
  </section>
</template>

<style scoped>
.movies-page-forward-enter-active,
.movies-page-forward-leave-active,
.movies-page-backward-enter-active,
.movies-page-backward-leave-active {
  transition:
    opacity 0.38s ease,
    transform 0.38s ease,
    filter 0.38s ease;
}

.movies-page-forward-enter-from,
.movies-page-backward-leave-to {
  opacity: 0;
  transform: translateX(28px) translateY(8px);
  filter: blur(8px);
}

.movies-page-forward-leave-to,
.movies-page-backward-enter-from {
  opacity: 0;
  transform: translateX(-28px) translateY(8px);
  filter: blur(8px);
}
</style>
