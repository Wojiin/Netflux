import { computed, onMounted, watch } from "vue";

function fallbackPortraitUrl(fullName, seedLabel) {
  return `https://picsum.photos/seed/${encodeURIComponent(`netflux-person-${fullName || seedLabel}`)}/800/1200`;
}

export function usePersonDetail(options) {
  const { entityRef, fetchEntity, seedLabel, id } = options;
  const portraitUrl = computed(() => {
    return entityRef.value?.portraitLink || fallbackPortraitUrl(entityRef.value?.fullName, seedLabel);
  });

  async function loadEntity(entityId = id?.value ?? id) {
    if (entityId) {
      await fetchEntity(entityId);
    }
  }

  onMounted(() => {
    loadEntity();
  });

  watch(
    () => id?.value ?? id,
    (entityId) => {
      loadEntity(entityId);
    },
  );

  return {
    portraitUrl,
  };
}
