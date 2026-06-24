<script setup>
import { computed } from "vue";

const props = defineProps({
  variant: {
    type: String,
    default: "info",
  },
});

const messageClass = computed(() => {
  const baseClass = "rounded-2xl border px-5 py-4";

  if (props.variant === "error") {
    return `${baseClass} border-pink-400/40 bg-pink-500/10 text-pink-100`;
  }

  if (props.variant === "success") {
    return `${baseClass} border-emerald-400/40 bg-emerald-400/10 text-emerald-100`;
  }

  return `${baseClass} border-cyan-400/36 bg-slate-950/50 text-slate-200`;
});
</script>

<template>
  <p :class="messageClass">
    <slot />
  </p>
</template>
