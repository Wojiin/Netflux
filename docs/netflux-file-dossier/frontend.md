# Frontend Netflux

## Application root

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/main.js` | Point d'entrée de l'application Vue. | Crée l'application, branche Pinia, le router et le CSS global, puis monte l'app sur la page HTML. |
| `frontend/src/App.vue` | Shell global de l'interface. | Affiche le layout principal, la navigation, les transitions de page, le menu mobile, le footer et déclenche `userStore.ensureInitialized()` au montage pour tenter une restauration de session. |
| `frontend/src/index.css` | Styles globaux du projet. | Charge Tailwind et les styles transverses utilisés par toutes les vues et composants. |

## API, routeur, stores et utilitaires

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/api/axios.js` | Client HTTP central de l'application. | Crée l'instance Axios avec `withCredentials`, ajoute automatiquement le Bearer token, détecte les `401`, tente un refresh via `/token/refresh`, puis rejoue la requête initiale si la session est restaurable. |
| `frontend/src/router/index.js` | Routeur Vue principal. | Déclare les routes publiques, utilisateur et admin, puis applique un `beforeEach` qui attend `ensureInitialized()` et bloque ou redirige selon `requiresAuth`, `requiresAdmin` et `guestOnly`. |
| `frontend/src/stores/pinia.js` | Instance Pinia partagée. | Exporte l'instance du store pour qu'elle soit réutilisable dans l'app et dans le routeur. |
| `frontend/src/stores/user.js` | Store utilisateur et session. | Centralise le JWT en mémoire, l'identité utilisateur, le login, le refresh, le logout, le profil courant, les favoris, l'historique, les notes et les appels aux endpoints `/users/{id}/...`. |
| `frontend/src/stores/movies.js` | Store du catalogue / film courant. | Garde en mémoire la liste des films et le film détaillé en cours de consultation afin de partager cet état entre plusieurs vues et interactions. |
| `frontend/src/constants/contentTypes.js` | Constantes de type de contenu. | Fournit les labels et valeurs réutilisés pour les films, séries, documentaires ou théâtre dans les vues et formulaires. |
| `frontend/src/utils/authRedirect.js` | Helper de redirection post-authentification. | Choisit la route par défaut selon l'utilisateur courant, notamment pour différencier admin et utilisateur classique. |
| `frontend/src/utils/apiResource.js` | Helper d'accès aux ressources API. | Normalise ou simplifie certains traitements utilitaires liés aux ressources API Platform côté frontend. |
| `frontend/src/utils/userValidation.js` | Validation côté client des formulaires utilisateur. | Regroupe les règles de validation liées aux comptes et profils avant envoi au backend. |

