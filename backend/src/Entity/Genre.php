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
use App\Dto\GenreOutput;
use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
#[ApiResource(
    description: 'Représente une catégorie de films exposée dans l API.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['genre:read']],
    denormalizationContext: ['groups' => ['genre:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/genres',
            output: GenreOutput::class,
            description: 'Retourne la liste des genres disponibles.',
        ),
        new Get(
            // On garde le GET item sur l'entité pour que les IRI du type
            // /api/genres/1 soient correctement dénormalisées quand un Film est
            // créé ou modifié en JSON-LD.
            description: "Retourne le détail d'un genre.",
        ),
        new Post(
            description: 'Crée un nouveau genre dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un genre.'
        ),
        new Put(
            description: 'Remplace complètement un genre existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un genre.'
        ),
        new Patch(
            description: 'Modifie partiellement un genre existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un genre.'
        ),
        new Delete(
            description: 'Supprime un genre du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un genre.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['genre:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[ApiProperty(openapiContext: ['example' => 'Science-fiction'])]
    #[Groups(['genre:read', 'genre:write', 'movie:read'])]
    #[Assert\NotBlank(message: 'Le nom du genre est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le nom du genre ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $name = null;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'genre')]
    #[Map(target: 'filmTitles', transform: [self::class, 'toFilmTitles'])]
    #[Groups(['genre:read'])]
    private Collection $films;

    public function __construct()
    {
        $this->films = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $film->setGenre($this);
        }

        return $this;
    }

    public function removeFilm(Film $film): static
    {
        if ($this->films->removeElement($film) && $film->getGenre() === $this) {
            $film->setGenre(null);
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
