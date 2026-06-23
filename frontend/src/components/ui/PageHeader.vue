<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
  eyebrow: {
    type: String,
    default: "",
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: "",
  },
  eyebrowClass: {
    type: String,
    default: "text-cyan-300",
  },
  compact: {
    type: Boolean,
    default: false,
  },
  viewName: {
    type: String,
    default: "",
  },
});

const fallbackBannerBase = "/img/bannerx";
const bannerBase = ref(fallbackBannerBase);
const bannerCandidates = ref([fallbackBannerBase]);
const bannerFormat = ref("jpg");

const wrapperClass = computed(() =>
  props.compact
    ? "lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)]"
    : "lg:grid-cols-[minmax(0,24rem)_minmax(0,1fr)]",
);

const imageClass = computed(() =>
  props.compact
    ? "h-full min-h-[11rem] w-full rounded-[1.6rem] object-cover object-center"
    : "h-full min-h-[13rem] w-full rounded-[1.6rem] object-cover object-center",
);

const contentClass = computed(() =>
  props.compact
    ? "grid min-h-[11rem] grid-rows-[auto_auto_1fr_auto] gap-3 px-1 py-1 sm:px-3"
    : "grid min-h-[13rem] grid-rows-[auto_auto_1fr_auto] gap-3 px-1 py-1 sm:px-3",
);

const bannerSrc = computed(() =>
  bannerFormat.value === "jpg"
    ? `${bannerBase.value}-1280.jpg`
    : `${bannerBase.value}.png`,
);
const bannerSrcSet = computed(() =>
  bannerFormat.value === "jpg"
    ? `${bannerBase.value}-768.jpg 768w, ${bannerBase.value}-1280.jpg 1280w`
    : undefined,
);
const bannerSizes = computed(() =>
  props.compact
    ? "(min-width: 1024px) 18rem, 100vw"
    : "(min-width: 1024px) 24rem, 100vw",
);

function buildBannerCandidates(viewName) {
  if (!viewName) {
    return [fallbackBannerBase];
  }

  const normalizedViewName = String(viewName).toLowerCase();
  const candidates = [
    `/img/banner${normalizedViewName}`,
  ];

  if (!normalizedViewName.endsWith("view")) {
    candidates.push(`/img/banner${normalizedViewName}view`);
  }

  if (normalizedViewName.startsWith("admin") && normalizedViewName !== "adminview") {
    candidates.push("/img/banneradminview");
  }

  candidates.push(fallbackBannerBase);

  return [...new Set(candidates)];
}

function resetBanner() {
  bannerCandidates.value = buildBannerCandidates(props.viewName);
  bannerBase.value = bannerCandidates.value[0];
  bannerFormat.value = "jpg";
}

function handleBannerError() {
  if (bannerFormat.value === "jpg") {
    bannerFormat.value = "png";
    return;
  }

  const currentIndex = bannerCandidates.value.indexOf(bannerBase.value);
  const nextBanner = bannerCandidates.value[currentIndex + 1];

  if (nextBanner) {
    bannerBase.value = nextBanner;
    bannerFormat.value = "jpg";
  }
}

watch(
  () => props.viewName,
  () => {
    resetBanner();
  },
  { immediate: true },
);
</script>

<template>
  <header
    class="rounded-[2rem] bg-[linear-gradient(90deg,rgba(250,204,21,0.07),rgba(244,114,182,0.06)_52%,rgba(103,232,249,0.08))] p-[2px] shadow-[0_22px_60px_rgba(5,8,22,0.42)]"
  >
    <div
      class="grid gap-6 rounded-[calc(2rem-2px)] bg-[linear-gradient(145deg,rgba(7,12,26,0.94),rgba(13,18,35,0.88))] p-4 sm:p-5 lg:items-stretch"
      :class="wrapperClass"
    >
      <div class="overflow-hidden rounded-[1.6rem]">
        <img
          :src="bannerSrc"
          :srcset="bannerSrcSet"
          :sizes="bannerSizes"
          :alt="`Image de couverture pour ${title}`"
          :class="imageClass"
          @error="handleBannerError"
        />
      </div>

      <div :class="contentClass">
        <p
          v-if="eyebrow"
          class="self-start font-display text-[0.65rem] uppercase tracking-[0.3em]"
          :class="eyebrowClass"
        >
          {{ eyebrow }}
        </p>
        <h1 class="self-start text-4xl font-semibold text-white sm:text-5xl">
          {{ title }}
        </h1>
        <p
          v-if="description"
          class="self-center max-w-2xl text-lg leading-8 text-slate-300"
        >
          {{ description }}
        </p>
        <div
          v-if="$slots.actions"
          class="flex flex-wrap items-center gap-3 self-end"
        >
          <slot name="actions" />
        </div>
      </div>
    </div>
  </header>
</template>
