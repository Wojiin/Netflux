<?php

namespace App\Dto;

use App\Entity\Play;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Play::class)]
final class PlayOutput
{
    #[Map(source: 'film.title')]
    #[Groups(['play:read'])]
    public ?string $filmTitle = null;

    #[Groups(['play:read'])]
    public ?string $actorName = null;

    #[Groups(['play:read'])]
    public ?string $roleName = null;

    public function getFilmTitle(): ?string
    {
        return $this->filmTitle;
    }

    public function getActorName(): ?string
    {
        return $this->actorName;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }
}
