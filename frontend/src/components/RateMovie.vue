<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { useNotifications } from "../composables/useNotifications";
import { useUserStore } from "../stores/user";

const props = defineProps({
  movie: {
    type: Object,
    required: true,
  },
});

const router = useRouter();
const route = useRoute();
const userStore = useUserStore();
const { isAuthenticated, ratingsError, ratingsLoaded, ratingsLoading } =
  storeToRefs(userStore);
const { notifyError, notifySuccess } = useNotifications();

const ratingValues = [0, 1, 2, 3, 4, 5];
const currentRating = computed(() => userStore.getMovieRating(props.movie.id));
const isPending = computed(() => userStore.isRatingPending(props.movie.id));
const averageRateLabel = computed(() => {
  const averageRate = props.movie.averageRate;

  if (typeof averageRate === "number") {
    return averageRate.toFixed(1);
  }

  if (typeof props.movie.rate === "number") {
    return props.movie.rate.toFixed(1);
  }

  return "N/A";
});

onMounted(() => {
  if (isAuthenticated.value && !ratingsLoaded.value && !ratingsLoading.value) {
    userStore.fetchRatings().catch(() => {});
  }
});

async function handleRate(rate) {
  if (!isAuthenticated.value) {
    await router.push({
      name: "login",
      query: { redirect: route.fullPath },
    });

    return;
  }

  try {
    await userStore.rateMovie(props.movie, rate);
    notifySuccess(`Note enregistree : ${rate}/5.`);
  } catch {
    notifyError(userStore.ratingsError || "Impossible d'enregistrer la note.");
  }
}
</script>

<template>
  <div class="grid gap-3">
    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
      <p class="text-amber-200">
        Note moyenne : {{ averageRateLabel }}/5
        <span class="text-slate-400">({{ movie.ratingsCount ?? 0 }} avis)</span>
      </p>
      <p class="text-slate-300">
        Ma note : {{ currentRating ?? "-" }}/5
      </p>
    </div>

    <div class="flex flex-wrap gap-2">
      <button
        v-for="rate in ratingValues"
        :key="rate"
        type="button"
        class="inline-flex min-w-10 items-center justify-center rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] transition duration-200 disabled:cursor-not-allowed disabled:opacity-50"
        :class="
          currentRating === rate
            ? 'border-amber-200 bg-amber-300/22 text-amber-50 shadow-[0_0_0_1px_rgba(250,204,21,0.24),0_0_20px_rgba(250,204,21,0.18)]'
            : 'border-slate-600 bg-slate-900/70 text-slate-200 hover:border-amber-300/60 hover:text-amber-100'
        "
        :data-active="currentRating === rate"
        :disabled="isPending"
        @click="handleRate(rate)"
      >
        {{ rate }}
      </button>
    </div>

    <p v-if="isAuthenticated && ratingsError" class="text-sm text-pink-200">
      {{ ratingsError }}
    </p>
  </div>
</template>
