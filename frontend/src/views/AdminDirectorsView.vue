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
  itemCount: directorCount,
  filteredItems: filteredDirectors,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/directors",
  params: {
    itemsPerPage: 100,
    order: { "person.lastName": "asc", "person.firstName": "asc" },
  },
  searchBy: (director) => director.fullName,
  deleteMessage: (director) => `Supprimer le profil réalisateur de ${director.fullName} ?`,
});
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Director Booth"
    eyebrow-class="text-pink-300"
    title="Gestion des réalisateurs"
    :description="`${filteredDirectors.length} réalisateur(s) affiché(s) sur ${directorCount}.`"
    view-name="admindirectors"
    search-placeholder="Rechercher un réalisateur..."
    search-tone="pink"
    search-class="w-full max-w-sm rounded-2xl border border-pink-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-pink-300 focus:ring-2 focus:ring-pink-300/30"
    :loading="loading"
    loading-message="Chargement des réalisateurs..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/directors/new">
        <BaseButton>Ajouter un réalisateur</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="director in filteredDirectors"
      :key="director.id"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ director.fullName }}</h2>
        <p class="text-slate-300">{{ director.gender || "Genre non renseigné" }}</p>
        <p class="text-sm uppercase tracking-[0.16em] text-slate-400">{{ director.directedMovies?.length || 0 }} contenu(x) réalisé(s)</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <BaseButton
          variant="danger"
          :disabled="deletingId === extractResourceId(director)"
          @click="handleDelete(director)"
        >
          {{ deletingId === extractResourceId(director) ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