## Composables métier et formulaire

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/composables/useLoginForm.js` | Logique du formulaire de connexion. | Gère l'état du formulaire, l'appel au `userStore.login()` et les messages affichés à l'utilisateur. |
| `frontend/src/composables/useRegisterForm.js` | Logique du formulaire d'inscription. | Centralise les champs, la validation, l'appel de `userStore.register()` et la gestion des erreurs de création de compte. |
| `frontend/src/composables/useProfileForm.js` | Logique du profil utilisateur courant. | Pilote la mise à jour du profil, s'appuie sur `/me` ou `/users/{id}` selon la logique exposée par le backend, et synchronise l'affichage local. |
| `frontend/src/composables/useAdminProfileForm.js` | Variante admin autour du profil/utilisateur. | Mutualise une partie de la logique de formulaire lorsqu'un administrateur édite un compte. |
| `frontend/src/composables/useAdminUserForm.js` | Formulaire d'administration des utilisateurs. | Gère la création et la modification d'utilisateurs côté back-office, avec rôles et données de compte. |
| `frontend/src/composables/useAdminPersonForm.js` | Formulaire d'administration des personnes. | Pilote les données de `Person`, base commune aux profils acteur et réalisateur. |
| `frontend/src/composables/useAdminMovieForm.js` | Formulaire d'administration des films. | Gère les champs d'un film, les listes de genres / réalisateurs et l'envoi des données au backend. |
| `frontend/src/composables/useAdminMovieCasting.js` | Gestion du casting d'un film en back-office. | Relie le formulaire de casting aux rôles, acteurs et participations `Play` exposés par le backend. |
| `frontend/src/composables/useAdminGenreForm.js` | Formulaire d'administration des genres. | Gère l'ajout et la modification des genres du catalogue. |
| `frontend/src/composables/useAdminEntityList.js` | Liste générique pour le back-office. | Mutualise la logique de chargement, recherche, suppression et état des listes d'administration. |
| `frontend/src/composables/useMovies.js` | Chargement commun des films. | Sert de couche réutilisable pour consulter le catalogue et brancher les vues qui ont besoin de listes de films. |
| `frontend/src/composables/useMovieList.js` | Logique de la vue liste de films. | Gère la recherche, les filtres, la pagination ou le rafraîchissement des films affichés dans `MovieListView.vue`. |
| `frontend/src/composables/useMovieDetail.js` | Logique de la fiche film. | Charge un film complet, relie les interactions de détail et prépare les données d'affichage pour `MovieDetailView.vue`. |
| `frontend/src/composables/usePersonDetail.js` | Logique des fiches acteur / réalisateur. | Rassemble le chargement et la présentation de détail pour les vues de personne liées au catalogue. |
| `frontend/src/composables/useNotifications.js` | Notifications applicatives. | Centralise la création de messages de succès, d'information ou d'erreur qui sont affichés par `ToastStack.vue`. |
| `frontend/src/composables/useYouTubePlayer.js` | Lecture vidéo / intégration YouTube. | Gère le cycle de vie du player, sa création et ses événements pour la lecture des bandes-annonces dans la fiche film. |

## Composants métier

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/components/MovieItem.vue` | Carte ou bloc d'affichage d'un film. | Affiche les informations principales d'un contenu et sert de brique réutilisable dans les listes, favoris, historique et parfois détail. |
| `frontend/src/components/AddFavorite.vue` | Bouton d'ajout / retrait des favoris. | S'appuie sur le `userStore`, affiche l'état courant, redirige vers le login si besoin et appelle `toggleFavorite(movie)` pour piloter l'endpoint backend. |
| `frontend/src/components/RateMovie.vue` | Interface de notation d'un film. | Permet à un utilisateur connecté d'attribuer ou mettre à jour une note, puis reflète la réponse dans le store et les écrans. |

