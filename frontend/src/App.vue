<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import BaseButton from "./components/ui/BaseButton.vue";
import GlobalScrollTheme from "./components/ui/GlobalScrollTheme.vue";
import ToastStack from "./components/ui/ToastStack.vue";
import { useNotifications } from "./composables/useNotifications";
import { useUserStore } from "./stores/user";

const router = useRouter();
const route = useRoute();
const userStore = useUserStore();
const { notifyInfo } = useNotifications();
const { isAuthenticated, isAdmin, userEmail, initialized } =
  storeToRefs(userStore);

const mobileMenuOpen = ref(false);
const authLabel = computed(() => userEmail.value || "Session active");
const authResolved = computed(() => initialized.value);
const isHomeRoute = computed(() => route.name === "home");
const isAuthRoute = computed(() =>
  route.name === "login" || route.name === "register",
);
const pageTransitionName = computed(() =>
  route.name === "movie-detail" ? "page-fade" : "page-slide",
);

// Les helpers d'UI ci-dessous ne touchent qu'à l'état local du menu mobile.
function closeMobileMenu() {
  mobileMenuOpen.value = false;
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value;
}

async function handleLogout() {
  await userStore.logout();
  closeMobileMenu();
  notifyInfo("Session fermee.");
  router.push("/");
}

watch(
  () => route.fullPath,
  () => {
    closeMobileMenu();
  },
);

// Au démarrage, l'application tente immédiatement de restaurer une session via
// le refresh token si un cookie valide existe encore côté navigateur.
onMounted(() => {
  userStore.ensureInitialized().catch(() => {});
});
</script>

