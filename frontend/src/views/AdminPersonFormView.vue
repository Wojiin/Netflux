<script setup>
import { computed } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminPersonForm } from "../composables/useAdminPersonForm";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const personId = computed(() => props.id || null);

const {
  isEdit,
  loading,
  error,
  form,
  genderOptions,
  handleSubmit,
} = useAdminPersonForm(personId);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Identity Registry"
      title="Gestion des personnes"
      :description="isEdit ? 'Modifier une fiche personne.' : 'Ajouter une nouvelle fiche personne.'"
      view-name="adminpersonform"
    >
      <template #actions>
        <router-link to="/admin">
          <BaseButton variant="secondary">Retour</BaseButton>
        </router-link>
        <router-link to="/admin/people">
          <BaseButton variant="secondary">Retour à la liste</BaseButton>
        </router-link>
      </template>
    </PageHeader>

    <BaseCard tone="amber" class="gap-6">
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

      <form class="grid gap-5 md:grid-cols-2" @submit.prevent="handleSubmit">
        <FormField label="Prenom" v-slot="{ controlClass }">
          <input v-model="form.firstName" type="text" required :class="controlClass" />
        </FormField>

        <FormField label="Nom" tone="pink" v-slot="{ controlClass }">
          <input v-model="form.lastName" type="text" required :class="controlClass" />
        </FormField>

        <FormField label="Genre" tone="amber" v-slot="{ controlClass }">
          <select v-model="form.gender" required :class="controlClass">
            <option value="">Sélectionner un genre</option>
            <option v-for="option in genderOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </FormField>

        <FormField label="Date de naissance" v-slot="{ controlClass }">
          <input v-model="form.birthday" type="date" required :class="controlClass" />
        </FormField>

        <FormField label="URL portrait" tone="pink" full-row v-slot="{ controlClass }">
          <input v-model="form.portraitLink" type="url" required :class="controlClass" />
        </FormField>

        <div class="flex flex-wrap gap-3 md:col-span-2">
          <BaseButton type="submit" :disabled="loading">
            {{ loading ? "Enregistrement..." : "Enregistrer" }}
          </BaseButton>
          <router-link to="/admin/people">
            <BaseButton type="button" variant="secondary">Annuler</BaseButton>
          </router-link>
        </div>
      </form>
    </BaseCard>
  </section>
</template>
