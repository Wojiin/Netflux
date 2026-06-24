<script setup>
import { getContentTypeLabel } from "../constants/contentTypes";
import { useAdminEntityList } from "../composables/useAdminEntityList";
import AdminEntityLayout from "../components/ui/AdminEntityLayout.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";

const {
  searchQuery,
  loading,
  error,
  deletingId,
  itemCount: movieCount,
  filteredItems: filteredMovies,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/movies",
  params: {
    itemsPerPage: 100,
    order: { title: "asc" },
  },
  searchBy: (movie) => movie.title,
  deleteMessage: (movie) => `Supprimer "${movie.title}" ?`,
});
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Control Room"
    title="Gestion des films"
    :description="`${filteredMovies.length} film(s) affiché(s) sur ${movieCount}.`"
    view-name="adminmovies"
    search-placeholder="Rechercher par titre..."
    search-tone="cyan"
    search-class="w-full max-w-sm rounded-2xl border border-cyan-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/30"
    :loading="loading"
    loading-message="Chargement des films..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/movies/new">
        <BaseButton>Ajouter un film</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="movie in filteredMovies"
      :key="movie.id"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ movie.title }}</h2>
        <p class="text-slate-300">{{ movie.genreNames?.join(", ") || "Genre inconnu" }}</p>
        <p class="text-sm uppercase tracking-[0.16em] text-slate-400">{{ getContentTypeLabel(movie.type) }} - {{ movie.duration ?? "N/A" }} min</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link :to="`/admin/movies/${movie.id}/edit`">
          <BaseButton variant="secondary">Modifier</BaseButton>
        </router-link>
        <router-link :to="`/admin/movies/${movie.id}/casting`">
          <BaseButton variant="accent">Casting</BaseButton>
        </router-link>
        <BaseButton
          variant="danger"
          :disabled="deletingId === movie.id"
          @click="handleDelete(movie)"
        >
          {{ deletingId === movie.id ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
