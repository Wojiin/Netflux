import { createRouter, createWebHistory } from "vue-router";
import { pinia } from "../stores/pinia";
import { getDefaultRouteForUser } from "../utils/authRedirect";

// Les vues sont chargées dynamiquement pour garder le bundle initial plus léger.
const HomeView = () => import("../views/HomeView.vue");
const LoginView = () => import("../views/LoginView.vue");
const RegisterView = () => import("../views/RegisterView.vue");
const AdminView = () => import("../views/AdminView.vue");
const AdminMoviesView = () => import("../views/AdminMoviesView.vue");
const AdminMovieFormView = () => import("../views/AdminMovieFormView.vue");
const AdminMovieCastingView = () => import("../views/AdminMovieCastingView.vue");
const AdminUsersView = () => import("../views/AdminUsersView.vue");
const AdminUserFormView = () => import("../views/AdminUserFormView.vue");
const AdminPeopleView = () => import("../views/AdminPeopleView.vue");
const AdminPersonFormView = () => import("../views/AdminPersonFormView.vue");
const AdminDirectorsView = () => import("../views/AdminDirectorsView.vue");
const AdminDirectorFormView = () => import("../views/AdminDirectorFormView.vue");
const AdminActorsView = () => import("../views/AdminActorsView.vue");
const AdminActorFormView = () => import("../views/AdminActorFormView.vue");
const AdminGenresView = () => import("../views/AdminGenresView.vue");
const AdminGenreFormView = () => import("../views/AdminGenreFormView.vue");
const FavoritesView = () => import("../views/FavoritesView.vue");
const HistoryView = () => import("../views/HistoryView.vue");
const MovieListView = () => import("../views/MovieListView.vue");
const MovieDetailView = () => import("../views/MovieDetailView.vue");
const ActorDetailView = () => import("../views/ActorDetailView.vue");
const DirectorDetailView = () => import("../views/DirectorDetailView.vue");
const ProfileView = () => import("../views/ProfileView.vue");

const routes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
  },
  {
    path: "/login",
    name: "login",
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: "/register",
    name: "register",
    component: RegisterView,
    meta: { guestOnly: true },
  },
  {
    path: "/movies",
    name: "movies",
    component: MovieListView,
    children: [
      {
        path: ":id",
        name: "movie-detail",
        component: MovieDetailView,
        props: true,
      },
    ],
  },
  {
    path: "/favorites",
    name: "favorites",
    component: FavoritesView,
    meta: { requiresAuth: true },
  },
  {
    path: "/history",
    name: "history",
    component: HistoryView,
    meta: { requiresAuth: true },
  },
  {
    path: "/profile",
    name: "profile",
    component: ProfileView,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin",
    name: "admin",
    component: AdminView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/movies",
    name: "admin-movies",
    component: AdminMoviesView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/movies/new",
    name: "admin-movie-create",
    component: AdminMovieFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/movies/:id/edit",
    name: "admin-movie-edit",
    component: AdminMovieFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/movies/:id/casting",
    name: "admin-movie-casting",
    component: AdminMovieCastingView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/users",
    name: "admin-users",
    component: AdminUsersView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/users/new",
    name: "admin-user-create",
    component: AdminUserFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/users/:id/edit",
    name: "admin-user-edit",
    component: AdminUserFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/people",
    name: "admin-people",
    component: AdminPeopleView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/people/new",
    name: "admin-person-create",
    component: AdminPersonFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/people/:id/edit",
    name: "admin-person-edit",
    component: AdminPersonFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/directors",
    name: "admin-directors",
    component: AdminDirectorsView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/directors/new",
    name: "admin-director-create",
    component: AdminDirectorFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/directors/:id/edit",
    name: "admin-director-edit",
    component: AdminDirectorFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/actors",
    name: "admin-actors",
    component: AdminActorsView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/actors/new",
    name: "admin-actor-create",
    component: AdminActorFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/actors/:id/edit",
    name: "admin-actor-edit",
    component: AdminActorFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/genres",
    name: "admin-genres",
    component: AdminGenresView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/genres/new",
    name: "admin-genre-create",
    component: AdminGenreFormView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/genres/:id/edit",
    name: "admin-genre-edit",
    component: AdminGenreFormView,
    props: true,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/actors/:id",
    name: "actor-detail",
    component: ActorDetailView,
    props: true,
  },
  {
    path: "/directors/:id",
    name: "director-detail",
    component: DirectorDetailView,
    props: true,
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Guard global : il restaure d'abord la session si besoin, puis applique les
// règles requiresAuth / requiresAdmin / guestOnly définies dans les routes.
router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth && !to.meta.requiresAdmin && !to.meta.guestOnly) {
    return true;
  }

  const { useUserStore } = await import("../stores/user");
  const userStore = useUserStore(pinia);

  await userStore.ensureInitialized();

  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    return {
      name: "login",
      query: { redirect: to.fullPath },
    };
  }

  if (to.meta.requiresAdmin && !userStore.isAdmin) {
    return { name: "movies" };
  }

  if (to.meta.guestOnly && userStore.isAuthenticated) {
    return getDefaultRouteForUser(userStore.user);
  }

  return true;
});
