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
use App\Dto\PlayOutput;
use App\Repository\PlayRepository;
use App\State\PlayWriteProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'film.id' => 'exact',
    'actor.id' => 'exact',
    'role.id' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'film.title', 'actor.person.lastName', 'role.characterFirstName'])]
#[ApiResource(
    description: "Représente la participation d'un acteur dans un film pour un rôle donné.",
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['play:read']],
    denormalizationContext: ['groups' => ['play:write']],
    operations: [
        new Get(
            output: PlayOutput::class,
            description: "Retourne le détail d'une participation.",
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter une participation.'
        ),
        new GetCollection(
            output: PlayOutput::class,
            description: 'Retourne la liste des participations.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter les participations.'
        ),
        new Post(
            output: false,
            processor: PlayWriteProcessor::class,
            description: 'Crée une nouvelle participation dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer une participation.'
        ),
        new Put(
            output: false,
            processor: PlayWriteProcessor::class,
            description: 'Remplace complètement une participation existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une participation.'
        ),
        new Patch(
            output: false,
            processor: PlayWriteProcessor::class,
            description: 'Modifie partiellement une participation existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une participation.'
        ),
        new Delete(
            description: 'Supprime une participation du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer une participation.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: PlayRepository::class)]
#[ORM\Table(name: 'play')]
#[ORM\UniqueConstraint(name: 'uniq_play_film_actor_role', columns: ['film_id', 'actor_id', 'role_id'])]
class Play
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['play:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plays')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Film concerné par cette participation.',
        openapiContext: ['example' => '/api/movies/1']
    )]
    #[Groups(['play:read'])]
    private ?Film $film = null;

    #[ORM\ManyToOne(inversedBy: 'plays')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Acteur associé à cette participation.',
        openapiContext: ['example' => '/api/actors/1']
    )]
    #[Map(target: 'actorName', transform: [self::class, 'toActorName'])]
    #[Groups(['play:read', 'movie:read'])]
    private ?Actor $actor = null;

    #[ORM\ManyToOne(inversedBy: 'plays')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: "Rôle joué par l’acteur dans ce film.",
        openapiContext: ['example' => '/api/roles/1']
    )]
    #[Map(target: 'roleName', transform: [self::class, 'toRoleName'])]
    #[Groups(['play:read', 'movie:read'])]
    private ?Role $role = null;

    #[ApiProperty(
        writable: true,
        readable: false,
        openapiContext: ['example' => 1]
    )]
    #[Groups(['play:write'])]
    #[Assert\NotNull(groups: ['Default'], message: 'Le film est obligatoire.')]
    #[Assert\Positive(groups: ['Default'], message: "L'identifiant du film doit être positif.")]
    private ?int $filmId = null;

    #[ApiProperty(
        writable: true,
        readable: false,
        openapiContext: ['example' => 1]
    )]
    #[Groups(['play:write'])]
    #[Assert\NotNull(groups: ['Default'], message: "L'acteur est obligatoire.")]
    #[Assert\Positive(groups: ['Default'], message: "L'identifiant de l'acteur doit être positif.")]
    private ?int $actorId = null;

    #[ApiProperty(
        writable: true,
        readable: false,
        openapiContext: ['example' => 1]
    )]
    #[Groups(['play:write'])]
    #[Assert\NotNull(groups: ['Default'], message: 'Le rôle est obligatoire.')]
    #[Assert\Positive(groups: ['Default'], message: "L'identifiant du rôle doit être positif.")]
    private ?int $roleId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): static
    {
        $this->film = $film;

        return $this;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getFilmId(): ?int
    {
        return $this->filmId;
    }

    public function setFilmId(?int $filmId): static
    {
        $this->filmId = $filmId;

        return $this;
    }

    public function getActorId(): ?int
    {
        return $this->actorId;
    }

    public function setActorId(?int $actorId): static
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function setRoleId(?int $roleId): static
    {
        $this->roleId = $roleId;

        return $this;
    }

    public static function toActorName(?Actor $actor): ?string
    {
        $fullName = trim(sprintf(
            '%s %s',
            $actor?->getPerson()?->getFirstName() ?? '',
            $actor?->getPerson()?->getLastName() ?? ''
        ));

        return '' === $fullName ? null : $fullName;
    }

    public static function toRoleName(?Role $role): ?string
    {
        $fullName = trim(sprintf(
            '%s %s',
            $role?->getCharacterFirstName() ?? '',
            $role?->getCharacterLastName() ?? ''
        ));

        return '' === $fullName ? null : $fullName;
    }
}
