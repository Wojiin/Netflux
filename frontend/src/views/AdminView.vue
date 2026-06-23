<script setup>
import BaseCard from "../components/ui/BaseCard.vue";
import PageHeader from "../components/ui/PageHeader.vue";

const sections = [
  {
    eyebrow: "Bloc A",
    eyebrowClass: "text-cyan-300",
    title: "Films",
    description: "Consulter, créer, modifier et supprimer les contenus du catalogue.",
    to: "/admin/movies",
    linkClass: "text-cyan-200 hover:text-cyan-100",
    linkLabel: "Gérer les films",
  },
  {
    eyebrow: "Bloc B",
    eyebrowClass: "text-pink-300",
    title: "Utilisateurs",
    description: "Administrer les comptes, les rôles et les informations de connexion.",
    to: "/admin/users",
    linkClass: "text-pink-200 hover:text-pink-100",
    linkLabel: "Gérer les utilisateurs",
  },
  {
    eyebrow: "Bloc C",
    eyebrowClass: "text-amber-300",
    title: "Personnes",
    description: "Piloter les fiches identité qui servent de base aux acteurs et réalisateurs.",
    to: "/admin/people",
    linkClass: "text-amber-200 hover:text-amber-100",
    linkLabel: "Gérer les personnes",
  },
  {
    eyebrow: "Bloc D",
    eyebrowClass: "text-cyan-300",
    title: "Réalisateurs",
    description: "Associer des profils de réalisation aux personnes déjà présentes dans le catalogue.",
    to: "/admin/directors",
    linkClass: "text-cyan-200 hover:text-cyan-100",
    linkLabel: "Gérer les réalisateurs",
  },
  {
    eyebrow: "Bloc E",
    eyebrowClass: "text-pink-300",
    title: "Acteurs",
    description: "Créer et modifier les profils acteurs reliés aux fiches personnes.",
    to: "/admin/actors",
    linkClass: "text-pink-200 hover:text-pink-100",
    linkLabel: "Gérer les acteurs",
  },
  {
    eyebrow: "Bloc F",
    eyebrowClass: "text-amber-300",
    title: "Genres",
    description: "Maintenir le référentiel de genres utilisés dans les formulaires de films.",
    to: "/admin/genres",
    linkClass: "text-amber-200 hover:text-amber-100",
    linkLabel: "Gérer les genres",
  },
];
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      eyebrow="Control Room"
      eyebrow-class="text-amber-300"
      title="Back office"
      description="Gère le catalogue et les comptes utilisateurs."
      view-name="adminview"
    />

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <BaseCard v-for="section in sections" :key="section.to" class="gap-5">
        <p class="font-display text-[0.55rem] uppercase tracking-[0.24em]" :class="section.eyebrowClass">{{ section.eyebrow }}</p>
        <h2 class="text-2xl font-semibold text-white">{{ section.title }}</h2>
        <p class="text-slate-300">{{ section.description }}</p>
        <router-link :to="section.to" class="text-sm font-semibold uppercase tracking-[0.18em] transition" :class="section.linkClass">
          {{ section.linkLabel }}
        </router-link>
      </BaseCard>
    </div>
  </section>
</template>
