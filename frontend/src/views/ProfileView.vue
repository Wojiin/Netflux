<script setup>
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useProfileForm } from "../composables/useProfileForm";

const {
  loading,
  saving,
  error,
  success,
  form,
  submitProfile,
  goBack,
} = useProfileForm();
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Identity Booth"
      title="Mon profil"
      description="Modifie ton e-mail et ton mot de passe."
      compact
      view-name="profile"
    />

    <BaseCard class="max-w-2xl">
      <StatusMessage v-if="loading">Chargement du profil...</StatusMessage>
      <template v-else>
        <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>
        <StatusMessage v-if="success" variant="success">Profil modifié avec succès.</StatusMessage>

        <form class="grid gap-5" autocomplete="off" @submit.prevent="submitProfile">
          <FormField label="Email" v-slot="{ controlClass }">
            <input
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              name="profile-email"
              :class="controlClass"
            />
          </FormField>

          <FormField label="Nouveau mot de passe" tone="pink" v-slot="{ controlClass }">
            <input
              v-model="form.plainPassword"
              type="password"
              minlength="8"
              placeholder="Laisser vide pour ne pas changer"
              autocomplete="new-password"
              name="profile-new-password"
              data-lpignore="true"
              data-1p-ignore="true"
              :class="controlClass"
            />
          </FormField>

          <div class="flex flex-wrap gap-3">
            <BaseButton type="submit" :disabled="saving">
              {{ saving ? "Enregistrement..." : "Enregistrer" }}
            </BaseButton>
            <BaseButton type="button" variant="secondary" @click="goBack">
              Retour
            </BaseButton>
          </div>
        </form>
      </template>
    </BaseCard>
  </section>
</template>
