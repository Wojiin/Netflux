<script setup>
import { computed } from "vue";
import BaseButton from "../components/ui/BaseButton.vue";
import BaseCard from "../components/ui/BaseCard.vue";
import FormField from "../components/ui/FormField.vue";
import PageHeader from "../components/ui/PageHeader.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useAdminUserForm } from "../composables/useAdminUserForm";

const props = defineProps({
  id: {
    type: String,
    default: "",
  },
});

const userId = computed(() => props.id || null);

const {
  isEdit,
  loading,
  error,
  form,
  roleOptions,
  toggleRole,
  handleSubmit,
} = useAdminUserForm(userId);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Access Registry"
      eyebrow-class="text-pink-300"
      :title="isEdit ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'"
      view-name="adminuserform"
    >
      <template #actions>
        <router-link to="/admin">
          <BaseButton variant="secondary">Retour</BaseButton>
        </router-link>
        <router-link to="/admin/users">
          <BaseButton variant="secondary">Retour à la liste</BaseButton>
        </router-link>
      </template>
    </PageHeader>

    <BaseCard tone="pink" class="gap-6">
      <StatusMessage v-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

      <form class="grid gap-5 md:grid-cols-2" @submit.prevent="handleSubmit">
        <FormField label="Email" v-slot="{ controlClass }">
          <input v-model="form.email" type="email" required :class="controlClass" />
        </FormField>

        <FormField :label="`Mot de passe ${isEdit ? '(laisser vide pour ne pas changer)' : ''}`" tone="pink" v-slot="{ controlClass }">
          <input
            v-model="form.plainPassword"
            type="password"
            :required="!isEdit"
            minlength="8"
            :class="controlClass"
          />
        </FormField>

        <fieldset class="grid gap-3 rounded-[1.5rem] border border-pink-400/34 bg-slate-900/55 p-5 md:col-span-2">
          <legend class="px-2 text-sm font-semibold uppercase tracking-[0.18em] text-pink-200">Rôles</legend>
          <label v-for="role in roleOptions" :key="role" class="flex items-center gap-3 text-slate-200">
            <input
              :checked="form.roles.includes(role)"
              type="checkbox"
              @change="toggleRole(role)"
              class="h-4 w-4 accent-pink-400"
            />
            {{ role }}
          </label>
        </fieldset>

        <div class="flex flex-wrap gap-3 md:col-span-2">
          <BaseButton type="submit" :disabled="loading">
            {{ loading ? "Enregistrement..." : "Enregistrer" }}
          </BaseButton>
          <router-link to="/admin/users">
            <BaseButton type="button" variant="secondary">Annuler</BaseButton>
          </router-link>
        </div>
      </form>
    </BaseCard>
  </section>
</template>
