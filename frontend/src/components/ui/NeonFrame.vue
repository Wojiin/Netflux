<script setup>
import { computed } from "vue";

const props = defineProps({
  as: {
    type: String,
    default: "div",
  },
  tone: {
    type: String,
    default: "cyan",
  },
  compact: {
    type: Boolean,
    default: false,
  },
  padded: {
    type: Boolean,
    default: true,
  },
});

const frameClass = computed(() => {
  const baseClass =
    "neon-frame relative overflow-hidden rounded-[1.75rem] border-2 bg-slate-950/60 backdrop-blur-md before:pointer-events-none before:absolute before:inset-0 before:rounded-[inherit] before:content-['']";
  const spacingClass = props.padded ? (props.compact ? " p-4" : " p-6") : "";
  const toneClass = props.tone === "auto" ? " neon-frame--auto" : ` neon-frame--${props.tone}`;

  return `${baseClass}${spacingClass}${toneClass}`;
});
</script>

<template>
  <component :is="as" :class="frameClass">
    <div class="relative z-10">
      <slot />
    </div>
  </component>
</template>
