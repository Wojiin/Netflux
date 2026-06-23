<script setup>
import { storeToRefs } from "pinia";
import BaseButton from "../components/ui/BaseButton.vue";
import NeonFrame from "../components/ui/NeonFrame.vue";
import { useUserStore } from "../stores/user";

const userStore = useUserStore();
const { isAuthenticated } = storeToRefs(userStore);
</script>

<template>
  <section class="grid w-full flex-1 place-items-center overflow-hidden">
    <NeonFrame
      class="w-full px-6 py-12 sm:px-10 lg:px-14 lg:py-16"
      tone="cyan"
      :padded="false"
    >
      <div class="pointer-events-none absolute inset-0"></div>
      <div
        class="pointer-events-none absolute inset-0 opacity-10 bg-[radial-gradient(circle,#fff_1px,transparent_1px)] [background-size:20px_20px]"
      ></div>

      <div class="relative mx-auto max-w-4xl">
        <div class="relative h-40 overflow-hidden sm:h-48 lg:h-52">
          <img
            src="/img/logo.png"
            alt="Logo"
            class="relative z-10 h-56 w-auto sm:h-64 lg:h-72"
          />
        </div>
        <h1
          class="mt-8 max-w-4xl text-4xl font-semibold leading-tight text-white sm:text-5xl lg:text-6xl"
        >
          Catalogue films et séries.
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
          Parcours le catalogue, consulte les fiches détaillées et connecte-toi
          pour enregistrer tes favoris.
        </p>

        <div class="mt-10 flex flex-wrap gap-4">
          <router-link to="/movies">
            <BaseButton>Voir le catalogue</BaseButton>
          </router-link>
          <router-link v-if="!isAuthenticated" to="/login">
            <BaseButton variant="secondary">Connexion</BaseButton>
          </router-link>
          <router-link v-if="!isAuthenticated" to="/register">
            <BaseButton variant="secondary">Inscription</BaseButton>
          </router-link>
        </div>
      </div>
    </NeonFrame>
  </section>
</template>
