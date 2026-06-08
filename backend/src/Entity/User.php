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
use App\Dto\FilmOutput;
use App\Dto\FavoriteToggleInput;
use App\Dto\FavoriteToggleOutput;
use App\Dto\UserMeOutput;
use App\Dto\UserOutput;
use App\Repository\UserRepository;
use App\State\CurrentUserProvider;
use App\State\FavoriteToggleProcessor;
use App\State\UserFavoritesProvider;
use App\State\UserPasswordHasher;
use App\State\UserWatchHistoryProvider;
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
            output: UserOutput::class,
            description: "Retourne le profil détaillé de l'utilisateur connecté.",
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: 'Vous devez être connecté avec le bon compte pour consulter ce profil.'
        ),
        new Post(
            uriTemplate: '/users',
            output: UserOutput::class,
            description: 'Crée un nouvel utilisateur depuis l’administration.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:create', 'user:write']],
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un utilisateur.'
        ),
        new Put(
            uriTemplate: '/users/{id}',
            output: UserOutput::class,
            description: 'Remplace complètement un utilisateur existant.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:write']],
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un utilisateur.'
        ),
        new Patch(
            uriTemplate: '/users/{id}',
            output: UserOutput::class,
            description: 'Modifie partiellement un utilisateur existant.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:write']],
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un utilisateur.'
        ),
        new Delete(
            uriTemplate: '/users/{id}',
            description: 'Supprime un utilisateur.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un utilisateur.'
        ),
        new Post(
            uriTemplate: '/register',
            description: 'Inscrit un nouvel utilisateur et hache automatiquement son mot de passe.',
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:create']],
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:create']]
        ),
        new Get(
            uriTemplate: '/users/me',
            provider: CurrentUserProvider::class,
            output: UserMeOutput::class,
            normalizationContext: ['groups' => ['user:me:read']],
            description: "Retourne le profil de l'utilisateur authentifié.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter votre profil.'
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/favorites',
            provider: UserFavoritesProvider::class,
            output: FilmOutput::class,
            normalizationContext: ['groups' => ['movie:read']],
            description: "Retourne la liste des films favoris d'un utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter vos favoris.'
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/history',
            provider: UserWatchHistoryProvider::class,
            output: FilmOutput::class,
            normalizationContext: ['groups' => ['movie:read']],
            description: "Retourne l'historique de visionnage d'un utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter votre historique.'
        ),
        new Post(
            uriTemplate: '/users/{id}/favorites',
            input: FavoriteToggleInput::class,
            output: FavoriteToggleOutput::class,
            processor: FavoriteToggleProcessor::class,
            normalizationContext: ['groups' => ['favorite:read', 'movie:read']],
            description: "Ajoute ou retire un film des favoris de l'utilisateur.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour modifier vos favoris.'
        ),
    ]
)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà enregistré.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
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
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\ManyToMany(targetEntity: Film::class, inversedBy: 'likedByUsers')]
    #[ORM\JoinTable(name: 'user_liked_film')]
    #[ApiProperty(description: "Films ajoutés aux favoris de l'utilisateur.")]
    #[Map(target: 'likedFilmTitles', transform: [self::class, 'toFilmTitles'])]
    #[Groups(['user:read'])]
    private Collection $likedFilms;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\ManyToMany(targetEntity: Film::class, inversedBy: 'watchedByUsers')]
    #[ORM\JoinTable(name: 'user_watched_film')]
    #[ApiProperty(description: "Films marqués comme vus par l'utilisateur.")]
    #[Map(target: 'watchedFilmTitles', transform: [self::class, 'toFilmTitles'])]
    #[Groups(['user:read'])]
    private Collection $watchedFilms;

    public function __construct()
    {
        $this->likedFilms = new ArrayCollection();
        $this->watchedFilms = new ArrayCollection();
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

    /**
     * Identifiant visuel représentant cet utilisateur.
     *
     * @see UserInterface
     */
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
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
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

    /**
     * Évite de stocker le vrai hash du mot de passe dans la session.
     */
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
     * @param Collection<int, Film> $films
     *
     * @return list<string>
     */
    public static function toFilmTitles(Collection $films): array
    {
        return $films->map(static fn (Film $film) => $film->getTitle())->toArray();
    }
}
