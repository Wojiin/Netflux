<script setup>
import { computed, ref } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import NeonScrollPanel from "../components/ui/NeonScrollPanel.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminMovieCasting } from "../composables/useAdminMovieCasting";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const movieId = computed(() => props.id || null);
const directorsExpanded = ref(false);

const {
  movie,
  directorOptions,
  actorOptions,
  selectedDirectorIds,
  castRows,
  loading,
  directorsSaving,
  error,
  successMessage,
  saveDirectors,
  addCastRow,
  saveCastRow,
  deleteCastRow,
} = useAdminMovieCasting(movieId);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Casting Console"
      eyebrow-class="text-amber-300"
      title="Gestion du casting"
      :description="movie?.title || ''"
      view-name="adminmoviecasting"
    >
      <template #actions>
        <router-link to="/admin">
          <BaseButton variant="secondary">Retour</BaseButton>
        </router-link>
        <router-link :to="`/admin/movies/${movieId}/edit`">
          <BaseButton variant="secondary">Retour au film</BaseButton>
        </router-link>
        <router-link to="/admin/movies">
          <BaseButton variant="secondary">Liste des films</BaseButton>
        </router-link>
      </template>
    </PageHeader>

    <StatusMessage v-if="loading">Chargement du casting...</StatusMessage>

    <template v-else>
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>
      <StatusMessage v-if="successMessage" variant="success">{{ successMessage }}</StatusMessage>

      <BaseCard tone="amber" class="gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <button
              type="button"
              class="inline-flex items-center gap-3 text-left text-2xl font-semibold text-white transition hover:text-amber-200"
              @click="directorsExpanded = !directorsExpanded"
            >
              <span>Réalisateurs</span>
              <span
                class="text-lg text-amber-200 transition-transform duration-200"
                :class="directorsExpanded ? 'rotate-180' : ''"
                aria-hidden="true"
              >
                ↓
              </span>
            </button>
            <p class="mt-2 text-slate-300">Associe un ou plusieurs réalisateurs au film.</p>
          </div>
          <BaseButton :disabled="directorsSaving" @click="saveDirectors">
            {{ directorsSaving ? "Enregistrement..." : "Enregistrer les réalisateurs" }}
          </BaseButton>
        </div>

        <NeonScrollPanel
          v-if="directorsExpanded"
          class="grid gap-3 pr-2 md:grid-cols-2 xl:grid-cols-3"
          max-height="min(50vh, 28rem)"
        >
          <label
            v-for="director in directorOptions"
            :key="director.id"
            class="flex items-center gap-3 rounded-2xl border border-amber-400/30 bg-slate-900/55 px-4 py-3 text-slate-200"
          >
            <input
              v-model="selectedDirectorIds"
              type="checkbox"
              :value="String(director.id)"
              class="h-4 w-4 accent-amber-400"
            />
            <span>{{ director.fullName }}</span>
          </label>
        </NeonScrollPanel>

        <p
          v-else
          class="rounded-2xl border border-amber-400/30 bg-slate-950/45 px-5 py-4 text-sm text-slate-300"
        >
          Clique sur "Réalisateurs" pour afficher ou masquer la liste.
        </p>
      </BaseCard>

      <BaseCard tone="pink" class="gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2 class="text-2xl font-semibold text-white">Casting</h2>
            <p class="mt-2 text-slate-300">Ajoute, modifie ou supprime les lignes acteur + rôle pour ce film.</p>
          </div>
          <BaseButton @click="addCastRow">Ajouter une ligne</BaseButton>
        </div>

        <div v-if="castRows.length" class="grid gap-4">
          <article
            v-for="row in castRows"
            :key="row.key"
            class="grid gap-4 rounded-[1.5rem] border border-pink-400/30 bg-slate-900/55 p-5 xl:grid-cols-[1.5fr_1fr_1fr_auto]"
          >
            <FormField label="Acteur" v-slot="{ controlClass }">
              <select v-model="row.actorId" :class="controlClass.replace('bg-slate-900/70', 'bg-slate-950/70')">
                <option value="">Sélectionner un acteur</option>
                <option
                  v-for="actor in actorOptions"
                  :key="actor.id"
                  :value="String(actor.id)"
                >
                  {{ actor.fullName }}
                </option>
              </select>
            </FormField>

            <FormField label="Prénom du rôle" tone="pink" v-slot="{ controlClass }">
              <input v-model="row.roleFirstName" type="text" placeholder="Ex : Neo" :class="controlClass.replace('bg-slate-900/70', 'bg-slate-950/70')" />
            </FormField>

            <FormField label="Nom du rôle" tone="amber" v-slot="{ controlClass }">
              <input v-model="row.roleLastName" type="text" placeholder="Ex : Anderson" :class="controlClass.replace('bg-slate-900/70', 'bg-slate-950/70')" />
            </FormField>

            <div class="flex flex-wrap gap-3 self-end">
              <BaseButton :disabled="row.saving || row.deleting" @click="saveCastRow(row)">
                {{ row.saving ? "Enregistrement..." : "Enregistrer" }}
              </BaseButton>
              <BaseButton
                variant="danger"
                :disabled="row.saving || row.deleting"
                @click="deleteCastRow(row)"
              >
                {{ row.deleting ? "Suppression..." : "Supprimer" }}
              </BaseButton>
            </div>
          </article>
        </div>

        <p v-else class="rounded-2xl border border-pink-400/30 bg-slate-950/45 px-5 py-6 text-slate-300">Aucune ligne de casting pour ce film.</p>
      </BaseCard>
    </template>
  </section>
</template>
