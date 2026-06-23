<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  tone: {
    type: String,
    default: "cyan",
  },
  fullRow: {
    type: Boolean,
    default: false,
  },
});

const controlClass = computed(() => {
  const baseClass =
    "w-full rounded-2xl bg-slate-900/70 px-4 py-3 text-slate-100 outline-none transition placeholder:text-slate-500";

  if (props.tone === "pink") {
    return `${baseClass} border border-pink-300/38 focus:border-pink-300 focus:ring-2 focus:ring-pink-300/30`;
  }

  if (props.tone === "amber") {
    return `${baseClass} border border-amber-300/38 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/30`;
  }

  return `${baseClass} border border-cyan-300/38 focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/30`;
});
</script>

<template>
  <label :class="[fullRow ? 'md:col-span-2' : '', 'grid gap-2 text-sm font-medium text-slate-200']">
    <span>{{ label }}</span>
    <slot :control-class="controlClass" />
  </label>
</template>
