<script setup>
import { onBeforeUnmount, onMounted } from "vue";

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

function updateGlobalScrollTheme() {
  const root = document.documentElement;
  const maxScrollTop = root.scrollHeight - window.innerHeight;
  const progress =
    maxScrollTop > 0 ? clamp(window.scrollY / maxScrollTop, 0, 1) : 0;
  const [red, green, blue] = interpolateColor(
    [
      [103, 232, 249],
      [250, 204, 21],
      [244, 114, 182],
    ],
    progress,
  );
  const thumbColor = `rgb(${red} ${green} ${blue})`;

  root.style.setProperty("--global-scroll-thumb-color", thumbColor);
  root.style.setProperty(
    "--global-scroll-thumb-glow",
    `0 0 10px ${thumbColor}, 0 0 22px ${thumbColor}`,
  );
}

onMounted(() => {
  updateGlobalScrollTheme();
  window.addEventListener("scroll", updateGlobalScrollTheme, { passive: true });
  window.addEventListener("resize", updateGlobalScrollTheme);
});

onBeforeUnmount(() => {
  window.removeEventListener("scroll", updateGlobalScrollTheme);
  window.removeEventListener("resize", updateGlobalScrollTheme);
});
</script>

<template>
  <span aria-hidden="true" class="hidden"></span>
</template>

<style>
html {
  --global-scroll-thumb-color: rgb(103 232 249);
  --global-scroll-thumb-glow: 0 0 10px rgb(103 232 249), 0 0 22px rgb(103 232 249);
  scrollbar-width: thin;
  scrollbar-color: var(--global-scroll-thumb-color) rgba(103, 232, 249, 0.12);
}

html::-webkit-scrollbar,
body::-webkit-scrollbar {
  width: 0.8rem;
}

html::-webkit-scrollbar-track,
body::-webkit-scrollbar-track {
  border-radius: 9999px;
  background:
    linear-gradient(180deg, rgba(103, 232, 249, 0.08), rgba(244, 114, 182, 0.06)),
    rgba(11, 16, 32, 0.88);
  box-shadow:
    inset 0 0 0 1px rgba(103, 232, 249, 0.1),
    inset 0 0 18px rgba(5, 8, 22, 0.75);
}

html::-webkit-scrollbar-thumb,
body::-webkit-scrollbar-thumb {
  border: 2px solid rgba(5, 8, 22, 0.92);
  border-radius: 9999px;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.38), transparent 28%),
    linear-gradient(180deg, var(--global-scroll-thumb-color), color-mix(in srgb, var(--global-scroll-thumb-color) 52%, black));
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.14),
    var(--global-scroll-thumb-glow);
}

html::-webkit-scrollbar-thumb:hover,
body::-webkit-scrollbar-thumb:hover {
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.2),
    0 0 14px var(--global-scroll-thumb-color),
    0 0 28px var(--global-scroll-thumb-color);
}
</style>
