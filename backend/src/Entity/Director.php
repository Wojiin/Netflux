<?php

namespace App\Entity;

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
use App\Dto\DirectorOutput;
use App\Dto\PersonProfileInput;
use App\Repository\DirectorRepository;
use App\State\PersonProfileWriteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'person.firstName' => 'partial',
    'person.lastName' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'person.lastName', 'person.firstName'])]
#[ApiResource(
    description: 'Représente un réalisateur exposé dans l’API.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['director:read']],
    denormalizationContext: ['groups' => ['director:write']],
    operations: [
        new Get(
            // GET item laisse API Platform hydrater une vraie entité Director
            // depuis son IRI lorsqu'un Film référence /api/directors/{id}.
            description: "Retourne le détail d'un réalisateur.",
        ),
        new GetCollection(
            output: DirectorOutput::class,
            description: 'Retourne la liste des réalisateurs.',
        ),
        new Post(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Crée un nouveau réalisateur dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un réalisateur.'
        ),
        new Put(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Remplace complètement un réalisateur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un réalisateur.'
        ),
        new Patch(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Modifie partiellement un réalisateur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un réalisateur.'
        ),
        new Delete(
            description: 'Supprime un réalisateur du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un réalisateur.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: DirectorRepository::class)]
class Director
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['director:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'directorProfile', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Personne associée à ce réalisateur.',
        openapiContext: ['example' => '/api/people/5']
    )]
    #[Assert\DisableAutoMapping]
    #[Map(target: 'fullName', transform: [self::class, 'toFullName'])]
    #[Map(target: 'gender', source: 'person.gender')]
    #[Map(target: 'birthday', source: 'person.birthday')]
    #[Groups(['director:read', 'movie:read'])]
    private ?Person $person = null;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\ManyToMany(targetEntity: Film::class, mappedBy: 'directors')]
    #[Map(target: 'directedMovies', transform: [self::class, 'toDirectedMovies'])]
    private Collection $films;

    public function __construct()
    {
        $this->films = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(Film $film): static
    {
        if (!$this->films->contains($film)) {
            $this->films->add($film);
            $film->addDirector($this);
        }

        return $this;
    }

    public function removeFilm(Film $film): static
    {
        if ($this->films->removeElement($film)) {
            $film->removeDirector($this);
        }

        return $this;
    }

    public static function toFullName(?Person $person): ?string
    {
        $fullName = trim(sprintf('%s %s', $person?->getFirstName() ?? '', $person?->getLastName() ?? ''));

        return '' === $fullName ? null : $fullName;
    }

    /**
     * @param Collection<int, Film> $films
     *
     * @return list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, type:?string}>
     */
    public static function toDirectedMovies(Collection $films): array
    {
        return $films
            ->map(static function (Film $film): array {
                return [
                    'filmId' => $film->getId(),
                    'title' => $film->getTitle(),
                    'imgLink' => $film->getImgLink(),
                    'releasedAt' => $film->getReleasedAt(),
                    'type' => Film::toTypeValue($film->getType()),
                ];
            })
            ->toArray();
    }
}
