<script setup>
import { computed } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminProfileForm } from "../composables/useAdminProfileForm";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const directorId = computed(() => props.id || null);

const {
  isEdit,
  loading,
  peopleLoading,
  error,
  form,
  availablePeople,
  getPersonValue,
  getPersonLabel,
  handleSubmit,
} = useAdminProfileForm(
  {
    endpoint: "/directors",
    resourceName: "réalisateur",
    personFlag: "isDirector",
    redirectTo: "/admin/directors",
  },
  directorId,
);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Director Booth"
      eyebrow-class="text-pink-300"
      :title="isEdit ? 'Modifier un réalisateur' : 'Ajouter un réalisateur'"
      view-name="admindirectorform"
    >
      <template #actions>
        <router-link to="/admin">
          <BaseButton variant="secondary">Retour</BaseButton>
        </router-link>
        <router-link to="/admin/directors">
          <BaseButton variant="secondary">Retour à la liste</BaseButton>
        </router-link>
      </template>
    </PageHeader>

    <BaseCard tone="pink" class="gap-6">
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

      <form class="grid gap-5" @submit.prevent="handleSubmit">
        <FormField label="Personne associée" tone="pink" v-slot="{ controlClass }">
          <select v-model="form.personId" required :disabled="peopleLoading" :class="controlClass">
            <option value="">Sélectionner une personne</option>
            <option
              v-for="person in availablePeople"
              :key="getPersonValue(person)"
              :value="getPersonValue(person)"
            >
              {{ getPersonLabel(person) }}
            </option>
          </select>
        </FormField>

        <div class="flex flex-wrap gap-3">
          <BaseButton type="submit" :disabled="loading || peopleLoading">
            {{ loading ? "Enregistrement..." : "Enregistrer" }}
          </BaseButton>
          <router-link to="/admin/directors">
            <BaseButton type="button" variant="secondary">Annuler</BaseButton>
          </router-link>
        </div>
      </form>
    </BaseCard>
  </section>
</template>
