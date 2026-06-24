<script setup>
import { computed } from "vue";

const props = defineProps({
  type: {
    type: String,
    default: "button",
  },
  variant: {
    type: String,
    default: "primary",
  },
  block: {
    type: Boolean,
    default: false,
  },
});

const buttonClass = computed(() => {
  const baseClass =
    "inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300 disabled:cursor-not-allowed disabled:opacity-50";
  const widthClass = props.block ? " w-full" : "";

  if (props.variant === "secondary") {
    return `${baseClass}${widthClass} border-cyan-400/58 bg-slate-950/70 text-slate-100 shadow-[0_0_0_1px_rgba(103,232,249,0.18),0_0_24px_rgba(34,211,238,0.15)] hover:-translate-y-0.5 hover:border-cyan-300 hover:text-cyan-200 hover:shadow-[0_0_0_1px_rgba(103,232,249,0.36),0_0_28px_rgba(34,211,238,0.25)]`;
  }

  if (props.variant === "accent") {
    return `${baseClass}${widthClass} border-amber-300/78 bg-amber-300/15 text-amber-100 shadow-[0_0_0_1px_rgba(250,204,21,0.2),0_0_26px_rgba(250,204,21,0.18)] hover:-translate-y-0.5 hover:border-amber-200 hover:bg-amber-300/20 hover:text-amber-50`;
  }

  if (props.variant === "danger") {
    return `${baseClass}${widthClass} border-pink-400/68 bg-pink-500/15 text-pink-100 shadow-[0_0_0_1px_rgba(244,114,182,0.22),0_0_26px_rgba(244,114,182,0.2)] hover:-translate-y-0.5 hover:border-pink-300 hover:bg-pink-500/22 hover:text-white`;
  }

  return `${baseClass}${widthClass} border-cyan-300/68 bg-cyan-400/14 text-cyan-50 shadow-[0_0_0_1px_rgba(103,232,249,0.2),0_0_24px_rgba(34,211,238,0.16)] hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-cyan-400/20 hover:text-white hover:shadow-[0_0_0_1px_rgba(103,232,249,0.32),0_0_30px_rgba(34,211,238,0.22)]`;
});
</script>

<template>
  <button :type="type" :class="buttonClass">
    <slot />
  </button>
</template>
