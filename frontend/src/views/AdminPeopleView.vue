<script setup>
import { useAdminEntityList } from "../composables/useAdminEntityList";
import { extractResourceId, resolveItemPath } from "../utils/apiResource";
import AdminEntityLayout from "../components/ui/AdminEntityLayout.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";

const {
  searchQuery,
  loading,
  error,
  deletingId,
  itemCount: peopleCount,
  filteredItems: filteredPeople,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/people",
  params: {
    itemsPerPage: 100,
    order: { lastName: "asc", firstName: "asc" },
  },
  searchBy: (person) => [person.firstName, person.lastName].filter(Boolean).join(" "),
  deleteMessage: (person) => `Supprimer ${person.firstName} ${person.lastName} ?`,
});

function getPersonIdentifier(person) {
  return extractResourceId(person) ?? resolveItemPath(person, "/people");
}
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Identity Registry"
    eyebrow-class="text-cyan-300"
    title="Gestion des personnes"
    :description="`${filteredPeople.length} personne(s) affichée(s) sur ${peopleCount}.`"
    view-name="adminpeople"
    search-placeholder="Rechercher par nom..."
    search-tone="cyan"
    search-class="w-full max-w-sm rounded-2xl border border-cyan-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/30"
    :loading="loading"
    loading-message="Chargement des personnes..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/people/new">
        <BaseButton>Ajouter une personne</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="person in filteredPeople"
      :key="person['@id'] || `${person.firstName}-${person.lastName}-${person.birthday}`"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ person.firstName }} {{ person.lastName }}</h2>
        <p class="text-slate-300">{{ person.gender || "Genre non renseigné" }}</p>
        <p class="text-sm uppercase tracking-[0.16em] text-slate-400">
          {{ person.isActor ? "Acteur" : "Non acteur" }} • {{ person.isDirector ? "Réalisateur" : "Non réalisateur" }}
        </p>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link :to="`/admin/people/${extractResourceId(person)}/edit`">
          <BaseButton variant="secondary">Modifier</BaseButton>
        </router-link>
        <BaseButton
          variant="danger"
          :disabled="deletingId === getPersonIdentifier(person)"
          @click="handleDelete(person)"
        >
          {{ deletingId === getPersonIdentifier(person) ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
