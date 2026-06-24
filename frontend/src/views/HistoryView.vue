<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import BaseCard from "../components/ui/BaseCard.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import MovieItem from "../components/MovieItem.vue";
import { useUserStore } from "../stores/user";

const userStore = useUserStore();
const { watched, watchedError, watchedLoading } = storeToRefs(userStore);

const watchedCount = computed(() => watched.value.length);

onMounted(() => {
  userStore.fetchWatched().catch(() => {});
});
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Viewing Log"
      eyebrow-class="text-amber-300"
      title="Historique"
      description="Retrouve ici les films que tu as déjà lancés et regardés."
      view-name="history"
    />

    <p
      v-if="watchedLoading"
      class="rounded-2xl border border-cyan-400/36 bg-slate-950/50 px-5 py-4 text-slate-200"
    >
      Chargement de l'historique...
    </p>
    <p
      v-else-if="watchedError"
      class="rounded-2xl border border-pink-400/40 bg-pink-500/10 px-5 py-4 text-pink-100"
    >
      Erreur : {{ watchedError }}
    </p>
    <template v-else>
      <p class="text-sm uppercase tracking-[0.18em] text-slate-300">
        {{ watchedCount }} film(s) vu(s)
      </p>

      <div
        v-if="watched.length"
        class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
      >
        <BaseCard v-for="movie in watched" :key="movie.id" compact>
          <MovieItem :movie="movie" />
        </BaseCard>
      </div>

      <p
        v-else
        class="rounded-2xl border border-cyan-400/30 bg-slate-950/45 px-5 py-6 text-slate-300"
      >
        Aucun film dans l'historique pour le moment.
      </p>
    </template>
  </section>
</template>
