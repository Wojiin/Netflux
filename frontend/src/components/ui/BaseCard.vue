<script setup>
import NeonFrame from "./NeonFrame.vue";

defineProps({
  as: {
    type: String,
    default: "article",
  },
  tone: {
    type: String,
    default: "auto",
  },
  compact: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <NeonFrame
    :as="as"
    :compact="compact"
    :tone="tone"
    :class="[
      'grid',
      compact ? 'gap-3' : 'gap-4',
    ]"
  >
    <slot />
  </NeonFrame>
</template>
