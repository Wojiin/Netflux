# Netflux

Netflux est une application full stack de catalogue video construite avec Symfony, API Platform, Vue 3 et Pinia.
Le projet permet de consulter un catalogue de films, series, documentaires et pieces de theatre, de gerer une authentification JWT avec refresh token, d'ajouter des favoris, de suivre l'historique de visionnage et d'administrer le contenu.

## Fonctionnalites

- Catalogue public avec filtres par titre, genre et type
- Fiche detaillee pour chaque contenu
- Authentification JWT avec refresh token en cookie HTTP-only
- Profil utilisateur via `GET /api/me` et `PATCH /api/me`
- Favoris, historique et notes utilisateur
- Interface d'administration protegee par `ROLE_ADMIN`
- API REST documentee par API Platform

## Stack technique

### Backend

- PHP `>= 8.4`
- Symfony `8.1`
- API Platform `4.3`
- Doctrine ORM `3.6`
- LexikJWTAuthenticationBundle `3.2`
- GesdinetJWTRefreshTokenBundle `2.0`
- NelmioCorsBundle `2.6`

### Frontend

- Vue `3.5.32`
- Vue Router `4.6.4`
- Pinia `3.0.4`
- Axios `1.17.0`
- Vite `8.0.8`
- Tailwind CSS `4.3.0`

## Arborescence

```text
Netflux/
├─ backend/      API Symfony + API Platform
├─ frontend/     application Vue 3 + Vite
├─ docker/       configuration Nginx
└─ docker-compose.dev.yaml
```

## Installation et lancement

## Prerequis

- Docker et Docker Compose
- ou, pour un lancement manuel :
  - PHP 8.4+
  - Composer
  - Node.js 20.19+ ou 22.12+
  - MySQL 8

## Lancement recommande avec Docker

```bash
docker compose -f docker-compose.dev.yaml up -d --build
```

Services exposes :

- Frontend : `http://localhost:5173`
- API backend : `http://localhost:8080/api`
- MySQL : `127.0.0.1:3307`

## Backend en local

Depuis `backend/` :

```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony server:start
```

Variables importantes dans `backend/.env` :

- `DATABASE_URL`
- `JWT_SECRET_KEY`
- `JWT_PUBLIC_KEY`
- `JWT_PASSPHRASE`
- `CORS_ALLOW_ORIGIN`

## Frontend en local

Depuis `frontend/` :

```bash
npm install
npm run dev
```

Variables Docker deja prevues :

- `VITE_API_BASE_URL=/api`
- `VITE_API_PROXY_TARGET=http://nginx_backend`

## Comptes de demonstration

Fixtures utilisateurs :

- Admin : `admin@netflux.local` / `Admin1234!`
- User : `user@netflux.local` / `User1234!`

## Endpoints API principaux

Base URL : `http://localhost:8080/api`

### Authentification

#### `POST /login`

Connexion utilisateur.

Exemple payload :

```json
{
  "email": "user@netflux.local",
  "password": "User1234!"
}
```

Exemple reponse :

```json
{
  "token": "jwt_access_token",
  "user": {
    "id": 2,
    "email": "user@netflux.local",
    "roles": ["ROLE_USER"]
  }
}
```

#### `POST /token/refresh`

Renouvelle le JWT a partir du cookie `refresh_token`.

#### `POST /token/invalidate`

Supprime les refresh tokens du compte connecte et efface le cookie de refresh.

### Utilisateur courant

#### `GET /me`

Retourne le profil du compte authentifie.

#### `PATCH /me`

Met a jour l'email et/ou le mot de passe du compte authentifie.

Exemple payload :

```json
{
  "email": "newuser@example.com",
  "plainPassword": "NouveauMotDePasse123!"
}
```

### Catalogue

#### `GET /movies`

Catalogue public pagine.

Parametres utiles :

- `page`
- `itemsPerPage`
- `title`
- `genre.id`
- `type`

#### `GET /movies/{id}`

Detail complet d'un contenu.

### Favoris

#### `GET /users/{id}/favorites`

Retourne les favoris de l'utilisateur connecte.

#### `POST /users/{id}/favorites/{movieId}`

Ajoute ou retire un film des favoris.

Exemple reponse :

```json
{
  "status": "added",
  "favoriteTitles": ["Inception", "The Matrix"]
}
```

### Historique

#### `GET /users/{id}/watched`

Retourne l'historique du compte.

#### `POST /users/{id}/watched/{movieId}`

Marque un contenu comme vu.

### Notes

#### `GET /users/{id}/ratings`

Liste des notes de l'utilisateur.

#### `POST /users/{id}/ratings/{movieId}/{rate}`

Cree ou met a jour une note entre `0` et `5`.

### Administration

Routes reservees a `ROLE_ADMIN` :

- `POST /movies`
- `PUT /movies/{id}`
- `PATCH /movies/{id}`
- `DELETE /movies/{id}`
- CRUD sur `genres`, `actors`, `directors`, `users`
- consultation admin de `roles`, `people`, `plays`, `posters`

Exemple creation film :

```json
{
  "title": "Dune Part Two",
  "type": "film",
  "releasedAt": "2024-02-28",
  "imgLink": "https://example.com/poster.jpg",
  "videoLink": "https://example.com/trailer",
  "duration": 166,
  "synopsis": "Suite du conflit sur Arrakis.",
  "genre": "/api/genres/1",
  "rate": 5
}
```

## Roles et permissions

### `PUBLIC_ACCESS`

- lecture publique du catalogue :
  - `GET /movies`
  - `GET /movies/{id}`
  - `GET /genres`
  - `GET /actors`
  - `GET /directors`
- `POST /login`
- `POST /register`
- `POST /token/refresh`

### `ROLE_USER`

- acces au profil courant
- gestion des favoris
- gestion de l'historique
- notation des contenus

### `ROLE_ADMIN`

- administration du catalogue
- administration des utilisateurs
- consultation des ressources techniques internes exposees pour le back-office

## Guide d'utilisation du frontend

### Navigation principale

- `Accueil`
- `Catalogue`
- `Favoris`
- `Historique`
- `Profil`
- `Admin` pour les comptes admin

### Favoris

- Depuis une carte film ou la fiche detaillee, le bouton coeur appelle le store Pinia utilisateur.
- Le store envoie `POST /users/{id}/favorites/{movieId}`.
- L'etat local est resynchronise ensuite avec `GET /users/{id}/favorites`.

### Profil

- Le formulaire charge `GET /me`
- La sauvegarde utilise `PATCH /me`
- Si l'email change, le backend resynchronise les refresh tokens associes

### Administration

- Les formulaires admin manipulent les films, genres, acteurs, realisateurs et utilisateurs
- Les pages admin sont protegees a la fois par le router frontend et par les regles backend

## Securite

- Les mots de passe ne sont jamais exposes dans les reponses API
- Le JWT d'acces circule dans l'en-tete `Authorization`
- Le refresh token est stocke dans un cookie HTTP-only
- Le logout invalide les refresh tokens du compte
- Les roles sont verifies a la fois dans `security.yaml` et dans les operations API Platform
- Les donnees sont validees cote serveur via les contraintes Symfony Validator
- Le CORS est configure par Nelmio pour les origines locales autorisees

## Verification et maintenance

Commandes utiles :

```bash
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console debug:router
npm run build
```

## Tests

A ce stade, le projet ne contient pas encore de suite PHPUnit ou Vitest dediee.
Les verifications fonctionnelles ont ete faites manuellement sur les flux critiques :

- login / refresh / logout
- pages protegees
- favoris
- protections USER / ADMIN
- create / update / delete film cote admin

