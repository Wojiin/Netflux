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
use App\Repository\DirectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

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
            output: DirectorOutput::class,
            description: "Retourne le détail d'un réalisateur.",
        ),
        new GetCollection(
            output: DirectorOutput::class,
            description: 'Retourne la liste des réalisateurs.',
        ),
        new Post(
            description: 'Crée un nouveau réalisateur dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un réalisateur.'
        ),
        new Put(
            description: 'Remplace complètement un réalisateur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un realisateur.'
        ),
        new Patch(
            description: 'Modifie partiellement un realisateur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un realisateur.'
        ),
        new Delete(
            description: 'Supprime un realisateur du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un realisateur.'
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

    #[ORM\OneToOne(inversedBy: 'directorProfile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Personne associée à ce réalisateur.',
        openapiContext: ['example' => '/api/people/5']
    )]
    #[Map(target: 'fullName', transform: [self::class, 'toFullName'])]
    #[Groups(['director:read', 'director:write', 'movie:read'])]
    private ?Person $person = null;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'director')]
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
            $film->setDirector($this);
        }

        return $this;
    }

    public function removeFilm(Film $film): static
    {
        if ($this->films->removeElement($film)) {
            if ($film->getDirector() === $this) {
                $film->setDirector(null);
            }
        }

        return $this;
    }

    public static function toFullName(?Person $person): ?string
    {
        $fullName = trim(sprintf('%s %s', $person?->getFirstName() ?? '', $person?->getLastName() ?? ''));

        return '' === $fullName ? null : $fullName;
    }
}
