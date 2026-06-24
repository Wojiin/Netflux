# Backend Netflux

## Noyau et bootstrap

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Kernel.php` | Noyau Symfony de l'application. | Démarre l'application backend, charge la configuration et branche les bundles nécessaires. |

## Contrôleur

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Controller/ApiLogoutController.php` | Logout applicatif du refresh token. | Répond à `/api/token/invalidate`, supprime les lignes `refresh_tokens` associées à l'utilisateur courant et efface le cookie `refresh_token`. |

## Event subscribers

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/EventSubscriber/JWTCreatedSubscriber.php` | Enrichissement JWT / login. | Ajoute l'id utilisateur au payload JWT et injecte aussi un bloc `user` dans la réponse d'authentification pour simplifier l'initialisation du store frontend. |
| `backend/src/EventSubscriber/RefreshTokenLoginSubscriber.php` | Nettoyage du cookie au login. | Intervient juste après une authentification réussie sur `/api/login` pour repartir d'un cookie `refresh_token` propre avant émission du nouveau. |

## Fixtures

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/DataFixtures/AppFixtures.php` | Jeu principal de données de démo. | Charge des données initiales du catalogue pour faciliter le développement et les démonstrations. |
| `backend/src/DataFixtures/UserFixtures.php` | Utilisateurs de test / d'administration. | Crée des comptes connus, avec rôles et mots de passe hashés, pour travailler ou tester rapidement. |

## Enum

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Enum/ContentType.php` | Énumération des types de contenu. | Encadre les valeurs possibles du champ `type` d'un film et aligne backend et frontend sur les mêmes options. |

## Filter

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Filter/MovieSearchFilter.php` | Filtre de recherche personnalisé pour les films. | Complète les filtres API Platform pour affiner la recherche dans le catalogue au-delà des cas couverts par les filtres standards. |

## DTO de sortie et d'entrée

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Dto/FilmListOutput.php` | Projection légère d'un film en liste. | Sert aux collections `/movies` ou aux listes de films pour éviter d'exposer tout le détail d'une fiche film. |
| `backend/src/Dto/FilmOutput.php` | Projection détaillée d'un film. | Structure les données renvoyées par la fiche complète d'un contenu. |
| `backend/src/Dto/ActorOutput.php` | Projection détaillée d'un acteur. | Expose le nom, les infos de personne et la filmographie d'un acteur. |
| `backend/src/Dto/DirectorOutput.php` | Projection détaillée d'un réalisateur. | Expose les données d'un réalisateur et les films qui lui sont rattachés. |
| `backend/src/Dto/GenreOutput.php` | Projection d'un genre. | Sert à renvoyer un genre avec les informations utiles au frontend. |
| `backend/src/Dto/PersonOutput.php` | Projection d'une personne. | Expose les données communes à une personne, notamment pour l'administration. |
| `backend/src/Dto/RoleOutput.php` | Projection d'un rôle. | Expose les noms de personnage reliés au casting. |
| `backend/src/Dto/PosterOutput.php` | Projection d'une affiche. | Retourne les URLs d'affiches liées aux films. |
| `backend/src/Dto/PlayOutput.php` | Projection d'une participation de casting. | Structure les données d'un lien film / acteur / rôle pour l'admin ou l'affichage détaillé. |
| `backend/src/Dto/UserOutput.php` | Projection de l'utilisateur. | Retourne les données lues côté frontend sans exposer le mot de passe. |
| `backend/src/Dto/UserFilmRatingOutput.php` | Projection des notes utilisateur. | Structure la liste des notes données par un utilisateur aux films. |
| `backend/src/Dto/FilmRatingOutput.php` | Réponse de notation. | Retourne la note posée ainsi que les infos de synthèse utiles pour mettre à jour le frontend. |
| `backend/src/Dto/FavoriteToggleOutput.php` | Réponse de favoris. | Renvoie l'état `added` ou `removed` ainsi que des données synchronisables après un toggle de favori. |
| `backend/src/Dto/WatchedFilmOutput.php` | Réponse de marquage "vu". | Retourne l'état produit lorsqu'un film est ajouté à l'historique. |
| `backend/src/Dto/PersonProfileInput.php` | Entrée de création / édition de profil acteur ou réalisateur. | Sert de payload d'entrée pour les processors qui composent un profil de personne métier. |

## Entités métier

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Entity/User.php` | Entité des comptes. | Porte l'email, les rôles, le mot de passe hashé, les favoris, l'historique, les notes et expose de nombreuses opérations API, dont `/me`, `/register`, `/users/{id}/favorites`, `/users/{id}/watched` et `/users/{id}/ratings`. |
| `backend/src/Entity/Film.php` | Entité centrale du catalogue. | Porte le titre, le type, les dates, les images, la bande-annonce, le genre, les réalisateurs, le casting, les affiches, les notes et les relations de favoris / historique côté inverse. |
| `backend/src/Entity/Genre.php` | Entité de catégorie. | Regroupe les films sous un genre principal et supporte la recherche / tri côté catalogue. |
| `backend/src/Entity/Person.php` | Entité de base des personnes. | Porte le nom, le genre, la date de naissance et le portrait ; elle sert de socle commun aux profils `Actor` et `Director`. |
| `backend/src/Entity/Actor.php` | Profil d'acteur. | Relie une `Person` à ses participations `Play` et expose la filmographie correspondante. |
| `backend/src/Entity/Director.php` | Profil de réalisateur. | Relie une `Person` à la collection de films qu'elle réalise. |
| `backend/src/Entity/Role.php` | Rôle de personnage. | Stocke le nom du personnage interprété et se combine avec `Play` pour construire le casting. |
| `backend/src/Entity/Play.php` | Participation de casting. | Entité pivot entre `Film`, `Actor` et `Role`, avec contrainte d'unicité pour éviter les doublons de participation. |
| `backend/src/Entity/Poster.php` | Affiche secondaire de film. | Stocke des URLs d'affiches rattachées à un film. |
| `backend/src/Entity/FilmRating.php` | Note d'un utilisateur pour un film. | Entité dédiée à la notation, avec contrainte d'unicité `user_id + film_id` pour garantir une seule note par utilisateur et par film. |
| `backend/src/Entity/RefreshToken.php` | Point d'ancrage Doctrine du refresh token. | Branche localement le bundle Gesdinet sur la table `refresh_tokens` sans réimplémenter toute sa logique interne. |

