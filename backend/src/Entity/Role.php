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
use App\Dto\RoleOutput;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: [
    'characterFirstName' => 'partial',
    'characterLastName' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'characterFirstName', 'characterLastName'])]
#[ApiResource(
    description: 'Représente un rôle interprété dans un film.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['role:read']],
    denormalizationContext: ['groups' => ['role:write']],
    operations: [
        new Get(
            output: RoleOutput::class,
            description: "Retourne le détail d'un rôle.",
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter un rôle.'
        ),
        new GetCollection(
            output: RoleOutput::class,
            description: 'Retourne la liste des rôles.',
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Vous devez être connecté pour consulter les rôles.'
        ),
        new Post(
            description: 'Crée un nouveau rôle dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer un rôle.'
        ),
        new Put(
            description: 'Remplace complètement un rôle existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un role.'
        ),
        new Patch(
            description: 'Modifie partiellement un role existant.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier un role.'
        ),
        new Delete(
            description: 'Supprime un role du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer un role.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['role:read', 'play:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[ApiProperty(openapiContext: ['example' => 'Neo'])]
    #[Groups(['role:read', 'role:write', 'play:read', 'movie:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $characterFirstName = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[ApiProperty(openapiContext: ['example' => 'Anderson'])]
    #[Map(target: 'characterName', transform: [self::class, 'toCharacterName'])]
    #[Groups(['role:read', 'role:write', 'play:read', 'movie:read'])]
    #[Assert\Length(max: 150)]
    private ?string $characterLastName = null;

    /**
     * @var Collection<int, Play>
     */
    #[ORM\OneToMany(targetEntity: Play::class, mappedBy: 'role')]
    private Collection $plays;

    public function __construct()
    {
        $this->plays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacterFirstName(): ?string
    {
        return $this->characterFirstName;
    }

    public function setCharacterFirstName(string $characterFirstName): static
    {
        $this->characterFirstName = $characterFirstName;

        return $this;
    }

    public function getCharacterLastName(): ?string
    {
        return $this->characterLastName;
    }

    public function setCharacterLastName(?string $characterLastName): static
    {
        $this->characterLastName = $characterLastName;

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
            $play->setRole($this);
        }

        return $this;
    }

    public function removePlay(Play $play): static
    {
        if ($this->plays->removeElement($play)) {
            if ($play->getRole() === $this) {
                $play->setRole(null);
            }
        }

        return $this;
    }

    public static function toCharacterName(?string $characterLastName, object $source): ?string
    {
        if (!$source instanceof self) {
            return null;
        }

        $fullName = trim(sprintf(
            '%s %s',
            $source->getCharacterFirstName() ?? '',
            $characterLastName ?? ''
        ));

        return '' === $fullName ? null : $fullName;
    }
}
