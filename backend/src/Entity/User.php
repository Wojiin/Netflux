<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\FavoriteToggleOutput;
use App\Dto\FilmRatingOutput;
use App\Dto\FilmListOutput;
use App\Dto\UserFilmRatingOutput;
use App\Dto\UserOutput;
use App\Dto\WatchedFilmOutput;
use App\Repository\UserRepository;
use App\State\CurrentUserProvider;
use App\State\FilmRatingProcessor;
use App\State\FavoriteToggleProcessor;
use App\State\UserFavoritesProvider;
use App\State\UserPasswordHasher;
use App\State\UserRatingsProvider;
use App\State\UserWatchedProvider;
use App\State\WatchedFilmProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    description: "Représente un utilisateur de l'application.",
    normalizationContext: ['groups' => ['user:read']],
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            output: UserOutput::class,
            description: 'Retourne la liste des utilisateurs.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter la liste des utilisateurs.'
        ),
        new Get(
            uriTemplate: '/users/{id}',
            requirements: ['id' => '\d+'],
            output: UserOutput::class,
            description: "Retourne le profil détaillé de l'utilisateur connecté.",
            security: "is_granted('ROLE_ADMIN') or (user and object and object.getId() == user.getId())",
            securityMessage: 'Vous devez être connecté avec le bon compte pour consulter ce profil.'
        ),
        new Get(
            uriTemplate: '/me',
            output: UserOutput::class,
            provider: CurrentUserProvider::class,
            description: "Retourne le profil de l'utilisateur actuellement connecté.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter votre profil.'
        ),
        new Post(
            uriTemplate: '/users',
            output: false,
            description: "Crée un nouvel utilisateur depuis l'administration.",
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:create', 'user:write']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un utilisateur.'
        ),
        new Put(
            uriTemplate: '/users/{id}',
            requirements: ['id' => '\d+'],
            output: false,
            description: 'Remplace complètement un utilisateur existant.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:write']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN') or (user and object and object.getId() == user.getId())",
            securityMessage: 'Seul un administrateur ou le propriétaire du compte peut modifier cet utilisateur.'
        ),
        new Patch(
            uriTemplate: '/users/{id}',
            requirements: ['id' => '\d+'],
            output: false,
            description: 'Modifie partiellement un utilisateur existant.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:write']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN') or (user and object and object.getId() == user.getId())",
            securityMessage: 'Seul un administrateur ou le propriétaire du compte peut modifier cet utilisateur.'
        ),
        new Patch(
            uriTemplate: '/me',
            output: false,
            provider: CurrentUserProvider::class,
            description: "Modifie le profil de l'utilisateur actuellement connecté.",
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:write']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour modifier votre profil.'
        ),
        new Delete(
            uriTemplate: '/users/{id}',
            requirements: ['id' => '\d+'],
            description: 'Supprime un utilisateur.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un utilisateur.'
        ),
        new Post(
            uriTemplate: '/register',
            description: "Inscrit un nouvel utilisateur et hache automatiquement son mot de passe.",
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:create']],
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:create']]
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/favorites',
            requirements: ['id' => '\d+'],
            provider: UserFavoritesProvider::class,
            output: FilmListOutput::class,
            normalizationContext: ['groups' => ['movie:list:read']],
            description: "Retourne la liste des films favoris d'un utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter vos favoris.'
        ),
        new Post(
            uriTemplate: '/users/{id}/favorites/{movieId}',
            requirements: ['id' => '\d+', 'movieId' => '\d+'],
            input: false,
            output: FavoriteToggleOutput::class,
            processor: FavoriteToggleProcessor::class,
            normalizationContext: ['groups' => ['favorite:read', 'movie:read']],
            description: "Ajoute ou retire un film des favoris de l'utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour modifier vos favoris.'
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/watched',
            requirements: ['id' => '\d+'],
            provider: UserWatchedProvider::class,
            output: FilmListOutput::class,
            normalizationContext: ['groups' => ['movie:list:read']],
            description: "Retourne l'historique des films vus d'un utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter votre historique.'
        ),
        new Post(
            uriTemplate: '/users/{id}/watched/{movieId}',
            requirements: ['id' => '\d+', 'movieId' => '\d+'],
            input: false,
            output: WatchedFilmOutput::class,
            processor: WatchedFilmProcessor::class,
            normalizationContext: ['groups' => ['watched:read', 'movie:read']],
            description: "Marque un film comme vu pour l'utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour mettre à jour votre historique.'
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/ratings',
            requirements: ['id' => '\d+'],
            provider: UserRatingsProvider::class,
            output: UserFilmRatingOutput::class,
            normalizationContext: ['groups' => ['rating:list:read']],
            description: "Retourne les notes données par un utilisateur aux films.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter vos notes.'
        ),
        new Post(
            uriTemplate: '/users/{id}/ratings/{movieId}/{rate}',
            requirements: ['id' => '\d+', 'movieId' => '\d+', 'rate' => '[0-5]'],
            input: false,
            output: FilmRatingOutput::class,
            processor: FilmRatingProcessor::class,
            normalizationContext: ['groups' => ['rating:read']],
            description: "Crée ou met à jour la note d'un utilisateur pour un film.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour noter un film.'
        ),
    ]
)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà enregistré.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private const ASSIGNABLE_ROLES = ['ROLE_USER', 'ROLE_ADMIN'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(description: "Identifiant unique de l'utilisateur.")]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[ApiProperty(openapiContext: ['example' => 'newuser@example.com'])]
    #[Groups(['user:read', 'user:create', 'user:write'])]
    #[Assert\NotBlank(message: "L'email est obligatoire.", groups: ['user:create'])]
    #[Assert\Email(message: "L'email doit être valide.", groups: ['user:create'])]
    #[Assert\NotBlank(message: "L'email est obligatoire.", groups: ['user:write'])]
    #[Assert\Email(message: "L'email doit être valide.", groups: ['user:write'])]
    #[Assert\Length(max: 180, groups: ['user:create', 'user:write'])]
    private ?string $email = null;

    /**
     * @var list<string> Rôles attribués à l'utilisateur
     */
    #[ORM\Column]
    #[ApiProperty(description: "Rôles attribués à l'utilisateur.")]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\All(
        constraints: [
            new Assert\Choice(
                choices: self::ASSIGNABLE_ROLES,
                message: 'Le rôle "{{ value }}" n\'est pas autorisé.',
            ),
        ],
        groups: ['user:write']
    )]
    private array $roles = [];

    /**
     * @var string Mot de passe haché
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ApiProperty(
        openapiContext: ['example' => 'motdepasse123'],
        writable: true,
        readable: false
    )]
    #[Groups(['user:create', 'user:write'])]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['user:create'])]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        groups: ['user:create', 'user:write']
    )]
    #[Assert\NotCompromisedPassword(
        message: 'Ce mot de passe est compromis. Merci d en choisir un autre.',
        groups: ['user:create', 'user:write']
    )]
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Film>
     */
    // Relation ManyToMany des favoris : un utilisateur peut aimer plusieurs
    // films et un même film peut apparaître dans les favoris de plusieurs users.
    #[ORM\ManyToMany(targetEntity: Film::class, inversedBy: 'likedByUsers')]
    #[ORM\JoinTable(name: 'user_liked_film')]
    #[ApiProperty(description: "Films ajoutés aux favoris de l'utilisateur.")]
    #[Map(target: 'likedFilmTitles', transform: [self::class, 'toFilmTitles'])]
    #[Groups(['user:read'])]
    private Collection $likedFilms;

    /**
     * @var Collection<int, Film>
     */
    // Historique ManyToMany séparé des favoris pour ne pas mélanger "vu" et
    // "aimé" dans une seule collection métier.
    #[ORM\ManyToMany(targetEntity: Film::class, inversedBy: 'watchedByUsers')]
    #[ORM\JoinTable(name: 'user_watched_film')]
    #[ApiProperty(description: "Films marqués comme vus par l'utilisateur.")]
    #[Map(target: 'watchedFilmTitles', transform: [self::class, 'toFilmTitles'])]
    #[Groups(['user:read'])]
    private Collection $watchedFilms;

    /**
     * @var Collection<int, FilmRating>
     */
    #[ORM\OneToMany(targetEntity: FilmRating::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $filmRatings;

    public function __construct()
    {
        $this->likedFilms = new ArrayCollection();
        $this->watchedFilms = new ArrayCollection();
        $this->filmRatings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // ROLE_USER reste forcé à la lecture pour garantir un socle minimal
        // même si la colonne en base ne contient que ROLE_ADMIN.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        // On filtre ici les rôles acceptés pour éviter qu'un payload API puisse
        // injecter une valeur inattendue dans la colonne JSON.
        $this->roles = array_values(array_unique(array_filter(
            $roles,
            static fn (mixed $role): bool => is_string($role) && in_array($role, self::ASSIGNABLE_ROLES, true),
        )));

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        if (null === $password) {
            return $this;
        }

        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getLikedFilms(): Collection
    {
        return $this->likedFilms;
    }

    public function addLikedFilm(Film $likedFilm): static
    {
        if (!$this->likedFilms->contains($likedFilm)) {
            // Le côté propriétaire de la relation favoris est User::likedFilms :
            // c'est cette collection qui pilote l'écriture de la table pivot.
            $this->likedFilms->add($likedFilm);
        }

        return $this;
    }

    public function removeLikedFilm(Film $likedFilm): static
    {
        $this->likedFilms->removeElement($likedFilm);

        return $this;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getWatchedFilms(): Collection
    {
        return $this->watchedFilms;
    }

    public function addWatchedFilm(Film $watchedFilm): static
    {
        if (!$this->watchedFilms->contains($watchedFilm)) {
            // Même logique pour l'historique : on écrit depuis l'utilisateur
            // pour garder une API cohérente côté session courante.
            $this->watchedFilms->add($watchedFilm);
        }

        return $this;
    }

    public function removeWatchedFilm(Film $watchedFilm): static
    {
        $this->watchedFilms->removeElement($watchedFilm);

        return $this;
    }

    /**
     * @return Collection<int, FilmRating>
     */
    public function getFilmRatings(): Collection
    {
        return $this->filmRatings;
    }

    public function addFilmRating(FilmRating $filmRating): static
    {
        if (!$this->filmRatings->contains($filmRating)) {
            $this->filmRatings->add($filmRating);
            $filmRating->setUser($this);
        }

        return $this;
    }

    public function removeFilmRating(FilmRating $filmRating): static
    {
        if ($this->filmRatings->removeElement($filmRating)) {
            if ($filmRating->getUser() === $this) {
                $filmRating->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param Collection<int, Film> $films
     *
     * @return list<string>
     */
    public static function toFilmTitles(Collection $films): array
    {
        return $films->map(static fn (Film $film) => $film->getTitle())->toArray();
    }
}
