<script setup>
import { useAdminEntityList } from "../composables/useAdminEntityList";
import { extractResourceId } from "../utils/apiResource";
import AdminEntityLayout from "../components/ui/AdminEntityLayout.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";

const {
  searchQuery,
  loading,
  error,
  deletingId,
  itemCount: genreCount,
  filteredItems: filteredGenres,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/genres",
  params: {
    itemsPerPage: 100,
    order: { name: "asc" },
  },
  searchBy: (genre) => genre.name,
  deleteMessage: (genre) => `Supprimer le genre ${genre.name} ?`,
});
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Genre Matrix"
    eyebrow-class="text-cyan-300"
    title="Gestion des genres"
    :description="`${filteredGenres.length} genre(s) affiché(s) sur ${genreCount}.`"
    view-name="admingenres"
    search-placeholder="Rechercher un genre..."
    search-tone="cyan"
    search-class="w-full max-w-sm rounded-2xl border border-cyan-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/30"
    :loading="loading"
    loading-message="Chargement des genres..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/genres/new">
        <BaseButton>Ajouter un genre</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="genre in filteredGenres"
      :key="genre.id"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ genre.name }}</h2>
        <p class="text-sm uppercase tracking-[0.16em] text-slate-400">{{ genre.filmTitles?.length || 0 }} film(s) associé(s)</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link :to="`/admin/genres/${extractResourceId(genre)}/edit`">
          <BaseButton variant="secondary">Modifier</BaseButton>
        </router-link>
        <BaseButton
          variant="danger"
          :disabled="deletingId === extractResourceId(genre)"
          @click="handleDelete(genre)"
        >
          {{ deletingId === extractResourceId(genre) ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
