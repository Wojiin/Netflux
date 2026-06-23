<script setup>
import { computed } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminMovieForm } from "../composables/useAdminMovieForm";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const movieId = computed(() => props.id || null);

const {
  isEdit,
  loading,
  genresLoading,
  error,
  genreOptions,
  typeOptions,
  form,
  getGenreIri,
  handleSubmit,
} = useAdminMovieForm(movieId);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Editing Bay"
      :title="isEdit ? 'Modifier un film' : 'Ajouter un film'"
      view-name="adminmovieform"
    >
      <template #actions>
          <router-link to="/admin">
            <BaseButton variant="secondary">Retour</BaseButton>
          </router-link>
          <router-link
            v-if="isEdit"
            :to="`/admin/movies/${movieId}/casting`"
          >
            <BaseButton variant="accent">Gerer le casting</BaseButton>
          </router-link>
          <router-link to="/admin/movies">
            <BaseButton variant="secondary">Retour à la liste</BaseButton>
          </router-link>
      </template>
    </PageHeader>

    <BaseCard tone="cyan" class="gap-6">
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

      <form class="grid gap-5 md:grid-cols-2" @submit.prevent="handleSubmit">
        <FormField label="Titre" v-slot="{ controlClass }">
          <input v-model="form.title" type="text" required :class="controlClass" />
        </FormField>

        <FormField label="Type" tone="pink" v-slot="{ controlClass }">
          <select v-model="form.type" required :class="controlClass">
            <option v-for="contentType in typeOptions" :key="contentType.value" :value="contentType.value">
              {{ contentType.label }}
            </option>
          </select>
        </FormField>

        <FormField label="Genre" tone="amber" v-slot="{ controlClass }">
          <select v-model="form.genre" :disabled="genresLoading" required :class="controlClass">
            <option value="">Sélectionner un genre</option>
            <option
              v-for="genre in genreOptions"
              :key="genre.id"
              :value="getGenreIri(genre)"
            >
              {{ genre.name }}
            </option>
          </select>
        </FormField>

        <FormField label="Date de sortie" v-slot="{ controlClass }">
          <input v-model="form.releasedAt" type="date" required :class="controlClass" />
        </FormField>

        <FormField label="Durée" tone="pink" v-slot="{ controlClass }">
          <input v-model="form.duration" type="number" min="1" required :class="controlClass" />
        </FormField>

        <FormField label="Note" tone="amber" v-slot="{ controlClass }">
          <input v-model="form.rate" type="number" min="0" max="5" :class="controlClass" />
        </FormField>

        <FormField label="URL affiche" v-slot="{ controlClass }">
          <input v-model="form.imgLink" type="url" required :class="controlClass" />
        </FormField>

        <FormField label="URL bande-annonce" tone="pink" v-slot="{ controlClass }">
          <input v-model="form.videoLink" type="url" required :class="controlClass" />
        </FormField>

        <FormField label="Synopsis" full-row v-slot="{ controlClass }">
          <textarea v-model="form.synopsis" rows="6" required :class="controlClass.replace('rounded-2xl', 'rounded-[1.5rem]')" />
        </FormField>

        <div class="flex flex-wrap gap-3 md:col-span-2">
          <BaseButton type="submit" :disabled="loading">
            {{ loading ? "Enregistrement..." : "Enregistrer" }}
          </BaseButton>
          <router-link to="/admin/movies">
            <BaseButton type="button" variant="secondary">Annuler</BaseButton>
          </router-link>
        </div>
      </form>
    </BaseCard>
  </section>
</template>
