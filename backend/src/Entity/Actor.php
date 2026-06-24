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
use App\Dto\ActorOutput;
use App\Dto\PersonProfileInput;
use App\Repository\ActorRepository;
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
    description: 'Représente un acteur exposé dans l’API.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['actor:read']],
    denormalizationContext: ['groups' => ['actor:write']],
    operations: [
        new Get(
            output: ActorOutput::class,
            description: "Retourne le détail d'un acteur.",
        ),
        new GetCollection(
            output: ActorOutput::class,
            description: 'Retourne la liste des acteurs.',
        ),
        new Post(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Crée un nouvel acteur dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un acteur.'
        ),
        new Put(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Remplace complètement un acteur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un acteur.'
        ),
        new Patch(
            input: PersonProfileInput::class,
            processor: PersonProfileWriteProcessor::class,
            description: 'Modifie partiellement un acteur existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un acteur.'
        ),
        new Delete(
            description: 'Supprime un acteur du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un acteur.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: ActorRepository::class)]
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['actor:read', 'play:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'actorProfile', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Personne associée à cet acteur.',
        openapiContext: ['example' => '/api/people/1']
    )]
    #[Assert\DisableAutoMapping]
    #[Map(target: 'fullName', transform: [self::class, 'toFullName'])]
    #[Map(target: 'gender', source: 'person.gender')]
    #[Map(target: 'birthday', source: 'person.birthday')]
    #[Map(target: 'filmography', transform: [self::class, 'toFilmography'])]
    #[Groups(['actor:read', 'play:read', 'movie:read'])]
    private ?Person $person = null;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'actor')]
    private Collection $plays;

    public function __construct()
    {
        $this->plays = new ArrayCollection();
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
            $play->setActor($this);
        }

        return $this;
    }

    public function removePlay(Play $play): static
    {
        if ($this->plays->removeElement($play)) {
            if ($play->getActor() === $this) {
                $play->setActor(null);
            }
        }

        return $this;
    }

    public static function toFullName(?Person $person): ?string
    {
        $fullName = trim(sprintf('%s %s', $person?->getFirstName() ?? '', $person?->getLastName() ?? ''));

        return '' === $fullName ? null : $fullName;
    }

    /**
     * @param Person|null $person
     *
     * @return list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, roleId:int|null, roleName:?string}>
     */
    public static function toFilmography(?Person $person, object $source): array
    {
        if (!$source instanceof self) {
            return [];
        }

        return $source->getPlays()
            ->map(static function (Play $play): array {
                return [
                    'filmId' => $play->getFilm()?->getId(),
                    'title' => $play->getFilm()?->getTitle(),
                    'imgLink' => $play->getFilm()?->getImgLink(),
                    'releasedAt' => $play->getFilm()?->getReleasedAt(),
                    'roleId' => $play->getRole()?->getId(),
                    'roleName' => Play::toRoleName($play->getRole()),
                ];
            })
            ->toArray();
    }
}
