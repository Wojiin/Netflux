<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useAttrs } from "vue";

defineOptions({
  inheritAttrs: false,
});

const props = defineProps({
  maxHeight: {
    type: String,
    default: "",
  },
});

const attrs = useAttrs();
const panelRef = ref(null);
const scrollProgress = ref(0);

let resizeObserver = null;

function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max);
}

function interpolateChannel(start, end, progress) {
  return Math.round(start + (end - start) * progress);
}

function interpolateColor(stops, progress) {
  if (progress <= 0) {
    return stops[0];
  }

  if (progress >= 1) {
    return stops.at(-1);
  }

  const segmentSize = 1 / (stops.length - 1);
  const segmentIndex = Math.min(
    Math.floor(progress / segmentSize),
    stops.length - 2,
  );
  const segmentProgress = (progress - segmentIndex * segmentSize) / segmentSize;
  const start = stops[segmentIndex];
  const end = stops[segmentIndex + 1];

  return [
    interpolateChannel(start[0], end[0], segmentProgress),
    interpolateChannel(start[1], end[1], segmentProgress),
    interpolateChannel(start[2], end[2], segmentProgress),
  ];
}

function updateScrollProgress() {
  const panel = panelRef.value;

  if (!panel) {
    scrollProgress.value = 0;
    return;
  }

  const maxScrollTop = panel.scrollHeight - panel.clientHeight;

  if (maxScrollTop <= 0) {
    scrollProgress.value = 0;
    return;
  }

  scrollProgress.value = clamp(panel.scrollTop / maxScrollTop, 0, 1);
}

const panelStyle = computed(() => {
  const [red, green, blue] = interpolateColor(
    [
      [103, 232, 249],
      [250, 204, 21],
      [244, 114, 182],
    ],
    scrollProgress.value,
  );
  const thumbColor = `rgb(${red} ${green} ${blue})`;

  return {
    "--scroll-thumb-color": thumbColor,
    "--scroll-thumb-glow": `0 0 10px ${thumbColor}, 0 0 22px ${thumbColor}`,
    maxHeight: props.maxHeight || undefined,
  };
});

onMounted(async () => {
  await nextTick();
  updateScrollProgress();

  if (typeof ResizeObserver !== "undefined") {
    resizeObserver = new ResizeObserver(() => {
      updateScrollProgress();
    });

    if (panelRef.value) {
      resizeObserver.observe(panelRef.value);
    }
  }
});

onBeforeUnmount(() => {
  resizeObserver?.disconnect();
});
</script>

<template>
  <div
    ref="panelRef"
    class="neon-scroll-panel relative overflow-y-auto overscroll-contain min-h-0"
    v-bind="attrs"
    :style="panelStyle"
    @scroll.passive="updateScrollProgress"
  >
    <slot />
  </div>
</template>

<style scoped>
.neon-scroll-panel {
  scrollbar-width: thin;
  scrollbar-color: var(--scroll-thumb-color) rgba(103, 232, 249, 0.12);
}

.neon-scroll-panel::-webkit-scrollbar {
  width: 0.75rem;
}

.neon-scroll-panel::-webkit-scrollbar-track {
  border-radius: 9999px;
  background:
    linear-gradient(180deg, rgba(103, 232, 249, 0.08), rgba(244, 114, 182, 0.06)),
    rgba(11, 16, 32, 0.88);
  box-shadow:
    inset 0 0 0 1px rgba(103, 232, 249, 0.1),
    inset 0 0 18px rgba(5, 8, 22, 0.75);
}

.neon-scroll-panel::-webkit-scrollbar-thumb {
  border: 2px solid rgba(5, 8, 22, 0.92);
  border-radius: 9999px;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.38), transparent 28%),
    linear-gradient(180deg, var(--scroll-thumb-color), color-mix(in srgb, var(--scroll-thumb-color) 52%, black));
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.14),
    var(--scroll-thumb-glow);
}

.neon-scroll-panel::-webkit-scrollbar-thumb:hover {
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.2),
    0 0 14px var(--scroll-thumb-color),
    0 0 28px var(--scroll-thumb-color);
}
</style>