## Repositories

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/Repository/UserRepository.php` | Dépôt Doctrine des utilisateurs. | Sert aux providers, processors et à la sécurité pour retrouver ou manipuler des comptes. |
| `backend/src/Repository/FilmRepository.php` | Dépôt Doctrine des films. | Sert au catalogue, aux filtres et aux traitements métier sur les contenus. |
| `backend/src/Repository/GenreRepository.php` | Dépôt Doctrine des genres. | Sert aux opérations CRUD et au chargement des catégories de films. |
| `backend/src/Repository/PersonRepository.php` | Dépôt Doctrine des personnes. | Sert à l'administration des personnes et à la composition des profils. |
| `backend/src/Repository/ActorRepository.php` | Dépôt Doctrine des acteurs. | Sert au détail acteur et à la gestion admin des profils acteur. |
| `backend/src/Repository/DirectorRepository.php` | Dépôt Doctrine des réalisateurs. | Sert au détail réalisateur et à la gestion admin des profils réalisateur. |
| `backend/src/Repository/RoleRepository.php` | Dépôt Doctrine des rôles. | Sert aux traitements de casting et à la gestion des personnages. |
| `backend/src/Repository/PlayRepository.php` | Dépôt Doctrine des participations. | Sert à la gestion du casting et aux vérifications de relations film / acteur / rôle. |
| `backend/src/Repository/PosterRepository.php` | Dépôt Doctrine des affiches. | Sert à l'administration et au chargement des posters de films. |
| `backend/src/Repository/FilmRatingRepository.php` | Dépôt Doctrine des notes. | Sert aux traitements de notation et aux calculs de moyenne / compteur. |

## State: providers et processors

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/src/State/CurrentUserProvider.php` | Provider de l'utilisateur courant. | Alimente `/me` en renvoyant l'utilisateur authentifié sans demander son id dans l'URL. |
| `backend/src/State/UserFavoritesProvider.php` | Lecture des favoris. | Répond à `/users/{id}/favorites`, vérifie l'identité ou le rôle admin, puis renvoie les films favoris du bon utilisateur. |
| `backend/src/State/UserWatchedProvider.php` | Lecture de l'historique. | Répond à `/users/{id}/watched` avec les films vus de l'utilisateur autorisé. |
| `backend/src/State/UserRatingsProvider.php` | Lecture des notes utilisateur. | Répond à `/users/{id}/ratings` avec les notations déjà posées. |
| `backend/src/State/FavoriteToggleProcessor.php` | Ajout / retrait de favori. | Traite la route de toggle des favoris, vérifie l'identité du demandeur, ajoute ou retire le film et renvoie le nouvel état. |
| `backend/src/State/WatchedFilmProcessor.php` | Ajout à l'historique. | Marque un film comme vu pour l'utilisateur autorisé et persiste la relation correspondante. |
| `backend/src/State/FilmRatingProcessor.php` | Création / mise à jour d'une note. | Crée ou met à jour `FilmRating`, recalcule la synthèse de notation du film et renvoie les données utiles au frontend. |
| `backend/src/State/UserPasswordHasher.php` | Hash des mots de passe et garde-fous de compte. | Hash le mot de passe sur inscription ou édition, empêche des escalades de rôle abusives, traite le cas spécial de `/me` et met à jour la table `refresh_tokens` si l'email change. |
| `backend/src/State/PersonProfileWriteProcessor.php` | Écriture d'acteur / réalisateur depuis une personne. | Transforme le payload d'entrée en `Person` plus `Actor` ou `Director`, puis persiste le bon profil métier. |
| `backend/src/State/PersonDeleteProcessor.php` | Suppression propre d'une personne. | Centralise la suppression d'une `Person` en tenant compte des profils liés. |
| `backend/src/State/PlayWriteProcessor.php` | Écriture du casting. | Prend les ids film / acteur / rôle envoyés par l'admin et construit la participation `Play` valide. |

## Remarques utiles

- Les favoris et l'historique ne sont pas la même relation : Netflux garde deux ManyToMany distincts dans `User`.
- Le genre d'un film est actuellement un **genre principal unique** (`ManyToOne` vers `Genre`), pas un multi-genre pivot côté backend.
- `RefreshToken` n'est pas relié à `User` par une vraie relation Doctrine ; le lien technique passe par le `username` géré par le bundle Gesdinet.
