<script setup>
import BaseButton from "../components/ui/BaseButton.vue";
import AuthShell from "../components/ui/AuthShell.vue";
import FormField from "../components/ui/FormField.vue";
import StatusMessage from "../components/ui/StatusMessage.vue";
import { useLoginForm } from "../composables/useLoginForm";

const {
  loading,
  error,
  infoMessage,
  loginForm,
  submitLogin,
  goToRegister,
} = useLoginForm();
</script>

<template>
  <AuthShell
    eyebrow="Access Gate"
    title="Connexion"
    tone="cyan"
    active-tab="login"
    @select-register="goToRegister"
  >
    <StatusMessage v-if="infoMessage">{{ infoMessage }}</StatusMessage>
    <StatusMessage v-if="error" variant="error">{{ error }}</StatusMessage>

    <form class="grid gap-5" @submit.prevent="submitLogin">
      <FormField label="Email" v-slot="{ controlClass }">
        <input
          v-model="loginForm.email"
          type="email"
          required
          :class="controlClass"
        />
      </FormField>

      <FormField label="Mot de passe" tone="pink" v-slot="{ controlClass }">
        <input
          v-model="loginForm.password"
          type="password"
          required
          :class="controlClass"
        />
      </FormField>

      <BaseButton type="submit" :disabled="loading" block>
        {{ loading ? "Connexion..." : "Se connecter" }}
      </BaseButton>
    </form>
  </AuthShell>
</template>