## Composants UI

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/components/ui/BaseButton.vue` | Bouton réutilisable. | Standardise variantes, tailles, état désactivé et rendu visuel des actions dans tout le frontend. |
| `frontend/src/components/ui/BaseCard.vue` | Carte réutilisable. | Donne un cadre visuel cohérent aux blocs de contenu comme les fiches ou les listes de films. |
| `frontend/src/components/ui/FormField.vue` | Champ de formulaire standardisé. | Uniformise labels, messages d'erreur et structure visuelle des formulaires. |
| `frontend/src/components/ui/StatusMessage.vue` | Bloc de message d'état. | Affiche des retours utilisateurs du type erreur, chargement ou confirmation. |
| `frontend/src/components/ui/PageHeader.vue` | En-tête de vue. | Affiche le titre, la description et l'identité visuelle de la page courante. |
| `frontend/src/components/ui/ToastStack.vue` | Pile de notifications. | Rend visuellement les messages émis par `useNotifications()`. |
| `frontend/src/components/ui/GlobalScrollTheme.vue` | Habillage du scroll global. | Applique la direction visuelle du thème Netflux aux zones de scroll. |
| `frontend/src/components/ui/NeonFrame.vue` | Cadre décoratif néon. | Ajoute un style visuel cohérent aux zones à mettre en valeur. |
| `frontend/src/components/ui/NeonScrollPanel.vue` | Panneau scrollable stylisé. | Fournit un conteneur visuel cohérent pour des blocs de contenu avec scroll. |
| `frontend/src/components/ui/AuthShell.vue` | Coquille des écrans d'authentification. | Encadre les pages de login et d'inscription avec un layout dédié. |
| `frontend/src/components/ui/AdminEntityLayout.vue` | Layout du back-office. | Unifie la structure visuelle des écrans d'administration : en-tête, actions, liste, formulaire, etc. |

## Vues publiques et utilisateur

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/views/HomeView.vue` | Page d'accueil. | Introduit le projet, met en avant le catalogue et sert de porte d'entrée visuelle vers l'application. |
| `frontend/src/views/LoginView.vue` | Page de connexion. | Rend le formulaire de login, s'appuie sur `useLoginForm.js` et initialise la session via le store utilisateur. |
| `frontend/src/views/RegisterView.vue` | Page d'inscription. | Affiche le formulaire d'inscription, valide les champs puis appelle l'endpoint de création de compte. |
| `frontend/src/views/MovieListView.vue` | Vue liste du catalogue. | Affiche les films, la recherche, les filtres et la navigation dans le catalogue. |
| `frontend/src/views/MovieDetailView.vue` | Fiche détaillée d'un contenu. | Affiche les données complètes d'un film, ses genres, son casting, sa bande-annonce et les interactions utilisateur. |
| `frontend/src/views/ActorDetailView.vue` | Fiche détaillée d'un acteur. | Consomme les données exposées par l'API pour afficher le profil et la filmographie d'un acteur. |
| `frontend/src/views/DirectorDetailView.vue` | Fiche détaillée d'un réalisateur. | Affiche les informations d'un réalisateur et les films qui lui sont associés. |
| `frontend/src/views/FavoritesView.vue` | Liste des favoris du compte. | Charge les films favoris depuis le `userStore` et les affiche à partir des données renvoyées par `/users/{id}/favorites`. |
| `frontend/src/views/HistoryView.vue` | Historique de visionnage. | Affiche les contenus marqués comme vus via le store utilisateur et le backend. |
| `frontend/src/views/ProfileView.vue` | Profil de l'utilisateur courant. | Permet de consulter et modifier les informations du compte selon les routes exposées par l'API. |

## Vues d'administration

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/src/views/AdminView.vue` | Tableau de bord d'administration. | Sert de page d'entrée vers les différentes sections du back-office. |
| `frontend/src/views/AdminUsersView.vue` | Liste admin des utilisateurs. | Charge les comptes et les actions de gestion disponibles pour l'administration. |
| `frontend/src/views/AdminUserFormView.vue` | Formulaire admin d'utilisateur. | Crée ou modifie un utilisateur via les endpoints protégés du backend. |
| `frontend/src/views/AdminPeopleView.vue` | Liste admin des personnes. | Gère les `Person` du catalogue qui servent de base aux profils acteur et réalisateur. |
| `frontend/src/views/AdminPersonFormView.vue` | Formulaire admin de personne. | Crée ou modifie une `Person` indépendamment de son profil acteur / réalisateur. |
| `frontend/src/views/AdminMoviesView.vue` | Liste admin des films. | Affiche le catalogue en mode gestion avec accès aux éditions, suppressions et pages liées. |
| `frontend/src/views/AdminMovieFormView.vue` | Formulaire admin de film. | Crée ou modifie un film, avec ses champs, ses relations et ses données de présentation. |
| `frontend/src/views/AdminMovieCastingView.vue` | Gestion admin du casting d'un film. | Permet d'ajouter, modifier ou supprimer les participations `Play` d'un film donné. |
| `frontend/src/views/AdminGenresView.vue` | Liste admin des genres. | Gère les catégories de contenu exposées dans le catalogue. |
| `frontend/src/views/AdminGenreFormView.vue` | Formulaire admin de genre. | Crée ou modifie un genre côté back-office. |
| `frontend/src/views/AdminDirectorsView.vue` | Liste admin des réalisateurs. | Affiche et gère les profils `Director` du catalogue. |
| `frontend/src/views/AdminDirectorFormView.vue` | Formulaire admin de réalisateur. | Crée ou modifie un profil de réalisateur à partir d'une personne. |
| `frontend/src/views/AdminActorsView.vue` | Liste admin des acteurs. | Affiche et gère les profils `Actor` du catalogue. |
| `frontend/src/views/AdminActorFormView.vue` | Formulaire admin d'acteur. | Crée ou modifie un profil d'acteur à partir d'une personne. |
