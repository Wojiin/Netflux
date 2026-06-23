# Configuration Netflux

## Frontend: outillage et entrée applicative

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `frontend/package.json` | Manifeste npm du frontend. | Déclare les dépendances Vue, Pinia, Axios, Tailwind et les scripts de build / dev utilisés par l'application. |
| `frontend/vite.config.js` | Configuration Vite. | Définit la façon dont le frontend est servi en développement et compilé en production. |
| `frontend/jsconfig.json` | Configuration JS / VS Code. | Facilite la résolution de chemins et l'aide à l'édition dans le projet frontend. |
| `frontend/index.html` | Page HTML racine du frontend. | Sert de support au montage de l'application Vue et au chargement du bundle généré. |
| `frontend/postcss.config.js` | Configuration PostCSS. | Branche Tailwind et les transformations CSS utilisées pendant le build. |
| `frontend/tailwind.config.js` | Configuration Tailwind. | Définit le thème, les chemins scannés et les variantes de style utilisées dans Netflux. |
| `frontend/Dockerfile` | Image Docker du frontend. | Décrit comment construire et lancer le frontend dans un environnement conteneurisé. |
| `frontend/README.md` | Note locale du frontend. | Sert de documentation rapide sur le projet frontend, ses commandes ou conventions. |

## Backend: bootstrap et bundles

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/config/bundles.php` | Déclaration des bundles Symfony. | Active API Platform, Doctrine, Lexik JWT, Gesdinet refresh token, Nelmio CORS et les autres bundles utilisés par le backend. |
| `backend/config/services.yaml` | Configuration des services. | Active l'autowiring / autoconfiguration et rend disponibles les classes du projet en tant que services Symfony. |
| `backend/config/routes.yaml` | Agrégateur de routes. | Charge les fichiers de routes du backend et les intègre dans l'application Symfony. |
| `backend/config/preload.php` | Optimisation de preload. | Peut être utilisé pour précharger certaines classes ou configurations dans certains environnements PHP. |
| `backend/config/reference.php` | Fichier de référence généré. | Sert de référence de configuration Symfony ; utile pour le développement, mais ce n'est pas une pièce métier de Netflux. |

## Backend: routes

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/config/routes/api_platform.yaml` | Routes d'API Platform. | Expose automatiquement les ressources API déclarées dans les entités et classes associées. |
| `backend/config/routes/framework.yaml` | Routes techniques du framework. | Charge les routes techniques Symfony nécessaires à l'application. |
| `backend/config/routes/security.yaml` | Routes d'authentification. | Déclare les points d'entrée `/api/login`, `/api/token/refresh` et `/api/token/invalidate`. |

## Backend: packages Symfony et bundles

| Fichier | À quoi il sert | Comment il fonctionne dans l'app |
|---|---|---|
| `backend/config/packages/framework.yaml` | Configuration cœur Symfony. | Règle le fonctionnement général du framework backend. |
| `backend/config/packages/routing.yaml` | Configuration de routing. | Paramètre le comportement du composant de routage Symfony. |
| `backend/config/packages/security.yaml` | Configuration de sécurité principale. | Définit les providers utilisateur, la hiérarchie des rôles, les firewalls `login`, `token_refresh`, `api`, et les `access_control` qui protègent les endpoints. |
| `backend/config/packages/lexik_jwt_authentication.yaml` | Configuration JWT d'accès. | Règle les clés de signature, la passphrase, la durée de vie de 1 heure (`token_ttl: 3600`) et l'interdiction des tokens sans expiration. |
| `backend/config/packages/gesdinet_jwt_refresh_token.yaml` | Configuration du refresh token. | Définit la classe `RefreshToken`, le TTL de 30 jours, le cookie `http_only`, son `same_site` et le fait que le token n'est pas renvoyé dans le body. |
| `backend/config/packages/nelmio_cors.yaml` | Configuration CORS. | Autorise le frontend à appeler l'API avec `allow_credentials: true`, ce qui est indispensable pour transporter le cookie de refresh. |
| `backend/config/packages/api_platform.yaml` | Configuration API Platform. | Règle les comportements transverses de l'API REST exposée par le backend. |
| `backend/config/packages/doctrine.yaml` | Configuration Doctrine ORM / DBAL. | Règle la connexion base de données, le mapping des entités et le fonctionnement ORM. |
| `backend/config/packages/doctrine_migrations.yaml` | Configuration des migrations. | Paramètre l'outillage de migration du schéma SQL. |
| `backend/config/packages/validator.yaml` | Configuration de validation. | Branche le composant de validation utilisé par les `Assert` des entités et DTO. |
| `backend/config/packages/property_info.yaml` | Configuration PropertyInfo. | Aide Symfony et API Platform à deviner et manipuler les types de propriétés. |
| `backend/config/packages/cache.yaml` | Configuration de cache. | Règle les caches Symfony utilisés par le backend. |
| `backend/config/packages/twig.yaml` | Configuration Twig. | Fournit le moteur Twig, utile surtout pour des pages techniques ou de debug côté backend. |

## Ce que ces fichiers changent concrètement dans l'app

- `security.yaml` + `lexik_jwt_authentication.yaml` + `gesdinet_jwt_refresh_token.yaml` pilotent la chaîne login / JWT / refresh / logout.
- `nelmio_cors.yaml` est indispensable pour que le frontend Vue puisse envoyer et recevoir le cookie `refresh_token`.
- `api_platform.yaml`, `doctrine.yaml` et les routes API Platform rendent utilisables les entités exposées comme vraies ressources REST.
- `vite.config.js`, `tailwind.config.js` et `package.json` pilotent toute l'exécution et la compilation du frontend.
