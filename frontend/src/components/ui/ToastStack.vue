<script setup>
import { useNotifications } from "../../composables/useNotifications";

const { notifications, removeNotification } = useNotifications();

function getToastClass(variant) {
  if (variant === "error") {
    return "border-pink-300/55 bg-[linear-gradient(135deg,rgba(244,114,182,0.28),rgba(15,23,42,0.96)_38%,rgba(251,191,36,0.22))] text-pink-50 shadow-[0_0_0_1px_rgba(244,114,182,0.22),0_0_26px_rgba(244,114,182,0.24),0_22px_52px_rgba(5,8,22,0.46)]";
  }

  if (variant === "success") {
    return "border-emerald-300/55 bg-[linear-gradient(135deg,rgba(16,185,129,0.28),rgba(15,23,42,0.96)_38%,rgba(34,211,238,0.2))] text-emerald-50 shadow-[0_0_0_1px_rgba(52,211,153,0.22),0_0_26px_rgba(52,211,153,0.22),0_22px_52px_rgba(5,8,22,0.46)]";
  }

  return "border-cyan-300/55 bg-[linear-gradient(135deg,rgba(34,211,238,0.3),rgba(15,23,42,0.96)_40%,rgba(244,114,182,0.24))] text-cyan-50 shadow-[0_0_0_1px_rgba(34,211,238,0.24),0_0_28px_rgba(34,211,238,0.22),0_22px_52px_rgba(5,8,22,0.46)]";
}
</script>

<template>
  <Teleport to="body">
    <div class="pointer-events-none fixed left-4 top-24 z-[140] grid w-[min(26rem,calc(100vw-2rem))] gap-3 lg:left-8 xl:left-[max(2rem,calc((100vw-80rem)/2+2rem))]">
      <TransitionGroup name="toast">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="pointer-events-auto relative overflow-hidden rounded-[1.55rem] border px-4 py-4 backdrop-blur-xl"
          :class="getToastClass(notification.variant)"
        >
          <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(120deg,rgba(255,255,255,0.24)_0%,transparent_28%,transparent_72%,rgba(255,255,255,0.14)_100%)]"></div>
          <div class="pointer-events-none absolute -left-12 top-0 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
          <div class="pointer-events-none absolute bottom-[-2rem] right-[-1rem] h-20 w-24 rounded-full bg-cyan-300/10 blur-2xl"></div>
          <div class="flex items-start justify-between gap-4">
            <div class="relative">
              <p class="text-[0.62rem] font-semibold uppercase tracking-[0.26em] text-white/70">
                Notification
              </p>
              <p class="mt-2 text-sm font-semibold leading-6">
                {{ notification.message }}
              </p>
            </div>
            <button
              type="button"
              class="relative shrink-0 rounded-full border border-white/15 bg-black/10 px-2.5 py-1 text-[0.65rem] uppercase tracking-[0.18em] text-white/80 transition hover:border-white/35 hover:bg-white/10 hover:text-white"
              @click="removeNotification(notification.id)"
            >
              Fermer
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 0.28s ease,
    transform 0.28s ease,
    filter 0.28s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-12px) scale(0.97);
  filter: blur(6px);
}

.toast-move {
  transition: transform 0.28s ease;
}
</style>
