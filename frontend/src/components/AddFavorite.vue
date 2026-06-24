<script setup>
import { computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import BaseButton from "./ui/BaseButton.vue";
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
const { favoritesError, favoritesLoaded, favoritesLoading, isAuthenticated } =
  storeToRefs(userStore);
const { notifyError, notifyInfo, notifySuccess } = useNotifications();

const isFavorite = computed(() => userStore.isFavorite(props.movie.id));

const favoriteLabel = computed(() =>
  !isAuthenticated.value
    ? "Connexion requise"
    : isFavorite.value
      ? "Retirer favori"
      : "Ajouter favori",
);

const isDisabled = computed(() => userStore.isFavoritePending(props.movie.id));
const buttonVariant = computed(() => (isFavorite.value ? "danger" : "accent"));

onMounted(() => {
  if (isAuthenticated.value && !favoritesLoaded.value && !favoritesLoading.value) {
    userStore.fetchFavorites().catch(() => {});
  }
});

async function handleToggle() {
  if (!isAuthenticated.value) {
    await router.push({
      name: "login",
      query: { redirect: route.fullPath },
    });
    return;
  }

  try {
    const status = await userStore.toggleFavorite(props.movie);

    if (status === "added") {
      notifySuccess(`"${props.movie.title}" ajoute aux favoris.`);
    } else if (status === "removed") {
      notifyInfo(`"${props.movie.title}" retire des favoris.`);
    }
  } catch {
    notifyError(userStore.favoritesError || "Impossible de mettre a jour les favoris.");
  }
}
</script>

<template>
  <div class="mt-auto grid gap-2 pt-2">
    <BaseButton
      type="button"
      :variant="buttonVariant"
      class="min-h-12 min-w-[16rem] justify-self-start"
      :disabled="isDisabled"
      @click="handleToggle"
    >
      <span class="flex w-full items-center justify-center gap-2 text-center">
        <span>{{ isDisabled ? "Chargement..." : favoriteLabel }}</span>
        <svg
          class="pointer-events-none h-4 w-4 shrink-0 transition duration-200"
          :class="
            [
              isFavorite
                ? 'fill-pink-400 text-pink-300'
                : 'fill-transparent text-pink-200/70',
              isDisabled ? 'motion-safe:animate-pulse' : '',
            ]
          "
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </span>
    </BaseButton>
    <p v-if="isAuthenticated && favoritesError" class="text-sm text-pink-200">
      {{ favoritesError }}
    </p>
  </div>
</template>
