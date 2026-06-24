<script setup>
import { useAdminEntityList } from "../composables/useAdminEntityList";
import AdminEntityLayout from "../components/ui/AdminEntityLayout.vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";

const {
  searchQuery,
  loading,
  error,
  deletingId,
  itemCount: userCount,
  filteredItems: filteredUsers,
  deleteItem: handleDelete,
} = useAdminEntityList({
  endpoint: "/users",
  params: {
    itemsPerPage: 100,
    order: { email: "asc" },
  },
  searchBy: (user) => user.email,
  deleteMessage: (user) => `Supprimer le compte ${user.email} ?`,
});
</script>

<template>
  <AdminEntityLayout
    v-model="searchQuery"
    eyebrow="Access Registry"
    eyebrow-class="text-pink-300"
    title="Gestion des utilisateurs"
    :description="`${filteredUsers.length} utilisateur(s) affiché(s) sur ${userCount}.`"
    view-name="adminusers"
    search-placeholder="Rechercher par email..."
    search-tone="pink"
    search-class="w-full max-w-sm rounded-2xl border border-pink-300/38 bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-pink-300 focus:ring-2 focus:ring-pink-300/30"
    :loading="loading"
    loading-message="Chargement des utilisateurs..."
    :error="error"
  >
    <template #actions>
      <router-link to="/admin">
        <BaseButton variant="secondary">Retour</BaseButton>
      </router-link>
      <router-link to="/admin/users/new">
        <BaseButton>Ajouter un utilisateur</BaseButton>
      </router-link>
    </template>

    <BaseCard
      v-for="user in filteredUsers"
      :key="user.id"
      class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
    >
      <div class="grid gap-2">
        <h2 class="text-2xl font-semibold text-white">{{ user.email }}</h2>
        <p class="text-slate-300">{{ user.roles?.join(", ") }}</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <router-link :to="`/admin/users/${user.id}/edit`">
          <BaseButton variant="secondary">Modifier</BaseButton>
        </router-link>
        <BaseButton
          variant="danger"
          :disabled="deletingId === user.id"
          @click="handleDelete(user)"
        >
          {{ deletingId === user.id ? "Suppression..." : "Supprimer" }}
        </BaseButton>
      </div>
    </BaseCard>
  </AdminEntityLayout>
</template>
