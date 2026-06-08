<?php

namespace App\Dto;

use App\Entity\Person;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Person::class)]
final class PersonOutput
{
    #[Groups(['person:read'])]
    public ?string $firstName = null;

    #[Groups(['person:read'])]
    public ?string $lastName = null;

    #[Groups(['person:read'])]
    public ?string $gender = null;

    #[Groups(['person:read'])]
    public ?\DateTimeImmutable $birthday = null;

    #[Groups(['person:read'])]
    public bool $isActor = false;

    #[Groups(['person:read'])]
    public bool $isDirector = false;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function isActor(): bool
    {
        return $this->isActor;
    }

    public function isDirector(): bool
    {
        return $this->isDirector;
    }
}
