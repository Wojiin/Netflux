<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import MovieItem from "../components/MovieItem.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import { useUserStore } from "../stores/user";

const userStore = useUserStore();
const { favorites, favoritesError, favoritesLoading } = storeToRefs(userStore);

const favoriteCount = computed(() => favorites.value.length);

onMounted(() => {
  userStore.fetchFavorites().catch(() => {});
});
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Personal Archive"
      eyebrow-class="text-pink-300"
      title="Mes favoris"
      description="Retrouve ici les films que tu as ajoutés à ta liste personnelle."
      view-name="favorites"
    />

    <p v-if="favoritesLoading" class="rounded-2xl border border-cyan-400/36 bg-slate-950/50 px-5 py-4 text-slate-200">Chargement des favoris...</p>
    <p v-else-if="favoritesError" class="rounded-2xl border border-pink-400/40 bg-pink-500/10 px-5 py-4 text-pink-100">Erreur : {{ favoritesError }}</p>
    <template v-else>
      <p class="text-sm uppercase tracking-[0.18em] text-slate-300">{{ favoriteCount }} film(s) en favori(s)</p>

      <div v-if="favorites.length" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <BaseCard
          v-for="movie in favorites"
          :key="movie.id"
          compact
        >
          <MovieItem :movie="movie" />
        </BaseCard>
      </div>

      <p v-else class="rounded-2xl border border-cyan-400/30 bg-slate-950/45 px-5 py-6 text-slate-300">
        Aucun film en favori pour le moment.
      </p>
    </template>
  </section>
</template>
