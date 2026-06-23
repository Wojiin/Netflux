<script setup>
import BaseButton from "../components/ui/BaseButton.vue";
import AuthShell from "../components/ui/AuthShell.vue";
import FormField from "../components/ui/FormField.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useRegisterForm } from "../composables/useRegisterForm";

const { loading, error, registerForm, submitRegister, goToLogin } =
  useRegisterForm();
</script>

<template>
  <AuthShell
    eyebrow="New Ticket"
    title="Creer un compte"
    tone="pink"
    active-tab="register"
    @select-login="goToLogin"
  >
    <StatusMessage v-if="error" variant="error">{{ error }}</StatusMessage>

    <form class="grid gap-5" @submit.prevent="submitRegister">
      <FormField label="Email" v-slot="{ controlClass }">
        <input
          v-model="registerForm.email"
          type="email"
          required
          :class="controlClass"
        />
      </FormField>

      <FormField label="Mot de passe" tone="pink" v-slot="{ controlClass }">
        <input
          v-model="registerForm.plainPassword"
          type="password"
          minlength="8"
          required
          :class="controlClass"
        />
      </FormField>

      <FormField
        label="Confirmer le mot de passe"
        tone="amber"
        v-slot="{ controlClass }"
      >
        <input
          v-model="registerForm.confirmPassword"
          type="password"
          minlength="8"
          required
          :class="controlClass"
        />
      </FormField>

      <BaseButton type="submit" :disabled="loading" block>
        {{ loading ? "Creation..." : "Creer un compte" }}
      </BaseButton>
    </form>
  </AuthShell>
</template>
