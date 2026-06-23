<script setup>
import { computed } from "vue";
import NeonFrame from "./NeonFrame.vue";

const props = defineProps({
  eyebrow: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  tone: {
    type: String,
    default: "cyan",
  },
  activeTab: {
    type: String,
    default: "login",
  },
});

const emit = defineEmits(["select-login", "select-register"]);

const accentClass = computed(() =>
  props.tone === "pink" ? "text-pink-300" : "text-cyan-300",
);

const logoClass = computed(() =>
  props.tone === "pink"
    ? "mt-5 h-28 w-auto drop-shadow-[0_0_30px_rgba(244,114,182,0.34)] sm:h-36"
    : "mt-5 h-28 w-auto drop-shadow-[0_0_30px_rgba(103,232,249,0.34)] sm:h-36",
);

const loginButtonClass = computed(() =>
  props.activeTab === "login"
    ? "border-cyan-300/58 bg-cyan-400/15 text-cyan-100"
    : "border-slate-500/80 bg-slate-900/70 text-slate-300 hover:border-cyan-300/50 hover:text-cyan-100",
);

const registerButtonClass = computed(() =>
  props.activeTab === "register"
    ? "border-pink-300/58 bg-pink-500/15 text-pink-100"
    : "border-slate-500/80 bg-slate-900/70 text-slate-300 hover:border-pink-300/50 hover:text-pink-100",
);
</script>

<template>
  <section class="grid w-full flex-1 place-items-center overflow-hidden">
    <NeonFrame
      class="w-full max-w-xl px-8 py-8 sm:px-10"
      :tone="tone"
      :padded="false"
    >
      <p
        class="font-display text-[0.65rem] uppercase tracking-[0.32em]"
        :class="accentClass"
      >
        {{ eyebrow }}
      </p>
      <img src="/img/logo.png" alt="Logo" :class="logoClass" />
      <h1 class="mt-5 text-3xl font-semibold text-white sm:text-4xl">
        {{ title }}
      </h1>

      <div class="mt-8 flex flex-wrap gap-3">
        <button
          type="button"
          class="rounded-full border px-4 py-2 text-sm font-semibold uppercase tracking-[0.18em] transition"
          :class="loginButtonClass"
          @click="emit('select-login')"
        >
          Connexion
        </button>
        <button
          type="button"
          class="rounded-full border px-4 py-2 text-sm font-semibold uppercase tracking-[0.18em] transition"
          :class="registerButtonClass"
          @click="emit('select-register')"
        >
          Inscription
        </button>
      </div>

      <div class="mt-6 grid gap-5">
        <slot />
      </div>
    </NeonFrame>
  </section>
</template>
