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
use App\Dto\PersonOutput;
use App\Repository\PersonRepository;
use App\State\PersonDeleteProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'firstName' => 'partial',
    'lastName' => 'partial',
    'gender' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'firstName', 'lastName', 'birthday'])]
#[ApiResource(
    description: 'Représente une personne pouvant être acteur ou réalisateur.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['person:read']],
    denormalizationContext: ['groups' => ['person:write']],
    operations: [
        new Get(
            output: PersonOutput::class,
            description: "Retourne le détail d'une personne.",
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter une personne.'
        ),
        new GetCollection(
            output: PersonOutput::class,
            description: 'Retourne la liste des personnes.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter les personnes.'
        ),
        new Post(
            description: 'Crée une nouvelle personne dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer une personne.'
        ),
        new Put(
            description: 'Remplace complètement une personne existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une personne.'
        ),
        new Patch(
            description: 'Modifie partiellement une personne existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une personne.'
        ),
        new Delete(
            processor: PersonDeleteProcessor::class,
            description: 'Supprime une personne du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer une personne.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['person:read', 'director:read', 'actor:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[ApiProperty(openapiContext: ['example' => 'Leonardo'])]
    #[Groups(['person:read', 'person:write', 'director:read', 'actor:read', 'movie:read'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    #[ApiProperty(openapiContext: ['example' => 'DiCaprio'])]
    #[Groups(['person:read', 'person:write', 'director:read', 'actor:read', 'movie:read'])]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $lastName = null;

    #[ORM\Column(length: 50)]
    #[ApiProperty(openapiContext: ['example' => 'male'])]
    #[Groups(['person:read', 'person:write'])]
    #[Assert\NotBlank(message: 'Le genre de la personne est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le genre de la personne ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $gender = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[ApiProperty(openapiContext: ['example' => '1974-11-11'])]
    #[Groups(['person:read', 'person:write'])]
    #[Assert\NotNull(message: 'La date de naissance est obligatoire.')]
    #[Assert\LessThan('today', message: "La date de naissance doit être antérieure à aujourd'hui.")]
    private ?\DateTimeImmutable $birthday = null;

    #[ORM\OneToOne(mappedBy: 'person', cascade: ['persist'])]
    #[Map(target: 'isActor', transform: [self::class, 'hasActorProfile'])]
    private ?Actor $actorProfile = null;

    #[ORM\OneToOne(mappedBy: 'person', cascade: ['persist'])]
    #[Map(target: 'isDirector', transform: [self::class, 'hasDirectorProfile'])]
    private ?Director $directorProfile = null;

    #[ORM\Column(name: 'portrait_link', length: 1024)]
    #[ApiProperty(
        description: 'URL du portrait de la personne.',
        openapiContext: ['example' => 'https://image.tmdb.org/t/p/w500/wo2hJpn04vbtmh0B9utCFdsQhxM.jpg']
    )]
    #[Groups(['person:read', 'person:write', 'director:read', 'actor:read', 'movie:read'])]
    #[Assert\NotBlank(message: 'Le portrait est obligatoire.')]
    #[Assert\Url(message: 'Le portrait doit être une URL valide.')]
    #[Assert\Length(max: 1024, maxMessage: 'Le portrait ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $portraitLink = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeImmutable $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getActorProfile(): ?Actor
    {
        return $this->actorProfile;
    }

    public function setActorProfile(?Actor $actorProfile): static
    {
        if ($actorProfile !== null && $actorProfile->getPerson() !== $this) {
            $actorProfile->setPerson($this);
        }

        $this->actorProfile = $actorProfile;

        return $this;
    }

    public function getDirectorProfile(): ?Director
    {
        return $this->directorProfile;
    }

    public function setDirectorProfile(?Director $directorProfile): static
    {
        if ($directorProfile !== null && $directorProfile->getPerson() !== $this) {
            $directorProfile->setPerson($this);
        }

        $this->directorProfile = $directorProfile;

        return $this;
    }

    public static function hasActorProfile(?Actor $actorProfile): bool
    {
        return null !== $actorProfile;
    }

    public static function hasDirectorProfile(?Director $directorProfile): bool
    {
        return null !== $directorProfile;
    }

    public function getPortraitLink(): ?string
    {
        return $this->portraitLink;
    }

    public function setPortraitLink(string $portraitLink): static
    {
        $this->portraitLink = $portraitLink;

        return $this;
    }
}
