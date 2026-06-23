<?php

namespace App\Dto;

use App\Entity\Person;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Person::class)]
final class PersonOutput
{
    #[Map(source: 'id')]
    #[Groups(['person:read'])]
    public ?int $id = null;

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

    #[Groups(['person:read'])]
    public ?string $portraitLink = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPortraitLink(): ?string
    {
        return $this->portraitLink;
    }
}
