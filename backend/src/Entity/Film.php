<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BackedEnumFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\FilmListOutput;
use App\Dto\FilmOutput;
use App\Enum\ContentType;
use App\Filter\MovieSearchFilter;
use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'genre.id' => 'exact',
    'title' => 'partial',
    'genre.name' => 'partial',
    'synopsis' => 'partial',
])]
#[ApiFilter(MovieSearchFilter::class)]
#[ApiFilter(BackedEnumFilter::class, properties: ['type'])]
#[ApiFilter(OrderFilter::class, properties: ['title', 'releasedAt', 'rate', 'averageRate', 'ratingsCount'])]
#[ApiResource(
    description: "Représente un film exposé dans l'API.",
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['movie:read']],
    denormalizationContext: ['groups' => ['movie:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/movies',
            output: FilmListOutput::class,
            normalizationContext: ['groups' => ['movie:list:read']],
            description: 'Retourne une collection paginée de films. La pagination utilise `page` et `itemsPerPage`.',
        ),
        new Get(
            uriTemplate: '/movies/{id}',
            output: FilmOutput::class,
            description: "Retourne la fiche détaillée d'un film.",
        ),
        new Post(
            uriTemplate: '/movies',
            description: 'Crée un nouveau film dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un film.'
        ),
        new Put(
            uriTemplate: '/movies/{id}',
            description: 'Remplace complètement un film existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un film.'
        ),
        new Patch(
            uriTemplate: '/movies/{id}',
            description: 'Modifie partiellement un film existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un film.'
        ),
        new Delete(
            uriTemplate: '/movies/{id}',
            description: 'Supprime un film du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un film.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(description: 'Identifiant unique du film.')]
    #[Groups(['movie:read', 'genre:read', 'user:read', 'play:read', 'poster:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(description: 'Titre du film.', openapiContext: ['example' => 'Inception'])]
    #[Groups(['movie:read', 'movie:write', 'genre:read', 'user:read'])]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $title = null;

    #[ORM\Column(enumType: ContentType::class)]
    #[ApiProperty(description: 'Type de contenu du film.', openapiContext: ['example' => 'film'])]
    #[Map(target: 'type', transform: [self::class, 'toTypeValue'])]
    #[Groups(['movie:read', 'movie:write', 'genre:read', 'user:read'])]
    #[Assert\NotNull(message: 'Le type est obligatoire.')]
    private ?ContentType $type = null;

    #[ORM\Column(name: 'released_at', type: Types::DATE_IMMUTABLE)]
    #[ApiProperty(description: 'Date de sortie du film.', openapiContext: ['example' => '2010-07-16'])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotNull(message: 'La date de sortie est obligatoire.')]
    private ?\DateTimeImmutable $releasedAt = null;

    #[ORM\Column(name: 'img_link', length: 1024)]
    #[ApiProperty(
        description: "URL de l'affiche principale du film.",
        openapiContext: ['example' => 'https://image.tmdb.org/t/p/w500/8IB2e4r4oVhHnANbnm7O3Tj6tF8.jpg']
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank(message: "L'image principale est obligatoire.")]
    #[Assert\Url(message: "L'image principale doit être une URL valide.")]
    #[Assert\Length(max: 1024, maxMessage: "L'image principale ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $imgLink = null;

    #[ORM\Column]
    #[ApiProperty(description: 'Durée du film en minutes.', openapiContext: ['example' => 148])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Positive(message: 'La durée doit être un entier positif.')]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT)]
    #[ApiProperty(
        description: 'Synopsis du film.',
        openapiContext: ['example' => "Dom Cobb infiltre les rêves de ses cibles pour voler des secrets."]
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank(message: 'Le synopsis est obligatoire.')]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    #[ApiProperty(description: 'Note du film sur 5.', openapiContext: ['example' => 5])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.')]
    private ?int $rate = null;

    #[ORM\Column(name: 'average_rate', type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    #[ApiProperty(description: 'Moyenne des notes utilisateurs sur 5, arrondie au dixième.', openapiContext: ['example' => 4.3])]
    #[Groups(['movie:read'])]
    private ?string $averageRate = null;

    #[ORM\Column(name: 'ratings_count', options: ['default' => 0])]
    #[ApiProperty(description: 'Nombre total de notes utilisateurs attribuées au film.', openapiContext: ['example' => 12])]
    #[Groups(['movie:read'])]
    private int $ratingsCount = 0;

    /**
     * @var Collection<int, Director>
     */
    // Un film peut avoir plusieurs réalisateurs et un réalisateur peut être
    // rattaché à plusieurs films : la relation ManyToMany gère cette table pivot.
    #[ORM\ManyToMany(targetEntity: Director::class, inversedBy: 'films')]
    #[ORM\JoinTable(name: 'film_director')]
    #[ApiProperty(
        description: 'Réalisateurs associés au film.',
        openapiContext: ['example' => ['/api/directors/1', '/api/directors/2']]
    )]
    #[Map(target: 'directorName', transform: [self::class, 'toDirectorLabel'])]
    #[Map(target: 'directorNames', transform: [self::class, 'toDirectorNames'])]
    #[Map(target: 'directorDetails', transform: [self::class, 'toDirectorDetails'])]
    #[Groups(['movie:read', 'movie:write'])]
    private Collection $directors;

    #[ORM\ManyToOne(inversedBy: 'films')]
    #[ORM\JoinColumn(nullable: false)]
    // Le genre reste volontairement en ManyToOne : un film n'a qu'un genre
    // principal dans ce modèle, ce qui simplifie les filtres de catalogue.
    #[ApiProperty(
        description: 'Genre associé au film.',
        openapiContext: ['example' => '/api/genres/1']
    )]
    #[Map(target: 'genreNames', transform: [self::class, 'toGenreNames'])]
    #[Groups(['movie:read', 'movie:write'])]
    private ?Genre $genre = null;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'film', orphanRemoval: true)]
    #[Map(target: 'cast', transform: [self::class, 'toCast'])] 
    #[Groups(['movie:read'])]
    private Collection $plays;

    /**
     * @var Collection<int, Poster>
     */
    #[ORM\OneToMany(targetEntity: Poster::class, mappedBy: 'film', orphanRemoval: true)]
    #[Map(target: 'posterUrls', transform: [self::class, 'toPosterUrls'])]
    #[Groups(['movie:read'])]
    private Collection $posters;

    /**
     * @var Collection<int, User>
     */
    // Côté inverse des favoris : cette collection est lue pour exposer l'état,
    // mais les écritures passent par User::likedFilms.
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'likedFilms')]
    private Collection $likedByUsers;

    /**
     * @var Collection<int, User>
     */
    // Côté inverse de l'historique "vus", synchronisé depuis User::watchedFilms.
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'watchedFilms')]
    private Collection $watchedByUsers;

    /**
     * @var Collection<int, FilmRating>
     */
    #[ORM\OneToMany(targetEntity: FilmRating::class, mappedBy: 'film', orphanRemoval: true)]
    private Collection $filmRatings;

    #[ORM\Column(name: 'video_link', length: 1024)]
    #[ApiProperty(
        description: 'URL de la bande-annonce du film.',
        openapiContext: ['example' => 'https://www.imdb.com/video/vi2861040665/']
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank(message: 'La bande-annonce est obligatoire.')]
    #[Assert\Url(message: 'La bande-annonce doit être une URL valide.')]
    #[Assert\Length(max: 1024, maxMessage: 'La bande-annonce ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $videoLink = null;

    public function __construct()
    {
        $this->plays = new ArrayCollection();
        $this->posters = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->likedByUsers = new ArrayCollection();
        $this->watchedByUsers = new ArrayCollection();
        $this->filmRatings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?ContentType
    {
        return $this->type;
    }

    public function setType(ContentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeImmutable
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(\DateTimeImmutable $releasedAt): static
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function getImgLink(): ?string
    {
        return $this->imgLink;
    }

    public function setImgLink(string $imgLink): static
    {
        $this->imgLink = $imgLink;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getAverageRate(): ?float
    {
        return null === $this->averageRate ? null : (float) $this->averageRate;
    }

    public function setAverageRate(?float $averageRate): static
    {
        $this->averageRate = null === $averageRate ? null : number_format($averageRate, 1, '.', '');

        return $this;
    }

    public function getRatingsCount(): int
    {
        return $this->ratingsCount;
    }

    public function setRatingsCount(int $ratingsCount): static
    {
        $this->ratingsCount = max(0, $ratingsCount);

        return $this;
    }

    /**
     * @return Collection<int, Director>
     */
    public function getDirectors(): Collection
    {
        return $this->directors;
    }

    public function addDirector(Director $director): static
    {
        if (!$this->directors->contains($director)) {
            $this->directors->add($director);
        }

        return $this;
    }

    public function removeDirector(Director $director): static
    {
        $this->directors->removeElement($director);

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @return Collection<int, Play>
     */
    public function getPlays(): Collection
    {
        return $this->plays;
    }

    public function addPlay(Play $play): static
    {
        if (!$this->plays->contains($play)) {
            $this->plays->add($play);
            $play->setFilm($this);
        }

        return $this;
    }

    public function removePlay(Play $play): static
    {
        if ($this->plays->removeElement($play)) {
            if ($play->getFilm() === $this) {
                $play->setFilm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Poster>
     */
    public function getPosters(): Collection
    {
        return $this->posters;
    }

    public function addPoster(Poster $poster): static
    {
        if (!$this->posters->contains($poster)) {
            $this->posters->add($poster);
            $poster->setFilm($this);
        }

        return $this;
    }

    public function removePoster(Poster $poster): static
    {
        if ($this->posters->removeElement($poster)) {
            if ($poster->getFilm() === $this) {
                $poster->setFilm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikedByUsers(): Collection
    {
        return $this->likedByUsers;
    }

    public function addLikedByUser(User $likedByUser): static
    {
        if (!$this->likedByUsers->contains($likedByUser)) {
            $this->likedByUsers->add($likedByUser);
            // On maintient les deux collections en phase en mémoire, même si le
            // vrai côté propriétaire de la table pivot reste User::likedFilms.
            $likedByUser->addLikedFilm($this);
        }

        return $this;
    }

    public function removeLikedByUser(User $likedByUser): static
    {
        if ($this->likedByUsers->removeElement($likedByUser)) {
            $likedByUser->removeLikedFilm($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getWatchedByUsers(): Collection
    {
        return $this->watchedByUsers;
    }

    public function addWatchedByUser(User $watchedByUser): static
    {
        if (!$this->watchedByUsers->contains($watchedByUser)) {
            $this->watchedByUsers->add($watchedByUser);
            // Synchronisation bidirectionnelle équivalente pour l'historique.
            $watchedByUser->addWatchedFilm($this);
        }

        return $this;
    }

    public function removeWatchedByUser(User $watchedByUser): static
    {
        if ($this->watchedByUsers->removeElement($watchedByUser)) {
            $watchedByUser->removeWatchedFilm($this);
        }

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
            $filmRating->setFilm($this);
        }

        return $this;
    }

    public function removeFilmRating(FilmRating $filmRating): static
    {
        if ($this->filmRatings->removeElement($filmRating)) {
            if ($filmRating->getFilm() === $this) {
                $filmRating->setFilm(null);
            }
        }

        return $this;
    }

    public static function toTypeValue(?ContentType $type): ?string
    {
        return $type?->value;
    }

    /**
     * @param Collection<int, Director> $directors
     *
     * @return list<string>
     */
    public static function toDirectorNames(Collection $directors): array
    {
        return $directors
            ->map(static function (Director $director): string {
                return trim(sprintf(
                    '%s %s',
                    $director->getPerson()?->getFirstName() ?? '',
                    $director->getPerson()?->getLastName() ?? ''
                ));
            })
            ->filter(static fn (string $fullName): bool => '' !== $fullName)
            ->toArray();
    }

    /**
     * @param Collection<int, Director> $directors
     */
    public static function toDirectorLabel(Collection $directors): ?string
    {
        $directorNames = self::toDirectorNames($directors);

        return [] === $directorNames ? null : implode(', ', $directorNames);
    }

    /**
     * @return list<string>
     */
    public static function toGenreNames(?Genre $genre): array
    {
        return $genre?->getName() ? [$genre->getName()] : [];
    }

    /**
     * @param Collection<int, Director> $directors
     *
     * @return list<array{id:int|null, fullName:string, portraitLink:?string}>
     */
    public static function toDirectorDetails(Collection $directors): array
    {
        return $directors
            ->map(static function (Director $director): array {
                $fullName = trim(sprintf(
                    '%s %s',
                    $director->getPerson()?->getFirstName() ?? '',
                    $director->getPerson()?->getLastName() ?? ''
                ));

                return [
                    'id' => $director->getId(),
                    'fullName' => $fullName,
                    'portraitLink' => $director->getPerson()?->getPortraitLink(),
                ];
            })
            ->toArray();
    }

    /**
     * @param Collection<int, Play> $plays
     *
     * @return list<array{id:int|null, actorId:int|null, actorName:?string, portraitLink:?string, roleId:int|null, roleName:?string}>
     */
    public static function toCast(Collection $plays): array
    {
        return $plays
            ->map(static function (Play $play): array {
                return [
                    'id' => $play->getId(),
                    'actorId' => $play->getActor()?->getId(),
                    'actorName' => Play::toActorName($play->getActor()),
                    'portraitLink' => $play->getActor()?->getPerson()?->getPortraitLink(),
                    'roleId' => $play->getRole()?->getId(),
                    'roleName' => Play::toRoleName($play->getRole()),
                ];
            })
            ->toArray();
    }

    /**
     * @param Collection<int, Poster> $posters
     *
     * @return list<string>
     */
    public static function toPosterUrls(Collection $posters): array
    {
        return $posters->map(static fn (Poster $poster) => $poster->getUrl())->toArray();
    }

    public function getVideoLink(): ?string
    {
        return $this->videoLink;
    }

    public function setVideoLink(string $videoLink): static
    {
        $this->videoLink = $videoLink;

        return $this;
    }
}
