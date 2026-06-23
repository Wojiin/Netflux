<script setup>
import { computed } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminGenreForm } from "../composables/useAdminGenreForm";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const genreId = computed(() => props.id || null);

const {
  isEdit,
  loading,
  error,
  form,
  handleSubmit,
} = useAdminGenreForm(genreId);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Genre Matrix"
      eyebrow-class="text-cyan-300"
      :title="isEdit ? 'Modifier un genre' : 'Ajouter un genre'"
      view-name="admingenreform"
    >
      <template #actions>
        <router-link to="/admin">
          <BaseButton variant="secondary">Retour</BaseButton>
        </router-link>
        <router-link to="/admin/genres">
          <BaseButton variant="secondary">Retour a la liste</BaseButton>
        </router-link>
      </template>
    </PageHeader>

    <BaseCard tone="cyan" class="gap-6">
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

      <form class="grid gap-5" @submit.prevent="handleSubmit">
        <FormField label="Nom du genre" tone="cyan" v-slot="{ controlClass }">
          <input v-model="form.name" type="text" required :class="controlClass" />
        </FormField>

        <div class="flex flex-wrap gap-3">
          <BaseButton type="submit" :disabled="loading">
            {{ loading ? "Enregistrement..." : "Enregistrer" }}
          </BaseButton>
          <router-link to="/admin/genres">
            <BaseButton type="button" variant="secondary">Annuler</BaseButton>
          </router-link>
        </div>
      </form>
    </BaseCard>
  </section>
</template>
