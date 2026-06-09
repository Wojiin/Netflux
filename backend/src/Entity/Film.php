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
use App\Dto\FilmOutput;
use App\Enum\ContentType;
use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'genres.id' => 'exact',
    'title' => 'partial',
    'synopsis' => 'partial',
])]
#[ApiFilter(BackedEnumFilter::class, properties: ['type'])]
#[ApiFilter(OrderFilter::class, properties: ['title', 'releasedAt', 'rate'])]
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
            output: FilmOutput::class,
            description: 'Retourne une collection paginée de films. La pagination utilise `page` et `itemsPerPage`. Les filtres sont exposés comme dans le cours avec des ApiFilter : recherche partielle sur `title` et `synopsis`, filtre exact sur `genres.id` et `type`, et tri via `order[...]`.',
        ),
        new Get(
            uriTemplate: '/movies/{id}',
            output: FilmOutput::class,
            description: "Retourne la fiche détaillée d'un film.",
        ),
        new Post(
            uriTemplate: '/movies',
            description: 'Cree un nouveau film dans le catalogue.',
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
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(enumType: ContentType::class)]
    #[ApiProperty(description: 'Type de contenu du film.', openapiContext: ['example' => 'film'])]
    #[Map(target: 'type', transform: [self::class, 'toTypeValue'])]
    #[Groups(['movie:read', 'movie:write', 'genre:read', 'user:read'])]
    #[Assert\NotNull]
    private ?ContentType $type = null;

    #[ORM\Column(name: 'released_at', type: Types::DATE_IMMUTABLE)]
    #[ApiProperty(description: 'Date de sortie du film.', openapiContext: ['example' => '2010-07-16'])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $releasedAt = null;

    #[ORM\Column(name: 'img_link', length: 255)]
    #[ApiProperty(
        description: "URL de l'affiche principale du film.",
        openapiContext: ['example' => 'https://image.tmdb.org/t/p/w500/8IB2e4r4oVhHnANbnm7O3Tj6tF8.jpg']
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private ?string $imgLink = null;

    #[ORM\Column]
    #[ApiProperty(description: 'Durée du film en minutes.', openapiContext: ['example' => 148])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Positive]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT)]
    #[ApiProperty(
        description: 'Synopsis du film.',
        openapiContext: ['example' => "Dom Cobb infiltre les rêves de ses cibles pour voler des secrets, jusqu'au jour où une mission d'implantation d'idée met toute son équipe en danger."]
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    #[ApiProperty(description: 'Note du film sur 5.', openapiContext: ['example' => 5])]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\Range(min: 0, max: 5)]
    private ?int $rate = null;

    #[ORM\ManyToOne(inversedBy: 'films')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Réalisateur associé au film.',
        openapiContext: ['example' => '/api/directors/1']
    )]
    #[Map(target: 'directorName', transform: [self::class, 'toDirectorName'])]
    #[Groups(['movie:read', 'movie:write'])]
    private ?Director $director = null;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'film', orphanRemoval: true)]
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
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class, mappedBy: 'films')]
    #[ApiProperty(
        description: 'Genres associés au film.',
        openapiContext: ['example' => ['/api/genres/1', '/api/genres/2']]
    )]
    #[Map(target: 'genreNames', transform: [self::class, 'toGenreNames'])]
    #[Groups(['movie:read', 'movie:write'])]
    private Collection $genres;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'likedFilms')]
    private Collection $likedByUsers;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'watchedFilms')]
    private Collection $watchedByUsers;

    #[ORM\Column(name: 'video_link', length: 255)]
    #[ApiProperty(
        description: 'URL de la bande-annonce du film.',
        openapiContext: ['example' => 'https://www.imdb.com/video/vi2861040665/']
    )]
    #[Groups(['movie:read', 'movie:write'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private ?string $videoLink = null;

    public function __construct()
    {
        $this->plays = new ArrayCollection();
        $this->posters = new ArrayCollection();
        $this->genres = new ArrayCollection();
        $this->likedByUsers = new ArrayCollection();
        $this->watchedByUsers = new ArrayCollection();
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

    public function getDirector(): ?Director
    {
        return $this->director;
    }

    public function setDirector(?Director $director): static
    {
        $this->director = $director;

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
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
            $genre->addFilm($this);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        if ($this->genres->removeElement($genre)) {
            $genre->removeFilm($this);
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

    public static function toTypeValue(?ContentType $type): ?string
    {
        return $type?->value;
    }

    public static function toDirectorName(?Director $director): ?string
    {
        $fullName = trim(sprintf(
            '%s %s',
            $director?->getPerson()?->getFirstName() ?? '',
            $director?->getPerson()?->getLastName() ?? ''
        ));

        return '' === $fullName ? null : $fullName;
    }

    /**
     * @param Collection<int, Genre> $genres
     *
     * @return list<string>
     */
    public static function toGenreNames(Collection $genres): array
    {
        return $genres->map(static fn (Genre $genre) => $genre->getName())->toArray();
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