<template>
  <div class="relative flex min-h-screen flex-col overflow-hidden font-sans text-slate-50">
    <GlobalScrollTheme />
    <ToastStack />
    <div
      class="pointer-events-none fixed inset-0 bg-[linear-gradient(120deg,rgba(34,211,238,0.16)_0%,rgba(244,114,182,0.12)_22%,rgba(250,204,21,0.08)_42%,rgba(34,211,238,0.12)_60%,transparent_78%)] bg-[length:300%_300%] opacity-70"
    ></div>
    <div
      class="pointer-events-none fixed inset-0 opacity-15 bg-[repeating-linear-gradient(0deg,transparent,transparent_3px,rgba(103,232,249,0.14)_3px,rgba(103,232,249,0.14)_4px)]"
    ></div>
    <div
      class="pointer-events-none fixed left-[-12rem] top-24 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"
    ></div>
    <div
      class="pointer-events-none fixed bottom-0 right-[-8rem] h-80 w-80 rounded-full bg-pink-500/20 blur-3xl"
    ></div>

    <header
      class="sticky top-0 z-30 border-b border-cyan-300/15 bg-slate-950/75 backdrop-blur-xl"
    >
      <div
        class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8 xl:flex-row xl:items-center xl:justify-between"
      >
        <div class="flex items-center justify-between gap-4 xl:flex-none">
          <button
            type="button"
            class="flex items-center transition hover:opacity-90"
            @click="router.push('/')"
          >
            <img
              src="/img/logo.png"
              alt="Logo"
              class="h-20 w-auto drop-shadow-[0_0_28px_rgba(103,232,249,0.38)] sm:h-24 lg:h-28"
            />
          </button>

          <button
            type="button"
            class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-cyan-300/35 bg-slate-900/70 text-cyan-100 shadow-[0_0_0_1px_rgba(103,232,249,0.12),0_0_24px_rgba(34,211,238,0.16)] transition hover:border-cyan-200 hover:text-cyan-50 xl:hidden"
            :aria-expanded="mobileMenuOpen"
            aria-controls="mobile-main-menu"
            aria-label="Ouvrir le menu"
            @click="toggleMobileMenu"
          >
            <svg
              v-if="!mobileMenuOpen"
              class="h-6 w-6"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
            >
              <path d="M4 7h16" />
              <path d="M4 12h16" />
              <path d="M4 17h16" />
            </svg>
            <svg
              v-else
              class="h-6 w-6"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
            >
              <path d="M6 6l12 12" />
              <path d="M18 6l-12 12" />
            </svg>
          </button>
        </div>

        <nav
          class="hidden flex-wrap items-center gap-4 text-base font-semibold text-slate-200 sm:text-lg xl:flex xl:justify-end"
        >
          <router-link
            to="/movies"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Catalogue
          </router-link>
          <router-link
            v-if="authResolved && isAdmin"
            to="/admin"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Admin
          </router-link>
          <router-link
            v-if="authResolved && isAuthenticated"
            to="/favorites"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Favoris
          </router-link>
          <router-link
            v-if="authResolved && isAuthenticated"
            to="/history"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Historique
          </router-link>
          <router-link
            v-if="authResolved && !isAuthenticated"
            to="/login"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Connexion
          </router-link>
          <router-link
            v-if="authResolved && !isAuthenticated"
            to="/register"
            class="rounded-full border border-transparent px-4 py-2.5 tracking-[0.04em] transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
          >
            Inscription
          </router-link>
          <router-link
            v-if="authResolved && isAuthenticated"
            to="/profile"
            class="rounded-full border border-pink-400/25 bg-pink-500/10 px-5 py-2.5 text-pink-100 tracking-[0.04em] transition hover:border-pink-300/40 hover:bg-pink-500/15"
          >
            {{ authLabel }}
          </router-link>
          <BaseButton
            v-if="authResolved && isAuthenticated"
            type="button"
            variant="danger"
            @click="handleLogout"
          >
            Deconnexion
          </BaseButton>
        </nav>

        <Transition name="mobile-menu">
          <nav
            v-if="mobileMenuOpen"
            id="mobile-main-menu"
            class="grid gap-3 rounded-[1.5rem] border border-cyan-300/20 bg-slate-950/92 p-4 text-sm font-semibold tracking-[0.04em] text-slate-100 shadow-[0_0_0_1px_rgba(103,232,249,0.1),0_18px_60px_rgba(5,8,22,0.48)] xl:hidden"
          >
            <router-link
              to="/movies"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Catalogue
            </router-link>
            <router-link
              v-if="authResolved && isAdmin"
              to="/admin"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Admin
            </router-link>
            <router-link
              v-if="authResolved && isAuthenticated"
              to="/favorites"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Favoris
            </router-link>
            <router-link
              v-if="authResolved && isAuthenticated"
              to="/history"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Historique
            </router-link>
            <router-link
              v-if="authResolved && !isAuthenticated"
              to="/login"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Connexion
            </router-link>
            <router-link
              v-if="authResolved && !isAuthenticated"
              to="/register"
              class="rounded-2xl border border-transparent px-4 py-3 transition hover:border-cyan-300/40 hover:bg-slate-900/60 hover:text-cyan-200"
            >
              Inscription
            </router-link>
            <router-link
              v-if="authResolved && isAuthenticated"
              to="/profile"
              class="rounded-2xl border border-pink-400/25 bg-pink-500/10 px-4 py-3 text-pink-100 transition hover:border-pink-300/40 hover:bg-pink-500/15"
            >
              {{ authLabel }}
            </router-link>
            <BaseButton
              v-if="authResolved && isAuthenticated"
              type="button"
              variant="danger"
              block
              @click="handleLogout"
            >
              Deconnexion
            </BaseButton>
          </nav>
        </Transition>
      </div>
      <div
        class="h-px w-full bg-[linear-gradient(90deg,transparent,rgba(103,232,249,0.65),rgba(244,114,182,0.65),transparent)]"
      ></div>
    </header>

    <main
      :class="[
        'relative z-10 mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8',
        isHomeRoute || isAuthRoute ? 'flex' : '',
      ]"
    >
      <router-view v-slot="{ Component }">
        <Transition :name="pageTransitionName" mode="out-in">
          <component
            :is="Component"
            :key="route.name === 'movie-detail' ? 'movies' : route.fullPath"
          />
        </Transition>
      </router-view>
    </main>

    <footer
      class="relative z-10 mt-auto bg-slate-950/55 backdrop-blur-xl"
    >
      <div
        class="h-px w-full bg-[linear-gradient(90deg,transparent,rgba(103,232,249,0.65),rgba(244,114,182,0.65),transparent)]"
      ></div>
      <div
        class="mx-auto flex max-w-7xl flex-row items-center justify-center gap-6 px-4 py-8 text-center sm:px-6 lg:gap-8 lg:px-8"
      >
        <img
          src="/img/logo.png"
          alt="Logo"
          class="h-16 w-auto opacity-95 drop-shadow-[0_0_28px_rgba(103,232,249,0.28)] sm:h-20 lg:h-24"
        />
        <p class="text-center text-sm font-medium tracking-[0.08em] text-slate-300 sm:text-base">
          Copyright © 2026 Wojiin. Tous droits reserves.
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.page-slide-enter-active,
.page-slide-leave-active {
  transition:
    opacity 0.45s ease,
    transform 0.45s ease,
    filter 0.45s ease;
}

.page-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
  filter: blur(8px);
}

.page-slide-leave-to {
  opacity: 0;
  transform: translateY(-16px);
  filter: blur(6px);
}

.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.25s ease;
}

.page-fade-enter-from,
.page-fade-leave-to {
  opacity: 1;
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition:
    opacity 0.22s ease,
    transform 0.22s ease,
    filter 0.22s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
  filter: blur(6px);
}
</style>
