<?php

namespace App\Dto;

use App\Entity\Role;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Role::class)]
final class RoleOutput
{
    #[Groups(['role:read'])]
    public ?int $id = null;

    #[Groups(['role:read'])]
    public ?string $characterFirstName = null;

    #[Groups(['role:read'])]
    public ?string $characterLastName = null;

    #[Groups(['role:read'])]
    public ?string $characterName = null;

    public function getCharacterFirstName(): ?string
    {
        return $this->characterFirstName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacterLastName(): ?string
    {
        return $this->characterLastName;
    }

    public function getCharacterName(): ?string
    {
        return $this->characterName;
    }
}
