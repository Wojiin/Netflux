<?php

namespace App\Dto;

use App\Entity\Actor;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Actor::class)]
final class ActorOutput
{
    #[Groups(['actor:read'])]
    public ?int $id = null;

    #[Map(source: 'person.firstName')]
    #[Groups(['actor:read'])]
    public ?string $firstName = null;

    #[Map(source: 'person.lastName')]
    #[Groups(['actor:read'])]
    public ?string $lastName = null;

    #[Groups(['actor:read'])]
    public ?string $fullName = null;

    #[Map(source: 'person.portraitLink')]
    #[Groups(['actor:read'])]
    public ?string $portraitLink = null;

    #[Map(source: 'person.gender')]
    #[Groups(['actor:read'])]
    public ?string $gender = null;

    #[Map(source: 'person.birthday')]
    #[Groups(['actor:read'])]
    public ?\DateTimeImmutable $birthday = null;

    /**
     * @var list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, roleId:int|null, roleName:?string}>
     */
    #[Groups(['actor:read'])]
    public array $filmography = [];

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getPortraitLink(): ?string
    {
        return $this->portraitLink;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    /**
     * @return list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, roleId:int|null, roleName:?string}>
     */
    public function getFilmography(): array
    {
        return $this->filmography;
    }
}
