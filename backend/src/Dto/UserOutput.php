<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: User::class)]
final class UserOutput
{
    #[Groups(['user:read'])]
    public ?int $id = null;

    #[Groups(['user:read'])]
    public ?string $email = null;

    /**
     * @var list<string>
     */
    #[Groups(['user:read'])]
    public array $roles = [];

    /**
     * @var list<string>
     */
    #[Groups(['user:read'])]
    public array $likedFilmTitles = [];

    /**
     * @var list<string>
     */
    #[Groups(['user:read'])]
    public array $watchedFilmTitles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return list<string>
     */
    public function getLikedFilmTitles(): array
    {
        return $this->likedFilmTitles;
    }

    /**
     * @return list<string>
     */
    public function getWatchedFilmTitles(): array
    {
        return $this->watchedFilmTitles;
    }
}
