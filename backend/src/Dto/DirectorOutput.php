<?php

namespace App\Dto;

use App\Entity\Director;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Director::class)]
final class DirectorOutput
{
    #[Groups(['director:read'])]
    public ?int $id = null;

    #[Map(source: 'person.firstName')]
    #[Groups(['director:read'])]
    public ?string $firstName = null;

    #[Map(source: 'person.lastName')]
    #[Groups(['director:read'])]
    public ?string $lastName = null;

    #[Groups(['director:read'])]
    public ?string $fullName = null;

    #[Map(source: 'person.portraitLink')]
    #[Groups(['director:read'])]
    public ?string $portraitLink = null;

    #[Map(source: 'person.gender')]
    #[Groups(['director:read'])]
    public ?string $gender = null;

    #[Map(source: 'person.birthday')]
    #[Groups(['director:read'])]
    public ?\DateTimeImmutable $birthday = null;

    /**
     * @var list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, type:?string}>
     */
    #[Groups(['director:read'])]
    public array $directedMovies = [];

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
     * @return list<array{filmId:int|null, title:?string, imgLink:?string, releasedAt:?\DateTimeImmutable, type:?string}>
     */
    public function getDirectedMovies(): array
    {
        return $this->directedMovies;
    }
}
