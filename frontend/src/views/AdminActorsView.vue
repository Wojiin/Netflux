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
  itemCount: actorCount,
  filteredItems: filteredActors,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/actors",
  params: {
    itemsPerPage: 100,
    order: { "person.lastName": "asc", "person.firstName": "asc" },
  },
  searchBy: (actor) => actor.fullName,
  deleteMessage: (actor) => `Supprimer le profil acteur de ${actor.fullName} ?`,
});
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Casting Grid"
    eyebrow-class="text-amber-300"
    title="Gestion des acteurs"
    :description="`${filteredActors.length} acteur(s) affiché(s) sur ${actorCount}.`"
    view-name="adminactors"
    search-placeholder="Rechercher un acteur..."
    search-tone="amber"
    search-class="w-full max-w-sm rounded-2xl border border-amber-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/30"
    :loading="loading"
    loading-message="Chargement des acteurs..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/actors/new">
        <BaseButton>Ajouter un acteur</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="actor in filteredActors"
      :key="actor.id"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ actor.fullName }}</h2>
        <p class="text-slate-300">{{ actor.gender || "Genre non renseigné" }}</p>
        <p class="text-sm uppercase tracking-[0.16em] text-slate-400">{{ actor.filmography?.length || 0 }} role(s) en filmographie</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link :to="`/admin/actors/${extractResourceId(actor)}/edit`">
          <BaseButton variant="secondary">Modifier</BaseButton>
        </router-link>
        <BaseButton
          variant="danger"
          :disabled="deletingId === extractResourceId(actor)"
          @click="handleDelete(actor)"
        >
          {{ deletingId === extractResourceId(actor) ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
