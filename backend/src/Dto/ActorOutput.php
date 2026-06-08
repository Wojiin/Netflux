<?php

namespace App\Dto;

use App\Entity\Actor;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Actor::class)]
final class ActorOutput
{
    #[Map(source: 'person.firstName')]
    #[Groups(['actor:read'])]
    public ?string $firstName = null;

    #[Map(source: 'person.lastName')]
    #[Groups(['actor:read'])]
    public ?string $lastName = null;

    #[Groups(['actor:read'])]
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
