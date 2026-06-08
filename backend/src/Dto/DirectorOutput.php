<?php

namespace App\Dto;

use App\Entity\Director;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Director::class)]
final class DirectorOutput
{
    #[Map(source: 'person.firstName')]
    #[Groups(['director:read'])]
    public ?string $firstName = null;

    #[Map(source: 'person.lastName')]
    #[Groups(['director:read'])]
    public ?string $lastName = null;

    #[Groups(['director:read'])]
    public ?string $fullName = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }
}
